<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Permintaan Uji (Gabungan Seluruh Tab) - {{ $filename }}</title>
    <style>
        @page {
            size: A4 {{ $orientation ?? 'landscape' }};
            margin: 10mm 10mm 12mm 10mm;
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
            margin-bottom: 4px;
        }
        .header-logo {
            width: 65px;
            text-align: center;
            vertical-align: middle;
        }
        .header-logo-img {
            max-width: 58px;
            max-height: 58px;
            display: block;
            margin: 0 auto;
        }
        .header-text {
            text-align: center;
            vertical-align: middle;
            padding: 0 8px;
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
            margin-bottom: 8px;
        }

        /* FORMAT SPU & BERITA ACARA */
        .spu-box {
            margin-bottom: 8px;
        }
        .spu-title-box {
            text-align: center;
            margin-bottom: 6px;
        }
        .spu-title {
            font-size: 11pt;
            font-weight: bold;
            text-decoration: underline;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
        }
        .spu-no {
            font-size: 9pt;
            font-weight: bold;
            color: #005baa;
            margin-top: 2px;
        }
        .spu-preamble-text {
            font-size: 8pt;
            color: #1e293b;
            margin: 4px 0;
        }
        .spu-meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 6px;
        }
        .spu-meta-table td {
            padding: 1.5px 0;
            vertical-align: top;
        }
        .spu-label {
            width: 100px;
            font-weight: 600;
            color: #334155;
        }
        .spu-sep {
            width: 12px;
            text-align: center;
            color: #64748b;
        }
        .spu-val {
            color: #0f172a;
            font-weight: 600;
        }

        /* SECTION TAB BANNER */
        .sheet-section-title {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-left: 4px solid #005baa;
            padding: 4px 8px;
            margin-top: 8px;
            margin-bottom: 6px;
            font-size: 8.5pt;
            font-weight: bold;
            color: #005baa;
        }

        /* DATA TABULAR GRID */
        .tabular-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 8px;
        }
        .tabular-table th {
            background-color: #005baa;
            color: #ffffff;
            font-size: {{ $headerFontSize ?? '7.5pt' }};
            font-weight: bold;
            padding: 5px 4px;
            text-align: left;
            border: 1px solid #004b8d;
            text-transform: uppercase;
            word-wrap: break-word;
            word-break: break-all;
            overflow-wrap: break-word;
        }
        .tabular-table th.col-no {
            text-align: center;
            width: 28px;
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

        /* VERTICAL PER-ROW DATA TABLE (UNTUK SHEET > 15 KOLOM) */
        .vertical-data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 10px;
            font-size: 7.5pt;
        }
        .vertical-data-table th {
            background-color: #005baa;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 4px 6px;
            border: 1px solid #004b8d;
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
            width: 180px;
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
            margin-top: 6px;
            text-align: center;
        }
    </style>
