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

        $docNamaUnit = null;
        if (preg_match('/Nama Unit\s*:\s*([^\n]+)/i', $fullText, $unitM)) {
            $docNamaUnit = trim($unitM[1]);
        }

        $docKomoditi = null;
        if (preg_match('/Komoditi\s*:\s*([^\n]+)/i', $fullText, $komM)) {
            $docKomoditi = trim($komM[1]);
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

        // Ekstraksi Dinamis Pejabat Penandatangan SPU sesuai dokumen asli yang diunggah
        $signers = $this->extractSignersFromDocument($fullText, $pages, $tanggalSpu);

        $petugasPenerima = null;
        $nipPetugasPenerima = null;
        $managerAdministrasi = null;
        $nipManagerAdministrasi = null;
        $kepalaBalai = null;
        $nipKepalaBalai = null;

        if (!empty($signers)) {
            $petugasPenerima = $signers[0]['name'] ?? null;
            $nipPetugasPenerima = !empty($signers[0]['nip']) ? $signers[0]['nip'] : null;

            foreach ($signers as $sigItem) {
                $titlesJoined = implode(' ', $sigItem['titles'] ?? []);
                if (stripos($titlesJoined, 'Manager Administrasi') !== false) {
                    $managerAdministrasi = $sigItem['name'] ?? null;
                    $nipManagerAdministrasi = !empty($sigItem['nip']) ? $sigItem['nip'] : null;
                } elseif (stripos($titlesJoined, 'Kepala Balai') !== false || stripos($titlesJoined, 'Mengetahui') !== false) {
                    $kepalaBalai = $sigItem['name'] ?? null;
                    $nipKepalaBalai = !empty($sigItem['nip']) ? $sigItem['nip'] : null;
                }
            }
        } elseif (preg_match_all('/([^\n]+)\s*\n\s*NIP\.\s*([0-9]{8,})/i', $fullText, $sigMatches, PREG_SET_ORDER)) {
            $filteredSigs = [];
            foreach ($sigMatches as $sm) {
                $rawLine = trim($sm[1]);
                $nip = trim($sm[2]);
                $parts = preg_split('/\t+/', $rawLine);
                $candidate = trim($parts[0]);
                $candidate = preg_replace('/^(?:Diterima Oleh|MT Pengujian|Penerima|Pemohon)\s*/i', '', $candidate);
                $candidate = trim($candidate);

                if (!empty($candidate) && !preg_match('/^(?:Tanggal|Pemohon|Penerima|Mengetahui|Kode Sampel|Jumlah)/i', $candidate)) {
                    $filteredSigs[] = ['name' => $candidate, 'nip' => $nip];
                }
            }

            if (count($filteredSigs) === 1) {
                $petugasPenerima = $filteredSigs[0]['name'];
                $nipPetugasPenerima = $filteredSigs[0]['nip'];
            } elseif (count($filteredSigs) >= 3) {
                $petugasPenerima = $filteredSigs[0]['name'];
                $nipPetugasPenerima = $filteredSigs[0]['nip'];
                $managerAdministrasi = $filteredSigs[1]['name'];
                $nipManagerAdministrasi = $filteredSigs[1]['nip'];
                $kepalaBalai = $filteredSigs[2]['name'];
                $nipKepalaBalai = $filteredSigs[2]['nip'];
            } elseif (count($filteredSigs) === 2) {
                $petugasPenerima = $filteredSigs[0]['name'];
                $nipPetugasPenerima = $filteredSigs[0]['nip'];
                $managerAdministrasi = $filteredSigs[1]['name'];
                $nipManagerAdministrasi = $filteredSigs[1]['nip'];
            }
        }

        // 1. Ekstraksi dari Tabel Halaman 1 (Tabel Surat Permintaan Uji)
        // Pola fleksibel mendukung '1 Kode Sampel:' maupun '1Kode Sampel:'
        $spuPattern = '/(?:^|\n)\s*(\d+)\s*Kode Sampel[:\s]*\n?([0-9A-Za-z\.\-\/]+)(.*?)(?=(?:\n\s*\d+\s*Kode Sampel[:\s]*)|\nKeterangan Jumlah Sampel|\nDAFTAR LAMPIRAN SAMPEL|$)/s';
        $extraIdentityBlocks = [];
        
        if (preg_match_all($spuPattern, $fullText, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $rowNo = (int) $m[1];
                $code = trim($m[2]);
                $body = $m[3];

                // Validasi bahwa kode sampel harus memuat angka dan bukan kata header
                if (!preg_match('/[0-9]/', $code) || in_array(strtolower($code), ['identitas', 'nomor', 'sample', 'sampel', 'manual', 'no', 'keterangan'])) {
                    continue;
                }

                // Jika dalam satu blok terdapat lebih dari 1 'Nama Produk:' (akibat urutan stream PDF 2 baris di 1 halaman),
                // pisahkan blok identitas kedua dst untuk dipasangkan ke baris yang kehilangan blok identitas
                $identitySource = $body;
                if (preg_match_all('/(?:-\s*)?•\s*Nama Produk[:\s].*?(?=(?:-\s*)?•\s*Nama Produk[:\s]|\nNo\.\s*Kode Sampel|$)/is', $body, $idBlocks) && count($idBlocks[0]) > 1) {
                    $identitySource = $idBlocks[0][0];
                    for ($bi = 1; $bi < count($idBlocks[0]); $bi++) {
                        $extraIdentityBlocks[] = $idBlocks[0][$bi];
                    }
                }

                $namaProduk = '';
                if (preg_match('/Nama Produk[:\s]*([^•\n]+(?:\n[ \t]*[^•\n]+)*)/i', $identitySource, $pn)) {
                    $rawNama = trim($pn[1]);
                    $rawNama = preg_split('/(•|\b(?:NIE|Merk|Komposisi|ED|Bets|Pabrik|Provinsi|Evaluasi|Kemasan|Kondisi|Rapid)\s*:)/i', $rawNama)[0];
                    $namaProduk = preg_replace('/\s+/', ' ', trim($rawNama));
                }

                // Ekstraksi NIE (Nomor Izin Edar) - jika kosong/strip fallback '-'
                $nie = '-';
                if (preg_match('/•\s*NIE\s*:\s*([^•\n]*)/i', $identitySource, $niem)) {
                    $rawNie = trim($niem[1]);
                    $nie = ($rawNie !== '' && $rawNie !== '-') ? $rawNie : '-';
                }

                // Ekstraksi Merk
                $merk = '-';
                if (preg_match('/•\s*Merk\s*:\s*([^•\n]*)/i', $identitySource, $merkm)) {
                    $rawMerk = trim($merkm[1]);
                    $merk = ($rawMerk !== '' && $rawMerk !== '-') ? $rawMerk : '-';
                }

                // Ekstraksi Jenis Sampel (bullet point persis setelah '• Merk:')
                // Contoh: 'Obat Traditional (OT) - Rutin' atau 'Narkotika - Pihak Ke 3 - Kepolisian'
                $jenisSampel = '';
                if (preg_match('/•\s*Merk\s*:[^\n]*\n\s*•\s*([^•\n]+(?:\n[ \t]*[^•\n]+)*)/i', $identitySource, $jm)) {
                    $rawJ = trim($jm[1]);
                    $rawJ = preg_split('/(•|\b(?:Komposisi|ED|Bets|Pabrik|Provinsi)\s*:)/i', $rawJ)[0];
                    $jenisSampel = preg_replace('/\s+/', ' ', trim($rawJ));
                }

                // Ekstraksi Catatan dan konversi ke Lokasi Sampel (K = Kimia, M = Mikro, R = Retensi, A = Arsip, P = Penandaan)
                $catatan = '-';
                if (preg_match('/(?:^|[\s,;])([KM]\s*[:=]?\s*\d+(?:[\s,;\/\-]+[KMRAPS]\s*[:=]?\s*\d+)*)/i', $body, $kmr)) {
                    $catatan = trim($kmr[1]);
                } elseif (preg_match('/(?:Jumlah Retain Sampel\s*:[^\n]*\n\s*)([^\n]+)/i', $body, $rm)) {
                    $rawCat = trim($rm[1]);
                    if ($rawCat !== '' && !preg_match('/^(?:No\.|Kode Sampel|\d+\s*Kode|Keterangan Jumlah|T\s*K\s*M\s*P)/i', $rawCat)) {
                        $catatan = $rawCat;
                    }
                } elseif (preg_match('/Catatan[:\s]*([^\n]+)/i', $body, $cm)) {
                    $rawCat = trim($cm[1]);
                    if ($rawCat !== '' && $rawCat !== '-' && !preg_match('/(?:Evaluasi Penandaan|^T\s*K\s*M\s*P)/i', $rawCat)) {
                        $catatan = $rawCat;
                    }
                } elseif (preg_match('/(?:NTPN[^\n]*\n\s*)([^\n]+)(?:\n\s*([^\n]+))?/i', $body, $cm)) {
                    if (!empty($cm[2])) {
                        $candidate = trim($cm[2]);
                        if (!preg_match('/^(?:Kode Sampel|\d+Kode Sampel|Keterangan Jumlah|T\s*K\s*M\s*P)/i', $candidate)) {
                            $catatan = $candidate;
                        }
                    }
                }
                $komoditi = $this->mapKomoditi($jenisSampel ?: $docKomoditi);
                $lokasiSampel = $this->parseLokasiSampel($catatan, $jenisSampel ?: $docKomoditi, $komoditi);

                $keteranganSampling = '';
                if (preg_match('/(?:•\s*)?Kemasan[:\s]*(.+?)(?=\s*(?:•|\bKondisi Sampel\b|\bRapid Test\b|$))/is', $identitySource, $km)) {
                    $keteranganSampling = 'Kemasan : ' . preg_replace('/\s+/', ' ', trim($km[1]));
                } elseif (preg_match('/(Plastik|Kardus|Botol|Aluminium|Kemasan|Amplop|Klip)[^•\n]+(?:\n[ \t]*[^•\n]+)*/i', $body, $sm)) {
                    $rawSm = trim($sm[0]);
                    $rawSm = preg_split('/(•|\bTempat\b|\bTanggal\b|\bPengirim\b|\bKondisi\b|\bRapid\b)/i', $rawSm)[0];
                    $keteranganSampling = preg_replace('/\s+/', ' ', trim($rawSm));
                }

                $tempatSampling = '';
                if (preg_match('/(?:•\s*)?Tempat Sampling[:\s]*(.*?)(?=(?:\n\s*(?:•\s*)?(?:Tanggal|Pengirim|Identitas Pengirim|Jumlah|No\.?\s*Surat|Total Pembayaran|No\.?\s*Billing|SPB|NTPN|Keterangan Jumlah|No\.\s*Kode Sampel)|\n\s*\d+\s*-\s*-|$))/is', $body, $ts)) {
                    $cleanTs = preg_replace('/^\s*•\s*/u', '', trim($ts[1]));
                    $tempatSampling = preg_replace('/\s+/', ' ', trim($cleanTs));
                }

                $tanggalDiterima = '';
                if (preg_match('/Tanggal\s+(?:Diterima[^:]*|Sampling)\s*[:\s]*([0-9]{4}-[0-9]{2}-[0-9]{2})/i', $body, $td)) {
                    $tanggalDiterima = trim($td[1]);
                }

                $pengirim = '';
                if (preg_match('/(?:•\s*)?Pengirim[:\s]*(.*?)(?=(?:\n\s*(?:•\s*)?(?:Identitas Pengirim|Tanggal|Tempat|No\.?\s*Surat|Total Pembayaran|No\.?\s*Billing|SPB|NTPN|Jumlah)|\n\s*\d+\s*-\s*-|$))/is', $body, $pg)) {
                    $rawPg = trim($pg[1]);
                    $rawPg = preg_split('/(•|\bIdentitas Pengirim\b|\bNo\.?\s*Surat\b)/i', $rawPg)[0];
                    $pengirim = preg_replace('/\s+/', ' ', trim($rawPg));
                }

                $identitasPengirim = '';
                if (preg_match('/(?:•\s*)?Identitas Pengirim[:\s]*([^\n•]+)/i', $body, $ipg)) {
                    $rawIpg = preg_replace('/\s+\d+\s*-\s*-.*$/', '', trim($ipg[1]));
                    $identitasPengirim = preg_replace('/\s+/', ' ', trim($rawIpg));
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
                if (preg_match('/•\s*Komposisi[:\s]*(.+?)(?=\s*•|$)/is', $identitySource, $kpm)) {
                    $rawKpm = trim($kpm[1]);
                    $komposisi = preg_replace('/\s+/', ' ', $rawKpm);
                }

                $samples[$code] = [
                    'row_no' => $rowNo,
                    'kode_sampel' => $code,
                    'nama_produk' => $namaProduk,
                    'nie' => $nie,
                    'merk' => $merk,
                    'jenis_sampel' => $jenisSampel,
                    'komoditi' => $komoditi,
                    'lokasi_sampel' => $lokasiSampel,
                    'catatan' => $catatan ?: '-',
                    'keterangan_sampling' => $keteranganSampling,
                    'tempat_sampling' => $tempatSampling,
                    'tanggal_diterima' => $tanggalDiterima,
                    'pengirim' => $pengirim,
                    'identitas_pengirim' => $identitasPengirim,
                    'no_surat' => $noSurat,
                    'no_billing' => $noBilling,
                    'komposisi' => $komposisi,
                ];
            }

            // Pasangkan blok identitas ekstra ke sampel yang belum memiliki nama_produk
            if (!empty($extraIdentityBlocks)) {
                foreach ($samples as $cKey => &$sVal) {
                    if (empty($sVal['nama_produk']) && !empty($extraIdentityBlocks)) {
                        $extraBlock = array_shift($extraIdentityBlocks);
                        if (preg_match('/Nama Produk[:\s]*([^•\n]+(?:\n[ \t]*[^•\n]+)*)/i', $extraBlock, $pn)) {
                            $rawNama = preg_split('/(•|\b(?:NIE|Merk|Komposisi|ED|Bets|Pabrik|Provinsi|Evaluasi|Kemasan|Kondisi|Rapid)\s*:)/i', trim($pn[1]))[0];
                            $sVal['nama_produk'] = preg_replace('/\s+/', ' ', trim($rawNama));
                        }
                        if (preg_match('/•\s*NIE\s*:\s*([^•\n]*)/i', $extraBlock, $niem)) {
                            $rawNie = trim($niem[1]);
                            $sVal['nie'] = ($rawNie !== '' && $rawNie !== '-') ? $rawNie : '-';
                        }
                        if (preg_match('/•\s*Merk\s*:\s*([^•\n]*)/i', $extraBlock, $merkm)) {
                            $rawMerk = trim($merkm[1]);
                            $sVal['merk'] = ($rawMerk !== '' && $rawMerk !== '-') ? $rawMerk : '-';
                        }
                        if (preg_match('/•\s*Merk\s*:[^\n]*\n\s*•\s*([^•\n]+(?:\n[ \t]*[^•\n]+)*)/i', $extraBlock, $jm)) {
                            $rawJ = preg_split('/(•|\b(?:Komposisi|ED|Bets|Pabrik|Provinsi)\s*:)/i', trim($jm[1]))[0];
                            $sVal['jenis_sampel'] = preg_replace('/\s+/', ' ', trim($rawJ));
                            $sVal['komoditi'] = $this->mapKomoditi($sVal['jenis_sampel']);
                            $sVal['lokasi_sampel'] = $this->parseLokasiSampel($sVal['catatan'], $sVal['jenis_sampel'], $sVal['komoditi']);
                        }
                        if (preg_match('/(?:•\s*)?Kemasan[:\s]*(.+?)(?=\s*(?:•|\bKondisi Sampel\b|\bRapid Test\b|$))/is', $extraBlock, $km)) {
                            $sVal['keterangan_sampling'] = 'Kemasan : ' . preg_replace('/\s+/', ' ', trim($km[1]));
                        }
                        if (preg_match('/•\s*Komposisi[:\s]*(.+?)(?=\s*•|$)/is', $extraBlock, $kpm)) {
                            $sVal['komposisi'] = preg_replace('/\s+/', ' ', trim($kpm[1]));
                        }
                    }
                }
                unset($sVal);
            }
        }

        // 2. Ekstraksi Tambahan dari Halaman "DAFTAR LAMPIRAN SAMPEL" (Netto, Bentuk Sediaan, Komoditi, Catatan)
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
                if (preg_match('/Nama Sampel\s+(.*?)(?=\n\s*Pabrik\b|$)/is', $block, $nsm)) {
                    $rawNama = trim(explode('Pabrik', $nsm[1])[0]);
                    $namaSampel = preg_replace('/\s+/', ' ', $rawNama);
                }

                $catatanLampiran = '';
                if (preg_match('/(?:^|[\s,;])([KM]\s*[:=]?\s*\d+(?:[\s,;\/\-]+[KMRAPS]\s*[:=]?\s*\d+)*)/i', $block, $clm)) {
                    $catatanLampiran = trim($clm[1]);
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
                    if ($catatanLampiran && (empty($samples[$code]['catatan']) || $samples[$code]['catatan'] === '-')) {
                        $samples[$code]['catatan'] = $catatanLampiran;
                        $samples[$code]['lokasi_sampel'] = $this->parseLokasiSampel(
                            $catatanLampiran,
                            $samples[$code]['jenis_sampel'] ?? '',
                            $samples[$code]['komoditi'] ?? ''
                        );
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
                        'catatan' => $catatanLampiran ?: '-',
                        'lokasi_sampel' => $this->parseLokasiSampel($catatanLampiran ?: '-', $komoditiLampiran ?: 'Narkotika', $komoditiMapped),
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
                'Lokasi Sampel' => (isset($data['komoditi']) && preg_match('/narkotika|narkoba/i', $data['komoditi']))
                    ? 'Kimia'
                    : ((!empty($data['lokasi_sampel']) && $data['lokasi_sampel'] !== '') ? $data['lokasi_sampel'] : '-'),
            ];

            if (!empty($data['merk']) && $data['merk'] !== '-') {
                $mappedData['Merk'] = $data['merk'];
            }
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
            if (!empty($data['identitas_pengirim'])) {
                $mappedData['Identitas Pengirim'] = $data['identitas_pengirim'];
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
                'nama_unit' => $docNamaUnit,
                'komoditi' => $docKomoditi ?: ($data['komoditi'] ?? null),
                'anggaran' => $docAnggaran,
                'asal_sampling' => $docAsalSampling,
                'tanggal_spu' => $tanggalSpu,
                'petugas_penerima' => $petugasPenerima,
                'nip_petugas_penerima' => $nipPetugasPenerima,
                'manager_administrasi' => $managerAdministrasi,
                'nip_manager_administrasi' => $nipManagerAdministrasi,
                'kepala_balai' => $kepalaBalai,
                'nip_kepala_balai' => $nipKepalaBalai,
                'signers' => $signers,
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
     * Extract exact signature blocks from SPU PDF pages (coordinates-first, text-fallback).
     */
    protected function extractSignersFromDocument(string $fullText, array $pages, ?string $defaultDate = null): array
    {
        try {
            foreach ($pages as $page) {
                $pt = $page->getText();
                if (stripos($pt, 'Pemohon / Pengirim Sampel') === false && stripos($pt, 'Penerima Sampel') === false) {
                    continue;
                }

                $tm = method_exists($page, 'getDataTm') ? $page->getDataTm() : [];
                if (empty($tm)) {
                    continue;
                }

                $anchors = [];
                foreach ($tm as $idx => $entry) {
                    $text = trim($entry[1] ?? '');
                    if (preg_match('/^Tanggal,\s*(.+)$/i', $text, $m)) {
                        $anchors[] = [
                            'idx' => $idx,
                            'x' => (float)($entry[0][4] ?? 0),
                            'y' => (float)($entry[0][5] ?? 0),
                            'date' => trim($m[1]),
                            'titles' => [],
                            'name' => null,
                            'nip' => null,
                        ];
                    }
                }

                if (empty($anchors)) {
                    continue;
                }

                foreach ($anchors as $aIdx => &$anchor) {
                    $minY = $anchor['y'] - 160;
                    foreach ($anchors as $otherIdx => $other) {
                        if ($otherIdx !== $aIdx && abs($other['x'] - $anchor['x']) < 30 && $other['y'] < $anchor['y']) {
                            $minY = max($minY, $other['y'] + 5);
                        }
                    }

                    foreach ($tm as $idx => $entry) {
                        if ($idx === $anchor['idx']) {
                            continue;
                        }
                        $x = (float)($entry[0][4] ?? 0);
                        $y = (float)($entry[0][5] ?? 0);
                        $text = trim($entry[1] ?? '');
                        if ($text === '') {
                            continue;
                        }

                        if (abs($x - $anchor['x']) < 30 && $y < $anchor['y'] && $y >= $minY) {
                            $dy = $anchor['y'] - $y;
                            if ($dy < 45) {
                                $anchor['titles'][] = $text;
                            } else {
                                if (preg_match('/^NIP\.?\s*(.*)$/i', $text, $nm)) {
                                    $anchor['nip'] = trim($nm[1]);
                                } else {
                                    $anchor['name'] = $anchor['name'] ? ($anchor['name'] . ' ' . $text) : $text;
                                }
                            }
                        }
                    }
                }
                unset($anchor);

                $result = [];
                foreach ($anchors as $a) {
                    $result[] = [
                        'date' => $a['date'] ?: $defaultDate,
                        'titles' => $a['titles'],
                        'name' => $a['name'],
                        'nip' => $a['nip'],
                    ];
                }

                if (!empty($result)) {
                    return $result;
                }
            }
        } catch (\Throwable $e) {
            // Fallback to regex below if coordinate extraction fails
        }

        return [];
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
