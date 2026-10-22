<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Permintaan Uji - {{ $filename }}</title>
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

        /* DATA TABULAR GRID */
        .tabular-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 4px;
            margin-bottom: 10px;
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
            letter-spacing: 0.3px;
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
            margin-top: 12px;
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

        // Ambil data pertama untuk membaca informasi SPU jika ada
        $firstItem = $items->first();
        $firstData = $firstItem && is_array($firstItem->data) ? $firstItem->data : [];
        $spuNum = $firstData['no_spu'] ?? $firstData['spu'] ?? $firstData['nomor_spu'] ?? 'SPU.117.05.26.10.16.0036';
        $jenisSampel = $firstData['jenis_sampel'] ?? $firstData['Jenis Sampel'] ?? null;
        $komoditi = $firstData['komoditi'] ?? $firstData['Komoditi'] ?? ($jenisSampel ? (str_contains($jenisSampel, ' - ') ? explode(' - ', $jenisSampel)[0] : $jenisSampel) : 'Narkotika');
        $anggaran = $firstData['anggaran'] ?? $firstData['Anggaran'] ?? 'Pihak Ke 3 - Kepolisian';
        $asalSampling = $firstData['asal_sampling'] ?? $firstData['Asal Sampling'] ?? $firstData['asal_sampel'] ?? 'Kepolisian';

        // Filter out kolom QR Code
        $filteredHeaders = [];
        $hasNoCol = false;
        foreach($headers as $h) {
            $hl = strtolower(trim($h));
            if (in_array($hl, ['no', 'nomor', 'no.'])) {
                $hasNoCol = true;
            }
            if (!str_contains($hl, 'qr')) {
                $filteredHeaders[] = $h;
            }
        }
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
                <p>Jl. Cendrawasih No. 84, Cakranegara, Kota Mataram, Nusa Tenggara Barat 83239 | Telp: (0370) 621926 | Email: bpom_mataram@pom.go.id</p>
            </td>
        </tr>
    </table>

    <div class="double-line"></div>

    <!-- FORMAT BERITA ACARA SPU -->
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
                <td style="width: 25px;"></td>
                <td class="spu-label">Anggaran</td>
                <td class="spu-sep">:</td>
                <td class="spu-val">{{ $anggaran }}</td>
                <td style="width: 25px;"></td>
                <td class="spu-label">Asal Sampling</td>
                <td class="spu-sep">:</td>
                <td class="spu-val">{{ $asalSampling }}</td>
            </tr>
            @if(!empty($jenisSampel))
            <tr>
                <td class="spu-label">Jenis Sampel</td>
                <td class="spu-sep">:</td>
                <td class="spu-val" colspan="7">{{ $jenisSampel }}</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- TABEL UTAMA: HEADERS DARI TABEL MENJADI HEADER KOLOM TABEL PDF (TANPA QR CODE) -->
    <table class="tabular-table">
        <thead>
            <tr>
                @if(!$hasNoCol)
                    <th class="col-no" style="width: 26px; text-align: center;">No</th>
                @endif
                
                @foreach($filteredHeaders as $h)
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
                                <span style="font-size: 5.5pt; opacity: 0.8; font-weight: normal;">{{ str_replace('_', ' ', $mainH) }}</span><br/>
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
            @foreach($items as $index => $item)
                <tr>
                    @if(!$hasNoCol)
                        <td class="col-no">{{ $index + 1 }}</td>
                    @endif

                    @foreach($filteredHeaders as $h)
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
        Dokumen ini dibuat otomatis oleh Sistem Pengujian Laboratorium BBPOM di Mataram. Seluruh data tercatat sah dan valid.
    </div>

</body>
</html>