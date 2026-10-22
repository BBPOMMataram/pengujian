<?php

namespace App\Imports;

use App\Models\PengujianItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;

class PengujianImport implements ToCollection
{
    protected string $batchId;
    protected string $filename;

    public function __construct(string $batchId, string $filename)
    {
        $this->batchId = $batchId;
        $this->filename = $filename;
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            return;
        }

        // 1. Bersihkan baris mentah dari Excel: trim sel, konversi null, hapus sel kosong di ujung kanan
        $cleanedRows = [];
        $maxCols = 0;

        foreach ($rows as $r) {
            $rowArray = is_array($r) ? $r : $r->toArray();
            $cells = [];
            foreach ($rowArray as $c) {
                $val = is_null($c) ? '' : trim((string)$c);
                $cells[] = $val;
            }

            // Hapus trailing empty cells
            while (!empty($cells) && end($cells) === '') {
                array_pop($cells);
            }

            $filledCount = count(array_filter($cells, fn($v) => $v !== ''));
            if ($filledCount > $maxCols) {
                $maxCols = $filledCount;
            }

            $cleanedRows[] = [
                'cells' => $cells,
                'filled' => $filledCount,
            ];
        }

        if (empty($cleanedRows) || $maxCols === 0) {
            return;
        }

        $totalRows = count($cleanedRows);
        $startIndex = 0;

        // 2. Lewati baris awal yang kosong atau berupa judul/kop dokumen (single-cell title banner)
        while ($startIndex < $totalRows) {
            $row = $cleanedRows[$startIndex];
            $filled = $row['filled'];

            // Jika baris benar-benar kosong
            if ($filled === 0) {
                $startIndex++;
                continue;
            }

            // Jika baris hanya memiliki 1 atau 2 sel terisi pada tabel lebar (kop/judul seperti "LAPORAN...", "BALAI BESAR POM...")
            if ($maxCols >= 3 && $filled <= 2) {
                $cellText = strtolower(implode(' ', $row['cells']));
                $isTitle = $this->isTitleBanner($cellText);

                $hasWiderRowsAhead = false;
                for ($k = $startIndex + 1; $k < min($startIndex + 6, $totalRows); $k++) {
                    if ($cleanedRows[$k]['filled'] >= 3) {
                        $hasWiderRowsAhead = true;
                        break;
                    }
                }

                if ($isTitle || $hasWiderRowsAhead) {
                    $startIndex++;
                    continue;
                }
            }

            break;
        }

        if ($startIndex >= $totalRows) {
            return;
        }

        // 3. Deteksi apakah baris pertama yang ditemukan adalah header
        $firstRowCells = $cleanedRows[$startIndex]['cells'];
        $isHeader = $this->isHeaderRow($firstRowCells);

        $headerRows = [];
        $dataStartIndex = $startIndex;

        if ($isHeader) {
            $headerRows[] = $firstRowCells;
            $dataStartIndex = $startIndex + 1;

            // 4. Deteksi apakah ada sub-header (merged header baris 2) atau baris penomoran kolom (1, 2, 3...)
            while ($dataStartIndex < $totalRows) {
                $nextCells = $cleanedRows[$dataStartIndex]['cells'];
                $nextFilled = $cleanedRows[$dataStartIndex]['filled'];

                if ($nextFilled === 0) {
                    $dataStartIndex++;
                    continue;
                }

                // Baris penomoran kolom seperti (1), (2), (3) -> lewati
                if ($this->isColumnIndexRow($nextCells)) {
                    $dataStartIndex++;
                    continue;
                }

                // Cek apakah baris ini adalah sub-header (maksimal 2 baris header)
                if (count($headerRows) < 2 && $this->isSubHeaderRow($nextCells, $headerRows[0])) {
                    $headerRows[] = $nextCells;
                    $dataStartIndex++;
                    continue;
                }

                // Baris penomoran kolom setelah sub-header -> lewati
                if ($this->isColumnIndexRow($nextCells)) {
                    $dataStartIndex++;
                    continue;
                }

                // Jika bukan sub-header dan bukan penomoran kolom, berarti ini baris data riil!
                break;
            }

            // Gabungkan header bertingkat / merge headers menjadi nama kolom yang rapi
            $headers = $this->buildMergedHeaders($headerRows, $maxCols);
        } else {
            // Jika tidak ada header (langsung data 2 kolom atau multi kolom)
            $headers = [];
            $colCount = max(2, $maxCols);
            for ($i = 0; $i < $colCount; $i++) {
                if ($i === 0) {
                    $headers[$i] = 'Informasi Data';
                } elseif ($i === 1) {
                    $headers[$i] = 'QR Code';
                } else {
                    $headers[$i] = 'Kolom ' . ($i + 1);
                }
            }
            $dataStartIndex = $startIndex;
        }

