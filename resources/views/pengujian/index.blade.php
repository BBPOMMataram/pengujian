<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pengujian Laboratorium - Upload Excel & QR Code PDF</title>
    <!-- Tailwind CSS CDN for instant, zero-build rendering -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col justify-between antialiased">

    <!-- TOP HEADER -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-700 to-indigo-600 flex items-center justify-center text-white font-extrabold text-sm shadow-md shadow-blue-500/20">
                    BPOM
                </div>
                <div>
                    <h1 class="text-base sm:text-lg font-bold text-slate-900 leading-tight">
                        Laboratorium Pengujian BPOM Mataram
                    </h1>
                    <p class="text-xs text-slate-500">
                        Upload Excel &bull; Generator QR Code &bull; Scan Langsung Dokumen PDF
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('pengujian.template') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors shadow-2xs">
                    <span class="material-symbols-outlined text-[17px]">download</span>
                    <span>Download Template Excel</span>
                </a>

                <form action="{{ route('pengujian.sample') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition-colors shadow-2xs">
                        <span class="material-symbols-outlined text-[17px]">science</span>
                        <span>Muat Data Contoh</span>
                    </button>
                </form>

                @if($totalOverall > 0)
                    <form action="{{ route('pengujian.clear') }}" method="POST" onsubmit="return confirm('Yakin ingin mengosongkan semua data pengujian?')" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium rounded-lg text-rose-600 hover:bg-rose-50 transition-colors" title="Kosongkan Semua Data">
                            <span class="material-symbols-outlined text-[17px]">delete_sweep</span>
                            <span class="hidden md:inline">Reset</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full flex-1 space-y-6">

        <!-- NOTIFICATION ALERTS -->
        @if(session('success'))
            <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 flex items-start gap-3 shadow-xs animate-in fade-in">
                <span class="material-symbols-outlined text-emerald-600 text-2xl shrink-0">check_circle</span>
                <div class="flex-1 text-sm text-emerald-800 font-medium">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-xl bg-rose-50 border border-rose-200 p-4 flex items-start gap-3 shadow-xs animate-in fade-in">
                <span class="material-symbols-outlined text-rose-600 text-2xl shrink-0">error</span>
                <div class="flex-1 text-sm text-rose-800 font-medium">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-xl bg-rose-50 border border-rose-200 p-4 flex items-start gap-3 shadow-xs">
                <span class="material-symbols-outlined text-rose-600 text-2xl shrink-0">warning</span>
                <div class="flex-1 text-sm text-rose-800">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- SECTION 1: UPLOAD EXCEL CARD -->
        <section class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-5 border-b border-slate-100">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-600">upload_file</span>
                            Unggah File Data Pengujian (Excel / CSV)
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                            Pilih file spreadsheet yang berisi daftar sampel pengujian. Sistem otomatis membaca seluruh kolom dan membuat QR code untuk tiap baris.
                        </p>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-500 bg-slate-50 px-3 py-2 rounded-xl border border-slate-200">
                        <span class="material-symbols-outlined text-blue-600 text-[18px]">info</span>
                        <span>Format didukung: <strong>.xlsx, .xls, .csv</strong></span>
                    </div>
                </div>

                <form action="{{ route('pengujian.upload') }}" method="POST" enctype="multipart/form-data" class="mt-5">
                    @csrf
                    <div id="dropzone" class="border-2 border-dashed border-slate-300 hover:border-blue-500 rounded-2xl p-6 sm:p-8 text-center transition-all cursor-pointer bg-slate-50/50 hover:bg-blue-50/20 group">
                        <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls,.csv" class="hidden" required onchange="handleFileSelected(this)">
                        
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-blue-100/70 text-blue-700 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined text-3xl">cloud_upload</span>
                        </div>

                        <div id="file-label" class="space-y-1">
                            <p class="text-sm font-semibold text-slate-700">
                                Klik untuk memilih file Excel atau tarik & letakkan ke sini
                            </p>
                            <p class="text-xs text-slate-400">
                                Maksimal ukuran file: 20 MB
                            </p>
                        </div>

                        <div id="file-selected-name" class="hidden mt-3 text-xs sm:text-sm font-semibold text-blue-700 bg-blue-100/60 inline-flex items-center gap-2 px-3 py-1.5 rounded-full">
                            <span class="material-symbols-outlined text-base">description</span>
                            <span id="selected-filename-text">-</span>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div class="text-xs text-slate-500 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-emerald-600 text-[18px]">verified</span>
                            <span>Ketika QR Code di-scan, output berupa dokumen <strong>PDF Resmi Hasil Pengujian</strong>.</span>
                        </div>

                        <button type="submit" id="btn-submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-98 text-white font-semibold text-sm shadow-md shadow-blue-500/20 transition-all">
                            <span class="material-symbols-outlined text-[19px]">bolt</span>
                            <span>Proses & Tampilkan Data</span>
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- SECTION 2: BATCH SELECTION & FILTER -->
        @if($batches->isNotEmpty())
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Pilih Batch Unggahan:</span>
                    <div class="flex items-center gap-1.5 flex-wrap">
                        @foreach($batches as $b)
                            <a href="{{ route('pengujian.index', ['batch' => $b->batch_id]) }}" 
                               class="px-3 py-1 rounded-lg text-xs font-medium border transition-colors {{ $selectedBatchId === $b->batch_id ? 'bg-blue-600 text-white border-blue-600 shadow-2xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                                {{ $b->filename ?: $b->batch_id }} ({{ $b->total_items }} data)
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Search box -->
                <form action="{{ route('pengujian.index') }}" method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="batch" value="{{ $selectedBatchId }}">
                    <div class="relative">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kode/nama sampel..." 
                               class="pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-300 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 w-48 sm:w-64">
                        <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[16px]">search</span>
                    </div>
                    @if(request('q'))
                        <a href="{{ route('pengujian.index', ['batch' => $selectedBatchId]) }}" class="text-xs text-rose-600 hover:underline">Reset</a>
                    @endif
                </form>
            </div>
        @endif

        <!-- SECTION 3: DATA TABLE WITH QR CODE COLUMN -->
        <section class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-indigo-600">table_chart</span>
                        Tabel Data Pengujian & QR Code
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">
                            {{ $items->count() }} Baris
                        </span>
                    </h3>
                    @if($currentBatch)
                        <p class="text-xs text-slate-500 mt-0.5">
                            File: <strong class="text-slate-700">{{ $currentBatch->filename }}</strong> &bull; Diunggah pada {{ $currentBatch->created_at->format('d/m/Y H:i') }}
                        </p>
                    @endif
                </div>

                <div class="flex items-center gap-2 text-xs">
                    <span class="flex items-center gap-1 text-slate-500">
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        Kolom paling kanan: <strong>QR Code & Aksi Popup/PDF</strong>
                    </span>
                </div>
            </div>

            @if($items->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                        <span class="material-symbols-outlined text-3xl">inventory_2</span>
                    </div>
                    <h4 class="text-sm font-semibold text-slate-700">Belum ada data pengujian</h4>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        Silakan unggah file Excel di atas atau klik tombol <strong>"Muat Data Contoh"</strong> untuk melihat demo tabel dan QR code.
                    </p>
                    <div class="mt-4 flex items-center justify-center gap-2">
                        <a href="{{ route('pengujian.template') }}" class="btn inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition">
                            <span class="material-symbols-outlined text-[16px]">download</span>
                            Download Template Excel
                        </a>
                        <form action="{{ route('pengujian.sample') }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition">
                                <span class="material-symbols-outlined text-[16px]">play_arrow</span>
                                Muat Contoh Sekarang
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-100/80 text-slate-700 border-b border-slate-200">
                                <th class="py-3 px-3.5 font-bold uppercase tracking-wider text-center w-12">No</th>
                                <th class="py-3 px-3.5 font-bold uppercase tracking-wider min-w-[120px]">Kode Sampel</th>
                                <th class="py-3 px-3.5 font-bold uppercase tracking-wider min-w-[180px]">Nama Sampel</th>
                                
                                <!-- DYNAMIC HEADERS FROM EXCEL -->
                                @foreach($headers as $h)
                                    @if(!in_array(strtolower($h), ['no', 'kode_sampel', 'nama_sampel', 'kodesampel', 'namasampel']))
                                        <th class="py-3 px-3.5 font-semibold text-slate-600 uppercase tracking-wider min-w-[140px] whitespace-nowrap">
                                            {{ str_replace(['_', '-'], ' ', $h) }}
                                        </th>
                                    @endif
                                @endforeach

                                <!-- RIGHTMOST COLUMN: QR CODE & AKSI -->
                                <th class="py-3 px-4 font-bold uppercase tracking-wider text-center bg-blue-50/80 text-blue-900 border-l border-blue-200 min-w-[210px] sticky right-0 z-10 shadow-[-4px_0_8px_rgba(0,0,0,0.02)]">
                                    QR Code & Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($items as $index => $item)
                                <tr class="hover:bg-blue-50/30 transition-colors group">
                                    <td class="py-3 px-3.5 text-center text-slate-400 font-mono">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="py-3 px-3.5 font-bold text-blue-700 whitespace-nowrap">
                                        {{ $item->code ?: ('SMP-' . str_pad($item->id, 4, '0', STR_PAD_LEFT)) }}
                                    </td>
                                    <td class="py-3 px-3.5 font-semibold text-slate-900">
                                        {{ $item->name ?: '-' }}
                                    </td>

                                    <!-- DYNAMIC DATA CELLS -->
                                    @foreach($headers as $h)
                                        @if(!in_array(strtolower($h), ['no', 'kode_sampel', 'nama_sampel', 'kodesampel', 'namasampel']))
                                            <td class="py-3 px-3.5 text-slate-600 max-w-xs truncate" title="{{ is_array($item->data[$h] ?? null) ? json_encode($item->data[$h]) : ($item->data[$h] ?? '-') }}">
                                                @php
                                                    $val = $item->data[$h] ?? '-';
                                                @endphp
                                                @if(is_array($val))
                                                    {{ json_encode($val) }}
                                                @else
                                                    @if(str_contains(strtolower($val), 'memenuhi syarat') || str_contains(strtolower($val), 'ms'))
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                                            {{ $val }}
                                                        </span>
                                                    @elseif(str_contains(strtolower($val), 'tms') || str_contains(strtolower($val), 'tidak memenuhi'))
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-800">
                                                            {{ $val }}
                                                        </span>
                                                    @else
                                                        {{ $val }}
                                                    @endif
                                                @endif
                                            </td>
                                        @endif
                                    @endforeach

                                    <!-- KOLOM PALING KANAN: GAMBAR QR CODE & TOMBOL AKSI POPUP -->
                                    <td class="py-2.5 px-4 bg-blue-50/40 border-l border-blue-100 sticky right-0 z-10 group-hover:bg-blue-50/70 transition-colors">
                                        <div class="flex items-center justify-center gap-3">
                                            
                                            <!-- QR CODE THUMBNAIL (Clickable to open popup) -->
                                            <button type="button" 
                                                    class="p-1 bg-white rounded-lg border border-slate-200 hover:border-blue-500 hover:shadow-sm transition-all group/qr shrink-0" 
                                                    title="Klik untuk memperbesar QR Code"
                                                    onclick="openQrModal({{ $item->id }}, '{{ addslashes($item->code) }}', '{{ addslashes($item->name) }}', '{{ route('pengujian.scan', $item->qr_token) }}', '{{ route('pengujian.pdf', $item->id) }}', '{{ route('pengujian.pdf.fpdf', $item->id) }}')">
                                                <div class="w-14 h-14 flex items-center justify-center">
                                                    {!! $item->qr_svg_small !!}
                                                </div>
                                            </button>

                                            <!-- AKSI: TOMBOL LIHAT QR & UNDUH PDF -->
                                            <div class="flex flex-col gap-1 text-left">
                                                <button type="button"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition-colors"
                                                        onclick="openQrModal({{ $item->id }}, '{{ addslashes($item->code) }}', '{{ addslashes($item->name) }}', '{{ route('pengujian.scan', $item->qr_token) }}', '{{ route('pengujian.pdf', $item->id) }}', '{{ route('pengujian.pdf.fpdf', $item->id) }}')">
                                                    <span class="material-symbols-outlined text-[14px]">qr_code_scanner</span>
                                                    <span>Lihat QR</span>
                                                </button>

                                                <a href="{{ route('pengujian.pdf', $item->id) }}" 
                                                   target="_blank"
                                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 transition-colors"
                                                   title="Buka Dokumen PDF Resmi (DomPDF)">
                                                    <span class="material-symbols-outlined text-rose-600 text-[14px]">picture_as_pdf</span>
                                                    <span>Buka PDF</span>
                                                </a>
                                            </div>

                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

    </main>

    <!-- FOOTER -->
    <footer class="bg-white border-t border-slate-200 py-4 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} Balai Besar Pengawas Obat dan Makanan (BBPOM) di Mataram &bull; Sistem Pengujian Laboratorium Terpadu (Laravel + Excel + DomPDF + FPDF)
        </div>
    </footer>

    <!-- POPUP MODAL QR CODE -->
    <div id="qr-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden transition-opacity">
        <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all text-slate-800">
            
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-blue-700 to-indigo-700 p-5 text-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center text-white">
                        <span class="material-symbols-outlined text-xl">qr_code_2</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm leading-tight">Detail QR Code Sampel</h3>
                        <p class="text-xs text-blue-100 mt-0.5">Scan untuk melihat PDF resmi pengujian</p>
                    </div>
                </div>
                <button type="button" onclick="closeQrModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition">
                    &times;
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 text-center space-y-4">
                
                <!-- Sample Name & Code Badge -->
                <div>
                    <span id="modal-sample-code" class="inline-block px-3 py-1 rounded-full text-xs font-extrabold bg-blue-100 text-blue-800 tracking-wide font-mono">
                        SMP-0001
                    </span>
                    <h4 id="modal-sample-name" class="text-base font-bold text-slate-900 mt-1.5 leading-snug">
                        Nama Sampel
                    </h4>
                </div>

                <!-- Big QR Code Box -->
                <div class="p-4 bg-slate-50 rounded-2xl border-2 border-dashed border-blue-200 inline-block shadow-inner">
                    <div id="modal-qr-container" class="w-56 h-56 mx-auto flex items-center justify-center bg-white p-2 rounded-xl shadow-xs">
                        <!-- Injected via JS -->
                    </div>
                </div>

                <!-- Guidance Info -->
                <div class="bg-blue-50/70 border border-blue-100 rounded-xl p-3 text-xs text-blue-900 flex items-start gap-2 text-left">
                    <span class="material-symbols-outlined text-blue-600 text-lg shrink-0 mt-0.5">document_scanner</span>
                    <div>
                        <p class="font-bold">Arahkan kamera HP ke QR Code di atas!</p>
                        <p class="text-[11px] text-blue-700 mt-0.5">Ketika dipindai, kamera akan membuka tautan yang langsung menampilkan atau mengunduh dokumen PDF resmi Hasil Pengujian Sampel.</p>
                    </div>
                </div>

                <!-- Action Buttons in Modal -->
                <div class="grid grid-cols-2 gap-2.5 pt-2">
                    <a id="modal-btn-pdf" href="#" target="_blank" class="inline-flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition-all">
                        <span class="material-symbols-outlined text-base">picture_as_pdf</span>
                        <span>Buka PDF (DomPDF)</span>
                    </a>

                    <a id="modal-btn-fpdf" href="#" target="_blank" class="inline-flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-bold text-xs transition-all">
                        <span class="material-symbols-outlined text-base">description</span>
                        <span>Buka PDF (FPDF)</span>
                    </a>
                </div>

                <!-- Scan URL display -->
                <div class="pt-1 text-[10px] text-slate-400 truncate font-mono" id="modal-target-url">
                    https://...
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="bg-slate-50 px-6 py-3 border-t border-slate-100 flex items-center justify-end">
                <button type="button" onclick="closeQrModal()" class="px-4 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        // Dropzone & File Selection Handlers
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('excel_file');

        dropzone.addEventListener('click', () => fileInput.click());

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('border-blue-600', 'bg-blue-50/40');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('border-blue-600', 'bg-blue-50/40');
            }, false);
        });

        dropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelected(fileInput);
            }
        });

        function handleFileSelected(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                document.getElementById('file-label').classList.add('hidden');
                const badge = document.getElementById('file-selected-name');
                const text = document.getElementById('selected-filename-text');
                badge.classList.remove('hidden');
                text.innerText = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            }
        }

        // Modal Handlers
        const modal = document.getElementById('qr-modal');

        function openQrModal(id, code, name, scanUrl, pdfUrl, fpdfUrl) {
            document.getElementById('modal-sample-code').innerText = code;
            document.getElementById('modal-sample-name').innerText = name;
            document.getElementById('modal-target-url').innerText = scanUrl;
            document.getElementById('modal-btn-pdf').href = pdfUrl;
            document.getElementById('modal-btn-fpdf').href = fpdfUrl;

            // Generate clean QR code via QuickChart API or SVG rendering
            const qrContainer = document.getElementById('modal-qr-container');
            qrContainer.innerHTML = `
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(scanUrl)}&margin=4" 
                     alt="QR Code ${code}" 
                     class="w-full h-full object-contain" />
            `;

            modal.classList.remove('hidden');
        }

        function closeQrModal() {
            modal.classList.add('hidden');
        }

        // Close on ESC key or clicking outside
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeQrModal();
            }
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeQrModal();
            }
        });
    </script>
</body>
</html>
