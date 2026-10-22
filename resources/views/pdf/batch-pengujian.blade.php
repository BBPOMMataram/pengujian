<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Tabular Pengujian - {{ $filename }}</title>
    <style>
        @page {
            size: A4 {{ $orientation ?? 'landscape' }};
            margin: 10mm 8mm 12mm 8mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #0f172a;
            font-size: {{ $fontSize ?? '8pt' }};
            line-height: 1.35;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .header-logo {
            width: 65px;
            text-align: center;
            vertical-align: middle;
        }
        .header-logo-badge {
            background-color: #03428e;
            color: #ffffff;
            font-weight: bold;
            font-size: 11pt;
            padding: 8px 4px;
            border-radius: 6px;
            text-align: center;
            letter-spacing: 1px;
            display: inline-block;
        }
        .header-text {
            text-align: center;
            vertical-align: middle;
            padding: 0 8px;
        }
        .header-text h3 {
            margin: 0;
            font-size: 9.5pt;
            font-weight: normal;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-text h2 {
            margin: 1px 0;
            font-size: 10.5pt;
            font-weight: bold;
            color: #03428e;
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
            border-top: 2px solid #03428e;
            border-bottom: 1px solid #03428e;
            height: 2px;
            margin-top: 4px;
            margin-bottom: 10px;
        }
        .report-title-container {
            text-align: center;
            margin-bottom: 10px;
        }
        .report-title {
            font-size: 11pt;
            font-weight: bold;
            text-decoration: underline;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
        }
        .report-meta {
            font-size: 7.5pt;
            color: #475569;
            margin-top: 2px;
        }
        .meta-pill {
            display: inline-block;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 1px 6px;
            margin: 0 2px;
            font-weight: 600;
        }

        /* DATA TABULAR GRID DENGAN HEADER DARI EXCEL */
        .tabular-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 6px;
            margin-bottom: 12px;
        }
        .tabular-table th {
            background-color: #03428e;
            color: #ffffff;
            font-size: {{ $headerFontSize ?? '7.5pt' }};
            font-weight: bold;
            padding: 5px 4px;
            text-align: left;
            border: 1px solid #02336e;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            word-wrap: break-word;
            word-break: break-all;
            overflow-wrap: break-word;
        }
        .tabular-table th.col-no {
            text-align: center;
            width: 28px;
        }
        .tabular-table th.col-qr {
            text-align: center;
            width: 55px;
            background-color: #0f4c9c;
        }
        .tabular-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 4px;
            font-size: {{ $fontSize ?? '7.5pt' }};
            color: #0f172a;
            vertical-align: middle;
            word-wrap: break-word;
            word-break: break-all;
            overflow-wrap: break-word;
        }
        .tabular-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .tabular-table td.col-no {
            text-align: center;
            font-family: monospace;
            color: #64748b;
        }
        .tabular-table td.col-qr {
            text-align: center;
            padding: 2px;
        }

        /* Status Pills */
        .badge-ms {
            display: inline-block;
            background-color: #dcfce7;
            color: #166534;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 6.5pt;
        }
        .badge-tms {
            display: inline-block;
            background-color: #fee2e2;
            color: #991b1b;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 6.5pt;
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
            font-size: 8pt;
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

    <!-- KOP RESMI BBPOM -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                <div class="header-logo-badge">
                    BADAN<br/>POM
                </div>
            </td>
            <td class="header-text">
                <h3>BADAN PENGAWAS OBAT DAN MAKANAN</h3>
                <h2>BALAI BESAR PENGAWAS OBAT DAN MAKANAN DI MATARAM</h2>
                <h1>LABORATORIUM PENGUJIAN TERPADU</h1>
                <p>Jl. Cendrawasih No. 84, Cakranegara, Kota Mataram, Nusa Tenggara Barat 83239 | Telp: (0370) 621926 | Email: bpom_mataram@pom.go.id</p>
            </td>
        </tr>
    </table>

    <div class="double-line"></div>

    <!-- JUDUL DOKUMEN & METADATA -->
    <div class="report-title-container">
        <h2 class="report-title">LAPORAN HASIL PENGUJIAN SAMPEL (TABULAR EXCEL)</h2>
        <div class="report-meta">
            File: <span class="meta-pill">{{ $filename }}</span>
            @if(!empty($sheetName) && $sheetName !== 'Sheet1' && $sheetName !== 'Worksheet')
                | Tab: <span class="meta-pill" style="color: #03428e; background-color: #eff6ff; border-color: #bfdbfe;">{{ $sheetName }}</span>
            @endif
            | Jumlah Data: <span class="meta-pill">{{ $items->count() }} Sampel</span>
            | Dicetak: <span class="meta-pill">{{ $printedAt }} WITA</span>
        </div>
    </div>

    <!-- TABEL UTAMA: HEADERS DARI EXCEL MENJADI HEADER KOLOM TABEL PDF -->
    <table class="tabular-table">
        <thead>
            <tr>
                <th class="col-no">No</th>
                
                @foreach($headers as $h)
                    @if(!str_contains(strtolower($h), 'qr'))
                        <th>
                            @if(str_contains($h, ' - '))
                                @php [$mainH, $subH] = explode(' - ', $h, 2); @endphp
                                <span style="font-size: 6pt; opacity: 0.8; font-weight: normal;">{{ str_replace('_', ' ', $mainH) }}</span><br/>
                                <span>{{ str_replace('_', ' ', $subH) }}</span>
                            @else
                                {{ str_replace('_', ' ', $h) }}
                            @endif
                        </th>
                    @endif
                @endforeach

                <th class="col-qr">QR Code</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                <tr>
                    <td class="col-no">{{ $index + 1 }}</td>

                    @foreach($headers as $h)
                        @if(!str_contains(strtolower($h), 'qr'))
                            <td>
                                @php
                                    $val = $item->data[$h] ?? '-';
                                @endphp
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
                        @endif
                    @endforeach

                    <!-- QR CODE GENERATED -->
                    <td class="col-qr">
                        @if(!empty($item->qr_base64))
                            <img src="data:image/svg+xml;base64,{{ $item->qr_base64 }}" width="45" height="45" style="display: block; margin: 0 auto;" />
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- TANDA TANGAN RESMI -->
    <table class="sign-table">
        <tr>
            <td>
                Mengetahui,<br/>
                <strong>Penyelia Laboratorium Pengujian</strong>
                <div style="height: 38px;"></div>
                <strong>Apt. I Made Sujana, S.Farm.</strong><br/>
                <span style="color: #64748b; font-size: 7pt;">NIP. 19840315 200812 1 002</span>
            </td>
            <td>
                Mataram, {{ date('d F Y') }}<br/>
                <strong>Kepala Balai Besar POM di Mataram</strong>
                <div style="height: 38px;"></div>
                <strong>Yosef Dwi Irwan, S.Si., Apt.</strong><br/>
                <span style="color: #64748b; font-size: 7pt;">NIP. 19780512 200212 1 001</span>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dokumen ini dibuat otomatis oleh Sistem Pengujian Laboratorium BBPOM di Mataram. Seluruh QR Code memuat informasi hasil verifikasi data pengujian.
    </div>

</body>
</html>
