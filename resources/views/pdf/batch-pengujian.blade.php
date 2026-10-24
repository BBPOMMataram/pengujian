<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Permintaan Uji - {{ $filename }}</title>
    <style>
        @page {
            size: A4 {{ $orientation ?? 'landscape' }};
            margin: 8mm 12mm 10mm 12mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8pt;
            color: #0f172a;
            line-height: 1.35;
        }

        /* HEADER DOKUMEN RESMI SPU (DISAMAKAN PERSIS DENGAN EXPORT PER BARIS) */
        .spu-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .spu-logo-td {
            width: 75px;
            vertical-align: middle;
            text-align: left;
        }
        .spu-logo-img {
            max-height: 48px;
            max-width: 65px;
            display: block;
        }
        .spu-title-td {
            text-align: center;
            vertical-align: middle;
        }
        .spu-doc-title {
            font-size: 13pt;
            font-weight: bold;
            color: #000;
            margin: 0;
            letter-spacing: 0.3px;
            text-transform: none;
        }
        .spu-doc-no {
            font-size: 10.5pt;
            font-weight: bold;
            color: #000;
            margin-top: 2px;
            letter-spacing: 0.5px;
        }
        .spu-qr-td {
            width: 120px;
            vertical-align: middle;
            text-align: right;
        }
        .spu-qr-box {
            display: inline-block;
            text-align: center;
            border: 1px solid #cbd5e1;
            padding: 3px 5px;
            border-radius: 4px;
            background-color: #f8fafc;
        }
        .spu-qr-box img {
            width: 44px;
            height: 44px;
            display: block;
            margin: 0 auto;
        }
        .spu-qr-caption {
            font-size: 6pt;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-top: 1px;
        }

        /* PREAMBLE & METADATA */
        .spu-preamble {
            margin-top: 4px;
            margin-bottom: 6px;
            font-size: 8.5pt;
            line-height: 1.35;
        }
        .spu-preamble p {
            margin: 0 0 2px 0;
        }
        .spu-meta-table {
            border-collapse: collapse;
            font-size: 8.5pt;
            margin: 2px 0;
        }
        .spu-meta-table td {
            padding: 1px 0;
            vertical-align: top;
        }
        .spu-meta-lbl {
            width: 105px;
            color: #1e293b;
        }
        .spu-meta-sep {
            width: 12px;
            text-align: center;
            color: #475569;
        }
        .spu-meta-val {
            font-weight: bold;
            color: #000;
        }

        /* TABEL SURAT PERMINTAAN UJI (SPU MAIN TABLE) */
        .spu-main-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 7.5pt;
            margin-top: 4px;
            margin-bottom: 4px;
        }
        .spu-main-table th {
            border: 1px solid #000;
            background-color: #ffffff;
            color: #000;
            font-weight: bold;
            padding: 4px 4px;
            text-align: left;
            vertical-align: middle;
            font-size: 7.5pt;
        }
        .spu-main-table th.center {
            text-align: center;
        }
        .spu-main-table td {
            border: 1px solid #000;
            padding: 4px 4px;
            vertical-align: top;
            color: #000;
            word-wrap: break-word;
            word-break: break-all;
            overflow-wrap: break-word;
        }
        .spu-main-table td.center {
            text-align: center;
        }

        .bullet-list {
            margin: 0;
            padding-left: 10px;
            list-style-type: disc;
        }
        .bullet-list li {
            margin-bottom: 1.5px;
            line-height: 1.25;
        }

        .table-footer-note {
            font-size: 6.5pt;
            color: #334155;
            margin-top: 2px;
            margin-bottom: 8px;
            line-height: 1.25;
        }

        /* TANDA TANGAN RESMI 3 PEJABAT SPU */
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            page-break-inside: avoid;
        }
        .sign-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            font-size: 8pt;
            color: #000;
            padding: 0 4px;
        }
        .sign-title {
            line-height: 1.25;
            min-height: 44px;
        }
        .sign-space {
            height: 48px;
        }
        .sign-name {
            font-weight: bold;
            text-decoration: underline;
            color: #000;
            font-size: 8.5pt;
        }
        .sign-nip {
            font-size: 7.5pt;
            color: #1e293b;
            margin-top: 2px;
        }

        .footer-note {
            margin-top: 14px;
            text-align: center;
            font-size: 7pt;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
        }
    </style>
