<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Permintaan Uji - {{ $item->display_title }}</title>
    <style>
        @page {
            margin: 18mm 15mm 18mm 15mm;
            size: A4 portrait;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #1e293b;
            line-height: 1.45;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .header-logo {
            width: 75px;
            text-align: center;
            vertical-align: middle;
        }
        .header-logo-img {
            max-width: 65px;
            max-height: 65px;
            display: block;
            margin: 0 auto;
        }
        .header-text {
            text-align: center;
            vertical-align: middle;
            padding: 0 10px;
        }
        .header-text h3 {
            margin: 0;
            font-size: 10pt;
            font-weight: normal;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-text h2 {
            margin: 2px 0;
            font-size: 11.5pt;
            font-weight: bold;
            color: #005baa;
            text-transform: uppercase;
        }
        .header-text h1 {
            margin: 2px 0;
            font-size: 12.5pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }
        .header-text p {
            margin: 3px 0 0 0;
            font-size: 7.5pt;
            color: #475569;
        }
        .double-line {
            border-top: 2px solid #005baa;
            border-bottom: 1px solid #005baa;
            height: 2px;
            margin-top: 4px;
            margin-bottom: 14px;
        }

        /* FORMAT SPU & BERITA ACARA */
        .spu-box {
            margin-bottom: 14px;
        }
        .spu-title-box {
            text-align: center;
            margin-bottom: 10px;
        }
        .spu-title {
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .spu-no {
            font-size: 9.5pt;
            font-weight: bold;
            color: #005baa;
            margin-top: 3px;
        }
        .spu-preamble-text {
            font-size: 9.5pt;
            color: #1e293b;
            margin: 8px 0 6px 0;
        }
        .spu-meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
            margin-bottom: 10px;
        }
        .spu-meta-table td {
            padding: 2.5px 0;
            vertical-align: top;
        }
        .spu-label {
            width: 130px;
            font-weight: 600;
            color: #334155;
        }
        .spu-sep {
            width: 15px;
            text-align: center;
            color: #64748b;
        }
        .spu-val {
            color: #0f172a;
            font-weight: 600;
        }

        .section-heading {
            font-size: 9.5pt;
            font-weight: bold;
            color: #005baa;
            text-transform: uppercase;
            margin-top: 14px;
            margin-bottom: 6px;
            border-bottom: 1.5px solid #e2e8f0;
            padding-bottom: 4px;
            letter-spacing: 0.3px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 16px;
            font-size: 9pt;
        }
        .data-table th {
            background-color: #005baa;
            color: #ffffff;
            font-weight: 600;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #005baa;
            font-size: 8.5pt;
            text-transform: uppercase;
            word-wrap: break-word;
            word-break: break-all;
        }
        .data-table td {
            padding: 5px 8px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
            word-wrap: break-word;
            word-break: break-all;
            overflow-wrap: break-word;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .sign-table {
            width: 100%;
            margin-top: 24px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .sign-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 9pt;
        }
        .footer-note {
            margin-top: 25px;
            text-align: center;
            font-size: 7.5pt;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
    </style>
</head>
<body>

    @php
        $logoPath = public_path('images/logobbpom.png');
        $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;

        // Ambil info SPU dari data sampel jika ada, atau gunakan default resmi
        $dataArr = is_array($item->data) ? $item->data : [];
        $spuNum = $dataArr['no_spu'] ?? $dataArr['spu'] ?? $dataArr['nomor_spu'] ?? 'SPU.117.05.26.10.16.0036';
        $jenisSampel = $dataArr['jenis_sampel'] ?? $dataArr['Jenis Sampel'] ?? null;
        $komoditi = $dataArr['komoditi'] ?? $dataArr['Komoditi'] ?? ($jenisSampel ? (str_contains($jenisSampel, ' - ') ? explode(' - ', $jenisSampel)[0] : $jenisSampel) : 'Narkotika');
        $rawNie = $dataArr['nie'] ?? $dataArr['NIE'] ?? $dataArr['nomor_izin_edar'] ?? '';
        $nie = ($rawNie !== '' && $rawNie !== '-') ? $rawNie : '-';
        $lokasiSampel = $dataArr['lokasi_sampel'] ?? $dataArr['Lokasi Sampel'] ?? null;
        $anggaran = $dataArr['anggaran'] ?? $dataArr['Anggaran'] ?? 'Pihak Ke 3 - Kepolisian';
        $asalSampling = $dataArr['asal_sampling'] ?? $dataArr['Asal Sampling'] ?? $dataArr['asal_sampel'] ?? 'Kepolisian';
    @endphp

    <!-- KOP SURAT RESMI BPOM -->
    <table class="header-table">
        <tr>
            @if(!empty($logoBase64))
                <td class="header-logo">
                    <img src="data:image/png;base64,{{ $logoBase64 }}" class="header-logo-img" alt="Logo BBPOM" />
                </td>
            @endif
            <td class="header-text" style="{{ empty($logoBase64) ? 'text-align: center; width: 100%;' : '' }}">
                <h3>BADAN PENGAWAS OBAT DAN MAKANAN</h3>
                <h2>BALAI BESAR PENGAWAS OBAT DAN MAKANAN DI MATARAM</h2>
                <h1>LABORATORIUM PENGUJIAN</h1>
                <p>Jl. Cendrawasih No. 84, Cakranegara, Kota Mataram, Nusa Tenggara Barat 83239<br/>
                Telepon: (0370) 621926 | Faksimili: (0370) 631481 | Email: bpom_mataram@pom.go.id</p>
            </td>
        </tr>
    </table>

    <div class="double-line"></div>

    <!-- FORMAT BERITA ACARA SPU (SURAT PERMINTAAN UJI) -->
    <div class="spu-box">
        <div class="spu-title-box">
            <h2 class="spu-title">SURAT PERMINTAAN UJI</h2>
            <div class="spu-no">{{ $spuNum }}</div>
        </div>

        <p class="spu-preamble-text">Bersama ini disampaikan daftar sampel:</p>
        
        <table class="spu-meta-table">
            <tr>
                <td class="spu-label">Komoditi</td>
                <td class="spu-sep">:</td>
                <td class="spu-val">{{ $komoditi }}</td>
            </tr>
            @if(!empty($jenisSampel))
            <tr>
                <td class="spu-label">Jenis Sampel</td>
                <td class="spu-sep">:</td>
                <td class="spu-val">{{ $jenisSampel }}</td>
            </tr>
            @endif
            <tr>
                <td class="spu-label">NIE</td>
                <td class="spu-sep">:</td>
                <td class="spu-val">{{ $nie }}</td>
            </tr>
            @if(!empty($lokasiSampel))
            <tr>
                <td class="spu-label">Lokasi Sampel</td>
                <td class="spu-sep">:</td>
                <td class="spu-val">{{ $lokasiSampel }}</td>
            </tr>
            @endif
            <tr>
                <td class="spu-label">Anggaran</td>
                <td class="spu-sep">:</td>
                <td class="spu-val">{{ $anggaran }}</td>
            </tr>
            <tr>
                <td class="spu-label">Asal Sampling</td>
                <td class="spu-sep">:</td>
                <td class="spu-val">{{ $asalSampling }}</td>
            </tr>
        </table>
    </div>

    <!-- TABEL DETAIL DATA DARI EXCEL/DOKUMEN TANPA KOLOM QR CODE -->
    <div class="section-heading">DATA DETAIL SAMPEL PENGUJIAN</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35px; text-align: center;">No</th>
                <th style="width: 220px;">Parameter / Informasi</th>
                <th>Keterangan / Hasil</th>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; @endphp
            @if(is_array($item->data))
                @foreach($item->data as $key => $val)
                    @php
                        $keyLower = strtolower(trim($key));
                        $isQrKey = str_contains($keyLower, 'qr');
                        $valStr = is_string($val) ? strtolower($val) : '';
                        $isQrVal = str_contains($valStr, 'otomatis di-generate');
                    @endphp
                    @if(!$isQrKey && !$isQrVal)
                        <tr>
                            <td style="text-align: center; color: #64748b;">{{ $i++ }}</td>
                            <td style="font-weight: 600; color: #334155;">
                                @if(str_contains($key, ' - '))
                                    @php [$mainK, $subK] = explode(' - ', $key, 2); @endphp
                                    <span style="font-size: 7.5pt; color: #64748b; font-weight: normal; text-transform: uppercase;">{{ str_replace('_', ' ', $mainK) }} &rsaquo;</span><br/>
                                    <span style="font-size: 9pt; color: #0f172a; font-weight: bold;">{{ str_replace('_', ' ', $subK) }}</span>
                                @else
                                    <span style="font-size: 9pt; text-transform: capitalize;">{{ str_replace('_', ' ', $key) }}</span>
                                @endif
                            </td>
                            <td style="color: #0f172a;">
                                @if(is_bool($val))
                                    {{ $val ? 'Ya' : 'Tidak' }}
                                @elseif(is_array($val))
                                    {{ json_encode($val) }}
                                @else
                                    {{ $val ?? '-' }}
                                @endif
                            </td>
                        </tr>
                    @endif
                @endforeach
            @endif
        </tbody>
    </table>

    <!-- TANDA TANGAN PEJABAT / ANALIS -->
    <table class="sign-table">
        <tr>
            <td>
                Mengetahui,<br/>
                <strong>Manajer Teknis / Penyelia</strong>
                <div style="height: 55px;"></div>
                <strong>Apt. I Made Sujana, S.Farm.</strong><br/>
                <span style="color: #64748b; font-size: 8pt;">NIP. 19840315 200812 1 002</span>
            </td>
            <td>
                Mataram, {{ now()->translatedFormat('d F Y') }}<br/>
                <strong>Analis Penguji</strong>
                <div style="height: 55px;"></div>
                <strong>{{ $item->data['nama_analis'] ?? $item->data['analis'] ?? $item->data['penguji'] ?? 'Petugas Laboratorium Pengujian' }}</strong><br/>
                <span style="color: #64748b; font-size: 8pt;">Balai Besar POM di Mataram</span>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dokumen ini sah dan diterbitkan resmi oleh Sistem Informasi Laboratorium Pengujian BBPOM di Mataram.
    </div>

</body>
</html>