</head>
<body>

    @php
        $logoPath = public_path('images/logobbpom.png');
        $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;

        // Ambil info SPU default
        $firstGroup = reset($sheetGroups);
        $firstItemInGroup = $firstGroup && isset($firstGroup['items']) ? $firstGroup['items']->first() : null;
        $gData = $firstItemInGroup && is_array($firstItemInGroup->data) ? $firstItemInGroup->data : [];
        $spuNum = $gData['no_spu'] ?? $gData['spu'] ?? $gData['nomor_spu'] ?? 'SPU.117.05.26.10.16.0036';
        $jenisSampel = $gData['jenis_sampel'] ?? $gData['Jenis Sampel'] ?? null;
        $komoditi = $gData['komoditi'] ?? $gData['Komoditi'] ?? ($jenisSampel ? (str_contains($jenisSampel, ' - ') ? explode(' - ', $jenisSampel)[0] : $jenisSampel) : 'Narkotika');
        $anggaran = $gData['anggaran'] ?? $gData['Anggaran'] ?? 'Pihak Ke 3 - Kepolisian';
        $asalSampling = $gData['asal_sampling'] ?? $gData['Asal Sampling'] ?? $gData['asal_sampel'] ?? 'Kepolisian';
    @endphp

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
                <h1>LABORATORIUM PENGUJIAN TERPADU</h1>
                <p>Jl. Cendrawasih No. 84, Cakranegara, Kota Mataram, NTB 83239 | Telp: (0370) 621926 | Email: bpom_mataram@pom.go.id</p>
            </td>
        </tr>
    </table>

    <div class="double-line"></div>

    <!-- FORMAT BERITA ACARA SPU -->
    <div class="spu-box">
        <div class="spu-title-box">
            <h2 class="spu-title">SURAT PERMINTAAN UJI (GABUNGAN SELURUH TAB)</h2>
            <div class="spu-no">{{ $spuNum }}</div>
        </div>

        <p class="spu-preamble-text">Bersama ini disampaikan daftar sampel:</p>
        
        <table class="spu-meta-table">
            <tr>
                <td class="spu-label">Komoditi</td>
                <td class="spu-sep">:</td>
                <td class="spu-val">{{ $komoditi }}</td>
                <td style="width: 25px;"></td>
                <td class="spu-label">Anggaran</td>
                <td class="spu-sep">:</td>
                <td class="spu-val">{{ $anggaran }}</td>
                <td style="width: 25px;"></td>
                <td class="spu-label">Asal Sampling</td>
                <td class="spu-sep">:</td>
                <td class="spu-val">{{ $asalSampling }}</td>
            </tr>
        </table>
    </div>

    <!-- ITERASI SETIAP TAB / SHEET -->
    @foreach($sheetGroups as $sIdx => $group)
        @php
            $groupHeaders = [];
            $hasNoCol = false;
            foreach($group['headers'] as $h) {
                $hl = strtolower(trim($h));
                if (in_array($hl, ['no', 'nomor', 'no.'])) {
                    $hasNoCol = true;
                }
                if (!str_contains($hl, 'qr')) {
                    $groupHeaders[] = $h;
                }
            }
        @endphp

        <div class="sheet-section-title">
            TAB {{ $sIdx + 1 }}: {{ strtoupper($group['sheet_name']) }} ({{ $group['items']->count() }} Sampel)
        </div>

        @if($group['layout'] === 'vertical')
            <!-- JIKA KOLOM > 15: FORMAT VERTICAL PER BARIS DATA -->
            @foreach($group['items'] as $item)
                <div style="margin-bottom: 8px; page-break-inside: avoid;">
                    <div style="font-size: 8pt; font-weight: bold; color: #005baa; margin-bottom: 2px;">
                        Sampel #{{ $item->row_number }}: {{ $item->display_title ?: 'Sampel Pengujian' }}
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
                            @foreach($groupHeaders as $h)
                                @php
                                    $val = $item->data[$h] ?? '-';
                                    $vStr = is_string($val) ? strtolower($val) : '';
                                @endphp
                                @if(!str_contains($vStr, 'otomatis di-generate'))
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
                </div>
            @endforeach

        @else
            <!-- JIKA KOLOM <= 15: FORMAT TABULAR GRID HORIZONTAL (TANPA QR CODE) -->
            <table class="tabular-table">
                <thead>
                    <tr>
                        @if(!$hasNoCol)
                            <th class="col-no" style="width: 26px; text-align: center;">No</th>
                        @endif
                        @foreach($groupHeaders as $h)
                            @php
                                $hLower = strtolower(trim($h));
                                $isNo = in_array($hLower, ['no', 'nomor', 'no.']);
                            @endphp

                            @if($isNo)
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
                    </tr>
                </thead>
                <tbody>
                    @foreach($group['items'] as $index => $item)
                        <tr>
                            @if(!$hasNoCol)
                                <td class="col-no">{{ $index + 1 }}</td>
                            @endif
                            @foreach($groupHeaders as $h)
                                @php
                                    $hLower = strtolower(trim($h));
                                    $isNo = in_array($hLower, ['no', 'nomor', 'no.']);
                                    $val = $item->data[$h] ?? '-';
                                @endphp

                                @if($isNo)
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
                                            @php 
                                                $vStr = (string)$val;
                                                $vLower = strtolower($vStr);
                                            @endphp
                                            @if(str_contains($vLower, 'otomatis di-generate'))
                                                <span style="color: #94a3b8;">-</span>
                                            @elseif(str_contains($vLower, 'memenuhi syarat') || $vLower === 'ms')
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
        Dokumen ini merupakan Surat Permintaan Uji resmi gabungan seluruh tab dari satu file workbook terpadu BBPOM di Mataram.
    </div>

</body>
</html>