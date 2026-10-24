<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Permintaan Uji - {{ $filename }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 12mm 10mm 12mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #000000;
            font-size: 8pt;
            line-height: 1.35;
        }

        /* HEADER DOKUMEN RESMI SPU */
        .spu-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
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
        }
        .spu-doc-no {
            font-size: 10.5pt;
            font-weight: bold;
            color: #000;
            margin-top: 2px;
            letter-spacing: 0.5px;
        }
        .spu-qr-td {
            width: 85px;
            vertical-align: middle;
            text-align: right;
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
            color: #000;
        }
        .spu-meta-sep {
            width: 12px;
            text-align: center;
            color: #000;
        }
        .spu-meta-val {
            font-weight: normal;
            color: #000;
        }

        .spu-instruction-text {
            font-size: 8.5pt;
            margin: 4px 0 6px 0;
            color: #000;
        }

        /* TABEL SURAT PERMINTAAN UJI (3 KOLOM RESMI SESUAI PERMINTAAN) */
        .spu-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 8pt;
            margin-top: 4px;
            margin-bottom: 12px;
        }
        .spu-table th {
            border: 1px solid #000000;
            background-color: #ffffff;
            color: #000000;
            font-weight: bold;
            padding: 5px 6px;
            text-align: left;
            vertical-align: middle;
            font-size: 8pt;
        }
        .spu-table th.center {
            text-align: center;
        }
        .spu-table td {
            border: 1px solid #000000;
            padding: 6px 6px;
            vertical-align: top;
            color: #000000;
            word-wrap: break-word;
            word-break: break-all;
            overflow-wrap: break-word;
        }
        .spu-table td.center {
            text-align: center;
        }

        .sample-code-header {
            font-weight: bold;
            margin-bottom: 4px;
            color: #000;
        }
        .bullet-list {
            margin: 0;
            padding-left: 14px;
            list-style-type: disc;
        }
        .bullet-list li {
            margin-bottom: 3px;
            line-height: 1.35;
        }

        /* TANDA TANGAN RESMI 3 PEJABAT SPU BBPOM MATARAM */
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            page-break-inside: avoid;
        }
        .sign-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            font-size: 8.5pt;
            color: #000000;
            padding: 0 8px;
            border: none;
        }
        .sign-title {
            line-height: 1.25;
            min-height: 48px;
        }
        .sign-space {
            height: 52px;
        }
        .sign-name {
            font-weight: bold;
            text-decoration: underline;
            color: #000000;
            font-size: 9pt;
        }
        .sign-nip {
            font-size: 8pt;
            color: #000000;
            margin-top: 2px;
        }

        .footer-note {
            font-size: 7pt;
            color: #64748b;
            font-style: italic;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>

    @php
        $logoPath = public_path('images/logobbpom.png');
        $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;

        // Ambil data pertama untuk membaca informasi header SPU
        $firstItem = $items->first();
        $firstData = ($firstItem && is_array($firstItem->data)) ? $firstItem->data : [];
        $firstMeta = $firstData['_meta'] ?? [];

        // Nomor SPU dinamis
        $spuNum = $firstMeta['no_spu'] 
            ?? $firstData['no_spu'] 
            ?? $firstData['Nomor SPU'] 
            ?? $firstData['spu'] 
            ?? $firstData['nomor_spu'] 
            ?? 'SPU.117.05.26.10.16.0036';

        // Komoditi, Anggaran, Asal Sampling
        $jenisSampelHead = $firstData['jenis_sampel'] ?? $firstData['Jenis Sampel'] ?? null;
        $komoditi = $firstMeta['komoditi'] 
            ?? $firstData['komoditi'] 
            ?? $firstData['Komoditi'] 
            ?? ($jenisSampelHead ? (str_contains($jenisSampelHead, ' - ') ? explode(' - ', $jenisSampelHead)[0] : $jenisSampelHead) : 'Narkotika');
        
        $anggaran = $firstMeta['anggaran'] 
            ?? $firstData['anggaran'] 
            ?? $firstData['Anggaran'] 
            ?? 'Pihak Ke 3 - Kepolisian';
        
        $asalSampling = $firstMeta['asal_sampling'] 
            ?? $firstData['asal_sampling'] 
            ?? $firstData['Asal Sampling'] 
            ?? $firstData['asal_sampel'] 
            ?? 'Kepolisian';

        // Tanggal format Indonesia
        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $rawTgl = $firstMeta['tanggal_spu'] 
            ?? $firstData['tanggal_spu'] 
            ?? $firstData['Tanggal SPU'] 
            ?? $firstData['tanggal_diterima'] 
            ?? $firstData['Tanggal Diterima'] 
            ?? null;

        $tglBatchFormatted = null;
        if (!empty($rawTgl)) {
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', trim($rawTgl), $tm)) {
                $bln = (int)$tm[2];
                $tglBatchFormatted = (int)$tm[3] . ' ' . ($bulanIndo[$bln] ?? '') . ' ' . $tm[1];
            } elseif (preg_match('/^(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})$/', trim($rawTgl), $tm)) {
                $tglBatchFormatted = trim($rawTgl);
            }
        }
        if (empty($tglBatchFormatted)) {
            $now = now();
            $tglBatchFormatted = $now->day . ' ' . ($bulanIndo[$now->month] ?? $now->format('F')) . ' ' . $now->year;
        }

        // Pejabat resmi 3 kolom SPU
        $petugasPenerimaB = $firstMeta['petugas_penerima'] 
            ?? $firstData['petugas_penerima'] 
            ?? $firstData['Petugas Penerima'] 
            ?? 'Nirmala Nurhasanah, STP';
        $nipPetugasPenerimaB = $firstMeta['nip_petugas_penerima'] 
            ?? $firstData['nip_petugas_penerima'] 
            ?? '199205172023212054';

        $managerAdminB = $firstMeta['manager_administrasi'] 
            ?? $firstData['manager_administrasi'] 
            ?? $firstData['Manager Administrasi'] 
            ?? 'Basuki Murdi Hartono, SH.';
        $nipManagerAdminB = $firstMeta['nip_manager_administrasi'] 
            ?? $firstData['nip_manager_administrasi'] 
            ?? '197904052005011002';

        $kepalaBalaiB = $firstMeta['kepala_balai'] 
            ?? $firstData['kepala_balai'] 
            ?? $firstData['Kepala Balai'] 
            ?? 'Yogi Abaso Mataram, S.Si., Apt';
        $nipKepalaBalaiB = $firstMeta['nip_kepala_balai'] 
            ?? $firstData['nip_kepala_balai'] 
            ?? '198101242009121002';
    @endphp

    <!-- HEADER RESMI SPU BBPOM -->
    <table class="spu-header-table">
        <tr>
            <td class="spu-logo-td">
                @if(!empty($logoBase64))
                    <img src="data:image/png;base64,{{ $logoBase64 }}" class="spu-logo-img" alt="Logo BBPOM" />
                @endif
            </td>
            <td class="spu-title-td">
                <div class="spu-doc-title">Surat Permintaan Uji</div>
                <div class="spu-doc-no">{{ $spuNum }}</div>
            </td>
            <td class="spu-qr-td"></td>
        </tr>
    </table>

    <!-- PREAMBLE SPU -->
    <div class="spu-preamble">
        <p>Bersama ini disampaikan daftar sampel:</p>
        <table class="spu-meta-table">
            <tr>
                <td class="spu-meta-lbl">Komoditi</td>
                <td class="spu-meta-sep">:</td>
                <td class="spu-meta-val">{{ $komoditi }}</td>
            </tr>
            <tr>
                <td class="spu-meta-lbl">Anggaran</td>
                <td class="spu-meta-sep">:</td>
                <td class="spu-meta-val">{{ $anggaran }}</td>
            </tr>
            <tr>
                <td class="spu-meta-lbl">Asal Sampling</td>
                <td class="spu-meta-sep">:</td>
                <td class="spu-meta-val">{{ $asalSampling }}</td>
            </tr>
        </table>
    </div>

    <div class="spu-instruction-text">
        Untuk dilakukan pengujian sesuai ruang lingkup
    </div>

    <!-- TABEL UTAMA SPU: HANYA 3 KOLOM SESUAI INSTRUKSI (No, Identitas Sampel, Lokasi Sampel) -->
    <table class="spu-table">
        <thead>
            <tr>
                <th class="center" style="width: 38px;">No.</th>
                <th style="width: 67%;">Identitas Sample</th>
                <th style="width: 28%;">Lokasi Sampel</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                @php
                    $d = is_array($item->data) ? $item->data : [];

                    // Ekstraksi Identitas Sampel
                    $kode = $d['Kode Sampel'] ?? $d['kode_sampel'] ?? ($item->display_title ?: '-');
                    $namaProd = $d['Nama Produk / Sampel'] ?? $d['Nama Produk'] ?? $d['nama_produk'] ?? $d['nama_sampel'] ?? $d['Nama Sampel'] ?? '-';
                    $rawNie = $d['nie'] ?? $d['NIE'] ?? $d['nomor_izin_edar'] ?? '';
                    $nieVal = ($rawNie !== '' && $rawNie !== '-') ? $rawNie : '-';
                    $jenisVal = $d['jenis_sampel'] ?? $d['Jenis Sampel'] ?? '-';

                    // Ekstraksi Lokasi Sampel (diambil dari data kolom catatan atau aturan khusus narkotika)
                    $catatanVal = $d['catatan'] ?? $d['Catatan'] ?? '';
                    $lokasiVal = $d['lokasi_sampel'] ?? $d['Lokasi Sampel'] ?? '';

                    // Jika lokasi_sampel sudah dihitung oleh controller/import
                    if (!empty($lokasiVal) && $lokasiVal !== '-') {
                        $lokasiTampil = $lokasiVal;
                    } elseif (!empty($catatanVal) && $catatanVal !== '-') {
                        $lokasiTampil = $catatanVal;
                    } elseif (str_contains(strtolower($jenisVal), 'narkotika') || str_contains(strtolower($komoditi), 'narkotika')) {
                        $lokasiTampil = 'Kimia';
                    } else {
                        $lokasiTampil = '-';
                    }
                @endphp
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>
                        @if(!empty($kode) && $kode !== '-')
                            <div class="sample-code-header">Kode Sampel: {{ $kode }}</div>
                        @endif
                        <ul class="bullet-list">
                            <li><strong>Nama Produk:</strong> {{ $namaProd }}</li>
                            <li><strong>NIE:</strong> {{ $nieVal }}</li>
                            <li><strong>Jenis Sampel:</strong> {{ $jenisVal }}</li>
                        </ul>
                    </td>
                    <td>
                        {{ $lokasiTampil }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- TANDA TANGAN 3 PEJABAT RESMI SPU BBPOM MATARAM -->
    <table class="sign-table">
        <tr>
            <td>
                <div class="sign-title">
                    Tanggal, {{ $tglBatchFormatted }}<br/>
                    Pemohon / Pengirim Sampel<br/>
                    (Petugas Penerima Sampel)
                </div>
                <div class="sign-space"></div>
                <div class="sign-name">{{ $petugasPenerimaB }}</div>
                <div class="sign-nip">NIP. {{ $nipPetugasPenerimaB }}</div>
            </td>
            <td>
                <div class="sign-title">
                    Tanggal, {{ $tglBatchFormatted }}<br/>
                    Penerima Sampel<br/>
                    (Manager Administrasi)
                </div>
                <div class="sign-space"></div>
                <div class="sign-name">{{ $managerAdminB }}</div>
                <div class="sign-nip">NIP. {{ $nipManagerAdminB }}</div>
            </td>
            <td>
                <div class="sign-title">
                    Tanggal, {{ $tglBatchFormatted }}<br/>
                    Mengetahui<br/>
                    (Kepala Balai)
                </div>
                <div class="sign-space"></div>
                <div class="sign-name">{{ $kepalaBalaiB }}</div>
                <div class="sign-nip">NIP. {{ $nipKepalaBalaiB }}</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dokumen ini diterbitkan resmi oleh Sistem Pengujian Laboratorium BBPOM di Mataram.
    </div>

</body>
</html>
