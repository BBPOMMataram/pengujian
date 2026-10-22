<?php

namespace App\Services;

use App\Models\PengujianItem;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser;

class PdfImportService
{
    protected Parser $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * Import a PDF file and save extracted records to database.
     *
     * @param string $filePath Absolute path to PDF file
     * @param string $batchId Batch / File group identifier
     * @param string $filename Original uploaded filename
     * @return int Number of records imported
     */
    public function import(string $filePath, string $batchId, string $filename): int
    {
        $pdf = $this->parser->parseFile($filePath);
        $fullText = $pdf->getText();
        $pages = $pdf->getPages();

        $records = [];
        $isSpuDocument = $this->isSpuDocument($fullText);

        if ($isSpuDocument) {
            $records = $this->extractSpuDocument($fullText, $pages, $batchId, $filename);
        }

        // If not SPU or no records found via SPU parser, fallback to generic table parser
        if (empty($records)) {
            $records = $this->extractGenericTable($fullText, $pages, $batchId, $filename);
        }

        if (empty($records)) {
            throw new \Exception("Tidak dapat menemukan data tabel atau sampel yang valid di dalam dokumen PDF \"{$filename}\". Pastikan file PDF memuat teks tabel atau dokumen Surat Permintaan Uji (SPU).");
        }

        // Insert records in chunks
        foreach (array_chunk($records, 100) as $chunk) {
            PengujianItem::insert($chunk);
        }

        return count($records);
    }

