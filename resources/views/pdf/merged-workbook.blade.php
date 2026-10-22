<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Lengkap Gabungan Seluruh Tab - {{ $filename }}</title>
    <style>
        @page {
            size: A4 {{ $orientation ?? 'landscape' }};
            margin: 12mm 10mm 15mm 10mm;
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

        /* SECTION TAB BANNER */
        .sheet-section-title {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-left: 4px solid #03428e;
            padding: 5px 8px;
            margin-top: 10px;
            margin-bottom: 8px;
            font-size: 9pt;
            font-weight: bold;
            color: #03428e;
        }

        /* DATA TABULAR GRID */
        .tabular-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 10px;
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

        /* VERTICAL PER-ROW DATA TABLE (UNTUK SHEET > 15 KOLOM) */
        .verification-box {
            border: 1px solid #bfdbfe;
            background-color: #f0f7ff;
            border-radius: 6px;
            padding: 6px 10px;
            margin-bottom: 8px;
        }
        .verification-table {
            width: 100%;
            border-collapse: collapse;
        }
        .vertical-data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 12px;
            font-size: 7.5pt;
        }
        .vertical-data-table th {
            background-color: #03428e;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 4px 6px;
            border: 1px solid #03428e;
            text-transform: uppercase;
        }
        .vertical-data-table td {
            padding: 3.5px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            word-wrap: break-word;
            word-break: break-all;
        }
        .vertical-data-table td.col-no {
            width: 28px;
            text-align: center;
            font-family: monospace;
            color: #64748b;
        }
        .vertical-data-table td.col-param {
            width: 200px;
            font-weight: 600;
            color: #334155;
        }
        .vertical-data-table tr:nth-child(even) td {
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

        .page-break {
            page-break-after: always;
        }

        /* Signatures */
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
                <p>Jl. Cendrawasih No. 84, Cakranegara, Kota Mataram, NTB 83239 | Telp: (0370) 621926 | Email: bpom_mataram@pom.go.id</p>
            </td>
        </tr>
    </table>

    <div class="double-line"></div>

    <!-- JUDUL LAPORAN GABUNGAN -->
    <div class="report-title-container">
        <h2 class="report-title">LAPORAN LENGKAP PENGUJIAN (GABUNGAN SELURUH TAB / SHEET)</h2>
        <div class="report-meta">
            File: <span class="meta-pill">{{ $filename }}</span>
            | Total Tab: <span class="meta-pill">{{ count($sheetGroups) }} Tab</span>
            | Total Keseluruhan: <span class="meta-pill">{{ $totalItems }} Sampel</span>
            | Dicetak: <span class="meta-pill">{{ $printedAt }} WITA</span>
        </div>
    </div>

    <!-- ITERASI SETIAP TAB / SHEET -->
    @foreach($sheetGroups as $sIdx => $group)
        <div class="sheet-section-title">
            TAB {{ $sIdx + 1 }}: {{ strtoupper($group['sheet_name']) }} ({{ $group['items']->count() }} Sampel)
            @if($group['layout'] === 'vertical')
                <span style="font-size: 7.5pt; font-weight: normal; color: #475569;">&bull; Format: Detail Per-Baris ({{ count($group['headers']) }} Kolom)</span>
            @endif
        </div>

        @if($group['layout'] === 'vertical')
            <!-- JIKA KOLOM > 15: FORMAT VERTICAL PER BARIS DATA (PERSIS SEPERTI SCREENSHOT) -->
            @foreach($group['items'] as $item)
                <div style="margin-bottom: 12px; page-break-inside: avoid;">
                    <div class="verification-box">
                        <table class="verification-table">
                            <tr>
                                <td style="vertical-align: middle;">
                                    <div style="font-size: 9.5pt; font-weight: bold; color: #03428e;">
                                        {{ $item->display_title ?: 'Sampel Pengujian' }}
                                    </div>
                                    <div style="font-size: 7.5pt; color: #475569; margin-top: 2px;">
                                        Baris: #{{ $item->row_number }} &bull; Tab: {{ $group['sheet_name'] }}
                                    </div>
                                    <div style="font-size: 7pt; color: #047857; margin-top: 2px;">
                                        <strong>Isi QR Code:</strong> {{ $item->qr_content ?: ($item->code ?: '-') }}
                                    </div>
                                </td>
                                <td style="width: 75px; text-align: right; vertical-align: middle;">
                                    @if(!empty($item->qr_base64))
                                        <img src="data:image/svg+xml;base64,{{ $item->qr_base64 }}" width="55" height="55" style="display: inline-block;" />
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                    <table class="vertical-data-table">
                        <thead>
                            <tr>
                                <th style="width: 28px; text-align: center;">NO</th>
                                <th style="width: 180px;">PARAMETER / KOLOM EXCEL</th>
                                <th>NILAI PENGUJIAN / KETERANGAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $pIdx = 1; @endphp
                            @foreach($group['headers'] as $h)
                                <tr>
                                    <td class="col-no">{{ $pIdx++ }}</td>
                                    <td class="col-param">
                                        @if(str_contains($h, ' - '))
                                            @php [$mainH, $subH] = explode(' - ', $h, 2); @endphp
                                            <span style="font-size: 6pt; color: #64748b; font-weight: normal; text-transform: uppercase;">{{ str_replace('_', ' ', $mainH) }} &rsaquo;</span><br/>
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
                </div>
            @endforeach

        @else
            <!-- JIKA KOLOM <= 15: FORMAT TABULAR GRID HORIZONTAL BIASA -->
            @php
                $hasNoCol = false;
                $hasQrCol = false;
                foreach($group['headers'] as $h) {
                    $hl = strtolower(trim($h));
                    if (in_array($hl, ['no', 'nomor', 'no.'])) {
                        $hasNoCol = true;
                    }
                    if (str_contains($hl, 'qr')) {
                        $hasQrCol = true;
                    }
                }
            @endphp
            <table class="tabular-table">
                <thead>
                    <tr>
                        @if(!$hasNoCol)
                            <th class="col-no" style="width: 26px; text-align: center;">No</th>
                        @endif
                        @foreach($group['headers'] as $h)
                            @php
                                $hLower = strtolower(trim($h));
                                $isQr = str_contains($hLower, 'qr');
                                $isNo = in_array($hLower, ['no', 'nomor', 'no.']);
                            @endphp

                            @if($isQr)
                                <th class="col-qr" style="width: 52px; text-align: center;">QR Code</th>
                            @elseif($isNo)
                                <th class="col-no" style="width: 26px; text-align: center;">{{ $h }}</th>
                            @else
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
                        @if(!$hasQrCol)
                            <th class="col-qr" style="width: 52px; text-align: center;">QR Code</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($group['items'] as $index => $item)
                        <tr>
                            @if(!$hasNoCol)
                                <td class="col-no">{{ $index + 1 }}</td>
                            @endif
                            @foreach($group['headers'] as $h)
                                @php
                                    $hLower = strtolower(trim($h));
                                    $isQr = str_contains($hLower, 'qr');
                                    $isNo = in_array($hLower, ['no', 'nomor', 'no.']);
                                    $val = $item->data[$h] ?? '-';
                                @endphp

                                @if($isQr)
                                    <td class="col-qr" style="text-align: center; vertical-align: middle;">
                                        @if(!empty($item->qr_base64))
                                            <img src="data:image/svg+xml;base64,{{ $item->qr_base64 }}" width="42" height="42" style="display: block; margin: 0 auto;" />
                                        @else
                                            <span style="font-size: 6pt; color: #64748b;">(QR)</span>
                                        @endif
                                    </td>
                                @elseif($isNo)
                                    <td class="col-no">{{ $val !== '' ? $val : ($index + 1) }}</td>
                                @else
                                    <td>
                                        @if(is_bool($val))
                                            {{ $val ? 'Ya' : 'Tidak' }}
                                        @elseif(is_array($val))
                                            {{ json_encode($val) }}
                                        @elseif(is_null($val) || $val === '')
                                            <span style="color: #94a3b8;">-</span>
                                        @else
                                            @php $vLower = strtolower((string)$val); @endphp
                                            @if(str_contains($vLower, 'memenuhi syarat') || $vLower === 'ms')
                                                <span class="badge-ms">{{ $val }}</span>
                                            @elseif(str_contains($vLower, 'tidak memenuhi') || $vLower === 'tms')
                                                <span class="badge-tms">{{ $val }}</span>
                                            @else
                                                {{ $val }}
                                            @endif
                                        @endif
                                    </td>
                                @endif
                            @endforeach
                            @if(!$hasQrCol)
                                <td class="col-qr" style="text-align: center; vertical-align: middle;">
                                    @if(!empty($item->qr_base64))
                                        <img src="data:image/svg+xml;base64,{{ $item->qr_base64 }}" width="42" height="42" style="display: block; margin: 0 auto;" />
                                    @else
                                        <span style="font-size: 6pt; color: #64748b;">(QR)</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

    <!-- TANDA TANGAN DI AKHIR LAPORAN GABUNGAN -->
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
        Dokumen ini merupakan laporan hasil pengujian gabungan seluruh tab dari satu file workbook terpadu BBPOM di Mataram.
    </div>

</body>
</html>