        // 5. Ekstraksi baris data dan simpan ke database
        $now = now();
        $records = [];
        $rowNum = 1;

        for ($i = $dataStartIndex; $i < $totalRows; $i++) {
            $cells = $cleanedRows[$i]['cells'];
            $filled = $cleanedRows[$i]['filled'];

            if ($filled === 0) {
                continue;
            }

            // Lewati baris footer / tanda tangan / catatan di bawah tabel
            if ($this->isFooterRow($cells, $maxCols)) {
                continue;
            }

            // Lewati jika ada baris penomoran kolom yang terselip
            if ($this->isColumnIndexRow($cells)) {
                continue;
            }

            $mappedData = [];
            $dataColumnValue = null;

            foreach ($headers as $colIdx => $headerName) {
                $val = isset($cells[$colIdx]) ? trim((string)$cells[$colIdx]) : '';

                // Kolom QR Code
                if (str_contains(strtolower($headerName), 'qr')) {
                    $mappedData[$headerName] = '(Otomatis Di-generate)';
                    continue;
                }

                $mappedData[$headerName] = $val;

                // Tentukan data utama dari kolom pertama yang bukan kolom nomor urut
                $hLower = strtolower($headerName);
                $isNoCol = in_array($hLower, ['no', 'nomor', 'no.']);
                if ($dataColumnValue === null && $val !== '' && !$isNoCol) {
                    $dataColumnValue = $val;
                }
            }

            // Fallback jika belum ketemu
            if ($dataColumnValue === null) {
                foreach ($mappedData as $k => $v) {
                    if (!str_contains(strtolower($k), 'qr') && $v !== '') {
                        $dataColumnValue = $v;
                        break;
                    }
                }
            }

            // QR Code memuat isi dari data kolom di sebelahnya
            $qrContent = $dataColumnValue ?: ('SMP-' . str_pad((string)$rowNum, 4, '0', STR_PAD_LEFT));

            $records[] = [
                'batch_id' => $this->batchId,
                'filename' => $this->filename,
                'row_number' => $rowNum++,
                'qr_token' => Str::random(32),
                'qr_content' => $qrContent,
                'data' => json_encode($mappedData),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($records)) {
            foreach (array_chunk($records, 200) as $chunk) {
                PengujianItem::insert($chunk);
            }
        }
    }

