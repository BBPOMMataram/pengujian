<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Permintaan Uji - {{ $filename }}</title>
    <style>
        @page {
            size: A4 {{ $orientation ?? 'landscape' }};
            margin: 8mm 12mm 10mm 12mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8pt;
            color: #0f172a;
            line-height: 1.35;
        }

        /* HEADER DOKUMEN RESMI SPU (DISAMAKAN PERSIS DENGAN EXPORT PER BARIS) */
        .spu-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
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
            text-transform: none;
        }
        .spu-doc-no {
            font-size: 10.5pt;
            font-weight: bold;
            color: #000;
            margin-top: 2px;
            letter-spacing: 0.5px;
        }
        .spu-qr-td {
            width: 135px;
            vertical-align: middle;
            text-align: right;
        }
        .spu-qr-box {
            display: inline-block;
            text-align: center;
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            border-radius: 4px;
            background-color: #f8fafc;
        }
        .spu-qr-box img {
            width: 58px;
            height: 58px;
            display: block;
            margin: 0 auto;
        }
        .spu-qr-caption {
            font-size: 6.5pt;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-top: 2px;
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
            color: #1e293b;
        }
        .spu-meta-sep {
            width: 12px;
            text-align: center;
            color: #475569;
        }
        .spu-meta-val {
            font-weight: bold;
            color: #000;
        }

        /* TABEL SURAT PERMINTAAN UJI (SPU MAIN TABLE) */
        .spu-main-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 7.5pt;
            margin-top: 4px;
            margin-bottom: 4px;
        }
        .spu-main-table th {
            border: 1px solid #000;
            background-color: #ffffff;
            color: #000;
            font-weight: bold;
            padding: 4px 4px;
            text-align: left;
            vertical-align: middle;
            font-size: 7.5pt;
        }
        .spu-main-table th.center {
            text-align: center;
        }
        .spu-main-table td {
            border: 1px solid #000;
            padding: 4px 4px;
            vertical-align: top;
            color: #000;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .spu-main-table td.center {
            text-align: center;
        }

        .bullet-list {
            margin: 0;
            padding-left: 10px;
            list-style-type: disc;
        }
        .bullet-list li {
            margin-bottom: 1.5px;
            line-height: 1.25;
        }

        .table-footer-note {
            font-size: 6.5pt;
            color: #334155;
            margin-top: 2px;
            margin-bottom: 8px;
            line-height: 1.25;
        }

        /* TANDA TANGAN RESMI PEJABAT SPU (SESUAI DOKUMEN ASLI) */
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            page-break-inside: avoid;
        }
        .sign-table td {
            text-align: left;
            vertical-align: top;
            font-size: 8pt;
            color: #000;
            padding: 0 4px;
        }
        .sign-title {
            line-height: 1.3;
            min-height: 36px;
        }
        .sign-space {
            height: 42px;
        }
        .sign-name {
            color: #000;
            font-size: 8pt;
            line-height: 1.3;
        }
        .sign-nip {
            font-size: 8pt;
            color: #000;
            line-height: 1.3;
        }

        .footer-note {
            margin-top: 14px;
            text-align: center;
            font-size: 7pt;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
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
        $batchQrBase64 = $firstItem->qr_base64 ?? null;

        // 1. Ekstraksi Nomor SPU
        $spuNum = $firstMeta['no_spu'] 
            ?? $firstData['no_spu'] 
            ?? $firstData['Nomor SPU'] 
            ?? $firstData['spu'] 
            ?? $firstData['nomor_spu'] 
            ?? 'SPU.117.05.26.10.16.0036';
        $namaUnit = $firstMeta['nama_unit'] ?? null;

        // 2. Metadata Pengujian (Komoditi, Anggaran, Asal Sampling)
        $jenisSampelHead = $firstData['jenis_sampel'] ?? $firstData['Jenis Sampel'] ?? '-';
        $komoditi = $firstMeta['komoditi'] 
            ?? $firstData['komoditi'] 
            ?? $firstData['Komoditi'] 
            ?? ($jenisSampelHead && $jenisSampelHead !== '-' ? (str_contains($jenisSampelHead, ' - ') ? explode(' - ', $jenisSampelHead)[0] : $jenisSampelHead) : '-');
        
        $anggaran = $firstMeta['anggaran'] 
            ?? $firstData['anggaran'] 
            ?? $firstData['Anggaran'] 
            ?? null;
        
        $asalSampling = $firstMeta['asal_sampling'] 
            ?? $firstData['asal_sampling'] 
            ?? $firstData['Asal Sampling'] 
            ?? $firstData['asal_sampel'] 
            ?? null;

        // 3. Tanggal Resmi Format Indonesia
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

        $tanggalFormatted = null;
        if (!empty($rawTgl)) {
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', trim($rawTgl), $tm)) {
                $bln = (int)$tm[2];
                $tanggalFormatted = (int)$tm[3] . ' ' . ($bulanIndo[$bln] ?? '') . ' ' . $tm[1];
            } elseif (preg_match('/^(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})$/', trim($rawTgl), $tm)) {
                $tanggalFormatted = trim($rawTgl);
            }
        }
        if (empty($tanggalFormatted)) {
            $now = now();
            $tanggalFormatted = $now->day . ' ' . ($bulanIndo[$now->month] ?? $now->format('F')) . ' ' . $now->year;
        }

        // 4. Pejabat Resmi SPU BBPOM di Mataram (Dinamis sesuai dokumen asli)
        $petugasPenerimaNama = $firstMeta['petugas_penerima'] 
            ?? $firstData['petugas_penerima'] 
            ?? $firstData['Petugas Penerima'] 
            ?? null;
        $petugasPenerimaNip = $firstMeta['nip_petugas_penerima'] 
            ?? $firstData['nip_petugas_penerima'] 
            ?? null;

        $managerAdministrasiNama = $firstMeta['manager_administrasi'] 
            ?? $firstData['manager_administrasi'] 
            ?? $firstData['Manager Administrasi'] 
            ?? null;
        $managerAdministrasiNip = $firstMeta['nip_manager_administrasi'] 
            ?? $firstData['nip_manager_administrasi'] 
            ?? null;

        $kepalaBalaiNama = $firstMeta['kepala_balai'] 
            ?? $firstData['kepala_balai'] 
            ?? $firstData['Kepala Balai'] 
            ?? null;
        $nipKepalaBalai = $firstMeta['nip_kepala_balai'] 
            ?? $firstData['nip_kepala_balai'] 
            ?? null;

        $signers = $firstMeta['signers'] ?? null;
        if (empty($signers) || !is_array($signers)) {
            if (!empty($managerAdministrasiNama) && $managerAdministrasiNama !== '-') {
                $signers = [
                    [
                        'date' => $tanggalFormatted,
                        'titles' => ['Pemohon / Pengirim Sampel', '(Petugas Penerima Sampel)'],
                        'name' => $petugasPenerimaNama,
                        'nip' => $petugasPenerimaNip,
                    ],
                    [
                        'date' => $tanggalFormatted,
                        'titles' => ['Penerima Sampel', '(Manager Administrasi)'],
                        'name' => $managerAdministrasiNama,
                        'nip' => $managerAdministrasiNip,
                    ],
                    [
                        'date' => $tanggalFormatted,
                        'titles' => ['Mengetahui', '(Kepala Balai)'],
                        'name' => $kepalaBalaiNama,
                        'nip' => $nipKepalaBalai,
                    ],
                ];
            } else {
                $signers = [
                    [
                        'date' => $tanggalFormatted,
                        'titles' => ['Pemohon / Pengirim Sampel'],
                        'name' => $petugasPenerimaNama,
                        'nip' => $petugasPenerimaNip ?? '',
                    ],
                    [
                        'date' => $tanggalFormatted,
                        'titles' => ['Penerima Sampel/TPS'],
                        'name' => null,
                        'nip' => '',
                    ],
                    [
                        'date' => $tanggalFormatted,
                        'titles' => ['Diterima Oleh', 'MT Pengujian'],
                        'name' => null,
                        'nip' => '',
                    ],
                    [
                        'date' => $tanggalFormatted,
                        'titles' => ['Mengetahui', '(Kepala Balai)'],
                        'name' => $kepalaBalaiNama,
                        'nip' => $nipKepalaBalai ?? '',
                    ],
                ];
            }
        }
    @endphp

    <!-- ================================================================ -->
    <!-- SURAT PERMINTAAN UJI (SPU) SELURUH DATA & 3 TANDA TANGAN PEJABAT -->
    <!-- ================================================================ -->
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
            <td class="spu-qr-td">
                @if(!empty($batchQrBase64))
                    <div class="spu-qr-box">
                        <img src="data:image/svg+xml;base64,{{ $batchQrBase64 }}" alt="Verifikasi" />
                        <div class="spu-qr-caption">Verifikasi QR</div>
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <div class="spu-preamble">
        <p>Bersama ini disampaikan daftar sampel:</p>
        <table class="spu-meta-table">
            @if(!empty($namaUnit))
                <tr>
                    <td class="spu-meta-lbl">Nama Unit</td>
                    <td class="spu-meta-sep">:</td>
                    <td class="spu-meta-val">{{ $namaUnit }}</td>
                </tr>
            @endif
            @if(!empty($komoditi) && $komoditi !== '-')
                <tr>
                    <td class="spu-meta-lbl">Komoditi</td>
                    <td class="spu-meta-sep">:</td>
                    <td class="spu-meta-val">{{ $komoditi }}</td>
                </tr>
            @endif
            @if(!empty($anggaran))
                <tr>
                    <td class="spu-meta-lbl">Anggaran</td>
                    <td class="spu-meta-sep">:</td>
                    <td class="spu-meta-val">{{ $anggaran }}</td>
                </tr>
            @endif
            @if(!empty($asalSampling))
                <tr>
                    <td class="spu-meta-lbl">Asal Sampling</td>
                    <td class="spu-meta-sep">:</td>
                    <td class="spu-meta-val">{{ $asalSampling }}</td>
                </tr>
            @endif
        </table>
        <p style="margin-top: 3px;">Untuk dilakukan pengujian sesuai ruang lingkup</p>
    </div>

    <!-- TABEL UTAMA SPU (FORMAT & LAYOUT SAMA PERSIS DENGAN EXPORT PER BARIS) -->
    <table class="spu-main-table">
        <thead>
            <tr>
                <th style="width: 3%; padding: 4px 2px;" class="center">No.</th>
                <th style="width: 13%;">Kode Sampel<br/><span style="font-size: 6.5pt; font-weight: normal;">Nomor Manual</span></th>
                <th style="width: 24%;">Identitas Sample</th>
                <th style="width: 26%;">Keterangan Sampling</th>
                <th style="width: 8%;" class="center">Jumlah Sampel<br/><span style="font-size: 6.5pt; letter-spacing: 1.5px;">T K M P S</span></th>
                <th style="width: 13%;">No. Surat Petugas Sampling</th>
                <th style="width: 13%;">Lokasi Sampel</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                @php
                    $dataArr = is_array($item->data) ? $item->data : [];

                    // 1. Identitas & Jenis Sampel
                    $jenisSampel = $dataArr['jenis_sampel'] ?? $dataArr['Jenis Sampel'] ?? $jenisSampelHead;
                    $itemKomoditi = $dataArr['komoditi'] ?? $dataArr['Komoditi'] ?? $komoditi;
                    $kodeSampel = $dataArr['Kode Sampel'] ?? $dataArr['kode_sampel'] ?? ($item->display_title ?: '-');
                    $namaProduk = $dataArr['Nama Produk / Sampel'] ?? $dataArr['Nama Produk'] ?? $dataArr['nama_produk'] ?? $dataArr['nama_sampel'] ?? $dataArr['Nama Sampel'] ?? '-';
                    $rawNie = $dataArr['nie'] ?? $dataArr['NIE'] ?? $dataArr['nomor_izin_edar'] ?? '';
                    $nie = ($rawNie !== '' && $rawNie !== '-') ? $rawNie : '-';
                    $merk = $dataArr['merk'] ?? $dataArr['Merk'] ?? '-';
                    $komposisi = $dataArr['komposisi'] ?? $dataArr['Komposisi'] ?? (str_contains(strtolower($itemKomoditi), 'narkotika') ? 'EKSTASI' : '-');
                    $bentukSediaan = $dataArr['bentuk_sediaan'] ?? $dataArr['Bentuk Sediaan'] ?? (str_contains(strtolower($itemKomoditi), 'narkotika') ? 'Solid' : '-');
                    $netto = $dataArr['netto'] ?? $dataArr['Netto'] ?? '-';

                    // 2. Keterangan Sampling & Administrasi
                    $keteranganSampling = $dataArr['keterangan_sampling'] ?? $dataArr['Keterangan Sampling'] ?? $dataArr['kemasan'] ?? $dataArr['Kemasan'] ?? '-';
                    $tempatSampling = $dataArr['tempat_sampling'] ?? $dataArr['Tempat Sampling'] ?? '-';
                    $tanggalDiterima = $dataArr['tanggal_diterima'] ?? $dataArr['Tanggal Diterima'] ?? $dataArr['tanggal_sampling'] ?? $dataArr['Tanggal Sampling'] ?? '-';
                    $pengirim = $dataArr['pengirim'] ?? $dataArr['Pengirim'] ?? '-';
                    $noSurat = $dataArr['no_surat'] ?? $dataArr['No Surat Petugas Sampling'] ?? $dataArr['no_surat_petugas_sampling'] ?? '-';
                    $noBilling = $dataArr['no_billing'] ?? $dataArr['No Billing'] ?? '-';

                    // 3. Catatan & Dekode Lokasi Sampel (K2, M2, A1 -> Kimia 2 Sampel, Mikro 2 Sampel dan Arsip 1 Sampel)
                    $catatan = $dataArr['catatan'] ?? $dataArr['Catatan'] ?? '-';
                    $isNarkotikaItem = preg_match('/narkotika|narkoba/i', (string)$itemKomoditi) || preg_match('/narkotika|narkoba/i', (string)$jenisSampel);
                    $lokasiSampel = $isNarkotikaItem ? 'Kimia' : \App\Models\LokasiSampel::parseCatatan($catatan, $jenisSampel, $itemKomoditi);
                    if (!$isNarkotikaItem && $lokasiSampel === '-' && !empty($dataArr['lokasi_sampel']) && $dataArr['lokasi_sampel'] !== '-' && !preg_match('/^T\s*K\s*M\s*P/i', $dataArr['lokasi_sampel'])) {
                        $lokasiSampel = \App\Models\LokasiSampel::parseCatatan($dataArr['lokasi_sampel'], $jenisSampel, $itemKomoditi);
                    }
                    if (!$isNarkotikaItem && $lokasiSampel === '-' && !empty($dataArr['Lokasi Sampel']) && $dataArr['Lokasi Sampel'] !== '-' && !preg_match('/^T\s*K\s*M\s*P/i', $dataArr['Lokasi Sampel'])) {
                        $lokasiSampel = \App\Models\LokasiSampel::parseCatatan($dataArr['Lokasi Sampel'], $jenisSampel, $itemKomoditi);
                    }

                    // 4. Hitung Kode Jumlah Sampel (T K M P S)
                    $counts = \App\Models\LokasiSampel::extractCounts($catatan, $jenisSampel, $itemKomoditi, 1);
                    $countT = $counts['T'];
                    $countK = $counts['K'];
                    $countM = $counts['M'];
                    $countP = $counts['P'];
                    $countS = $counts['S'];

                    $ketTambahan = $dataArr['keterangan_tambahan'] ?? $dataArr['Keterangan Tambahan'] ?? $dataArr['rekomendasi_uji'] ?? $dataArr['Rekomendasi Uji'] ?? '-';
                @endphp
                <tr>
                    <td class="center" style="padding: 4px 2px;">{{ $dataArr['No'] ?? ($index + 1) }}</td>
                    <td>
                        <strong>Kode Sampel:</strong><br/>
                        <span style="font-family: monospace; font-size: 8pt; font-weight: bold; color: #000;">{{ $kodeSampel }}</span>
                    </td>
                    <td>
                        <ul class="bullet-list">
                            <li><strong>Nama Produk:</strong> {{ $namaProduk }}</li>
                            <li><strong>NIE:</strong> {{ $nie }}</li>
                            <li><strong>Jenis Sampel:</strong> {{ $jenisSampel }}</li>
                        </ul>
                    </td>
                    <td>
                        @if(!empty($keteranganSampling) && $keteranganSampling !== '-')
                            <div style="margin-bottom: 3px;">{{ $keteranganSampling }}</div>
                        @endif
                        <ul class="bullet-list">
                            @if(!empty($tempatSampling) && $tempatSampling !== '-')
                                <li><strong>Tempat Sampling:</strong> {{ $tempatSampling }}</li>
                            @endif
                            @if(!empty($tanggalDiterima) && $tanggalDiterima !== '-')
                                <li><strong>Tanggal Sampling:</strong> {{ $tanggalDiterima }}</li>
                            @endif
                            @if(!empty($pengirim) && $pengirim !== '-')
                                <li><strong>Pengirim:</strong> {{ $pengirim }}</li>
                            @endif
                        </ul>
                    </td>
                    <td class="center" style="font-family: monospace; font-size: 8pt; font-weight: bold; letter-spacing: 2px;">
                        {{ $countT }} {{ $countK }} {{ $countM }} {{ $countP }} {{ $countS }}
                    </td>
                    <td>
                        @if((!empty($noSurat) && $noSurat !== '-') || (!empty($noBilling) && $noBilling !== '-') || !empty($dataArr['Catatan Surat']))
                            <ul class="bullet-list">
                                @if(!empty($noSurat) && $noSurat !== '-')
                                    <li><strong>No Surat:</strong> {{ $noSurat }}</li>
                                @endif
                                @if(!empty($noBilling) && $noBilling !== '-')
                                    <li><strong>No Billing:</strong> {{ $noBilling }}</li>
                                @endif
                                @if(!empty($dataArr['Catatan Surat']))
                                    <li>{{ $dataArr['Catatan Surat'] }}</li>
                                @endif
                            </ul>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ (!empty($lokasiSampel) && $lokasiSampel !== '-') ? $lokasiSampel : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="table-footer-note">
        <strong>Keterangan Jumlah Sampel:</strong><br/>
        Jumlah T = Jumlah Total Sampel, Jumlah K = Jumlah Sampel Kimia, Jumlah M = Jumlah Sampel Mikro, Jumlah P = Jumlah Sampel Penandaan, Jumlah S = Jumlah Sisa Sampel
    </div>

    <!-- TANDA TANGAN RESMI PEJABAT SPU (SESUAI DOKUMEN ASLI) -->
    <table class="sign-table">
        @foreach(array_chunk($signers, 3) as $rowIdx => $signerRow)
            <tr>
                @foreach($signerRow as $colIdx => $sig)
                    @php
                        $sigDate = !empty($sig['date']) ? $sig['date'] : $tanggalFormatted;
                        $sigTitles = (!empty($sig['titles']) && is_array($sig['titles'])) ? $sig['titles'] : [];
                        $sigName = (!empty($sig['name']) && $sig['name'] !== '-') ? $sig['name'] : null;
                        $sigNip = ($sig['nip'] ?? null);
                        $colWidth = $colIdx === 0 ? '42%' : ($colIdx === 1 ? '31%' : '27%');
                    @endphp
                    <td style="width: {{ $colWidth }}; {{ $rowIdx > 0 ? 'padding-top: 18px;' : '' }}">
                        <div class="sign-title">
                            Tanggal, {{ $sigDate }}
                            @foreach($sigTitles as $tLine)
                                <br/>{{ $tLine }}
                            @endforeach
                        </div>
                        <div class="sign-space"></div>
                        <div class="sign-name">{!! $sigName ? e($sigName) : '&nbsp;' !!}</div>
                        @if($sigNip !== null || $sigName !== null)
                            <div class="sign-nip">NIP.{{ (!empty($sigNip) && $sigNip !== '-') ? ' ' . $sigNip : '' }}</div>
                        @endif
                    </td>
                @endforeach
                @for($emptyCol = count($signerRow); $emptyCol < 3; $emptyCol++)
                    @php
                        $emptyWidth = $emptyCol === 0 ? '42%' : ($emptyCol === 1 ? '31%' : '27%');
                    @endphp
                    <td style="width: {{ $emptyWidth }}; {{ $rowIdx > 0 ? 'padding-top: 18px;' : '' }}"></td>
                @endfor
            </tr>
        @endforeach
    </table>

    <div class="footer-note">
        Dokumen ini sah dan diterbitkan resmi oleh Sistem Informasi Laboratorium Pengujian BBPOM di Mataram &bull; SPU #{{ $spuNum }}
    </div>

</body>
</html>
