<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Hasil Pengujian Sampel - {{ $filename }}</title>
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
            margin-bottom: 12px;
        }
        .doc-title-container {
            text-align: center;
            margin-bottom: 12px;
        }
        .doc-title {
            font-size: 11.5pt;
            font-weight: bold;
            text-decoration: underline;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-subtitle {
            font-size: 8pt;
            color: #64748b;
            margin-top: 2px;
        }

        /* KOTAK VERIFIKASI QR CODE (PERSIS SEPERTI GAMBAR CONTOH) */
        .verification-box {
            border: 1px solid #bfdbfe;
            background-color: #f0f7ff;
            border-radius: 8px;
            padding: 8px 12px;
            margin-bottom: 12px;
        }
        .verification-table {
            width: 100%;
            border-collapse: collapse;
        }
        .sample-title {
            font-size: 11pt;
            font-weight: bold;
            color: #03428e;
        }
        .sample-sub {
            font-size: 8pt;
            color: #475569;
            margin-top: 2px;
        }
        .qr-info-text {
            font-size: 7.5pt;
            color: #047857;
            margin-top: 3px;
            word-break: break-all;
        }

        /* TABEL DETAIL HASIL PENGUJIAN (KOLOM A: HEADER, KOLOM B: DATA) */
        .section-heading {
            font-size: 9pt;
            font-weight: bold;
            color: #03428e;
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
            background-color: #03428e;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 5px 8px;
            border: 1px solid #03428e;
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
            margin-top: 10px;
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
            margin-top: 6px;
            text-align: center;
        }
    </style>
</head>
<body>

    @foreach($items as $itemIndex => $item)
        <div class="page-container {{ !$loop->last ? 'page-break' : '' }}">
            
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
                        <h1>LABORATORIUM PENGUJIAN</h1>
                        <p>Jl. Cendrawasih No. 84, Cakranegara, Kota Mataram, Nusa Tenggara Barat 83239<br/>
                        Telepon: (0370) 621926 | Faksimili: (0370) 631481 | Email: bpom_mataram@pom.go.id</p>
                    </td>
                </tr>
            </table>

            <div class="double-line"></div>

            <!-- JUDUL DOKUMEN -->
            <div class="doc-title-container">
                <h2 class="doc-title">LAPORAN HASIL PENGUJIAN SAMPEL</h2>
                <div class="doc-subtitle">Nomor: LHP/BBPOM-MTR/{{ date('Y') }}/{{ str_pad((string)$item->id, 5, '0', STR_PAD_LEFT) }}</div>
            </div>

            <!-- KOTAK VERIFIKASI QR CODE (PERSIS SEPERTI GAMBAR CONTOH) -->
            <div class="verification-box">
                <table class="verification-table">
                    <tr>
                        <td style="vertical-align: middle;">
                            <div class="sample-title">
                                {{ $item->display_title ?: 'Sampel Pengujian' }}
                            </div>
                            <div class="sample-sub">
                                Identifikasi: <strong>{{ $item->code ?: '-' }}</strong> &bull; Baris: #{{ $item->row_number }}
                                @if(!empty($sheetName) && $sheetName !== 'Sheet1')
                                    &bull; Tab: <strong>{{ $sheetName }}</strong>
                                @endif
                            </div>
                            <div class="qr-info-text">
                                <strong>Isi QR Code:</strong> {{ $item->qr_content ?: ($item->code ?: '-') }}
                            </div>
                        </td>
                        <td style="width: 95px; text-align: right; vertical-align: middle;">
                            @if(!empty($item->qr_base64))
                                <img src="data:image/svg+xml;base64,{{ $item->qr_base64 }}" width="75" height="75" style="display: inline-block;" />
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            <!-- TABEL DATA DETAIL: HEADER JADI KOLOM A, NILAI BARIS DI KOLOM B -->
            <div class="section-heading">DATA DETAIL HASIL PENGUJIAN</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="col-no">NO</th>
                        <th class="col-param">PARAMETER / KOLOM EXCEL</th>
                        <th>NILAI PENGUJIAN / KETERANGAN</th>
                    </tr>
                </thead>
                <tbody>
                    @php $pIndex = 1; @endphp
                    @foreach($headers as $h)
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
                                @php
                                    $isQrCol = str_contains(strtolower($h), 'qr');
                                    $val = $item->data[$h] ?? '-';
                                @endphp

                                @if($isQrCol)
                                    <span style="color: #64748b; font-style: italic;">(Otomatis Di-generate)</span>
                                @elseif(is_bool($val))
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
                    @endforeach
                </tbody>
            </table>

            <!-- TANDA TANGAN DI AKHIR SETIAP BARIS SAMPEL -->
            <table class="sign-table">
                <tr>
                    <td>
                        Mengetahui,<br/>
                        <strong>Penyelia Laboratorium Pengujian</strong>
                        <div style="height: 32px;"></div>
                        <strong>Apt. I Made Sujana, S.Farm.</strong><br/>
                        <span style="color: #64748b; font-size: 6.5pt;">NIP. 19840315 200812 1 002</span>
                    </td>
                    <td>
                        Mataram, {{ date('d F Y') }}<br/>
                        <strong>Kepala Balai Besar POM di Mataram</strong>
                        <div style="height: 32px;"></div>
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