    /**
     * Deteksi apakah baris adalah kop / judul dokumen di atas tabel.
     */
    protected function isTitleBanner(string $text): bool
    {
        $keywords = [
            'laporan', 'rekapitulasi', 'daftar', 'lampiran', 'balai', 'bbpom',
            'kementerian', 'badan pengawas', 'formulir', 'berita acara', 'hasil pengujian',
            'pemeriksaan', 'evaluasi', 'tahun', 'periode', 'upt'
        ];
        foreach ($keywords as $kw) {
            if (str_contains($text, $kw)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Deteksi apakah baris adalah header tabel.
     */
    protected function isHeaderRow(array $cells): bool
    {
        $headerKeywords = [
            'no', 'nomor', 'no.', 'kode', 'kode sampel', 'code', 'sample code',
            'nama', 'nama sampel', 'nama produk', 'nama barang', 'name', 'sample', 'sampel',
            'data', 'data sampel', 'informasi', 'info', 'qr', 'qr code', 'qrcode',
            'hasil', 'hasil uji', 'hasil pengujian', 'result',
            'status', 'kesimpulan', 'keterangan', 'satuan', 'unit',
            'parameter', 'parameter uji', 'kategori', 'jenis',
            'analis', 'penguji', 'penyelia', 'metode', 'metode uji',
            'spesifikasi', 'standar', 'standar acuan', 'syarat', 'persyaratan',
            'kemasan', 'izin', 'izin edar', 'registrasi', 'nie', 'no registrasi',
            'no bets', 'no batch', 'nomor batch',
            'exp', 'expired', 'kadaluarsa', 'tgl', 'tanggal',
            'bentuk', 'sediaan', 'target', 'realisasi', 'volume', 'alokasi'
        ];

        // Indikator bentuk sediaan dan dosis obat yang menandakan DATA, bukan header
        $dataPatterns = [
            '/\b(mg|ml|mcg|iu|ppm|gr|kg|g|l|tablet|tab|kaplet|kapsul|sirup|syr|suspensi|emulsi|salep|krim|cream|gel|lotion|tetes|drop|injeksi|inj|infus|vial|ampul|botol|blister|strip|sachet)\b/i',
            '/\b(batch|bets)\s*[:#]?\s*[a-z0-9_-]+/i',
            '/\b(exp|ed)\s*[:#]?\s*\d{2}/i',
        ];

        $matches = 0;
        $textCells = 0;
        $numericCount = 0;
        $dataIndicatorCount = 0;

        foreach ($cells as $c) {
            $cTrim = trim((string)$c);
            if ($cTrim === '') continue;
            $textCells++;

            if (is_numeric($cTrim) || preg_match('/^\d+([.,]\d+)?\s*%?$/', $cTrim)) {
                $numericCount++;
                continue;
            }

            foreach ($dataPatterns as $pat) {
                if (preg_match($pat, $cTrim)) {
                    $dataIndicatorCount++;
                    break;
                }
            }

            $cLower = strtolower($cTrim);

            foreach ($headerKeywords as $kw) {
                if ($cLower === $kw) {
                    $matches += 2;
                    break;
                } elseif (preg_match('/^' . preg_quote($kw, '/') . '\b/i', $cLower) || preg_match('/\b' . preg_quote($kw, '/') . '$/i', $cLower)) {
                    $matches += 1;
                    break;
                }
            }
        }

        if ($textCells === 0) return false;

        // Jika terdapat nama obat / dosis / satuan, ini adalah baris data
        if ($dataIndicatorCount > 0) {
            return false;
        }

        // Jika lebih dari 35% sel bernilai numerik, ini baris data
        if ($numericCount / $textCells > 0.35) {
            return false;
        }

        // Untuk format 1-2 kolom
        if ($textCells <= 2) {
            if ($matches >= 2) return true;
            if ($matches >= 1 && strlen($cells[0] ?? '') <= 35) return true;
            return false;
        }

        // Untuk format multi-kolom
        return $matches >= 2;
    }

    /**
     * Deteksi apakah baris adalah sub-header (header baris kedua pada sel ter-merge).
     */
    protected function isSubHeaderRow(array $cells, array $parentHeaderCells): bool
    {
        $subKeywords = [
            'min', 'max', 'maks', 'minimum', 'maksimum', 'batas', 'toleransi',
            'kadar', 'satuan', 'unit', 'rujukan', 'persyaratan', 'syarat',
            'kualitatif', 'kuantitatif', 'keterangan', 'nama', 'kode', 'nomor', 'no',
            'jenis', 'tanggal', 'status', 'hasil', 'standar', 'acuan', 'spesifikasi',
            'metode', 'angka', 'nominal', 'target', 'realisasi', 'volume', 'rupiah',
            'fisik', 'kimia', 'mikro', 'mikrobiologi', 'residu', 'uji', 'mutu',
            'wadah', 'tutup', 'penandaan', 'organoleptik'
        ];

        // Jika sel pertama berisi nomor urut (1, 2, 3...) atau kode sampel (SMP-..., BBPOM/...), ini PASTI baris data
        $firstCell = isset($cells[0]) ? trim((string)$cells[0]) : '';
        if ($firstCell !== '') {
            if (preg_match('/^\d+$/', $firstCell) || preg_match('/^(smp|bbpom|lab|bpom)[-\/]/i', $firstCell)) {
                return false;
            }
        }

        $numericCount = 0;
        $matches = 0;
        $nonEmpty = 0;

        foreach ($cells as $idx => $cell) {
            $cTrim = trim((string)$cell);
            if ($cTrim === '') continue;
            $nonEmpty++;

            if (is_numeric($cTrim) || preg_match('/^\d+([.,]\d+)?\s*%?$/', $cTrim)) {
                $numericCount++;
                continue;
            }

            $cLower = strtolower($cTrim);
            foreach ($subKeywords as $kw) {
                if ($cLower === $kw || preg_match('/\b' . preg_quote($kw, '/') . '\b/i', $cLower)) {
                    $matches++;
                    break;
                }
            }
        }

        if ($nonEmpty === 0) return false;

        // Jika baris berisi 2 atau lebih nilai angka hasil uji (misal 0.5, 2.0, 99.5%), ini DATA!
        if ($numericCount >= 2) {
            return false;
        }

        // Jika sel pertama kosong (akibat merge vertikal dengan 'No') dan memiliki keyword sub-header
        $isFirstCellEmpty = ($firstCell === '');
        if ($isFirstCellEmpty && $matches >= 1) {
            return true;
        }

        if ($matches / $nonEmpty >= 0.5) {
            return true;
        }

        return false;
    }

    /**
     * Deteksi apakah baris adalah baris penomoran kolom seperti (1), (2), (3) atau [1], [2], [3].
     */
    protected function isColumnIndexRow(array $cells): bool
    {
        $nonEmpty = array_filter($cells, fn($v) => trim((string)$v) !== '');
        if (empty($nonEmpty)) return false;

        $matches = 0;
        foreach ($nonEmpty as $val) {
            $v = trim((string)$val);
            if (preg_match('/^\(?\[?(\d{1,2})\)?\]?$/', $v)) {
                $matches++;
            }
        }

        return ($matches / count($nonEmpty)) >= 0.7;
    }

    /**
     * Deteksi baris footer, tanda tangan, atau catatan di bawah tabel.
     */
    protected function isFooterRow(array $cells, int $maxCols): bool
    {
        $nonEmpty = array_filter($cells, fn($v) => trim((string)$v) !== '');
        $count = count($nonEmpty);
        if ($count === 0) return false;

        $text = strtolower(implode(' ', $nonEmpty));

        $footerKeywords = [
            'mengetahui', 'kepala balai', 'nip.', 'nip ', 'penyelia',
            'analis penguji', 'catatan:', 'catatan :', 'keterangan tambahan',
            'ditetapkan di', 'total:', 'jumlah total'
        ];

        foreach ($footerKeywords as $kw) {
            if (str_contains($text, $kw)) {
                return true;
            }
        }

        if ($maxCols >= 3 && $count <= 2) {
            $firstCell = isset($cells[0]) ? trim((string)$cells[0]) : '';
            $secondCell = isset($cells[1]) ? trim((string)$cells[1]) : '';
            if ($firstCell === '' && $secondCell === '') {
                return true;
            }
        }

        return false;
    }

    /**
     * Gabungkan baris header ganda / merged header menjadi nama kolom yang rapi.
     */
    protected function buildMergedHeaders(array $headerRows, int $maxCols): array
    {
        if (count($headerRows) === 1) {
            $headers = [];
            $row = $headerRows[0];
            $total = max(count($row), $maxCols);
            for ($i = 0; $i < $total; $i++) {
                $val = isset($row[$i]) ? trim((string)$row[$i]) : '';
                if ($val === '') {
                    $val = ($i === 1) ? 'QR Code' : ('Kolom ' . ($i + 1));
                }
                $headers[$i] = $val;
            }
            return $headers;
        }

        // Multiple header rows (merged row 1 + row 2)
        $row1 = $headerRows[0];
        $row2 = $headerRows[1];
        $totalCols = max(count($row1), count($row2), $maxCols);

        $headers = [];
        $currentParent = '';

        for ($i = 0; $i < $totalCols; $i++) {
            $cell1 = isset($row1[$i]) ? trim((string)$row1[$i]) : '';
            $cell2 = isset($row2[$i]) ? trim((string)$row2[$i]) : '';

            if ($cell1 !== '') {
                $currentParent = $cell1;
            }

            if ($cell1 !== '' && $cell2 === '') {
                $headers[$i] = $cell1;
            } elseif ($cell1 === '' && $cell2 !== '') {
                if ($currentParent !== '' && strtolower($currentParent) !== strtolower($cell2)) {
                    $headers[$i] = $currentParent . ' - ' . $cell2;
                } else {
                    $headers[$i] = $cell2;
                }
            } elseif ($cell1 !== '' && $cell2 !== '') {
                if (strtolower($cell1) === strtolower($cell2)) {
                    $headers[$i] = $cell1;
                } else {
                    $headers[$i] = $cell1 . ' - ' . $cell2;
                }
            } else {
                if ($currentParent !== '') {
                    $headers[$i] = $currentParent . ' (' . ($i + 1) . ')';
                } else {
                    $headers[$i] = ($i === 1) ? 'QR Code' : ('Kolom ' . ($i + 1));
                }
            }
        }

        foreach ($headers as $idx => $h) {
            $h = preg_replace('/\s+/', ' ', trim($h));
            $headers[$idx] = $h;
        }

        return $headers;
    }
}
