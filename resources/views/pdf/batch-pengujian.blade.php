<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Permintaan Uji - {{ $filename }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 10mm 10mm 10mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 7.5pt;
            color: #000000;
            line-height: 1.3;
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
            max-height: 46px;
            max-width: 62px;
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
            font-size: 10pt;
            font-weight: bold;
            color: #000;
            margin-top: 1px;
            letter-spacing: 0.5px;
        }
        .spu-qr-td {
            width: 75px;
            vertical-align: middle;
            text-align: right;
        }

        /* PREAMBLE & METADATA */
        .spu-preamble {
            margin-top: 3px;
            margin-bottom: 4px;
            font-size: 8pt;
            line-height: 1.3;
        }
        .spu-preamble p {
            margin: 0 0 2px 0;
        }
        .spu-meta-table {
            border-collapse: collapse;
            font-size: 8pt;
            margin: 1px 0;
        }
        .spu-meta-table td {
            padding: 1px 0;
            vertical-align: top;
        }
        .spu-meta-lbl {
            width: 95px;
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
            font-size: 8pt;
            margin: 3px 0 5px 0;
            color: #000;
        }

        /* TABEL UTAMA SPU (SESUAI DOKUMEN RESMI SPU BBPOM MATARAM) */
        .spu-main-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 7.2pt;
            margin-top: 3px;
            margin-bottom: 4px;
        }
        .spu-main-table th {
            border: 1px solid #000000;
            background-color: #ffffff;
            color: #000000;
            font-weight: bold;
            padding: 3.5px 3px;
            text-align: left;
            vertical-align: middle;
            font-size: 7.2pt;
            word-wrap: break-word;
            word-break: break-all;
            overflow-wrap: break-word;
        }
        .spu-main-table th.center {
            text-align: center;
        }
        .spu-main-table td {
            border: 1px solid #000000;
            padding: 3.5px 3px;
            vertical-align: top;
            color: #000000;
            word-wrap: break-word;
            word-break: break-all;
            overflow-wrap: break-word;
        }
        .spu-main-table td.center {
            text-align: center;
        }

        .bullet-list {
            margin: 0;
            padding-left: 9px;
            list-style-type: disc;
        }
        .bullet-list li {
            margin-bottom: 1px;
            line-height: 1.25;
        }

        .table-footer-note {
            font-size: 6.5pt;
            color: #334155;
            margin-top: 2px;
            margin-bottom: 6px;
            line-height: 1.25;
        }

        /* TANDA TANGAN RESMI 3 PEJABAT SPU BBPOM MATARAM */
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            page-break-inside: avoid;
        }
        .sign-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            font-size: 8pt;
            color: #000000;
            padding: 0 6px;
            border: none;
        }
        .sign-title {
            line-height: 1.25;
            min-height: 42px;
        }
        .sign-space {
            height: 46px;
        }
        .sign-name {
            font-weight: bold;
            text-decoration: underline;
            color: #000000;
            font-size: 8.5pt;
        }
        .sign-nip {
            font-size: 7.5pt;
            color: #000000;
            margin-top: 1px;
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
                <div class="spu-doc-title">SURAT PERMINTAAN UJI</div>
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

    <!-- TABEL UTAMA SPU LENGKAP: SESUAI FORMAT SPU RESMI (SEPERTI LAPORAN-PENGUJIAN & GAMBAR USER) -->
    <table class="spu-main-table">
        <thead>
            <tr>
                <th style="width: 24px;" class="center">No.</th>
                <th style="width: 105px;">Kode Sampel<br/><span style="font-size: 6.2pt; font-weight: normal;">Nomor Manual</span></th>
                <th style="width: 240px;">Identitas Sample</th>
                <th style="width: 160px;">Keterangan Sampling</th>
                <th style="width: 68px;" class="center">Jumlah Sampel<br/><span style="font-size: 6.2pt; letter-spacing: 2px;">T K M P S</span></th>
                <th style="width: 75px;">Rekomendasi Uji</th>
                <th style="width: 165px;">No. Surat Petugas Sampling</th>
                <th style="width: 95px;">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                @php
                    $d = is_array($item->data) ? $item->data : [];

                    // 1. Kode & Nomor Manual
                    $kode = $d['Kode Sampel'] ?? $d['kode_sampel'] ?? ($item->display_title ?: '-');
                    $noManual = $d['Nomor Manual'] ?? $d['nomor_manual'] ?? '-';

                    // 2. Identitas Sampel
                    $namaProd = $d['Nama Produk / Sampel'] ?? $d['Nama Produk'] ?? $d['nama_produk'] ?? $d['nama_sampel'] ?? $d['Nama Sampel'] ?? '-';
                    $rawNie = $d['nie'] ?? $d['NIE'] ?? $d['nomor_izin_edar'] ?? '';
                    $nieVal = ($rawNie !== '' && $rawNie !== '-') ? $rawNie : '-';
                    $merkVal = $d['merk'] ?? $d['Merk'] ?? '-';
                    $jenisVal = $d['jenis_sampel'] ?? $d['Jenis Sampel'] ?? '-';
                    $komposisiVal = $d['komposisi'] ?? $d['Komposisi'] ?? null;
                    $bentukVal = $d['bentuk_sediaan'] ?? $d['Bentuk Sediaan'] ?? null;
                    $nettoVal = $d['netto'] ?? $d['Netto'] ?? null;

                    // 3. Keterangan Sampling
                    $kemasanVal = $d['keterangan_sampling'] ?? $d['Keterangan Sampling'] ?? $d['kemasan'] ?? $d['Kemasan'] ?? null;
                    $tempatVal = $d['tempat_sampling'] ?? $d['Tempat Sampling'] ?? null;
                    $tglSamplingVal = $d['tanggal_sampling'] ?? $d['Tanggal Sampling'] ?? $d['tanggal_diterima'] ?? $d['Tanggal Diterima'] ?? null;
                    $pengirimVal = $d['pengirim'] ?? $d['Pengirim'] ?? null;

                    // 4. Catatan & Lokasi Sampel Ter-decode
                    // Jika catatan berisi 'K2,M2,A1 Cluster B/ Compliance...'
                    // maka Lokasi Sampel = 'Kimia 2 Sampel, Mikro 2 Sampel dan Arsip 1 Sampel'
                    $catatanRaw = $d['catatan'] ?? $d['Catatan'] ?? '-';
                    $lokasiSampelParsed = \App\Models\LokasiSampel::parseCatatan($catatanRaw, $jenisVal, $komoditi);
                    
                    if ($lokasiSampelParsed === '-' && !empty($d['lokasi_sampel']) && $d['lokasi_sampel'] !== '-') {
                        $lokasiSampelParsed = $d['lokasi_sampel'];
                    }

                    // 5. Hitung Jumlah Sampel (T K M P S)
                    $counts = \App\Models\LokasiSampel::extractCounts($catatanRaw, $jenisVal, $komoditi, 1);
                    $countT = $d['Jumlah Sampel'] ?? $d['jumlah_sampel'] ?? $counts['T'];
                    $countK = $counts['K'];
                    $countM = $counts['M'];
                    $countP = $counts['P'];
                    $countS = $counts['S'];

                    // 6. Rekomendasi Uji & No Surat
                    $rekomendasiVal = $d['rekomendasi_uji'] ?? $d['Rekomendasi Uji'] ?? null;
                    $retainVal = $d['jumlah_retain'] ?? $d['Jumlah Retain Sampel'] ?? null;
                    $noSuratVal = $d['no_surat'] ?? $d['No Surat Petugas Sampling'] ?? $d['no_surat_petugas_sampling'] ?? null;
                    $noBillingVal = $d['no_billing'] ?? $d['No Billing'] ?? null;
                    $catatanSuratVal = $d['catatan_surat'] ?? $d['Catatan Surat'] ?? null;
                @endphp
                <tr>
                    <td class="center">{{ $d['No'] ?? ($index + 1) }}</td>
                    <td>
                        <strong>Kode Sampel:</strong><br/>
                        <span style="font-family: monospace; font-size: 7.5pt; font-weight: bold;">{{ $kode }}</span>
                        @if(!empty($noManual) && $noManual !== '-')
                            <div style="font-size: 6.5pt; color: #334155; margin-top: 2px;">Nomor Manual: {{ $noManual }}</div>
                        @endif
                    </td>
                    <td>
                        <ul class="bullet-list">
                            <li><strong>Nama Produk:</strong> {{ $namaProd }}</li>
                            @if(!empty($nieVal) && $nieVal !== '-')
                                <li><strong>NIE:</strong> {{ $nieVal }}</li>
                            @endif
                            @if(!empty($merkVal) && $merkVal !== '-')
                                <li><strong>Merk:</strong> {{ $merkVal }}</li>
                            @endif
                            <li><strong>Jenis Sampel:</strong> {{ $jenisVal }}</li>
                            @if(!empty($komposisiVal) && $komposisiVal !== '-')
                                <li><strong>Komposisi:</strong> {{ $komposisiVal }}</li>
                            @endif
                            @if(!empty($bentukVal) && $bentukVal !== '-')
                                <li><strong>Bentuk Sediaan:</strong> {{ $bentukVal }}</li>
                            @endif
                            @if(!empty($nettoVal) && $nettoVal !== '-')
                                <li><strong>Netto:</strong> {{ $nettoVal }}</li>
                            @endif
                            <li><strong>Evaluasi Penandaan:</strong> Tidak Ada</li>
                        </ul>
                    </td>
                    <td>
                        @if(!empty($kemasanVal) && $kemasanVal !== '-')
                            <div style="margin-bottom: 2px;">{{ $kemasanVal }}</div>
                        @endif
                        <ul class="bullet-list">
                            @if(!empty($tempatVal) && $tempatVal !== '-')
                                <li><strong>Tempat Sampling:</strong> {{ $tempatVal }}</li>
                            @endif
                            @if(!empty($tglSamplingVal) && $tglSamplingVal !== '-')
                                <li><strong>Tanggal Diterima Di TPS:</strong> {{ $tglSamplingVal }}</li>
                            @endif
                            @if(!empty($pengirimVal) && $pengirimVal !== '-')
                                <li><strong>Pengirim:</strong> {{ $pengirimVal }}</li>
                            @endif
                        </ul>
                    </td>
                    <td class="center" style="font-family: monospace; font-size: 7.5pt; font-weight: bold; letter-spacing: 2px;">
                        {{ $countT }} {{ $countK }} {{ $countM }} {{ $countP }} {{ $countS }}
                    </td>
                    <td>
                        @if(!empty($rekomendasiVal) && $rekomendasiVal !== '-')
                            <div>{{ $rekomendasiVal }}</div>
                        @endif
                        @if(!empty($retainVal) && $retainVal !== '-')
                            <div style="font-size: 6.5pt; color: #334155; margin-top: 2px;">Jumlah Retain: {{ $retainVal }}</div>
                        @endif
                        @if(empty($rekomendasiVal) && empty($retainVal))
                            -
                        @endif
                    </td>
                    <td>
                        <ul class="bullet-list">
                            @if(!empty($noSuratVal) && $noSuratVal !== '-')
                                <li><strong>No Surat:</strong> {{ $noSuratVal }}</li>
                            @endif
                            @if(!empty($noBillingVal) && $noBillingVal !== '-')
                                <li><strong>No Billing:</strong> {{ $noBillingVal }}</li>
                            @endif
                            @if(!empty($catatanSuratVal) && $catatanSuratVal !== '-')
                                <li>{{ $catatanSuratVal }}</li>
                            @endif
                        </ul>
                        @if(empty($noSuratVal) && empty($noBillingVal) && empty($catatanSuratVal))
                            -
                        @endif
                    </td>
                    <td>
                        <!-- KOLOM CATATAN: MENAMPILKAN LOKASI SAMPEL TER-DECODE (K2, M2, A1 -> KIMIA 2, MIKRO 2, ARSIP 1) -->
                        <div style="font-weight: 600; color: #000;">
                            {{ $lokasiSampelParsed }}
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="table-footer-note">
        <strong>Keterangan Jumlah Sampel:</strong><br/>
        Jumlah T = Jumlah Total Sampel, Jumlah K = Jumlah Sampel Kimia, Jumlah M = Jumlah Sampel Mikro, Jumlah P = Jumlah Sampel Penandaan, Jumlah S = Jumlah Sisa Sampel
    </div>

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
