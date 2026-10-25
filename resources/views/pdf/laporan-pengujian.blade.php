<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Permintaan Uji - {{ $item->display_title ?: 'Laporan Pengujian' }}</title>
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

        /* HEADER DOKUMEN RESMI SPU */
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

        /* HALAMAN 2: DAFTAR LAMPIRAN & LABEL */
        .page-break {
            page-break-after: always;
        }
        .section-header-title {
            font-size: 10pt;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }
        .lampiran-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
            margin-bottom: 12px;
        }
        .lampiran-table td {
            border: 1px solid #94a3b8;
            padding: 3.5px 6px;
            vertical-align: top;
        }
        .lampiran-lbl {
            width: 140px;
            background-color: #f8fafc;
            font-weight: bold;
            color: #1e293b;
        }
        .lampiran-val {
            color: #000;
        }

        /* STIKER LABEL BARCODE */
        .label-cards-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin-top: 6px;
        }
        .label-card-td {
            width: 33.33%;
            border: 1.5px solid #000;
            padding: 6px 8px;
            background-color: #fff;
            vertical-align: middle;
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

        $dataArr = is_array($item->data) ? $item->data : [];

        // 1. Ekstraksi Nomor SPU
        $spuNum = $dataArr['no_spu'] ?? $dataArr['Nomor SPU'] ?? $dataArr['spu'] ?? $dataArr['nomor_spu'] ?? 'SPU.117.05.26.10.16.0036';

        // 2. Metadata Pengujian
        $jenisSampel = $dataArr['jenis_sampel'] ?? $dataArr['Jenis Sampel'] ?? 'Narkotika - Pihak Ke 3 - Kepolisian';
        $komoditi = $dataArr['komoditi'] ?? $dataArr['Komoditi'] ?? ($jenisSampel ? (str_contains($jenisSampel, ' - ') ? explode(' - ', $jenisSampel)[0] : $jenisSampel) : 'Narkotika');
        $anggaran = $dataArr['anggaran'] ?? $dataArr['Anggaran'] ?? 'Pihak Ke 3 - Kepolisian';
        $asalSampling = $dataArr['asal_sampling'] ?? $dataArr['Asal Sampling'] ?? $dataArr['asal_sampel'] ?? 'Kepolisian';
        $catatan = $dataArr['catatan'] ?? $dataArr['Catatan'] ?? '-';
        $lokasiSampel = \App\Models\LokasiSampel::parseCatatan($catatan, $jenisSampel, $komoditi);
        if ($lokasiSampel === '-' && !empty($dataArr['lokasi_sampel']) && $dataArr['lokasi_sampel'] !== '-') {
            $lokasiSampel = $dataArr['lokasi_sampel'];
        }

        // 3. Identitas Fisik Sampel
        $kodeSampel = $dataArr['Kode Sampel'] ?? $dataArr['kode_sampel'] ?? ($item->display_title ?: '26.117.11.16.05.0731');
        $namaProduk = $dataArr['Nama Produk / Sampel'] ?? $dataArr['nama_produk'] ?? $dataArr['nama_sampel'] ?? $dataArr['Nama Sampel'] ?? '-';
        $rawNie = $dataArr['nie'] ?? $dataArr['NIE'] ?? $dataArr['nomor_izin_edar'] ?? '';
        $nie = ($rawNie !== '' && $rawNie !== '-') ? $rawNie : '-';
        $merk = $dataArr['merk'] ?? $dataArr['Merk'] ?? '-';
        $komposisi = $dataArr['komposisi'] ?? $dataArr['Komposisi'] ?? 'EKSTASI';
        $bentukSediaan = $dataArr['bentuk_sediaan'] ?? $dataArr['Bentuk Sediaan'] ?? 'Solid';
        $netto = $dataArr['netto'] ?? $dataArr['Netto'] ?? '0,288 gram';

        // 4. Keterangan Sampling & Administrasi
        $keteranganSampling = $dataArr['keterangan_sampling'] ?? $dataArr['Keterangan Sampling'] ?? $dataArr['kemasan'] ?? $dataArr['Kemasan'] ?? 'Plastik klip transparan dalam amplop warna coklat berlak segel';
        $tempatSampling = $dataArr['tempat_sampling'] ?? $dataArr['Tempat Sampling'] ?? 'POLDA NTB';
        $tanggalDiterima = $dataArr['tanggal_diterima'] ?? $dataArr['Tanggal Diterima'] ?? '2026-10-24';
        $pengirim = $dataArr['pengirim'] ?? $dataArr['Pengirim'] ?? 'BRIGPOL I MADE';
        $noSurat = $dataArr['no_surat'] ?? $dataArr['No Surat Petugas Sampling'] ?? $dataArr['no_surat_petugas_sampling'] ?? 'R/691/X/RES.4.2/2026/Ditresnarkoba';
        $noBilling = $dataArr['no_billing'] ?? $dataArr['No Billing'] ?? '820261024239019';

        // 5. Tanggal Resmi Format Indonesia
        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $rawTgl = $dataArr['tanggal_spu'] ?? $dataArr['Tanggal SPU'] ?? $tanggalDiterima;
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

        // 6. Pejabat Resmi SPU BBPOM di Mataram (3 Kolom Sesuai Format SPU):
        // 1. Pemohon / Pengirim Sampel (Petugas Penerima Sampel)
        // 2. Penerima Sampel (Manager Administrasi)
        // 3. Mengetahui (Kepala Balai)
        $petugasPenerimaNama = $dataArr['petugas_penerima'] ?? $dataArr['Petugas Penerima'] ?? 'Nirmala Nurhasanah, STP';
        $petugasPenerimaNip = $dataArr['nip_petugas_penerima'] ?? '199205172023212054';

        $managerAdministrasiNama = $dataArr['manager_administrasi'] ?? $dataArr['Manager Administrasi'] ?? 'Basuki Murdi Hartono, SH.';
        $managerAdministrasiNip = $dataArr['nip_manager_administrasi'] ?? '197904052005011002';

        $kepalaBalaiNama = $dataArr['kepala_balai'] ?? $dataArr['Kepala Balai'] ?? 'Yogi Abaso Mataram, S.Si., Apt';
        $kepalaBalaiNip = $dataArr['nip_kepala_balai'] ?? '198101242009121002';

        // 7. Hitung Kode Jumlah Sampel (T K M P S)
        $counts = \App\Models\LokasiSampel::extractCounts($catatan, $jenisSampel, $komoditi, 1);
        $countT = $counts['T'];
        $countK = $counts['K'];
        $countM = $counts['M'];
        $countP = $counts['P'];
        $countS = $counts['S'];

        $isKimia = ($countK !== '-' && (int)$countK > 0) || str_contains(strtolower($lokasiSampel), 'kimia');
        $isMikro = ($countM !== '-' && (int)$countM > 0) || str_contains(strtolower($lokasiSampel), 'mikro');
    @endphp

    <!-- ================================================================ -->
    <!-- HALAMAN 1: SURAT PERMINTAAN UJI (SPU) & 3 TANDA TANGAN PEJABAT   -->
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
                @if(!empty($qrBase64))
                    <div class="spu-qr-box">
                        <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" alt="QR Code" />
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

    <!-- TABEL UTAMA SPU (SESUAI DOKUMEN RESMI SPU BBPOM MATARAM) -->
    <table class="spu-main-table">
        <thead>
            <tr>
                <th style="width: 24px;" class="center">No.</th>
                <th style="width: 110px;">Kode Sampel<br/><span style="font-size: 6.5pt; font-weight: normal;">Nomor Manual</span></th>
                <th style="width: 250px;">Identitas Sample</th>
                <th style="width: 170px;">Keterangan Sampling</th>
                <th style="width: 72px;" class="center">Jumlah Sampel<br/><span style="font-size: 6.5pt; letter-spacing: 2px;">T K M P S</span></th>
                <th style="width: 70px;">Keterangan Tambahan</th>
                <th style="width: 170px;">No. Surat Petugas Sampling</th>
                <th style="width: 75px;">Catatan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="center">{{ $item->data['No'] ?? $item->row_number }}</td>
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
                <td>-</td>
                <td>
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
                </td>
                <td>{{ (!empty($lokasiSampel) && $lokasiSampel !== '-') ? $lokasiSampel : ($catatan !== '-' ? $catatan : '-') }}</td>
            </tr>
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
                <div class="sign-nip">NIP. {{ $kepalaBalaiNip }}</div>
            </td>
        </tr>
    </table>

    <!-- ================================================================ -->
    <!-- HALAMAN 2: DAFTAR LAMPIRAN SAMPEL & LABEL KODE SAMPEL / BARCODE  -->
    <!-- ================================================================ -->
    <div class="page-break"></div>

    <div class="section-header-title">DAFTAR LAMPIRAN SAMPEL</div>

    <table class="lampiran-table">
        <tr>
            <td class="lampiran-lbl">Kode Sampel</td>
            <td class="lampiran-val" style="font-weight: bold; font-family: monospace; font-size: 8.5pt;">{{ $kodeSampel }}</td>
            <td class="lampiran-lbl">Identitas Tambahan</td>
            <td class="lampiran-val">-</td>
        </tr>
        <tr>
            <td class="lampiran-lbl">Komoditi</td>
            <td class="lampiran-val">{{ $komoditi }}</td>
            <td class="lampiran-lbl">Kategori</td>
            <td class="lampiran-val">-</td>
        </tr>
        <tr>
            <td class="lampiran-lbl">Nama Sampel</td>
            <td class="lampiran-val" colspan="3" style="font-weight: 600;">{{ $namaProduk }}</td>
        </tr>
        <tr>
            <td class="lampiran-lbl">Bentuk Sediaan</td>
            <td class="lampiran-val">{{ $bentukSediaan }}</td>
            <td class="lampiran-lbl">Netto</td>
            <td class="lampiran-val">{{ $netto }}</td>
        </tr>
        <tr>
            <td class="lampiran-lbl">Kemasan</td>
            <td class="lampiran-val" colspan="3">{{ $keteranganSampling }}</td>
        </tr>
        <tr>
            <td class="lampiran-lbl">Evaluasi Penandaan</td>
            <td class="lampiran-val">Tidak Ada</td>
            <td class="lampiran-lbl">Cara Penyimpanan</td>
            <td class="lampiran-val">-</td>
        </tr>
        <tr>
            <td class="lampiran-lbl">Jumlah Sampel</td>
            <td class="lampiran-val">1</td>
            <td class="lampiran-lbl">Kondisi Sampel</td>
            <td class="lampiran-val">-</td>
        </tr>
        <tr>
            <td class="lampiran-lbl">Pengujian</td>
            <td class="lampiran-val" colspan="3">
                <span style="display: inline-block; margin-right: 20px;">
                    <strong>[ {{ $isKimia ? 'X' : ' ' }} ]</strong> Kimia
                </span>
                <span style="display: inline-block; margin-right: 20px;">
                    <strong>[ {{ $isMikro ? 'X' : ' ' }} ]</strong> Mikro
                </span>
                <span style="display: inline-block;">
                    <strong>[   ]</strong> Penandaan
                </span>
                <span style="float: right; color: #475569;">Sisa Sampel: 1</span>
            </td>
        </tr>
        <tr>
            <td class="lampiran-lbl">Komposisi</td>
            <td class="lampiran-val">{{ $komposisi }}</td>
            <td class="lampiran-lbl">Lokasi Sampel</td>
            <td class="lampiran-val" style="font-weight: bold;">{{ (!empty($lokasiSampel) && $lokasiSampel !== '-') ? $lokasiSampel : 'Kimia' }}</td>
        </tr>
        <tr>
            <td class="lampiran-lbl">Catatan</td>
            <td class="lampiran-val" colspan="3">{{ $catatan }}</td>
        </tr>
    </table>

    <div class="section-header-title" style="margin-top: 16px;">LABEL KODE SAMPEL</div>

    <table class="label-cards-table">
        <tr>
            @for($st = 0; $st < 3; $st++)
                <td class="label-card-td">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 42px; vertical-align: middle; text-align: center;">
                                @if(!empty($logoBase64))
                                    <img src="data:image/png;base64,{{ $logoBase64 }}" style="max-height: 38px; max-width: 40px;" alt="Logo BPOM" />
                                @endif
                            </td>
                            <td style="vertical-align: middle; padding: 0 6px;">
                                <div style="font-family: monospace; font-size: 8.5pt; font-weight: bold; color: #000;">
                                    {{ $kodeSampel }}
                                </div>
                                <div style="font-size: 6pt; font-weight: bold; color: #1e293b; text-transform: uppercase; margin-top: 2px; letter-spacing: 0.3px;">
                                    BALAI BESAR POM DI MATARAM
                                </div>
                            </td>
                            <td style="width: 42px; text-align: right; vertical-align: middle;">
                                @if(!empty($qrBase64))
                                    <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" style="width: 36px; height: 36px; display: inline-block;" alt="QR" />
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            @endfor
        </tr>
    </table>

    <div class="footer-note">
        Dokumen ini sah dan diterbitkan resmi oleh Sistem Informasi Laboratorium Pengujian BBPOM di Mataram &bull; SPU #{{ $spuNum }}
    </div>

    @if(!empty($photos) && count($photos) > 0)
        <!-- ================================================================ -->
        <!-- HALAMAN LAMPIRAN DOKUMENTASI FOTO PENGUJIAN (LAYOUT HARMONIS)    -->
        <!-- ================================================================ -->
        <div class="page-break"></div>

        <table class="spu-header-table" style="margin-bottom: 8px;">
            <tr>
                <td class="spu-logo-td">
                    @if(!empty($logoBase64))
                        <img src="data:image/png;base64,{{ $logoBase64 }}" class="spu-logo-img" alt="Logo BPOM" />
                    @endif
                </td>
                <td class="spu-title-td">
                    <div class="spu-doc-title">LAMPIRAN DOKUMENTASI FOTO PENGUJIAN</div>
                    <div class="spu-doc-no">Nomor Sampel: {{ $kodeSampel }} &bull; SPU #{{ $spuNum }}</div>
                </td>
                <td class="spu-qr-td">
                    @if(!empty($qrBase64))
                        <div class="spu-qr-box">
                            <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" alt="QR Code" />
                            <div class="spu-qr-caption">VERIFIKASI RESMI</div>
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        <div style="border-top: 2px solid #005baa; margin-bottom: 14px;"></div>

        @php
            $photoCount = count($photos);
        @endphp

        @if($photoCount === 1)
            <!-- 1 FOTO: CENTERED HERO CARD (Rapi & Proporsional di Tengah Kertas) -->
            <div style="text-align: center; margin: 10px auto; width: 75%;">
                <div style="border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px; background-color: #f8fafc; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <div style="height: 125mm; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #ffffff; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <img src="{{ $photos[0]['src'] }}" style="max-height: 122mm; max-width: 100%; height: auto; display: block; margin: 0 auto;" alt="Dokumentasi Foto" />
                    </div>
                    <div style="margin-top: 8px; font-size: 9pt; font-weight: bold; color: #0f172a;">
                        {{ $photos[0]['caption'] ?: 'Dokumentasi Foto Sampel / Pengujian' }}
                    </div>
                </div>
            </div>
        @elseif($photoCount === 2)
            <!-- 2 FOTO: DUA KOLOM BERDAMPINGAN DENGAN TINGGI SELARAS & EYE-COMFORTING -->
            <table style="width: 100%; border-collapse: separate; border-spacing: 12px; margin-top: 5px;">
                <tr>
                    @foreach($photos as $p)
                        <td style="width: 50%; vertical-align: top; border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px; background-color: #f8fafc; text-align: center;">
                            <div style="height: 105mm; overflow: hidden; background: #ffffff; border-radius: 6px; border: 1px solid #e2e8f0; text-align: center;">
                                <img src="{{ $p['src'] }}" style="max-height: 103mm; max-width: 100%; height: auto; display: block; margin: 0 auto;" alt="Foto" />
                            </div>
                            <div style="margin-top: 8px; font-size: 8.5pt; font-weight: bold; color: #0f172a;">
                                {{ $p['caption'] ?: 'Foto Dokumentasi' }}
                            </div>
                        </td>
                    @endforeach
                </tr>
            </table>
        @else
            <!-- 3 ATAU 4 FOTO: GRID 2x2 SEIMBANG, MENGISI KERTAS SECARA HARMONIS -->
            <table style="width: 100%; border-collapse: separate; border-spacing: 10px; margin-top: 2px;">
                @foreach(array_chunk($photos, 2) as $rowPhotos)
                    <tr>
                        @foreach($rowPhotos as $p)
                            <td style="width: 50%; vertical-align: top; border: 1px solid #cbd5e1; border-radius: 8px; padding: 6px; background-color: #f8fafc; text-align: center;">
                                <div style="height: 58mm; overflow: hidden; background: #ffffff; border-radius: 4px; border: 1px solid #e2e8f0; text-align: center;">
                                    <img src="{{ $p['src'] }}" style="max-height: 56mm; max-width: 100%; height: auto; display: block; margin: 0 auto;" alt="Foto" />
                                </div>
                                <div style="margin-top: 5px; font-size: 8pt; font-weight: bold; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $p['caption'] ?: 'Foto Dokumentasi' }}
                                </div>
                            </td>
                        @endforeach
                        @if(count($rowPhotos) === 1)
                            <td style="width: 50%; border: none;"></td>
                        @endif
                    </tr>
                @endforeach
            </table>
        @endif

        <div class="footer-note" style="margin-top: 12px;">
            Lampiran dokumentasi foto resmi Laboratorium Pengujian Balai Besar POM di Mataram &bull; Sampel {{ $kodeSampel }}
        </div>
    @endif

</body>
</html>