</head>
<body>

    @php
        $logoPath = public_path('images/logobbpom.png');
        $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;

        // Ambil data pertama untuk membaca informasi header SPU
        $firstItem = $items->first();
        $firstData = ($firstItem && is_array($firstItem->data)) ? $firstItem->data : [];
        $firstMeta = $firstData['_meta'] ?? [];
        $batchQrBase64 = $firstItem->qr_base64 ?? null;

        // 1. Ekstraksi Nomor SPU
        $spuNum = $firstMeta['no_spu'] 
            ?? $firstData['no_spu'] 
            ?? $firstData['Nomor SPU'] 
            ?? $firstData['spu'] 
            ?? $firstData['nomor_spu'] 
            ?? 'SPU.117.05.26.10.16.0036';

        // 2. Metadata Pengujian (Komoditi, Anggaran, Asal Sampling)
        $jenisSampelHead = $firstData['jenis_sampel'] ?? $firstData['Jenis Sampel'] ?? 'Narkotika - Pihak Ke 3 - Kepolisian';
        $komoditi = $firstMeta['komoditi'] 
            ?? $firstData['komoditi'] 
            ?? $firstData['Komoditi'] 
            ?? ($jenisSampelHead ? (str_contains($jenisSampelHead, ' - ') ? explode(' - ', $jenisSampelHead)[0] : $jenisSampelHead) : 'Narkotika');
        
        $anggaran = $firstMeta['anggaran'] 
            ?? $firstData['anggaran'] 
            ?? $firstData['Anggaran'] 
            ?? 'Pihak Ke 3 - Kepolisian';
        
        $asalSampling = $firstMeta['asal_sampling'] 
            ?? $firstData['asal_sampling'] 
            ?? $firstData['Asal Sampling'] 
            ?? $firstData['asal_sampel'] 
            ?? 'Kepolisian';

        // 3. Tanggal Resmi Format Indonesia
        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $rawTgl = $firstMeta['tanggal_spu'] 
            ?? $firstData['tanggal_spu'] 
            ?? $firstData['Tanggal SPU'] 
            ?? $firstData['tanggal_diterima'] 
            ?? $firstData['Tanggal Diterima'] 
            ?? null;

        $tanggalFormatted = null;
        if (!empty($rawTgl)) {
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', trim($rawTgl), $tm)) {
                $bln = (int)$tm[2];
                $tanggalFormatted = (int)$tm[3] . ' ' . ($bulanIndo[$bln] ?? '') . ' ' . $tm[1];
            } elseif (preg_match('/^(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})$/', trim($rawTgl), $tm)) {
                $tanggalFormatted = trim($rawTgl);
            }
        }
        if (empty($tanggalFormatted)) {
            $now = now();
            $tanggalFormatted = $now->day . ' ' . ($bulanIndo[$now->month] ?? $now->format('F')) . ' ' . $now->year;
        }

        // 4. Pejabat Resmi SPU BBPOM di Mataram (3 Kolom Sesuai Format SPU):
        $petugasPenerimaNama = $firstMeta['petugas_penerima'] 
            ?? $firstData['petugas_penerima'] 
            ?? $firstData['Petugas Penerima'] 
            ?? 'Nirmala Nurhasanah, STP';
        $petugasPenerimaNip = $firstMeta['nip_petugas_penerima'] 
            ?? $firstData['nip_petugas_penerima'] 
            ?? '199205172023212054';

        $managerAdministrasiNama = $firstMeta['manager_administrasi'] 
            ?? $firstData['manager_administrasi'] 
            ?? $firstData['Manager Administrasi'] 
            ?? 'Basuki Murdi Hartono, SH.';
        $managerAdministrasiNip = $firstMeta['nip_manager_administrasi'] 
            ?? $firstData['nip_manager_administrasi'] 
            ?? '197904052005011002';

        $kepalaBalaiNama = $firstMeta['kepala_balai'] 
            ?? $firstData['kepala_balai'] 
            ?? $firstData['Kepala Balai'] 
            ?? 'Yogi Abaso Mataram, S.Si., Apt';
        $nipKepalaBalai = $firstMeta['nip_kepala_balai'] 
            ?? $firstData['nip_kepala_balai'] 
            ?? '198101242009121002';
    @endphp

    <!-- ================================================================ -->
    <!-- SURAT PERMINTAAN UJI (SPU) SELURUH DATA & 3 TANDA TANGAN PEJABAT -->
    <!-- ================================================================ -->
    <table class="spu-header-table">
        <tr>
            <td class="spu-logo-td">
                @if(!empty($logoBase64))
                    <img src="data:image/png;base64,{{ $logoBase64 }}" class="spu-logo-img" alt="Logo BBPOM" />
                @endif
            </td>
            <td class="spu-title-td">
                <div class="spu-doc-title">SURAT PERMINTAAN UJI</div>
                <div class="spu-doc-no">{{ $spuNum }}</div>
            </td>
            <td class="spu-qr-td">
                @if(!empty($batchQrBase64))
                    <div class="spu-qr-box">
                        <img src="data:image/svg+xml;base64,{{ $batchQrBase64 }}" alt="Verifikasi" />
                        <div class="spu-qr-caption">Verifikasi QR</div>
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <div class="spu-preamble">
        <p>Bersama ini disampaikan daftar sampel:</p>
        <table class="spu-meta-table">
            <tr>
                <td class="spu-meta-lbl">Komoditi</td>
                <td class="spu-meta-sep">:</td>
                <td class="spu-meta-val">{{ $komoditi }}</td>
            </tr>
            <tr>
                <td class="spu-meta-lbl">Anggaran</td>
                <td class="spu-meta-sep">:</td>
                <td class="spu-meta-val">{{ $anggaran }}</td>
            </tr>
            <tr>
                <td class="spu-meta-lbl">Asal Sampling</td>
                <td class="spu-meta-sep">:</td>
                <td class="spu-meta-val">{{ $asalSampling }}</td>
            </tr>
        </table>
        <p style="margin-top: 3px;">Untuk dilakukan pengujian sesuai ruang lingkup</p>
    </div>

    <!-- TABEL UTAMA SPU (FORMAT & LAYOUT SAMA PERSIS DENGAN EXPORT PER BARIS) -->
    <table class="spu-main-table">
        <thead>
            <tr>
                <th style="width: 24px;" class="center">No.</th>
                <th style="width: 115px;">Kode Sampel<br/><span style="font-size: 6.5pt; font-weight: normal;">Nomor Manual</span></th>
                <th style="width: 260px;">Identitas Sample</th>
                <th style="width: 180px;">Keterangan Sampling</th>
                <th style="width: 72px;" class="center">Jumlah Sampel<br/><span style="font-size: 6.5pt; letter-spacing: 2px;">T K M P S</span></th>
                <th style="width: 175px;">No. Surat Petugas Sampling</th>
                <th style="width: 115px;">Lokasi Sampel</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                @php
                    $dataArr = is_array($item->data) ? $item->data : [];

                    // 1. Identitas & Jenis Sampel
                    $jenisSampel = $dataArr['jenis_sampel'] ?? $dataArr['Jenis Sampel'] ?? $jenisSampelHead;
                    $itemKomoditi = $dataArr['komoditi'] ?? $dataArr['Komoditi'] ?? $komoditi;
                    $kodeSampel = $dataArr['Kode Sampel'] ?? $dataArr['kode_sampel'] ?? ($item->display_title ?: '-');
                    $namaProduk = $dataArr['Nama Produk / Sampel'] ?? $dataArr['Nama Produk'] ?? $dataArr['nama_produk'] ?? $dataArr['nama_sampel'] ?? $dataArr['Nama Sampel'] ?? '-';
                    $rawNie = $dataArr['nie'] ?? $dataArr['NIE'] ?? $dataArr['nomor_izin_edar'] ?? '';
                    $nie = ($rawNie !== '' && $rawNie !== '-') ? $rawNie : '-';
                    $merk = $dataArr['merk'] ?? $dataArr['Merk'] ?? '-';
                    $komposisi = $dataArr['komposisi'] ?? $dataArr['Komposisi'] ?? (str_contains(strtolower($itemKomoditi), 'narkotika') ? 'EKSTASI' : '-');
                    $bentukSediaan = $dataArr['bentuk_sediaan'] ?? $dataArr['Bentuk Sediaan'] ?? (str_contains(strtolower($itemKomoditi), 'narkotika') ? 'Solid' : '-');
                    $netto = $dataArr['netto'] ?? $dataArr['Netto'] ?? '-';

                    // 2. Keterangan Sampling & Administrasi
                    $keteranganSampling = $dataArr['keterangan_sampling'] ?? $dataArr['Keterangan Sampling'] ?? $dataArr['kemasan'] ?? $dataArr['Kemasan'] ?? '-';
                    $tempatSampling = $dataArr['tempat_sampling'] ?? $dataArr['Tempat Sampling'] ?? '-';
                    $tanggalDiterima = $dataArr['tanggal_diterima'] ?? $dataArr['Tanggal Diterima'] ?? $dataArr['tanggal_sampling'] ?? $dataArr['Tanggal Sampling'] ?? '-';
                    $pengirim = $dataArr['pengirim'] ?? $dataArr['Pengirim'] ?? '-';
                    $noSurat = $dataArr['no_surat'] ?? $dataArr['No Surat Petugas Sampling'] ?? $dataArr['no_surat_petugas_sampling'] ?? '-';
                    $noBilling = $dataArr['no_billing'] ?? $dataArr['No Billing'] ?? '-';

                    // 3. Catatan & Dekode Lokasi Sampel (K2, M2, A1 -> Kimia 2 Sampel, Mikro 2 Sampel dan Arsip 1 Sampel)
                    $catatan = $dataArr['catatan'] ?? $dataArr['Catatan'] ?? '-';
                    $lokasiSampel = \App\Models\LokasiSampel::parseCatatan($catatan, $jenisSampel, $itemKomoditi);
                    if ($lokasiSampel === '-' && !empty($dataArr['lokasi_sampel']) && $dataArr['lokasi_sampel'] !== '-' && !preg_match('/^T\s*K\s*M\s*P/i', $dataArr['lokasi_sampel'])) {
                        $lokasiSampel = \App\Models\LokasiSampel::parseCatatan($dataArr['lokasi_sampel'], $jenisSampel, $itemKomoditi);
                    }
                    if ($lokasiSampel === '-' && !empty($dataArr['Lokasi Sampel']) && $dataArr['Lokasi Sampel'] !== '-' && !preg_match('/^T\s*K\s*M\s*P/i', $dataArr['Lokasi Sampel'])) {
                        $lokasiSampel = \App\Models\LokasiSampel::parseCatatan($dataArr['Lokasi Sampel'], $jenisSampel, $itemKomoditi);
                    }

                    // 4. Hitung Kode Jumlah Sampel (T K M P S)
                    $counts = \App\Models\LokasiSampel::extractCounts($catatan, $jenisSampel, $itemKomoditi, 1);
                    $countT = $counts['T'];
                    $countK = $counts['K'];
                    $countM = $counts['M'];
                    $countP = $counts['P'];
                    $countS = $counts['S'];

                    $ketTambahan = $dataArr['keterangan_tambahan'] ?? $dataArr['Keterangan Tambahan'] ?? $dataArr['rekomendasi_uji'] ?? $dataArr['Rekomendasi Uji'] ?? '-';
                @endphp
                <tr>
                    <td class="center">{{ $dataArr['No'] ?? ($index + 1) }}</td>
                    <td>
                        <strong>Kode Sampel:</strong><br/>
                        <span style="font-family: monospace; font-size: 8pt; font-weight: bold; color: #000;">{{ $kodeSampel }}</span>
                    </td>
                    <td>
                        <ul class="bullet-list">
                            <li><strong>Nama Produk:</strong> {{ $namaProduk }}</li>
                            @if(!empty($nie) && $nie !== '-')
                                <li><strong>NIE:</strong> {{ $nie }}</li>
                            @endif
                            @if(!empty($merk) && $merk !== '-')
                                <li><strong>Merk:</strong> {{ $merk }}</li>
                            @endif
                            <li><strong>Jenis Sampel:</strong> {{ $jenisSampel }}</li>
                            @if(!empty($komposisi) && $komposisi !== '-')
                                <li><strong>Komposisi:</strong> {{ $komposisi }}</li>
                            @endif
                            @if(!empty($bentukSediaan) && $bentukSediaan !== '-')
                                <li><strong>Bentuk Sediaan:</strong> {{ $bentukSediaan }}</li>
                            @endif
                            @if(!empty($netto) && $netto !== '-')
                                <li><strong>Netto:</strong> {{ $netto }}</li>
                            @endif
                            <li><strong>Evaluasi Penandaan:</strong> Tidak Ada</li>
                        </ul>
                    </td>
                    <td>
                        @if(!empty($keteranganSampling) && $keteranganSampling !== '-')
                            <div style="margin-bottom: 3px;">{{ $keteranganSampling }}</div>
                        @endif
                        <ul class="bullet-list">
                            @if(!empty($tempatSampling) && $tempatSampling !== '-')
                                <li><strong>Tempat Sampling:</strong> {{ $tempatSampling }}</li>
                            @endif
                            @if(!empty($tanggalDiterima) && $tanggalDiterima !== '-')
                                <li><strong>Tanggal Diterima Di TPS:</strong> {{ $tanggalDiterima }}</li>
                            @endif
                            @if(!empty($pengirim) && $pengirim !== '-')
                                <li><strong>Pengirim:</strong> {{ $pengirim }}</li>
                            @endif
                        </ul>
                    </td>
                    <td class="center" style="font-family: monospace; font-size: 8pt; font-weight: bold; letter-spacing: 2.5px;">
                        {{ $countT }} {{ $countK }} {{ $countM }} {{ $countP }} {{ $countS }}
                    </td>
                    <td>
                        @if((!empty($noSurat) && $noSurat !== '-') || (!empty($noBilling) && $noBilling !== '-') || !empty($dataArr['Catatan Surat']))
                            <ul class="bullet-list">
                                @if(!empty($noSurat) && $noSurat !== '-')
                                    <li><strong>No Surat:</strong> {{ $noSurat }}</li>
                                @endif
                                @if(!empty($noBilling) && $noBilling !== '-')
                                    <li><strong>No Billing:</strong> {{ $noBilling }}</li>
                                @endif
                                @if(!empty($dataArr['Catatan Surat']))
                                    <li>{{ $dataArr['Catatan Surat'] }}</li>
                                @endif
                            </ul>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ (!empty($lokasiSampel) && $lokasiSampel !== '-') ? $lokasiSampel : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="table-footer-note">
        <strong>Keterangan Jumlah Sampel:</strong><br/>
        Jumlah T = Jumlah Total Sampel, Jumlah K = Jumlah Sampel Kimia, Jumlah M = Jumlah Sampel Mikro, Jumlah P = Jumlah Sampel Penandaan, Jumlah S = Jumlah Sisa Sampel
    </div>

    <!-- TANDA TANGAN RESMI 3 PEJABAT SPU (SESUAI HALAMAN 2 SPU BBPOM) -->
    <table class="sign-table">
        <tr>
            <td>
                <div class="sign-title">
                    Tanggal, {{ $tanggalFormatted }}<br/>
                    Pemohon / Pengirim Sampel<br/>
                    <strong>(Petugas Penerima Sampel)</strong>
                </div>
                <div class="sign-space"></div>
                <div class="sign-name">{{ $petugasPenerimaNama }}</div>
                <div class="sign-nip">NIP. {{ $petugasPenerimaNip }}</div>
            </td>
            <td>
                <div class="sign-title">
                    Tanggal, {{ $tanggalFormatted }}<br/>
                    Penerima Sampel<br/>
                    <strong>(Manager Administrasi)</strong>
                </div>
                <div class="sign-space"></div>
                <div class="sign-name">{{ $managerAdministrasiNama }}</div>
                <div class="sign-nip">NIP. {{ $managerAdministrasiNip }}</div>
            </td>
            <td>
                <div class="sign-title">
                    Tanggal, {{ $tanggalFormatted }}<br/>
                    Mengetahui<br/>
                    <strong>(Kepala Balai)</strong>
                </div>
                <div class="sign-space"></div>
                <div class="sign-name">{{ $kepalaBalaiNama }}</div>
                <div class="sign-nip">NIP. {{ $nipKepalaBalai }}</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dokumen ini sah dan diterbitkan resmi oleh Sistem Informasi Laboratorium Pengujian BBPOM di Mataram &bull; SPU #{{ $spuNum }}
    </div>

</body>
</html>
