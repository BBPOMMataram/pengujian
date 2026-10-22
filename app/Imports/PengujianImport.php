<?php

namespace App\Imports;

use App\Models\PengujianItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\HasReferencesToOtherSheets;

class PengujianImport implements ToCollection, WithCalculatedFormulas, HasReferencesToOtherSheets
{
    protected string $batchId;
    protected string $filename;
    protected string $sheetName;
    protected string $fileGroupId;

    public function __construct(string $batchId, string $filename, ?string $sheetName = null, ?string $fileGroupId = null)
    {
        $this->batchId = $batchId;
        $this->filename = $filename;
        $this->sheetName = $sheetName ?: 'Sheet1';
        $this->fileGroupId = $fileGroupId ?: $batchId;
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

        // 5. Potong baris kosong di akhir data (trailing empty rows elimination)
        // Cari baris terakhir yang benar-benar memuat data dari arah bawah
        $dataEndIndex = -1;
        for ($i = $totalRows - 1; $i >= $dataStartIndex; $i--) {
            $cells = $cleanedRows[$i]['cells'];
            $filled = $cleanedRows[$i]['filled'];

            if ($filled === 0) {
                continue;
            }

            if ($this->isFooterRow($cells, $maxCols)) {
                continue;
            }

            if ($this->isColumnIndexRow($cells)) {
                continue;
            }

            // Cek apakah semua sel hanya tanda strip / spasi
            $allEmptyOrDashes = true;
            foreach ($cells as $c) {
                $cT = trim((string)$c);
                if ($cT !== '' && $cT !== '-' && $cT !== '–') {
                    $allEmptyOrDashes = false;
                    break;
                }
            }
            if ($allEmptyOrDashes) {
                continue;
            }

            // Ditemukan baris data riil paling bawah
            $dataEndIndex = $i;
            break;
        }

        if ($dataEndIndex < $dataStartIndex) {
            return; // Tidak ada data riil di sheet ini
        }

        // 6. Ekstraksi baris data dan simpan ke database (antara dataStartIndex sampai dataEndIndex saja)
        $now = now();
        $records = [];
        $rowNum = 1;

        for ($i = $dataStartIndex; $i <= $dataEndIndex; $i++) {
            $cells = $cleanedRows[$i]['cells'];
            $filled = $cleanedRows[$i]['filled'];

            if ($filled === 0) {
                continue;
            }

            // Lewati baris footer / tanda tangan jika masih ada di tengah
            if ($this->isFooterRow($cells, $maxCols)) {
                continue;
            }

            // Lewati jika ada baris penomoran kolom
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

            // Bangun isi QR Code yang rapi memuat Kode Sampel, Nama Produk, dan Jenis/Komoditi jika tersedia
            $kodeVal = null;
            $namaVal = null;
            $jenisVal = null;
            $paramVal = null;

            foreach ($mappedData as $k => $v) {
                if (str_contains(strtolower($k), 'qr') || $v === '' || $v === '-') continue;
                $kL = strtolower($k);

                if ($kodeVal === null && (str_contains($kL, 'kode') || str_contains($kL, 'code') || $kL === 'no sampel' || $kL === 'no. sampel')) {
                    $kodeVal = $v;
                } elseif ($namaVal === null && (str_contains($kL, 'nama') || str_contains($kL, 'produk') || str_contains($kL, 'sampel') || str_contains($kL, 'barang') || str_contains($kL, 'item'))) {
                    $namaVal = $v;
                } elseif ($jenisVal === null && (str_contains($kL, 'jenis') || str_contains($kL, 'komoditi') || str_contains($kL, 'kategori') || str_contains($kL, 'sediaan'))) {
                    $jenisVal = $v;
                } elseif ($paramVal === null && (str_contains($kL, 'parameter') || str_contains($kL, 'uji') || str_contains($kL, 'metode'))) {
                    $paramVal = $v;
                }
            }

            // Jika ada beberapa atribut penting (multi-kolom), susun baris per baris yang rapi
            $qrLines = [];
            if ($kodeVal) $qrLines[] = "Kode Sampel: " . $kodeVal;
            if ($namaVal) $qrLines[] = "Nama Produk: " . $namaVal;
            if ($jenisVal) $qrLines[] = "Jenis / Komoditi: " . $jenisVal;
            if ($paramVal && count($qrLines) < 4) $qrLines[] = "Parameter: " . $paramVal;

            if (!empty($qrLines)) {
                $qrContent = implode("\n", $qrLines);
            } else {
                // Untuk format minimal 2-kolom (1 kolom data & 1 kolom kosong)
                $qrContent = $dataColumnValue ?: ('SMP-' . str_pad((string)$rowNum, 4, '0', STR_PAD_LEFT));
            }

            $records[] = [
                'batch_id' => $this->batchId,
                'file_group_id' => $this->fileGroupId,
                'filename' => $this->filename,
                'sheet_name' => $this->sheetName,
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
            '/\b\d+\s*(mg|ml|mcg|iu|ppm|gr|kg|g|l)\b/i',
            '/\b(tablet|tab\.|kaplet|kapsul|sirup|syr|suspensi|emulsi|salep|krim|cream|gel|lotion|injeksi|inj|infus|vial|ampul)\b/i',
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

        // Jika terdapat nama obat / dosis kuat dan tidak ada header keywords yang signifikan
        if ($dataIndicatorCount > 0 && $matches < 2) {
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
