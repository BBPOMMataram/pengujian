<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengujian - {{ $item->display_title }}</title>
    <style>
        @page {
            margin: 20mm 15mm 20mm 15mm;
            size: A4 portrait;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11pt;
            color: #1e293b;
            line-height: 1.45;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .header-logo {
            width: 75px;
            text-align: center;
            vertical-align: middle;
        }
        .header-logo-badge {
            background-color: #03428e;
            color: #ffffff;
            font-weight: bold;
            font-size: 13pt;
            padding: 10px 6px;
            border-radius: 8px;
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
            font-size: 11pt;
            font-weight: normal;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-text h2 {
            margin: 2px 0;
            font-size: 12pt;
            font-weight: bold;
            color: #03428e;
            text-transform: uppercase;
        }
        .header-text h1 {
            margin: 2px 0;
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }
        .header-text p {
            margin: 3px 0 0 0;
            font-size: 8pt;
            color: #475569;
        }
        .double-line {
            border-top: 2px solid #03428e;
            border-bottom: 1px solid #03428e;
            height: 2px;
            margin-top: 4px;
            margin-bottom: 16px;
        }
        .doc-title-container {
            text-align: center;
            margin-bottom: 18px;
        }
        .doc-title {
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-subtitle {
            font-size: 9pt;
            color: #64748b;
            margin-top: 3px;
        }
        .verification-box {
            border: 1px solid #bfdbfe;
            background-color: #f0f7ff;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 18px;
            width: 100%;
            box-sizing: border-box;
        }
        .verification-table {
            width: 100%;
            border-collapse: collapse;
        }
        .badge-verified {
            background-color: #10b981;
            color: #ffffff;
            font-size: 8pt;
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
            text-transform: uppercase;
        }
        .section-heading {
            font-size: 10pt;
            font-weight: bold;
            color: #03428e;
            text-transform: uppercase;
            margin-top: 14px;
            margin-bottom: 6px;
            border-bottom: 1.5px solid #e2e8f0;
            padding-bottom: 4px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 16px;
            font-size: 9pt;
        }
        .data-table th {
            background-color: #03428e;
            color: #ffffff;
            font-weight: 600;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #03428e;
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
        .notes-box {
            font-size: 8pt;
            color: #64748b;
            background-color: #f8fafc;
            border-left: 3px solid #03428e;
            padding: 8px 12px;
            margin-top: 15px;
            margin-bottom: 25px;
        }
        .sign-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .sign-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 9pt;
        }
        .sign-qr-box {
            margin: 8px auto;
            display: inline-block;
            border: 1px dashed #cbd5e1;
            padding: 4px;
            border-radius: 4px;
            background: #ffffff;
        }
        .footer-note {
            margin-top: 30px;
            text-align: center;
            font-size: 7.5pt;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT RESMI BPOM -->
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
        <div class="doc-subtitle">Nomor: LHP/BBPOM-MTR/{{ date('Y') }}/{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</div>
    </div>

    <!-- KOTAK VERIFIKASI QR CODE -->
    <div class="verification-box">
        <table class="verification-table">
            <tr>
                <td style="vertical-align: middle;">
                    <div style="font-size: 10.5pt; font-weight: bold; color: #03428e;">
                        {{ $item->display_title ?? 'Sampel Pengujian' }}
                    </div>
                    <div style="font-size: 8.5pt; color: #475569; margin-top: 2px;">
                        Identifikasi: <strong>{{ $item->display_code ?? '-' }}</strong> &bull; Baris: #{{ $item->row_number }}
                    </div>
                    <div style="font-size: 8pt; color: #047857; margin-top: 3px; word-break: break-all;">
                        <strong>Isi QR Code:</strong> {{ $item->qr_content ?: ($item->code ?: '-') }}
                    </div>
                </td>
                <td style="width: 110px; text-align: right; vertical-align: middle;">
                    @if(!empty($qrBase64))
                        <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" width="90" height="90" style="display: inline-block;" />
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- TABEL DETAIL DATA DARI EXCEL -->
    <div class="section-heading">DATA DETAIL HASIL PENGUJIAN</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35px; text-align: center;">No</th>
                <th style="width: 220px;">Parameter / Kolom Excel</th>
                <th>Nilai Pengujian / Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; @endphp
            @if(is_array($item->data))
                @foreach($item->data as $key => $val)
                    <tr>
                        <td style="text-align: center; color: #64748b;">{{ $i++ }}</td>
                        <td style="font-weight: 600; color: #334155;">
                            @if(str_contains($key, ' - '))
                                @php [$mainK, $subK] = explode(' - ', $key, 2); @endphp
                                <span style="font-size: 8pt; color: #64748b; font-weight: normal; text-transform: uppercase;">{{ str_replace('_', ' ', $mainK) }} &rsaquo;</span><br/>
                                <span style="font-size: 9.5pt; color: #0f172a; font-weight: bold;">{{ str_replace('_', ' ', $subK) }}</span>
                            @else
                                <span style="font-size: 9.5pt; text-transform: capitalize;">{{ str_replace('_', ' ', $key) }}</span>
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
                @endforeach
            @endif
        </tbody>
    </table>

    <!-- TANDA TANGAN DIGITAL -->
    <table class="sign-table">
        <tr>
            <td>
                Mengetahui,<br/>
                <strong>Manajer Teknis / Penyelia</strong>
                <div style="height: 60px;"></div>
                <strong>Apt. I Made Sujana, S.Farm.</strong><br/>
                <span style="color: #64748b; font-size: 8pt;">NIP. 19840315 200812 1 002</span>
            </td>
            <td>
                Mataram, {{ now()->translatedFormat('d F Y') }}<br/>
                <strong>Analis Penguji</strong>
                <div style="height: 60px;"></div>
                <strong>{{ $item->data['nama_analis'] ?? $item->data['analis'] ?? $item->data['penguji'] ?? 'Lalu Naofal Arkan, S.T.' }}</strong><br/>
                <span style="color: #64748b; font-size: 8pt;">Petugas Laboratorium Pengujian</span>
            </td>
        </tr>
    </table>

</body>
</html>
