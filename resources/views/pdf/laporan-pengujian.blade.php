<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengujian - {{ $item->code }}</title>
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
            margin-bottom: 16px;
            font-size: 9.5pt;
        }
        .data-table th {
            background-color: #03428e;
            color: #ffffff;
            font-weight: 600;
            text-align: left;
            padding: 6px 10px;
            border: 1px solid #03428e;
            font-size: 9pt;
            text-transform: uppercase;
        }
        .data-table td {
            padding: 6px 10px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
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
                    <div style="margin-bottom: 4px;">
                        <span class="badge-verified">&#10003; TERVERIFIKASI RESMI</span>
                    </div>
                    <div style="font-size: 10.5pt; font-weight: bold; color: #03428e;">
                        {{ $item->name ?? 'Sampel Pengujian' }}
                    </div>
                    <div style="font-size: 8.5pt; color: #475569; margin-top: 2px;">
                        Kode Sampel: <strong>{{ $item->code ?? '-' }}</strong> &bull; Baris Excel: #{{ $item->row_number }}
                    </div>
                    <div style="font-size: 8pt; color: #64748b; margin-top: 4px;">
                        Token Verifikasi: <code style="font-family: monospace;">{{ $item->qr_token }}</code>
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
                        <td style="font-weight: 600; color: #334155; text-transform: capitalize;">
                            {{ str_replace(['_', '-'], ' ', $key) }}
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

    <!-- CATATAN LABORATORIUM -->
    <div class="notes-box">
        <strong>Catatan Laboratorium:</strong><br/>
        1. Hasil pengujian di atas hanya berlaku untuk sampel yang diterima dan diuji di laboratorium BBPOM di Mataram.<br/>
        2. Dokumen ini adalah dokumen resmi yang diterbitkan secara elektronik melalui Sistem Verifikasi QR Code Laboratorium.<br/>
        3. Keabsahan dokumen ini dapat diverifikasi kapan saja dengan memindai QR Code yang tertera di dokumen ini.
    </div>

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
                <strong>{{ $item->data['nama_analis'] ?? $item->data['analis'] ?? $item->data['penguji'] ?? 'Apt. Lalu Naofal Arkan, S.Farm.' }}</strong><br/>
                <span style="color: #64748b; font-size: 8pt;">Petugas Laboratorium Pengujian</span>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dicetak otomatis oleh Sistem Informasi Laboratorium BBPOM di Mataram pada {{ now()->translatedFormat('d F Y H:i:s') }} WITA &bull; Halaman 1 / 1
    </div>

</body>
</html>