    /**
     * Check if the document matches BBPOM Surat Permintaan Uji / Lampiran Sampel format.
     */
    protected function isSpuDocument(string $text): bool
    {
        $keywords = [
            'surat permintaan uji',
            'daftar lampiran sampel',
            'daftar sampel',
            'label kode sampel',
            'kode sampel',
            'spu.'
        ];

        $lower = strtolower($text);
        foreach ($keywords as $kw) {
            if (str_contains($lower, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract structured samples from BBPOM SPU / Permintaan Uji document.
     */
    protected function extractSpuDocument(string $fullText, array $pages, string $batchId, string $filename): array
    {
        $samples = [];
        $now = now();

        // 1. Ekstraksi dari Tabel Halaman 1 (Tabel Surat Permintaan Uji)
        $spuPattern = '/(?:^|\n)\s*(\d+)\s+Kode Sampel[:\s]*\n?([0-9A-Za-z\.\-\/]+)(.*?)(?=(?:\n\s*\d+\s+Kode Sampel[:\s]*)|\nKeterangan Jumlah Sampel|\nDAFTAR LAMPIRAN SAMPEL|$)/s';
        
        if (preg_match_all($spuPattern, $fullText, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $rowNo = (int) $m[1];
                $code = trim($m[2]);
                $body = $m[3];

                // Validasi bahwa kode sampel harus memuat angka dan bukan kata header
                if (!preg_match('/[0-9]/', $code) || in_array(strtolower($code), ['identitas', 'nomor', 'sample', 'sampel', 'manual', 'no', 'keterangan'])) {
                    continue;
                }

                $namaProduk = '';
                if (preg_match('/Nama Produk[:\s]*([^•\n]+(?:\n[ \t]*[^•\n]+)*)/i', $body, $pn)) {
                    $rawNama = trim($pn[1]);
                    // Potong jika ada label lain yang terbawa
                    $rawNama = preg_split('/(•|\bNIE\b|\bMerk\b|\bNarkotika\b|\bKomposisi\b|\bED\b|\bBets\b|\bPabrik\b)/i', $rawNama)[0];
                    $namaProduk = preg_replace('/\s+/', ' ', trim($rawNama));
                }

                $keteranganSampling = '';
                if (preg_match('/(Plastik|Kardus|Botol|Aluminium|Kemasan|Amplop|Klip)[^•\n]+(?:\n[ \t]*[^•\n]+)*/i', $body, $sm)) {
                    $rawSm = trim($sm[0]);
                    $rawSm = preg_split('/(•|\bTempat\b|\bTanggal\b|\bPengirim\b)/i', $rawSm)[0];
                    $keteranganSampling = preg_replace('/\s+/', ' ', trim($rawSm));
                }

                $tempatSampling = '';
                if (preg_match('/Tempat Sampling[:\s]*([^•\n]+)/i', $body, $ts)) {
                    $tempatSampling = preg_replace('/\s+/', ' ', trim($ts[1]));
                }

                $tanggalDiterima = '';
                if (preg_match('/Tanggal Diterima[^:]*[:\s]*([0-9]{4}-[0-9]{2}-[0-9]{2})/i', $body, $td)) {
                    $tanggalDiterima = trim($td[1]);
                }

                $pengirim = '';
                if (preg_match('/Pengirim[:\s]*([^•\n]+)/i', $body, $pg)) {
                    $rawPg = trim($pg[1]);
                    $rawPg = preg_split('/(•|\bIdentitas\b|\bNo Surat\b)/i', $rawPg)[0];
                    $pengirim = preg_replace('/\s+/', ' ', trim($rawPg));
                }

                $noSurat = '';
                if (preg_match('/No Surat[:\s]*([^•\n]+)/i', $body, $ns)) {
                    $noSurat = preg_replace('/\s+/', ' ', trim($ns[1]));
                }

                $noBilling = '';
                if (preg_match('/No Billing[:\s]*([0-9]+)/i', $body, $nb)) {
                    $noBilling = trim($nb[1]);
                }

                $samples[$code] = [
                    'row_no' => $rowNo,
                    'kode_sampel' => $code,
                    'nama_produk' => $namaProduk,
                    'keterangan_sampling' => $keteranganSampling,
                    'tempat_sampling' => $tempatSampling,
                    'tanggal_diterima' => $tanggalDiterima,
                    'pengirim' => $pengirim,
                    'no_surat' => $noSurat,
                    'no_billing' => $noBilling,
                ];
            }
        }

        // 2. Ekstraksi Tambahan dari Halaman "DAFTAR LAMPIRAN SAMPEL" (Netto, Bentuk Sediaan, Komoditi)
        $lampiranPattern = '/Kode Sampel\s+([0-9A-Za-z\.\-\/]+)(.*?)(?=(?:Kode Sampel\s+[0-9A-Za-z\.\-\/]+)|\nLABEL KODE SAMPEL|$)/s';
        if (preg_match_all($lampiranPattern, $fullText, $lMatches, PREG_SET_ORDER)) {
            $lIdx = 1;
            foreach ($lMatches as $lm) {
                $code = trim($lm[1]);
                $block = $lm[2];

                // Validasi kode sampel
                if (!preg_match('/[0-9]/', $code) || in_array(strtolower($code), ['identitas', 'nomor', 'sample', 'sampel', 'manual', 'no', 'keterangan'])) {
                    continue;
                }

                $netto = '';
                if (preg_match('/Netto\s+([0-9]+[.,]?[0-9]*\s*(?:gram|gr|mg|ml|kg|l)?)/i', $block, $nm)) {
                    $netto = trim($nm[1]);
                }

                $bentukSediaan = '';
                if (preg_match('/Bentuk Sediaan\s+([^\n]+)/i', $block, $bm)) {
                    $rawBs = trim(explode('No. Bets', $bm[1])[0]);
                    $bentukSediaan = preg_replace('/\s+/', ' ', trim($rawBs));
                }

                $komoditi = '';
                if (preg_match('/Komoditi\s+([^\n]+)/i', $block, $km)) {
                    $rawKm = trim(explode('Identitas', $km[1])[0]);
                    $komoditi = preg_replace('/\s+/', ' ', trim($rawKm));
                }

                $namaSampel = '';
                if (preg_match('/Nama Sampel\s+([^\n]+(?:\n[^\n]+)?)/i', $block, $nsm)) {
                    $rawNama = trim(explode('Pabrik', $nsm[1])[0]);
                    $namaSampel = preg_replace('/\s+/', ' ', $rawNama);
                }

                if (isset($samples[$code])) {
                    if ($netto) $samples[$code]['netto'] = $netto;
                    if ($bentukSediaan) $samples[$code]['bentuk_sediaan'] = $bentukSediaan;
                    if ($komoditi) $samples[$code]['komoditi'] = $komoditi;
                    if (!$samples[$code]['nama_produk'] && $namaSampel) {
                        $samples[$code]['nama_produk'] = $namaSampel;
                    }
                } else {
                    $samples[$code] = [
                        'row_no' => $lIdx++,
                        'kode_sampel' => $code,
                        'nama_produk' => $namaSampel,
                        'komoditi' => $komoditi,
                        'bentuk_sediaan' => $bentukSediaan,
                        'netto' => $netto,
                    ];
                }
            }
        }

        // 3. Ekstraksi dari "LABEL KODE SAMPEL" jika belum ada sampel sama sekali
        if (empty($samples)) {
            if (preg_match_all('/([0-9]{2}\.[0-9]{3}\.[0-9]{2}\.[0-9]{2}\.[0-9]{2}\.[0-9]{4}\.[A-Za-z])/i', $fullText, $labelMatches)) {
                $uniqueCodes = array_values(array_unique($labelMatches[1]));
                $lIdx = 1;
                foreach ($uniqueCodes as $uc) {
                    $samples[$uc] = [
                        'row_no' => $lIdx++,
                        'kode_sampel' => $uc,
                        'nama_produk' => 'Sampel Uji ' . $uc,
                    ];
                }
            }
        }

        if (empty($samples)) {
            return [];
        }

        $records = [];
        $rowNum = 1;

        foreach ($samples as $code => $data) {
            $mappedData = [
                'No' => (string) ($data['row_no'] ?? $rowNum),
                'Kode Sampel' => $code,
                'QR Code' => '(Otomatis Di-generate)',
                'Nama Produk / Sampel' => $data['nama_produk'] ?? '-',
            ];

            if (!empty($data['komoditi'])) {
                $mappedData['Komoditi'] = $data['komoditi'];
            }
            if (!empty($data['bentuk_sediaan'])) {
                $mappedData['Bentuk Sediaan'] = $data['bentuk_sediaan'];
            }
            if (!empty($data['netto'])) {
                $mappedData['Netto'] = $data['netto'];
            }
            if (!empty($data['tempat_sampling'])) {
                $mappedData['Tempat Sampling'] = $data['tempat_sampling'];
            }
            if (!empty($data['pengirim'])) {
                $mappedData['Pengirim'] = $data['pengirim'];
            }
            if (!empty($data['tanggal_diterima'])) {
                $mappedData['Tanggal Diterima'] = $data['tanggal_diterima'];
            }
            if (!empty($data['no_surat'])) {
                $mappedData['No Surat Petugas Sampling'] = $data['no_surat'];
            }
            if (!empty($data['no_billing'])) {
                $mappedData['No Billing'] = $data['no_billing'];
            }
            if (!empty($data['keterangan_sampling'])) {
                $mappedData['Keterangan Sampling'] = $data['keterangan_sampling'];
            }

            $records[] = [
                'batch_id' => $batchId,
                'file_group_id' => $batchId,
                'filename' => $filename,
                'sheet_name' => 'Dokumen SPU',
                'row_number' => $rowNum++,
                'qr_token' => Str::random(32),
                'qr_content' => $code, // QR Code berisi Kode Sampel BBPOM
                'data' => json_encode($mappedData),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $records;
    }

    /**
     * Fallback generic table parser for any other PDF with tabular lines.
     */
    protected function extractGenericTable(string $fullText, array $pages, string $batchId, string $filename): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $fullText);
        $cleanLines = [];

        foreach ($lines as $line) {
            $t = trim($line);
            if ($t !== '') {
                $cleanLines[] = $t;
            }
        }

        if (empty($cleanLines)) {
            return [];
        }

        $now = now();
        $records = [];
        $rowNum = 1;

        // Cek baris-baris yang memiliki pemisah tab atau spasi ganda
        foreach ($cleanLines as $line) {
            // Deteksi jika baris berisi kode sampel atau diawali nomor urut
            if (preg_match('/^(\d+)\s+([A-Za-z0-9\.\-\/]{4,})\s+(.*)$/', $line, $m)) {
                $no = $m[1];
                $code = $m[2];
                $desc = trim($m[3]);

                $mappedData = [
                    'No' => $no,
                    'Kode Sampel' => $code,
                    'QR Code' => '(Otomatis Di-generate)',
                    'Informasi Sampel' => $desc,
                ];

                $records[] = [
                    'batch_id' => $batchId,
                    'file_group_id' => $batchId,
                    'filename' => $filename,
                    'sheet_name' => 'Tabel PDF',
                    'row_number' => $rowNum++,
                    'qr_token' => Str::random(32),
                    'qr_content' => $code,
                    'data' => json_encode($mappedData),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        return $records;
    }
}
