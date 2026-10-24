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
        if (!class_exists(\Smalot\PdfParser\Parser::class)) {
            $fallbackFile = app_path('Support/PdfParser/Parser.php');
            if (file_exists($fallbackFile)) {
                require_once $fallbackFile;
            }
        }

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
     * Parse and decode code like 'K1 M0 R1' from Catatan into human readable Lokasi Sampel:
     * - If Catatan is empty:
     *     - If jenis sampel / komoditi is Narkotika: returns master name for 'K' (e.g. 'Kimia').
     *     - Besides Narkotika: returns '-'.
     * - If Catatan is provided (e.g. 'K1 M0 R1'):
     *     - Decodes K, M, R counts. Only includes items with count > 0 (0 is omitted).
     *     - Example: 'K1 M0 R1' -> 'Kimia 1 Sampel dan Retensi 1 Sampel' (Mikro omitted because 0).
     */
    public function parseLokasiSampel(?string $catatan, ?string $jenisSampel = null, ?string $komoditi = null): string
    {
        return \App\Models\LokasiSampel::parseCatatan($catatan, $jenisSampel, $komoditi);
    }

    /**
     * Map full Jenis Sampel string (e.g. 'Obat Traditional (OT) - Rutin') to standard Komoditi.
     */
    public function mapKomoditi(?string $jenisSampel): string
    {
        if (empty($jenisSampel)) {
            return 'Narkotika';
        }

        if (preg_match('/Obat\s*Tradisi?onal|\(OT\)/i', $jenisSampel)) {
            return 'Obat Tradisional';
        }
        if (preg_match('/Narkotika/i', $jenisSampel)) {
            return 'Narkotika';
        }
        if (preg_match('/Psikotropika/i', $jenisSampel)) {
            return 'Psikotropika';
        }
        if (preg_match('/Kosmetik/i', $jenisSampel)) {
            return 'Kosmetik';
        }
        if (preg_match('/Pangan|Makanan/i', $jenisSampel)) {
            return 'Pangan Olahan';
        }
        if (preg_match('/Suplemen|\(SK\)/i', $jenisSampel)) {
            return 'Suplemen Kesehatan';
        }
        if (preg_match('/Kuasi/i', $jenisSampel)) {
            return 'Obat Kuasi';
        }
        if (preg_match('/Obat\b/i', $jenisSampel)) {
            return 'Obat';
        }

        if (str_contains($jenisSampel, ' - ')) {
            return trim(explode(' - ', $jenisSampel)[0]);
        }

        return trim($jenisSampel);
    }

    /**
     * Extract structured samples from BBPOM SPU / Permintaan Uji document.
     */
    protected function extractSpuDocument(string $fullText, array $pages, string $batchId, string $filename): array
    {
        $samples = [];
        $now = now();

        // 0. Ekstraksi Metadata Header & Tanda Tangan Dokumen SPU
        $spuNo = null;
        if (preg_match('/(SPU\.[0-9A-Za-z\.\-\/]+)/i', $fullText, $spuM)) {
            $spuNo = trim($spuM[1]);
        } elseif (preg_match('/(SPU\.[0-9A-Za-z\.\-\/]+)/i', $filename, $spuM)) {
            $spuNo = trim($spuM[1]);
        }

        $docAnggaran = null;
        if (preg_match('/Anggaran\s*:\s*([^\n]+)/i', $fullText, $angM)) {
            $docAnggaran = trim($angM[1]);
        }

        $docAsalSampling = null;
        if (preg_match('/Asal Sampling\s*:\s*([^\n]+)/i', $fullText, $asalM)) {
            $docAsalSampling = trim($asalM[1]);
        }

        $tanggalSpu = null;
        if (preg_match('/Tanggal,\s*([0-9]+\s+[A-Za-z]+\s+[0-9]{4})/i', $fullText, $tglM)) {
            $tanggalSpu = trim($tglM[1]);
        }

        // Default & Ekstraksi 3 Pejabat Resmi SPU
        // 1. Pemohon / Pengirim Sampel (Petugas Penerima Sampel)
        // 2. Penerima Sampel (Manager Administrasi)
        // 3. Mengetahui (Kepala Balai)
        $petugasPenerima = 'Nirmala Nurhasanah, STP';
        $nipPetugasPenerima = '199205172023212054';
        $managerAdministrasi = 'Basuki Murdi Hartono, SH.';
        $nipManagerAdministrasi = '197904052005011002';
        $kepalaBalai = 'Yogi Abaso Mataram, S.Si., Apt';
        $nipKepalaBalai = '198101242009121002';

        if (preg_match('/(Nirmala Nurhasanah[^\n]*)\s*\n\s*NIP\.\s*([0-9]+)/i', $fullText, $pm)) {
            $petugasPenerima = trim($pm[1]);
            $nipPetugasPenerima = trim($pm[2]);
        }
        if (preg_match('/(Basuki Murdi Hartono[^\n]*)\s*\n\s*NIP\.\s*([0-9]+)/i', $fullText, $bm)) {
            $managerAdministrasi = trim($bm[1]);
            $nipManagerAdministrasi = trim($bm[2]);
        }
        if (preg_match('/(Yogi Abaso Mataram[^\n]*)\s*\n\s*NIP\.\s*([0-9]+)/i', $fullText, $ym)) {
            $kepalaBalai = trim($ym[1]);
            $nipKepalaBalai = trim($ym[2]);
        }

        // 1. Ekstraksi dari Tabel Halaman 1 (Tabel Surat Permintaan Uji)
        // Pola fleksibel mendukung '1 Kode Sampel:' maupun '1Kode Sampel:'
        $spuPattern = '/(?:^|\n)\s*(\d+)\s*Kode Sampel[:\s]*\n?([0-9A-Za-z\.\-\/]+)(.*?)(?=(?:\n\s*\d+\s*Kode Sampel[:\s]*)|\nKeterangan Jumlah Sampel|\nDAFTAR LAMPIRAN SAMPEL|$)/s';
        
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
                    $rawNama = preg_split('/(•|\bNIE\b|\bMerk\b|\bNarkotika\b|\bKomposisi\b|\bED\b|\bBets\b|\bPabrik\b)/i', $rawNama)[0];
                    $namaProduk = preg_replace('/\s+/', ' ', trim($rawNama));
                }

                // Ekstraksi NIE (Nomor Izin Edar) - jika kosong/strip fallback '-'
                $nie = '-';
                if (preg_match('/•\s*NIE\s*:\s*([^•\n]*)/i', $body, $niem)) {
                    $rawNie = trim($niem[1]);
                    $nie = ($rawNie !== '' && $rawNie !== '-') ? $rawNie : '-';
                }

                // Ekstraksi Jenis Sampel (bullet point persis setelah '• Merk:')
                // Contoh: 'Obat Traditional (OT) - Rutin' atau 'Narkotika - Pihak Ke 3 - Kepolisian'
                $jenisSampel = '';
                if (preg_match('/•\s*Merk\s*:[^\n]*\n\s*•\s*([^•\n]+(?:\n[ \t]*[^•\n]+)*)/i', $body, $jm)) {
                    $rawJ = trim($jm[1]);
                    $rawJ = preg_split('/(•|\bKomposisi\b|\bED\b|\bBets\b|\bPabrik\b|\bProvinsi\b)/i', $rawJ)[0];
                    $jenisSampel = preg_replace('/\s+/', ' ', trim($rawJ));
                }

                // Ekstraksi Catatan dan konversi ke Lokasi Sampel (K = Lab Kimia, M = Lab Mikro, R = Retensi)
                $catatan = '-';
                if (preg_match('/\b([Kk]\s*\d+[\s,;]*[Mm]\s*\d+[\s,;]*[Rr]\s*\d+)\b/', $body, $kmr)) {
                    $catatan = trim($kmr[1]);
                } elseif (preg_match('/Catatan[:\s]*([^\n]+)/i', $body, $cm)) {
                    $rawCat = trim($cm[1]);
                    $catatan = ($rawCat !== '' && $rawCat !== '-' && !preg_match('/Evaluasi Penandaan/i', $rawCat)) ? $rawCat : '-';
                } elseif (preg_match('/(?:NTPN[^\n]*\n\s*)([^\n]+)(?:\n\s*([^\n]+))?/i', $body, $cm)) {
                    if (!empty($cm[2])) {
                        $candidate = trim($cm[2]);
                        if (!preg_match('/^(?:Kode Sampel|\d+Kode Sampel|Keterangan Jumlah)/i', $candidate)) {
                            $catatan = $candidate;
                        }
                    }
                }
                $komoditi = $this->mapKomoditi($jenisSampel);
                $lokasiSampel = $this->parseLokasiSampel($catatan, $jenisSampel, $komoditi);

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

                $komposisi = '';
                if (preg_match('/•\s*Komposisi[:\s]*([^•\n]+)/i', $body, $kpm)) {
                    $komposisi = trim($kpm[1]);
                }

                $samples[$code] = [
                    'row_no' => $rowNo,
                    'kode_sampel' => $code,
                    'nama_produk' => $namaProduk,
                    'nie' => $nie,
                    'jenis_sampel' => $jenisSampel,
                    'komoditi' => $this->mapKomoditi($jenisSampel),
                    'lokasi_sampel' => $lokasiSampel,
                    'catatan' => $catatan ?: '-',
                    'keterangan_sampling' => $keteranganSampling,
                    'tempat_sampling' => $tempatSampling,
                    'tanggal_diterima' => $tanggalDiterima,
                    'pengirim' => $pengirim,
                    'no_surat' => $noSurat,
                    'no_billing' => $noBilling,
                    'komposisi' => $komposisi,
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

                $komoditiLampiran = '';
                if (preg_match('/Komoditi\s+([^\n]+)/i', $block, $km)) {
                    $rawKm = trim(explode('Identitas', $km[1])[0]);
                    $komoditiLampiran = preg_replace('/\s+/', ' ', trim($rawKm));
                }

                $namaSampel = '';
                if (preg_match('/Nama Sampel\s+([^\n]+(?:\n[^\n]+)?)/i', $block, $nsm)) {
                    $rawNama = trim(explode('Pabrik', $nsm[1])[0]);
                    $namaSampel = preg_replace('/\s+/', ' ', $rawNama);
                }

                if (isset($samples[$code])) {
                    if ($netto) $samples[$code]['netto'] = $netto;
                    if ($bentukSediaan) $samples[$code]['bentuk_sediaan'] = $bentukSediaan;
                    if ($komoditiLampiran && empty($samples[$code]['komoditi'])) {
                        $samples[$code]['komoditi'] = $komoditiLampiran;
                    }
                    if (!$samples[$code]['nama_produk'] && $namaSampel) {
                        $samples[$code]['nama_produk'] = $namaSampel;
                    }
                } else {
                    $komoditiMapped = $this->mapKomoditi($komoditiLampiran);
                    $samples[$code] = [
                        'row_no' => $lIdx++,
                        'kode_sampel' => $code,
                        'nama_produk' => $namaSampel ?: 'Sampel Uji ' . $code,
                        'nie' => '-',
                        'jenis_sampel' => $komoditiLampiran ?: 'Narkotika',
                        'komoditi' => $komoditiMapped,
                        'lokasi_sampel' => $this->parseLokasiSampel('-', $komoditiLampiran ?: 'Narkotika', $komoditiMapped),
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
                        'nie' => '-',
                        'jenis_sampel' => 'Narkotika',
                        'komoditi' => 'Narkotika',
                        'lokasi_sampel' => $this->parseLokasiSampel('-', 'Narkotika', 'Narkotika'),
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
                'NIE' => (!empty($data['nie']) && $data['nie'] !== '') ? $data['nie'] : '-',
                'Jenis Sampel' => !empty($data['jenis_sampel']) ? $data['jenis_sampel'] : (!empty($data['komoditi']) ? $data['komoditi'] : '-'),
                'Komoditi' => $data['komoditi'] ?? 'Narkotika',
                'Lokasi Sampel' => (!empty($data['lokasi_sampel']) && $data['lokasi_sampel'] !== '') ? $data['lokasi_sampel'] : '-',
            ];

            if (!empty($data['catatan']) && $data['catatan'] !== '-') {
                $mappedData['Catatan'] = $data['catatan'];
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
            if (!empty($data['komposisi'])) {
                $mappedData['Komposisi'] = $data['komposisi'];
            }

            // SPU Document Metadata & Pejabat Penandatangan Resmi BBPOM (Disimpan di _meta agar tidak mencemari tabel)
            $mappedData['_meta'] = [
                'no_spu' => $spuNo,
                'anggaran' => $docAnggaran,
                'asal_sampling' => $docAsalSampling,
                'tanggal_spu' => $tanggalSpu,
                'petugas_penerima' => $petugasPenerima,
                'nip_petugas_penerima' => $nipPetugasPenerima,
                'manager_administrasi' => $managerAdministrasi,
                'nip_manager_administrasi' => $nipManagerAdministrasi,
                'kepala_balai' => $kepalaBalai,
                'nip_kepala_balai' => $nipKepalaBalai,
            ];

            // Susun isi QR Code memuat Kode Sampel, Nama Produk, Jenis Sampel lengkap, NIE, dan Lokasi Sampel
            $qrLines = [];
            $qrLines[] = "Kode Sampel: " . $code;
            if (!empty($data['nama_produk']) && $data['nama_produk'] !== '-') {
                $qrLines[] = "Nama Produk: " . $data['nama_produk'];
            }
            $qrLines[] = "Jenis Sampel: " . (!empty($data['jenis_sampel']) ? $data['jenis_sampel'] : ($data['komoditi'] ?? '-'));
            $qrLines[] = "NIE: " . (!empty($data['nie']) ? $data['nie'] : '-');
            $qrLines[] = "Lokasi Sampel: " . (!empty($data['lokasi_sampel']) ? $data['lokasi_sampel'] : '-');
            if (!empty($data['bentuk_sediaan']) && $data['bentuk_sediaan'] !== '-') {
                $qrLines[] = "Bentuk Sediaan: " . $data['bentuk_sediaan'];
            }
            if (!empty($data['netto']) && $data['netto'] !== '-') {
                $qrLines[] = "Netto: " . $data['netto'];
            }
            if (!empty($data['tempat_sampling'])) {
                $qrLines[] = "Tempat Sampling: " . $data['tempat_sampling'];
            }
            if (!empty($data['pengirim'])) {
                $qrLines[] = "Pengirim: " . $data['pengirim'];
            }
            if (!empty($data['no_surat'])) {
                $qrLines[] = "No Surat: " . $data['no_surat'];
            }

            $qrContent = implode("\n", $qrLines);

            $records[] = [
                'batch_id' => $batchId,
                'file_group_id' => $batchId,
                'filename' => $filename,
                'sheet_name' => 'Dokumen SPU',
                'row_number' => $rowNum++,
                'qr_token' => Str::random(32),
                'qr_content' => $qrContent,
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

        foreach ($cleanLines as $line) {
            if (preg_match('/^(\d+)\s+([A-Za-z0-9\.\-\/]{4,})\s+(.*)$/', $line, $m)) {
                $no = $m[1];
                $code = $m[2];
                $desc = trim($m[3]);

                $mappedData = [
                    'No' => $no,
                    'Kode Sampel' => $code,
                    'QR Code' => '(Otomatis Di-generate)',
                    'Nama Produk / Sampel' => $desc,
                    'NIE' => '-',
                ];

                $records[] = [
                    'batch_id' => $batchId,
                    'file_group_id' => $batchId,
                    'filename' => $filename,
                    'sheet_name' => 'Tabel PDF',
                    'row_number' => $rowNum++,
                    'qr_token' => Str::random(32),
                    'qr_content' => "Kode Sampel: {$code}\nNama Produk: {$desc}\nNIE: -",
                    'data' => json_encode($mappedData),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        return $records;
    }
}
