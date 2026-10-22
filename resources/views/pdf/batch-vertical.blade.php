<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Permintaan Uji - {{ $filename }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 12mm 15mm 12mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8.5pt;
            color: #1e293b;
            line-height: 1.35;
        }
        .page-container {
            width: 100%;
        }
        .page-break {
            page-break-after: always;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .header-logo {
            width: 70px;
            text-align: center;
            vertical-align: middle;
        }
        .header-logo-img {
            max-width: 60px;
            max-height: 60px;
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
            font-size: 9pt;
            font-weight: normal;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-text h2 {
            margin: 1px 0;
            font-size: 10.5pt;
            font-weight: bold;
            color: #005baa;
            text-transform: uppercase;
        }
        .header-text h1 {
            margin: 1px 0;
            font-size: 11.5pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }
        .header-text p {
            margin: 2px 0 0 0;
            font-size: 7pt;
            color: #475569;
        }
        .double-line {
            border-top: 2px solid #005baa;
            border-bottom: 1px solid #005baa;
            height: 2px;
            margin-top: 4px;
            margin-bottom: 10px;
        }

        /* FORMAT SPU & BERITA ACARA */
        .spu-box {
            margin-bottom: 10px;
        }
        .spu-title-box {
            text-align: center;
            margin-bottom: 6px;
        }
        .spu-title {
            font-size: 11.5pt;
            font-weight: bold;
            text-decoration: underline;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .spu-no {
            font-size: 9pt;
            font-weight: bold;
            color: #005baa;
            margin-top: 2px;
        }
        .spu-preamble-text {
            font-size: 8.5pt;
            color: #1e293b;
            margin: 6px 0 4px 0;
        }
        .spu-meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 8px;
        }
        .spu-meta-table td {
            padding: 2px 0;
            vertical-align: top;
        }
        .spu-label {
            width: 110px;
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

        /* TABEL DETAIL HASIL PENGUJIAN (KOLOM A: HEADER, KOLOM B: DATA) */
        .section-heading {
            font-size: 9pt;
            font-weight: bold;
            color: #005baa;
            text-transform: uppercase;
            margin-bottom: 5px;
            border-bottom: 1.5px solid #e2e8f0;
            padding-bottom: 3px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 14px;
            font-size: 8pt;
        }
        .data-table th {
            background-color: #005baa;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 5px 8px;
            border: 1px solid #004b8d;
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .data-table th.col-no {
            width: 32px;
            text-align: center;
        }
        .data-table th.col-param {
            width: 195px;
        }
        .data-table td {
            padding: 4.5px 8px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            word-wrap: break-word;
            word-break: break-all;
            overflow-wrap: break-word;
        }
        .data-table td.col-no {
            text-align: center;
            color: #64748b;
            font-family: monospace;
        }
        .data-table td.col-param {
            font-weight: 600;
            color: #334155;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        /* Status Pills */
        .badge-ms {
            display: inline-block;
            background-color: #dcfce7;
            color: #166534;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 7pt;
        }
        .badge-tms {
            display: inline-block;
            background-color: #fee2e2;
            color: #991b1b;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 7pt;
        }

        /* Tanda Tangan */
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            page-break-inside: avoid;
        }
        .sign-table td {
            width: 50%;
            vertical-align: top;
            text-align: center;
            font-size: 7.5pt;
            border: none;
            padding: 0;
        }
        .footer-note {
            font-size: 6.5pt;
            color: #64748b;
            font-style: italic;
            margin-top: 8px;
            text-align: center;
        }
    </style>
</head>
<body>

    @php
        $logoPath = public_path('images/logobbpom.png');
        $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;

        // Filter out kolom QR Code
        $filteredHeaders = [];
        foreach($headers as $h) {
            $hl = strtolower(trim($h));
            if (!str_contains($hl, 'qr')) {
                $filteredHeaders[] = $h;
            }
        }
    @endphp

    @foreach($items as $itemIndex => $item)
        @php
            $itemData = is_array($item->data) ? $item->data : [];
            $spuNum = $itemData['no_spu'] ?? $itemData['spu'] ?? $itemData['nomor_spu'] ?? 'SPU.117.05.26.10.16.0036';
            $komoditi = $itemData['komoditi'] ?? 'Narkotika';
            $anggaran = $itemData['anggaran'] ?? 'Pihak Ke 3 - Kepolisian';
            $asalSampling = $itemData['asal_sampling'] ?? $itemData['asal_sampel'] ?? 'Kepolisian';
        @endphp

        <div class="page-container {{ !$loop->last ? 'page-break' : '' }}">
            
            <!-- KOP RESMI BBPOM -->
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

            <!-- FORMAT SPU & BERITA ACARA RESMI -->
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

            <!-- TABEL DATA DETAIL: PARAMETER JADI KOLOM A, NILAI BARIS DI KOLOM B (TANPA QR CODE) -->
            <div class="section-heading">DATA DETAIL HASIL PENGUJIAN SAMPEL #{{ $item->row_number }}</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="col-no">NO</th>
                        <th class="col-param">PARAMETER / INFORMASI</th>
                        <th>NILAI PENGUJIAN / KETERANGAN</th>
                    </tr>
                </thead>
                <tbody>
                    @php $pIndex = 1; @endphp
                    @foreach($filteredHeaders as $h)
                        @php
                            $val = $item->data[$h] ?? '-';
                            $vStr = is_string($val) ? strtolower($val) : '';
                        @endphp
                        @if(!str_contains($vStr, 'otomatis di-generate'))
                            <tr>
                                <td class="col-no">{{ $pIndex++ }}</td>
                                <td class="col-param">
                                    @if(str_contains($h, ' - '))
                                        @php [$mainH, $subH] = explode(' - ', $h, 2); @endphp
                                        <span style="font-size: 6.5pt; color: #64748b; font-weight: normal; text-transform: uppercase;">{{ str_replace('_', ' ', $mainH) }} &rsaquo;</span><br/>
                                        <span>{{ str_replace('_', ' ', $subH) }}</span>
                                    @else
                                        {{ str_replace('_', ' ', $h) }}
                                    @endif
                                </td>
                                <td>
                                    @if(is_bool($val))
                                        {{ $val ? 'Ya' : 'Tidak' }}
                                    @elseif(is_array($val))
                                        {{ json_encode($val) }}
                                    @else
                                        @php $vLower = strtolower((string)$val); @endphp
                                        @if(str_contains($vLower, 'memenuhi syarat') || $vLower === 'ms')
                                            <span class="badge-ms">{{ $val }}</span>
                                        @elseif(str_contains($vLower, 'tidak memenuhi') || $vLower === 'tms')
                                            <span class="badge-tms">{{ $val }}</span>
                                        @else
                                            {{ $val !== '' ? $val : '-' }}
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>

            <!-- TANDA TANGAN DI AKHIR SETIAP BARIS SAMPEL -->
            <table class="sign-table">
                <tr>
                    <td>
                        Mengetahui,<br/>
                        <strong>Penyelia Laboratorium Pengujian</strong>
                        <div style="height: 38px;"></div>
                        <strong>Apt. I Made Sujana, S.Farm.</strong><br/>
                        <span style="color: #64748b; font-size: 6.5pt;">NIP. 19840315 200812 1 002</span>
                    </td>
                    <td>
                        Mataram, {{ date('d F Y') }}<br/>
                        <strong>Kepala Balai Besar POM di Mataram</strong>
                        <div style="height: 38px;"></div>
                        <strong>Yosef Dwi Irwan, S.Si., Apt.</strong><br/>
                        <span style="color: #64748b; font-size: 6.5pt;">NIP. 19780512 200212 1 001</span>
                    </td>
                </tr>
            </table>

            <div class="footer-note">
                Dokumen Hasil Pengujian Sampel Baris #{{ $item->row_number }} &bull; Balai Besar Pengawas Obat dan Makanan di Mataram
            </div>

        </div>
    @endforeach

</body>
</html>