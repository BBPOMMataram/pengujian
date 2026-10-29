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
            width: 135px;
            vertical-align: middle;
            text-align: right;
        }
        .spu-qr-box {
            display: inline-block;
            text-align: center;
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            border-radius: 4px;
            background-color: #f8fafc;
        }
        .spu-qr-box img {
            width: 58px;
            height: 58px;
            display: block;
            margin: 0 auto;
        }
        .spu-qr-caption {
            font-size: 6.5pt;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-top: 2px;
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

        /* TANDA TANGAN RESMI PEJABAT SPU (SESUAI DOKUMEN ASLI) */
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            page-break-inside: avoid;
        }
        .sign-table td {
            text-align: left;
            vertical-align: top;
            font-size: 8pt;
            color: #000;
            padding: 0 4px;
        }
        .sign-title {
            line-height: 1.3;
            min-height: 36px;
        }
        .sign-space {
            height: 42px;
        }
        .sign-name {
            color: #000;
            font-size: 8pt;
            line-height: 1.3;
        }
        .sign-nip {
            font-size: 8pt;
            color: #000;
            line-height: 1.3;
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
        $meta = $dataArr['_meta'] ?? [];

        // 1. Ekstraksi Nomor SPU
        $spuNum = $meta['no_spu'] ?? $dataArr['no_spu'] ?? $dataArr['Nomor SPU'] ?? $dataArr['spu'] ?? $dataArr['nomor_spu'] ?? 'SPU.117.05.26.10.16.0036';
        $namaUnit = $meta['nama_unit'] ?? null;

        // 2. Metadata Pengujian
        $jenisSampel = $dataArr['jenis_sampel'] ?? $dataArr['Jenis Sampel'] ?? '-';
        $komoditi = $meta['komoditi'] ?? $dataArr['komoditi'] ?? $dataArr['Komoditi'] ?? ($jenisSampel && $jenisSampel !== '-' ? (str_contains($jenisSampel, ' - ') ? explode(' - ', $jenisSampel)[0] : $jenisSampel) : '-');
        $anggaran = $meta['anggaran'] ?? $dataArr['anggaran'] ?? $dataArr['Anggaran'] ?? null;
        $asalSampling = $meta['asal_sampling'] ?? $dataArr['asal_sampling'] ?? $dataArr['Asal Sampling'] ?? $dataArr['asal_sampel'] ?? null;
        $catatan = $dataArr['catatan'] ?? $dataArr['Catatan'] ?? '-';
        $isNarkotikaItem = preg_match('/narkotika|narkoba/i', (string)$komoditi) || preg_match('/narkotika|narkoba/i', (string)$jenisSampel);
        $lokasiSampel = $isNarkotikaItem ? 'Kimia' : \App\Models\LokasiSampel::parseCatatan($catatan, $jenisSampel, $komoditi);
        if (!$isNarkotikaItem && $lokasiSampel === '-' && !empty($dataArr['lokasi_sampel']) && $dataArr['lokasi_sampel'] !== '-') {
            $lokasiSampel = $dataArr['lokasi_sampel'];
        }

        // 3. Identitas Fisik Sampel
        $kodeSampel = $dataArr['Kode Sampel'] ?? $dataArr['kode_sampel'] ?? ($item->display_title ?: '-');
        $namaProduk = $dataArr['Nama Produk / Sampel'] ?? $dataArr['nama_produk'] ?? $dataArr['nama_sampel'] ?? $dataArr['Nama Sampel'] ?? '-';
        $rawNie = $dataArr['nie'] ?? $dataArr['NIE'] ?? $dataArr['nomor_izin_edar'] ?? '';
        $nie = ($rawNie !== '' && $rawNie !== '-') ? $rawNie : '-';
        $merk = $dataArr['merk'] ?? $dataArr['Merk'] ?? '-';
        $komposisi = $dataArr['komposisi'] ?? $dataArr['Komposisi'] ?? null;
        $bentukSediaan = $dataArr['bentuk_sediaan'] ?? $dataArr['Bentuk Sediaan'] ?? null;
        $netto = $dataArr['netto'] ?? $dataArr['Netto'] ?? null;

        // 4. Keterangan Sampling & Administrasi (Dinamis Sesuai File)
        $keteranganSampling = $dataArr['keterangan_sampling'] ?? $dataArr['Keterangan Sampling'] ?? $dataArr['kemasan'] ?? $dataArr['Kemasan'] ?? null;
        $tempatSampling = $dataArr['tempat_sampling'] ?? $dataArr['Tempat Sampling'] ?? null;
        $tanggalDiterima = $dataArr['tanggal_diterima'] ?? $dataArr['Tanggal Diterima'] ?? $dataArr['tanggal_sampling'] ?? $dataArr['Tanggal Sampling'] ?? null;
        $pengirim = $dataArr['pengirim'] ?? $dataArr['Pengirim'] ?? null;
        $identitasPengirim = $dataArr['identitas_pengirim'] ?? $dataArr['Identitas Pengirim'] ?? null;
        $noSurat = $dataArr['no_surat'] ?? $dataArr['No Surat Petugas Sampling'] ?? $dataArr['no_surat_petugas_sampling'] ?? null;
        $noBilling = $dataArr['no_billing'] ?? $dataArr['No Billing'] ?? null;

        // 5. Tanggal Resmi Format Indonesia
        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $rawTgl = $meta['tanggal_spu'] ?? $dataArr['tanggal_spu'] ?? $dataArr['Tanggal SPU'] ?? $tanggalDiterima;
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

        // 6. Pejabat Resmi SPU BBPOM di Mataram (Dinamis sesuai dokumen asli)
        $petugasPenerimaNama = $meta['petugas_penerima'] ?? $dataArr['petugas_penerima'] ?? $dataArr['Petugas Penerima'] ?? null;
        $petugasPenerimaNip = $meta['nip_petugas_penerima'] ?? $dataArr['nip_petugas_penerima'] ?? null;

        $managerAdministrasiNama = $meta['manager_administrasi'] ?? $dataArr['manager_administrasi'] ?? $dataArr['Manager Administrasi'] ?? null;
        $managerAdministrasiNip = $meta['nip_manager_administrasi'] ?? $dataArr['nip_manager_administrasi'] ?? null;

        $kepalaBalaiNama = $meta['kepala_balai'] ?? $dataArr['kepala_balai'] ?? $dataArr['Kepala Balai'] ?? null;
        $kepalaBalaiNip = $meta['nip_kepala_balai'] ?? $dataArr['nip_kepala_balai'] ?? null;

        $signers = $meta['signers'] ?? null;
        if (empty($signers) || !is_array($signers)) {
            if (!empty($managerAdministrasiNama) && $managerAdministrasiNama !== '-') {
                $signers = [
                    [
                        'date' => $tanggalFormatted,
                        'titles' => ['Pemohon / Pengirim Sampel', '(Petugas Penerima Sampel)'],
                        'name' => $petugasPenerimaNama,
                        'nip' => $petugasPenerimaNip,
                    ],
                    [
                        'date' => $tanggalFormatted,
                        'titles' => ['Penerima Sampel', '(Manager Administrasi)'],
                        'name' => $managerAdministrasiNama,
                        'nip' => $managerAdministrasiNip,
                    ],
                    [
                        'date' => $tanggalFormatted,
                        'titles' => ['Mengetahui', '(Kepala Balai)'],
                        'name' => $kepalaBalaiNama,
                        'nip' => $kepalaBalaiNip,
                    ],
                ];
            } else {
                $signers = [
                    [
                        'date' => $tanggalFormatted,
                        'titles' => ['Pemohon / Pengirim Sampel'],
                        'name' => $petugasPenerimaNama,
                        'nip' => $petugasPenerimaNip ?? '',
                    ],
                    [
                        'date' => $tanggalFormatted,
                        'titles' => ['Penerima Sampel/TPS'],
                        'name' => null,
                        'nip' => '',
                    ],
                    [
                        'date' => $tanggalFormatted,
                        'titles' => ['Diterima Oleh', 'MT Pengujian'],
                        'name' => null,
                        'nip' => '',
                    ],
                    [
                        'date' => $tanggalFormatted,
                        'titles' => ['Mengetahui', '(Kepala Balai)'],
                        'name' => $kepalaBalaiNama,
                        'nip' => $kepalaBalaiNip ?? '',
                    ],
                ];
            }
        }

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
            @if(!empty($namaUnit))
                <tr>
                    <td class="spu-meta-lbl">Nama Unit</td>
                    <td class="spu-meta-sep">:</td>
                    <td class="spu-meta-val">{{ $namaUnit }}</td>
                </tr>
            @endif
            @if(!empty($komoditi) && $komoditi !== '-')
                <tr>
                    <td class="spu-meta-lbl">Komoditi</td>
                    <td class="spu-meta-sep">:</td>
                    <td class="spu-meta-val">{{ $komoditi }}</td>
                </tr>
            @endif
            @if(!empty($anggaran))
                <tr>
                    <td class="spu-meta-lbl">Anggaran</td>
                    <td class="spu-meta-sep">:</td>
                    <td class="spu-meta-val">{{ $anggaran }}</td>
                </tr>
            @endif
            @if(!empty($asalSampling))
                <tr>
                    <td class="spu-meta-lbl">Asal Sampling</td>
                    <td class="spu-meta-sep">:</td>
                    <td class="spu-meta-val">{{ $asalSampling }}</td>
                </tr>
            @endif
        </table>
        <p style="margin-top: 3px;">Untuk dilakukan pengujian sesuai ruang lingkup</p>
    </div>

    <!-- TABEL UTAMA SPU (SESUAI DOKUMEN RESMI SPU BBPOM MATARAM) -->
    <table class="spu-main-table">
        <thead>
            <tr>
                <th style="width: 3%; padding: 4px 2px;" class="center">No.</th>
                <th style="width: 13%;">Kode Sampel<br/><span style="font-size: 6.5pt; font-weight: normal;">Nomor Manual</span></th>
                <th style="width: 24%;">Identitas Sample</th>
                <th style="width: 26%;">Keterangan Sampling</th>
                <th style="width: 8%;" class="center">Jumlah Sampel<br/><span style="font-size: 6.5pt; letter-spacing: 1.5px;">T K M P S</span></th>
                <th style="width: 13%;">No. Surat Petugas Sampling</th>
                <th style="width: 13%;">Lokasi Sampel</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="center" style="padding: 4px 2px;">{{ $item->data['No'] ?? $item->row_number }}</td>
                <td>
                    <strong>Kode Sampel:</strong><br/>
                    <span style="font-family: monospace; font-size: 8pt; font-weight: bold; color: #000;">{{ $kodeSampel }}</span>
                </td>
                <td>
                    <ul class="bullet-list">
                        <li><strong>Nama Produk:</strong> {{ $namaProduk }}</li>
                        <li><strong>NIE:</strong> {{ $nie }}</li>
                        <li><strong>Jenis Sampel:</strong> {{ $jenisSampel }}</li>
                    </ul>
                </td>
                <td>
                    @if(!empty($keteranganSampling) && $keteranganSampling !== '-' && !preg_match('/^Kemasan\s*:/i', trim($keteranganSampling)))
                        <div style="margin-bottom: 3px;">{{ $keteranganSampling }}</div>
                    @endif
                    <ul class="bullet-list">
                        @if(!empty($tempatSampling) && $tempatSampling !== '-')
                            <li><strong>Tempat Sampling:</strong> {{ $tempatSampling }}</li>
                        @endif
                        @if(!empty($tanggalDiterima) && $tanggalDiterima !== '-')
                            <li><strong>{{ (!empty($pengirim) && $pengirim !== '-') ? 'Tanggal Diterima Di TPS' : 'Tanggal Sampling' }}:</strong> {{ $tanggalDiterima }}</li>
                        @endif
                        @if(!empty($pengirim) && $pengirim !== '-')
                            <li><strong>Pengirim:</strong> {{ $pengirim }}</li>
                        @endif
                        @if(!empty($identitasPengirim) && $identitasPengirim !== '-')
                            <li><strong>Identitas Pengirim:</strong> {{ $identitasPengirim }}</li>
                        @endif
                    </ul>
                </td>
                <td class="center" style="font-family: monospace; font-size: 8pt; font-weight: bold; letter-spacing: 2px;">
                    {{ $countT }} {{ $countK }} {{ $countM }} {{ $countP }} {{ $countS }}
                </td>
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

    <!-- TANDA TANGAN RESMI PEJABAT SPU (SESUAI DOKUMEN ASLI) -->
    <table class="sign-table">
        @foreach(array_chunk($signers, 3) as $rowIdx => $signerRow)
            <tr>
                @foreach($signerRow as $colIdx => $sig)
                    @php
                        $sigDate = !empty($sig['date']) ? $sig['date'] : $tanggalFormatted;
                        $sigTitles = (!empty($sig['titles']) && is_array($sig['titles'])) ? $sig['titles'] : [];
                        $sigName = (!empty($sig['name']) && $sig['name'] !== '-') ? $sig['name'] : null;
                        $sigNip = ($sig['nip'] ?? null);
                        $colWidth = $colIdx === 0 ? '42%' : ($colIdx === 1 ? '31%' : '27%');
                    @endphp
                    <td style="width: {{ $colWidth }}; {{ $rowIdx > 0 ? 'padding-top: 18px;' : '' }}">
                        <div class="sign-title">
                            Tanggal, {{ $sigDate }}
                            @foreach($sigTitles as $tLine)
                                <br/>{{ $tLine }}
                            @endforeach
                        </div>
                        <div class="sign-space"></div>
                        <div class="sign-name">{!! $sigName ? e($sigName) : '&nbsp;' !!}</div>
                        @if($sigNip !== null || $sigName !== null)
                            <div class="sign-nip">NIP.{{ (!empty($sigNip) && $sigNip !== '-') ? ' ' . $sigNip : '' }}</div>
                        @endif
                    </td>
                @endforeach
                @for($emptyCol = count($signerRow); $emptyCol < 3; $emptyCol++)
                    @php
                        $emptyWidth = $emptyCol === 0 ? '42%' : ($emptyCol === 1 ? '31%' : '27%');
                    @endphp
                    <td style="width: {{ $emptyWidth }}; {{ $rowIdx > 0 ? 'padding-top: 18px;' : '' }}"></td>
                @endfor
            </tr>
        @endforeach
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
                            <td style="width: 52px; text-align: right; vertical-align: middle;">
                                @if(!empty($qrBase64))
                                    <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" style="width: 46px; height: 46px; display: inline-block;" alt="QR" />
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
