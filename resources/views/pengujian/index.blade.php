<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorium Pengujian BBPOM di Mataram - Upload & Generator QR Code PDF</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
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

        /* ─── HALO DROPZONE ANIMATIONS (BBPOM BRAND COLORS: #005baa & #059669) ─── */
        @keyframes halo-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes halo-pulse {
            0%, 100% { opacity: 0.55; transform: scale(1); }
            50% { opacity: 0.85; transform: scale(1.04); }
        }

        .halo-container {
            position: relative;
            overflow: hidden;
            border-radius: 1.25rem;
            padding: 2px;
            background: #e2e8f0;
            box-shadow: 0 4px 20px -2px rgba(0, 91, 170, 0.08);
            transition: all 0.3s ease;
        }
        .halo-container:hover {
            box-shadow: 0 8px 30px -4px rgba(0, 91, 170, 0.16);
        }

        .halo-trail {
            position: absolute;
            inset: -80%;
            background: conic-gradient(
                from 0deg, 
                transparent 0deg, 
                transparent 240deg, 
                rgba(0, 91, 170, 0.4) 280deg, 
                #005baa 320deg, 
                #059669 350deg, 
                #005baa 360deg
            );
            animation: halo-spin 7s linear infinite;
            pointer-events: none;
            opacity: 0.8;
        }

        .halo-blob-1 {
            position: absolute;
            top: -25px;
            left: 10%;
            width: 140px;
            height: 140px;
            background: radial-gradient(circle, rgba(0, 91, 170, 0.3) 0%, transparent 70%);
            filter: blur(24px);
            animation: halo-pulse 4s ease-in-out infinite;
            pointer-events: none;
        }

        .halo-blob-2 {
            position: absolute;
            bottom: -25px;
            right: 10%;
            width: 150px;
            height: 150px;
            background: radial-gradient(circle, rgba(5, 150, 105, 0.28) 0%, transparent 70%);
            filter: blur(24px);
            animation: halo-pulse 4s ease-in-out infinite 2s;
            pointer-events: none;
        }

        .halo-inner {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: calc(1.25rem - 2px);
            border: 2px dashed #cbd5e1;
            transition: all 0.25s ease;
            cursor: pointer;
        }
        .halo-inner:hover, .halo-inner.dragover {
            border-color: #005baa;
            background: rgba(240, 247, 255, 0.98);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col justify-between antialiased">

    <!-- TOP HEADER -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex flex-col md:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-3.5">
                @if(file_exists(public_path('images/logobbpom.png')))
                    <img src="{{ asset('images/logobbpom.png') }}" alt="Logo BBPOM" class="h-11 w-auto object-contain shrink-0" />
                @endif
                <div>
                    <h1 class="text-base sm:text-lg font-bold text-slate-900 leading-tight">
                        Laboratorium Pengujian BBPOM di Mataram
                    </h1>
                    <p class="text-xs text-slate-500">
                        Sistem Pengolahan Dokumen &amp; Generator QR Code Laporan Pengujian
                    </p>
                </div>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center gap-2">
                @if($totalOverall > 0)
                    <form action="{{ route('pengujian.clear') }}" method="POST" onsubmit="return confirm('Kosongkan semua riwayat pengujian?')" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition" title="Kosongkan Semua Data">
                            <span>Reset Data</span>
                        </button>
                    </form>
                    <span class="text-xs text-slate-500 bg-slate-100 px-3 py-1.5 rounded-lg font-medium border border-slate-200">
                        Total <strong>{{ number_format($totalOverall, 0, ',', '.') }}</strong> Sampel
                    </span>
                @endif
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full space-y-6">

        <!-- FLASH NOTIFICATIONS -->
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-start gap-3 shadow-2xs">
                <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1 font-medium">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-start gap-3 shadow-2xs">
                <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1 font-medium">{{ session('error') }}</div>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-2xs">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- SECTION 1: HALO DROPZONE (Excel / CSV / PDF) -->
        <section class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 sm:p-6">
            <div class="border-b border-slate-100 pb-3.5 mb-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">
                            Unggah Dokumen Pengujian (Excel / CSV / PDF)
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                            Mendukung unggah satu atau <strong>beberapa file sekaligus</strong> (.xlsx, .xls, .csv, atau PDF SPU). Sistem otomatis memproses data dan menghasilkan QR Code untuk verifikasi.
                        </p>
                    </div>
                    <div id="dropzone-badge-container" class="hidden">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-[#005baa] border border-blue-200">
                            <span class="w-2 h-2 rounded-full bg-[#005baa] animate-pulse"></span>
                            <span id="dropzone-badge-text">0 file dipilih</span>
                        </span>
                    </div>
                </div>
            </div>

            <form id="upload-form" action="{{ route('pengujian.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- HALO ANIMATED DROPZONE CONTAINER -->
                <div class="halo-container">
                    <!-- Rotating Glowing Conic Trail in BBPOM Colors -->
                    <div class="halo-trail"></div>
                    <!-- Blurred Glowing Blobs -->
                    <div class="halo-blob-1"></div>
                    <div class="halo-blob-2"></div>

                    <!-- Inner Frosted Glass Card -->
                    <div id="halo-dropzone" class="halo-inner p-5 sm:p-7 text-center transition-all cursor-pointer">
                        <input type="file" name="files[]" id="file_input" accept=".xlsx,.xls,.csv,.pdf" multiple class="hidden">

                        <!-- IDLE STATE: ICONS & PROMPT -->
                        <div id="dropzone-idle" class="space-y-3">
                            <!-- File Type Glyphs (Excel, PDF, CSV) -->
                            <div class="flex items-center justify-center gap-3">
                                <!-- Excel Glyph -->
                                <div class="w-11 h-11 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-700 shadow-2xs">
                                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path>
                                        <polyline stroke-linecap="round" stroke-linejoin="round" points="14 2 14 8 20 8"></polyline>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 13l3 3m0-3l-3 3m5-3h4m-4 3h4"></path>
                                    </svg>
                                </div>
                                <!-- PDF Glyph -->
                                <div class="w-11 h-11 rounded-xl bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 shadow-2xs">
                                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path>
                                        <polyline stroke-linecap="round" stroke-linejoin="round" points="14 2 14 8 20 8"></polyline>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15h6M9 11h6"></path>
                                    </svg>
                                </div>
                                <!-- CSV Glyph -->
                                <div class="w-11 h-11 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-[#005baa] shadow-2xs">
                                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            </div>

                            <div>
                                <p class="text-sm font-bold text-slate-800">
                                    Pilih atau seret satu atau beberapa file ke area ini
                                </p>
                                <p class="text-xs text-slate-400 mt-1">
                                    Bisa memilih lebih dari 1 file sekaligus &bull; Format didukung: <strong>.xlsx, .xls, .csv, .pdf</strong> (Maks 25 MB/file)
                                </p>
                            </div>
                        </div>

                        <!-- SELECTED STATE: FILE LIST & MANAGEMENT -->
                        <div id="dropzone-selected" class="hidden space-y-3" onclick="event.stopPropagation()">
                            <div class="flex items-center justify-between px-1">
                                <div class="text-xs font-semibold text-slate-700 flex items-center gap-1.5">
                                    <span>Daftar file yang akan diunggah:</span>
                                    <span id="selected-count-text" class="text-[#005baa] font-bold">(0 file)</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" id="btn-add-more" class="text-xs font-medium text-[#005baa] hover:underline cursor-pointer">
                                        + Tambah File
                                    </button>
                                    <span class="text-slate-300">|</span>
                                    <button type="button" id="btn-clear-all" class="text-xs font-medium text-rose-600 hover:underline cursor-pointer">
                                        Hapus Semua
                                    </button>
                                </div>
                            </div>

                            <div id="selected-files-list" class="max-h-56 overflow-y-auto space-y-2 pr-1 custom-scrollbar">
                                <!-- Dynamically generated file items -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Action -->
                <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div id="upload-hint" class="text-xs text-slate-500">
                        File yang diunggah akan langsung dipetakan ke tabel di bawah ini.
                    </div>
                    <button type="submit" id="btn-submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-[#005baa] hover:bg-[#004887] text-white font-semibold text-xs sm:text-sm shadow-sm transition cursor-pointer">
                        <span id="btn-submit-text">Proses &amp; Generate QR Code</span>
                    </button>
                </div>
            </form>
        </section>

        <!-- SECTION 2: BATCH SELECTION & FILTER -->
        @if($batches->isNotEmpty())
            <div class="space-y-3">
                <div class="flex flex-col gap-3 bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs">
                    @php
                        $catCounts = [];
                        foreach($batches as $b) {
                            $cat = $b->komoditi ?: 'Lainnya';
                            $catCounts[$cat] = ($catCounts[$cat] ?? 0) + 1;
                        }
                    @endphp

                    @if(count($catCounts) > 1)
                        <div class="flex items-center gap-1.5 flex-wrap pb-2.5 border-b border-slate-100" id="batch-cat-filters">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mr-1">Kategori:</span>
                            <button type="button" onclick="filterBatchesByCategory('ALL')" class="js-cat-pill active px-3 py-1 rounded-full text-xs font-bold transition-all bg-[#005baa] text-white shadow-xs cursor-pointer" data-cat="ALL">
                                Semua ({{ $batches->count() }})
                            </button>
                            @foreach($catCounts as $cName => $cCount)
                                <button type="button" onclick="filterBatchesByCategory('{{ $cName }}')" class="js-cat-pill px-3 py-1 rounded-full text-xs font-bold transition-all bg-slate-100 text-slate-600 hover:bg-slate-200 cursor-pointer" data-cat="{{ $cName }}">
                                    {{ $cName }} ({{ $cCount }})
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 flex-wrap flex-1 min-w-0">
                            <label for="batch-select" class="text-xs font-bold text-slate-500 uppercase tracking-wider shrink-0">
                                Pilihan Batch:
                            </label>
                            <div class="relative flex-1 max-w-md sm:max-w-lg">
                                <select id="batch-select" 
                                        onchange="onBatchSelectChange(this.value)"
                                        class="w-full pl-3 pr-8 py-2 text-xs font-semibold rounded-xl border border-slate-300 bg-white text-slate-800 shadow-2xs focus:outline-none focus:border-[#005baa] focus:ring-1 focus:ring-[#005baa] transition cursor-pointer truncate">
                                    @foreach($batches as $b)
                                        <option value="{{ $b->batch_id }}"
                                                data-komoditi="{{ $b->komoditi ?: 'Lainnya' }}"
                                                data-filename="{{ $b->filename ?: $b->batch_id }}"
                                                data-sheet="{{ $b->sheet_name ?: '' }}"
                                                data-total="{{ $b->total_items }}"
                                                {{ $selectedBatchId === $b->batch_id ? 'selected' : '' }}>
                                            {{ $b->filename ?: $b->batch_id }}{{ (!empty($b->sheet_name) && $b->sheet_name !== 'Sheet1') ? ' [' . $b->sheet_name . ']' : '' }}{{ !empty($b->komoditi) ? ' • ' . $b->komoditi : '' }} ({{ $b->total_items }} data)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Batch Export and Delete Actions -->
                        <div class="flex items-center gap-2 flex-wrap shrink-0" id="batch-actions-container">
                            <a id="btn-export-tabular"
                               href="{{ $selectedBatchId ? route('pengujian.batch.pdf', $selectedBatchId) : '#' }}" 
                               target="_blank"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition shadow-2xs {{ ($selectedBatchId && $items->isNotEmpty()) ? '' : 'hidden' }}"
                               title="Unduh seluruh data dalam format Surat Permintaan Uji PDF">
                                <span>Export PDF Tabular</span>
                            </a>

                            <form id="form-delete-batch" 
                                  action="{{ $selectedBatchId ? route('pengujian.batch.destroy', $selectedBatchId) : '#' }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Hapus batch ini?')" 
                                  class="inline {{ $selectedBatchId ? '' : 'hidden' }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50 border border-rose-200 transition cursor-pointer">
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- SUB-BAR JIKA FILE MEMILIKI BEBERAPA TAB / SHEETS -->
                <div id="sibling-sheets-container" class="{{ ($siblingSheets && $siblingSheets->count() > 1) ? '' : 'hidden' }}">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-blue-50/80 border border-blue-200 p-3 rounded-xl shadow-2xs">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-bold text-blue-900">
                                Tab File Ini:
                            </span>
                            <div class="flex items-center gap-1.5 flex-wrap" id="sibling-sheets-buttons">
                                @if($siblingSheets)
                                    @foreach($siblingSheets as $sheet)
                                        <button type="button"
                                                onclick="onBatchSelectChange('{{ $sheet->batch_id }}')" 
                                                class="js-sibling-tab-btn px-3 py-1 rounded-lg text-xs font-semibold border transition-all cursor-pointer {{ $selectedBatchId === $sheet->batch_id ? 'bg-[#005baa] text-white border-[#005baa]' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100' }}"
                                                data-batch-id="{{ $sheet->batch_id }}">
                                            {{ $sheet->sheet_name ?: 'Tab' }} ({{ $sheet->total_items }})
                                        </button>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <a id="btn-merged-pdf" 
                           href="{{ ($currentBatch && $currentBatch->file_group_id) ? route('pengujian.filegroup.pdf', $currentBatch->file_group_id) : '#' }}" 
                           target="_blank"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-[#005baa] hover:bg-[#004887] text-white shadow-xs transition shrink-0 {{ ($currentBatch && $currentBatch->file_group_id) ? '' : 'hidden' }}">
                            <span>Export Gabungan (Semua Tab ke 1 PDF)</span>
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- SECTION 3: DATATABLE WITH LIVE SEARCH & PAGINATION -->
        <section class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <!-- Table Header Controls: Search & Page Size -->
            <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span>Tabel Data Sampel Pengujian</span>
                        <span id="dt-badge-total" class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-100 text-[#005baa]">
                            {{ $items->count() }} Data
                        </span>
                    </h3>
                    <p id="dt-batch-subtitle" class="text-xs text-slate-500 mt-0.5">
                        @if($currentBatch)
                            Sumber: <strong class="text-slate-700">{{ $currentBatch->filename ?: $currentBatch->batch_id }}</strong>
                            @if(!empty($currentBatch->sheet_name) && $currentBatch->sheet_name !== 'Sheet1')
                                &bull; Tab: <span class="text-[#005baa] font-semibold">{{ $currentBatch->sheet_name }}</span>
                            @endif
                        @endif
                    </p>
                </div>

                <!-- Live Search & Page Size Selector -->
                <div id="dt-controls-container" class="flex items-center gap-2.5 flex-wrap {{ $items->isEmpty() ? 'hidden' : '' }}">
                    <!-- Search Input -->
                    <div class="relative">
                        <input type="text" id="dt-search-input" placeholder="Cari data di tabel..." 
                               class="w-48 sm:w-64 pl-3 pr-8 py-1.5 text-xs rounded-lg border border-slate-300 focus:outline-none focus:border-[#005baa] focus:ring-1 focus:ring-[#005baa] transition">
                        <button type="button" id="dt-search-clear" class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-sm font-bold">
                            &times;
                        </button>
                    </div>

                    <!-- Page Size Selector -->
                    <div class="flex items-center gap-1.5 text-xs text-slate-500">
                        <span>Tampilkan:</span>
                        <select id="dt-page-size" class="py-1.5 px-2 text-xs rounded-lg border border-slate-300 focus:outline-none focus:border-[#005baa] bg-white">
                            <option value="10" selected>10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="all">Semua</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="table-empty-container" class="p-12 text-center {{ $items->isEmpty() ? '' : 'hidden' }}">
                <p class="text-sm font-semibold text-slate-700">Belum ada data pengujian yang dimuat</p>
                <p class="text-xs text-slate-400 mt-1">Silakan unggah file Excel, CSV, atau Dokumen PDF pada formulir di atas.</p>
            </div>

            <div id="table-content-container" class="{{ $items->isEmpty() ? 'hidden' : '' }}">
                <div class="overflow-x-auto custom-scrollbar">
                    <table id="main-datatable" class="w-full text-left border-collapse text-xs transition-opacity duration-200">
                        <thead>
                            <tr class="bg-slate-50 text-slate-700 border-b border-slate-200">
                                <th class="py-3 px-3.5 font-bold uppercase tracking-wider text-center w-12">No</th>
                                
                                <!-- DYNAMIC DATA HEADERS FROM EXCEL -->
                                @php
                                    $ignoredCols = [
                                        'manager_administrasi', 'nip_manager_administrasi',
                                        'kepala_balai', 'nip_kepala_balai',
                                        'petugas_penerima', 'nip_petugas_penerima',
                                        'tanggal_spu', 'no_spu', 'nomor spu', 'nomor_spu',
                                        '_meta'
                                    ];
                                @endphp
                                @foreach($headers as $h)
                                    @php
                                        $hLower = strtolower(trim($h));
                                        $isIgnored = str_starts_with($h, '_') || in_array($hLower, $ignoredCols);
                                    @endphp
                                    @if(!str_contains($hLower, 'qr') && !$isIgnored)
                                        <th class="py-3 px-3.5 font-bold uppercase tracking-wider text-slate-800 min-w-[180px]">
                                            @if(str_contains($h, ' - '))
                                                @php [$mainH, $subH] = explode(' - ', $h, 2); @endphp
                                                <div class="text-[10px] text-slate-400 font-semibold normal-case tracking-normal">{{ str_replace('_', ' ', $mainH) }}</div>
                                                <div class="text-xs font-bold text-slate-800">{{ str_replace('_', ' ', $subH) }}</div>
                                            @else
                                                {{ str_replace('_', ' ', $h) }}
                                            @endif
                                        </th>
                                    @endif
                                @endforeach

                                <!-- GENERATED QR CODE COLUMN -->
                                <th class="py-3 px-4 font-bold uppercase tracking-wider text-center bg-blue-50/70 text-[#005baa] border-l border-blue-100 min-w-[120px]">
                                    QR Code Ter-generate
                                </th>

                                <!-- AKSI BARIS DATA -->
                                <th class="py-3 px-4 font-bold uppercase tracking-wider text-center bg-slate-50 text-slate-700 border-l border-slate-200 min-w-[160px] sticky right-0 z-10 shadow-[-4px_0_8px_rgba(0,0,0,0.03)]">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody id="dt-table-body" class="divide-y divide-slate-100">
                            @foreach($items as $index => $item)
                                <tr class="dt-row hover:bg-blue-50/20 transition-colors" data-row-index="{{ $index }}">
                                    <td class="dt-cell-no py-3 px-3.5 text-center text-slate-400 font-mono font-medium">
                                        {{ $index + 1 }}
                                    </td>

                                    <!-- DYNAMIC DATA CELLS -->
                                    @foreach($headers as $h)
                                        @php
                                            $hLower = strtolower(trim($h));
                                            $isIgnored = str_starts_with($h, '_') || in_array($hLower, $ignoredCols);
                                        @endphp
                                        @if(!str_contains($hLower, 'qr') && !$isIgnored)
                                            <td class="dt-cell-data py-3 px-3.5 text-slate-900 font-medium max-w-sm">
                                                @php
                                                    $val = $item->data[$h] ?? '-';
                                                @endphp
                                                @if(is_array($val))
                                                    {{ json_encode($val) }}
                                                @else
                                                    @php $vLower = strtolower((string)$val); @endphp
                                                    @if(str_contains($vLower, 'memenuhi syarat') || $vLower === 'ms')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                                            {{ $val }}
                                                        </span>
                                                    @elseif(str_contains($vLower, 'tms') || str_contains($vLower, 'tidak memenuhi'))
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

                                    <!-- KOLOM SAMPINGNYA: QR CODE -->
                                    <td class="py-2.5 px-4 bg-blue-50/20 border-l border-blue-100 text-center">
                                        <div class="flex items-center justify-center">
                                            <button type="button" 
                                                    class="js-btn-qr-modal p-1 bg-white rounded-xl border border-slate-200 hover:border-[#005baa] hover:shadow-md transition-all shrink-0" 
                                                    title="Klik untuk melihat Pop-up QR Code"
                                                    data-qr-token="{{ $item->qr_token }}"
                                                    data-title="{{ $item->display_title }}"
                                                    data-code="{{ $item->code }}"
                                                    data-target-url="{{ route('pengujian.scan', $item->qr_token) }}"
                                                    data-pdf-url="{{ route('pengujian.pdf', $item->id) }}"
                                                    data-info="{{ $item->qr_content ?: ($item->code ?: '-') }}"
                                                    data-qr-base64="{{ $item->qr_base64 }}">
                                                <div class="w-14 h-14 flex items-center justify-center p-0.5">
                                                    @if(!empty($item->qr_svg_small))
                                                        {!! $item->qr_svg_small !!}
                                                    @else
                                                        <img src="{{ route('pengujian.qr', $item->qr_token) }}" alt="QR Code" class="w-full h-full object-contain" loading="lazy" />
                                                    @endif
                                                </div>
                                            </button>
                                        </div>
                                    </td>

                                    <!-- KOLOM AKSI: POP-UP QR & EXPORT PDF PER BARIS -->
                                    <td class="py-2.5 px-4 bg-white border-l border-slate-200 sticky right-0 z-10 hover:bg-blue-50/30 transition-colors">
                                        <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                            <button type="button"
                                                    class="js-btn-qr-modal inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-[#005baa] hover:bg-[#004887] text-white shadow-2xs transition-colors"
                                                    title="Buka pop-up QR Code besar"
                                                    data-qr-token="{{ $item->qr_token }}"
                                                    data-title="{{ $item->display_title }}"
                                                    data-code="{{ $item->code }}"
                                                    data-target-url="{{ route('pengujian.scan', $item->qr_token) }}"
                                                    data-pdf-url="{{ route('pengujian.pdf', $item->id) }}"
                                                    data-info="{{ $item->qr_content ?: ($item->code ?: '-') }}"
                                                    data-qr-base64="{{ $item->qr_base64 }}">
                                                <span>Pop-up QR</span>
                                            </button>

                                            <a href="{{ route('pengujian.pdf', $item->id) }}" 
                                               target="_blank"
                                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[11px] font-semibold bg-white hover:bg-rose-50 text-rose-700 border border-rose-200 shadow-2xs transition-colors"
                                               title="Export dokumen PDF baris data ini">
                                                <span>Export PDF</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- DATATABLE PAGINATION BAR -->
                <div class="p-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 bg-white">
                    <div id="dt-info-text">
                        Menampilkan <span id="dt-start" class="font-bold text-slate-800">1</span> sampai <span id="dt-end" class="font-bold text-slate-800">10</span> dari <span id="dt-total" class="font-bold text-slate-800">{{ $items->count() }}</span> data
                    </div>
                    <div id="dt-pagination-container" class="flex items-center gap-1">
                        <!-- Dynamic pagination buttons generated by JS -->
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- FOOTER -->
    <footer class="bg-white border-t border-slate-200 py-3 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} Balai Besar Pengawas Obat dan Makanan di Mataram &bull; Laboratorium Pengujian
        </div>
    </footer>

    <!-- POPUP MODAL QR CODE BESAR -->
    <div id="qr-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden transition-opacity">
        <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all text-slate-800">
            
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-[#005baa] to-blue-700 p-5 text-white flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-base leading-tight">Pop-up QR Code Sampel</h3>
                    <p class="text-xs text-blue-100 mt-0.5">Pindai QR untuk langsung membuka Laporan Dokumen PDF baris ini</p>
                </div>
                <button type="button" onclick="closeQrModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition text-lg font-bold">
                    &times;
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 text-center space-y-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
                
                <div>
                    <span id="modal-sample-code" class="inline-block px-3 py-1 rounded-full text-xs font-extrabold bg-blue-100 text-[#005baa] tracking-wide font-mono">
                        SMP-0001
                    </span>
                    <h4 id="modal-sample-title" class="text-base font-bold text-slate-900 mt-1.5 leading-snug">
                        Nama Sampel Pengujian
                    </h4>
                </div>

                <!-- Big QR Code Container -->
                <div class="p-4 bg-slate-50 rounded-2xl border-2 border-dashed border-blue-200 inline-block shadow-inner">
                    <div id="modal-qr-container" class="w-56 h-56 mx-auto flex items-center justify-center bg-white p-2 rounded-xl shadow-xs">
                        <img id="modal-qr-img" src="" alt="QR Code" class="w-full h-full object-contain" />
                    </div>
                    <p class="text-[11px] text-slate-500 font-medium mt-2">
                        Pindai dengan kamera smartphone untuk membuka Laporan PDF
                    </p>
                </div>

                <!-- Card: Link Target Dokumen PDF -->
                <div class="bg-blue-50/70 border border-blue-200 rounded-2xl p-3 text-left space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#005baa]">
                            Link Target QR Code (File Export PDF):
                        </span>
                        <button type="button" onclick="copyTargetUrl()" id="btn-copy-url" class="text-[11px] font-bold text-[#005baa] hover:underline">
                            <span id="btn-copy-url-text">Salin Link</span>
                        </button>
                    </div>
                    <a id="modal-target-url" href="#" target="_blank" class="text-xs font-mono text-blue-700 hover:underline break-all block font-semibold bg-white p-2 rounded-lg border border-blue-100">
                        -
                    </a>
                </div>

                <!-- Card: Rincian Data Sampel Pengujian -->
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3.5 text-left space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            Rincian Data Sampel:
                        </span>
                        <button type="button" onclick="copyQrContent()" id="btn-copy" class="text-[11px] font-bold text-slate-600 hover:text-[#005baa]">
                            <span id="btn-copy-text">Salin Rincian</span>
                        </button>
                    </div>
                    <div id="modal-qr-content" class="text-xs font-mono text-slate-800 bg-white p-2.5 rounded-xl border border-slate-200 break-words whitespace-pre-line select-all font-medium leading-relaxed max-h-36 overflow-y-auto custom-scrollbar">
                        -
                    </div>
                </div>

                <!-- Action Buttons in Modal -->
                <div class="grid grid-cols-3 gap-2 pt-1">
                    <button type="button" onclick="printBarcodeLabel()" class="inline-flex items-center justify-center py-2.5 px-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-500/20 transition-all cursor-pointer" title="Cetak langsung stiker label barcode ke printer label">
                        <span>Cetak Barcode</span>
                    </button>

                    <button type="button" id="modal-btn-download-png" onclick="downloadQrPng()" class="inline-flex items-center justify-center py-2.5 px-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs transition-all border border-slate-200 cursor-pointer" title="Unduh gambar QR Code berformat PNG siap cetak / print">
                        <span>Unduh PNG</span>
                    </button>

                    <a id="modal-btn-pdf" href="#" target="_blank" class="inline-flex items-center justify-center py-2.5 px-2 rounded-xl bg-[#005baa] hover:bg-[#004887] text-white font-bold text-xs shadow-md shadow-blue-500/20 transition-all cursor-pointer">
                        <span>Buka PDF</span>
                    </a>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="bg-slate-50 px-6 py-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span class="text-[11px]">Dapat dipindai langsung dengan kamera smartphone</span>
                <button type="button" onclick="closeQrModal()" class="px-4 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL EXPORT PDF DENGAN LAMPIRAN FOTO DOKUMENTASI -->
    <div id="photo-export-modal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-xl w-full border border-slate-200 overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            <!-- Header -->
            <div class="px-6 py-4.5 bg-gradient-to-r from-emerald-50 via-teal-50 to-white border-b border-emerald-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Export Laporan + Lampiran Foto</h3>
                        <p id="photo-modal-subtitle" class="text-xs text-slate-500 font-mono">Sampel #</p>
                    </div>
                </div>
                <button type="button" onclick="closePhotoModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">
                    &times;
                </button>
            </div>

            <form id="photo-export-form" method="POST" enctype="multipart/form-data" target="_blank" class="p-6 space-y-4">
                @csrf

                <!-- File Upload / Drag Zone -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Pilih File Foto Lampiran (1 s.d. 4 Foto)
                    </label>
                    <div id="photo-dropzone" onclick="document.getElementById('photo-file-input').click()" class="border-2 border-dashed border-emerald-200 bg-emerald-50/20 hover:bg-emerald-50/40 rounded-xl p-5 text-center cursor-pointer transition">
                        <input type="file" id="photo-file-input" name="photos[]" multiple accept="image/*" class="hidden" onchange="handlePhotoSelect(this)">
                        <div class="flex items-center justify-center gap-2 mb-2">
                            <span class="text-emerald-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </span>
                        </div>
                        <p class="text-xs font-bold text-slate-800">Klik atau seret file foto ke sini</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Format: JPG, PNG, WEBP. Ukuran gambar otomatis diselaraskan proporsional agar kertas terisi rapi & eye comforting.</p>
                    </div>
                </div>

                <!-- Live Preview List -->
                <div id="photo-preview-container" class="space-y-2 hidden">
                    <label class="block text-xs font-bold text-slate-700">Preview & Keterangan Foto:</label>
                    <div id="photo-preview-grid" class="grid grid-cols-2 gap-2.5 max-h-52 overflow-y-auto pr-1"></div>
                </div>

                <!-- Orientation Option -->
                <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
                    <label class="font-bold text-slate-700">Orientasi Kertas:</label>
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" name="orientation" value="landscape" checked class="text-emerald-600 focus:ring-emerald-500">
                            <span class="font-semibold text-slate-700">Landscape (Standar SPU)</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" name="orientation" value="portrait" class="text-emerald-600 focus:ring-emerald-500">
                            <span class="font-semibold text-slate-700">Portrait</span>
                        </label>
                    </div>
                </div>

                <!-- Submit Footer -->
                <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closePhotoModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        Batal
                    </button>
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>Download PDF Berlampiran Foto</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- CLIENT-SIDE LOGIC: HALO DROPZONE & DYNAMIC DATATABLE -->
    <script>
        // ─── 1. HALO DROPZONE HANDLERS (MULTI-FILE ACCUMULATION & DEDUPLICATION) ───
        const dropzone = document.getElementById('halo-dropzone');
        const fileInput = document.getElementById('file_input');
        const uploadForm = document.getElementById('upload-form');
        const idleState = document.getElementById('dropzone-idle');
        const selectedState = document.getElementById('dropzone-selected');
        const badgeContainer = document.getElementById('dropzone-badge-container');
        const badgeText = document.getElementById('dropzone-badge-text');
        const selectedCountText = document.getElementById('selected-count-text');
        const filesListContainer = document.getElementById('selected-files-list');
        const btnAddMore = document.getElementById('btn-add-more');
        const btnClearAll = document.getElementById('btn-clear-all');
        const uploadHint = document.getElementById('upload-hint');
        const btnSubmit = document.getElementById('btn-submit');
        const btnSubmitText = document.getElementById('btn-submit-text');

        let selectedFiles = [];

        function formatBytes(bytes) {
            if (bytes < 1024 * 1024) {
                return (bytes / 1024).toFixed(1) + ' KB';
            }
            return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
        }

        function getFileIconHtml(filename) {
            const ext = (filename.split('.').pop() || '').toLowerCase();
            if (ext === 'pdf') {
                return `
                    <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path>
                            <polyline stroke-linecap="round" stroke-linejoin="round" points="14 2 14 8 20 8"></polyline>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15h6M9 11h6"></path>
                        </svg>
                    </div>`;
            }
            if (ext === 'csv') {
                return `
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#005baa] border border-blue-200 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>`;
            }
            return `
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path>
                        <polyline stroke-linecap="round" stroke-linejoin="round" points="14 2 14 8 20 8"></polyline>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 13l3 3m0-3l-3 3m5-3h4m-4 3h4"></path>
                    </svg>
                </div>`;
        }

        function syncInputFiles() {
            try {
                const dt = new DataTransfer();
                selectedFiles.forEach(f => dt.items.add(f));
                fileInput.files = dt.files;
            } catch (err) {
                console.warn('DataTransfer sync error:', err);
            }
        }

        function renderSelectedFiles() {
            if (selectedFiles.length === 0) {
                idleState.classList.remove('hidden');
                selectedState.classList.add('hidden');
                badgeContainer.classList.add('hidden');
                uploadHint.innerText = 'File yang diunggah akan langsung dipetakan ke tabel di bawah ini.';
                btnSubmitText.innerText = 'Proses & Generate QR Code';
                filesListContainer.innerHTML = '';
                return;
            }

            idleState.classList.add('hidden');
            selectedState.classList.remove('hidden');
            badgeContainer.classList.remove('hidden');

            const totalBytes = selectedFiles.reduce((acc, f) => acc + f.size, 0);
            const totalFormatted = formatBytes(totalBytes);

            badgeText.innerText = `${selectedFiles.length} file dipilih (${totalFormatted})`;
            selectedCountText.innerText = `(${selectedFiles.length} file)`;

            if (selectedFiles.length > 1) {
                uploadHint.innerText = `Semua ${selectedFiles.length} file akan diproses menjadi batch terpisah yang dapat dipilih pada filter batch.`;
                btnSubmitText.innerText = `Unggah ${selectedFiles.length} File & Proses Data`;
            } else {
                uploadHint.innerText = 'File yang diunggah akan langsung dipetakan ke tabel di bawah ini.';
                btnSubmitText.innerText = 'Proses & Generate QR Code';
            }

            filesListContainer.innerHTML = '';
            selectedFiles.forEach((file, idx) => {
                const card = document.createElement('div');
                card.className = 'flex items-center justify-between gap-3 bg-white p-2.5 rounded-xl border border-slate-200/90 shadow-2xs hover:border-blue-200 transition';
                card.innerHTML = `
                    <div class="flex items-center gap-2.5 min-w-0">
                        ${getFileIconHtml(file.name)}
                        <div class="text-left truncate">
                            <p class="text-xs font-semibold text-slate-800 truncate" title="${file.name}">
                                ${file.name}
                            </p>
                            <p class="text-[10px] text-slate-400">
                                ${formatBytes(file.size)}
                            </p>
                        </div>
                    </div>
                    <button type="button" class="js-remove-single-file w-6 h-6 rounded-md bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-400 flex items-center justify-center text-sm font-bold transition cursor-pointer shrink-0" data-index="${idx}" title="Hapus file ini">
                        &times;
                    </button>
                `;
                filesListContainer.appendChild(card);
            });
        }

        function addFiles(newFiles) {
            const allowed = ['xlsx', 'xls', 'csv', 'pdf'];
            const valid = [];
            let invalidCount = 0;

            Array.from(newFiles).forEach(f => {
                const ext = (f.name.split('.').pop() || '').toLowerCase();
                if (allowed.includes(ext)) {
                    valid.push(f);
                } else {
                    invalidCount++;
                }
            });

            if (invalidCount > 0) {
                alert('Beberapa file diabaikan karena bukan berformat .xlsx, .xls, .csv, atau .pdf.');
            }

            valid.forEach(f => {
                const exists = selectedFiles.some(existing => existing.name === f.name && existing.size === f.size);
                if (!exists) {
                    selectedFiles.push(f);
                }
            });

            syncInputFiles();
            renderSelectedFiles();
        }

        if (dropzone && fileInput) {
            dropzone.addEventListener('click', (e) => {
                if (e.target.closest('#dropzone-selected')) return;
                fileInput.value = '';
                fileInput.click();
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('dragover');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('dragover');
                }, false);
            });

            dropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                if (dt && dt.files && dt.files.length > 0) {
                    addFiles(dt.files);
                }
            });

            fileInput.addEventListener('change', (e) => {
                if (e.target.files && e.target.files.length > 0) {
                    addFiles(e.target.files);
                }
            });
        }

        if (btnAddMore) {
            btnAddMore.addEventListener('click', (e) => {
                e.stopPropagation();
                fileInput.value = '';
                fileInput.click();
            });
        }

        if (btnClearAll) {
            btnClearAll.addEventListener('click', (e) => {
                e.stopPropagation();
                selectedFiles = [];
                fileInput.value = '';
                syncInputFiles();
                renderSelectedFiles();
            });
        }

        if (filesListContainer) {
            filesListContainer.addEventListener('click', (e) => {
                const btn = e.target.closest('.js-remove-single-file');
                if (!btn) return;
                e.stopPropagation();
                const idx = parseInt(btn.getAttribute('data-index'), 10);
                if (!isNaN(idx) && idx >= 0 && idx < selectedFiles.length) {
                    selectedFiles.splice(idx, 1);
                    fileInput.value = '';
                    syncInputFiles();
                    renderSelectedFiles();
                }
            });
        }

        if (uploadForm) {
            uploadForm.addEventListener('submit', (e) => {
                if (selectedFiles.length === 0) {
                    e.preventDefault();
                    alert('Silakan pilih setidaknya satu file Excel, CSV, atau PDF terlebih dahulu.');
                    return;
                }
                syncInputFiles();
                btnSubmit.disabled = true;
                btnSubmitText.innerText = 'Memproses...';
            });
        }

        // ─── 2. DYNAMIC DATATABLE CONTROLLER (SEARCH & PAGINATION) ───
        let dtController = null;
        (function() {
            const tableBody = document.getElementById('dt-table-body');
            const searchInput = document.getElementById('dt-search-input');
            const searchClear = document.getElementById('dt-search-clear');
            const pageSizeSelect = document.getElementById('dt-page-size');
            const paginationContainer = document.getElementById('dt-pagination-container');
            const dtStart = document.getElementById('dt-start');
            const dtEnd = document.getElementById('dt-end');
            const dtTotal = document.getElementById('dt-total');

            let allRows = tableBody ? Array.from(tableBody.querySelectorAll('.dt-row')) : [];
            let currentPage = 1;
            let pageSize = pageSizeSelect ? (pageSizeSelect.value === 'all' ? 0 : (parseInt(pageSizeSelect.value, 10) || 10)) : 10;
            let filteredRows = [...allRows];

            function applyFilter() {
                const query = (searchInput ? searchInput.value || '' : '').trim().toLowerCase();
                if (query) {
                    if (searchClear) searchClear.classList.remove('hidden');
                    filteredRows = allRows.filter(row => {
                        const text = row.innerText.toLowerCase();
                        return text.includes(query);
                    });
                } else {
                    if (searchClear) searchClear.classList.add('hidden');
                    filteredRows = [...allRows];
                }
                currentPage = 1;
                renderTable();
            }

            function renderTable() {
                const total = filteredRows.length;
                const effectivePageSize = pageSize === 0 ? total : pageSize;
                const totalPages = Math.ceil(total / (effectivePageSize || 1)) || 1;

                if (currentPage > totalPages) currentPage = totalPages;
                if (currentPage < 1) currentPage = 1;

                const startIndex = (currentPage - 1) * effectivePageSize;
                const endIndex = Math.min(startIndex + effectivePageSize, total);

                // Hide all rows first
                allRows.forEach(r => r.style.display = 'none');

                // Show only paginated filtered rows
                for (let i = startIndex; i < endIndex; i++) {
                    if (filteredRows[i]) filteredRows[i].style.display = '';
                }

                // Update info text
                if (dtStart && dtEnd && dtTotal) {
                    if (total === 0) {
                        dtStart.innerText = '0';
                        dtEnd.innerText = '0';
                        dtTotal.innerText = '0';
                    } else {
                        dtStart.innerText = (startIndex + 1).toString();
                        dtEnd.innerText = endIndex.toString();
                        dtTotal.innerText = total.toString();
                    }
                }

                renderPaginationButtons(totalPages);
            }

            function renderPaginationButtons(totalPages) {
                if (!paginationContainer) return;
                paginationContainer.innerHTML = '';
                if (totalPages <= 1) return;

                // Previous Button
                const prevBtn = document.createElement('button');
                prevBtn.type = 'button';
                prevBtn.className = 'px-2.5 py-1 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition cursor-pointer';
                prevBtn.innerText = 'Sebelumnya';
                prevBtn.disabled = currentPage === 1;
                prevBtn.onclick = () => { if (currentPage > 1) { currentPage--; renderTable(); } };
                paginationContainer.appendChild(prevBtn);

                // Numbered Buttons
                let startPage = Math.max(1, currentPage - 2);
                let endPage = Math.min(totalPages, currentPage + 2);

                for (let p = startPage; p <= endPage; p++) {
                    const pageBtn = document.createElement('button');
                    pageBtn.type = 'button';
                    pageBtn.className = 'px-2.5 py-1 rounded-lg border text-xs font-semibold transition cursor-pointer ' + 
                        (p === currentPage 
                            ? 'bg-[#005baa] text-white border-[#005baa]' 
                            : 'border-slate-200 text-slate-700 hover:bg-slate-100');
                    pageBtn.innerText = p.toString();
                    const targetP = p;
                    pageBtn.onclick = () => { currentPage = targetP; renderTable(); };
                    paginationContainer.appendChild(pageBtn);
                }

                // Next Button
                const nextBtn = document.createElement('button');
                nextBtn.type = 'button';
                nextBtn.className = 'px-2.5 py-1 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition cursor-pointer';
                nextBtn.innerText = 'Selanjutnya';
                nextBtn.disabled = currentPage === totalPages;
                nextBtn.onclick = () => { if (currentPage < totalPages) { currentPage++; renderTable(); } };
                paginationContainer.appendChild(nextBtn);
            }

            function reinit() {
                const tbody = document.getElementById('dt-table-body');
                allRows = tbody ? Array.from(tbody.querySelectorAll('.dt-row')) : [];
                if (pageSizeSelect) {
                    const val = pageSizeSelect.value;
                    pageSize = val === 'all' ? 0 : (parseInt(val, 10) || 10);
                }
                if (searchInput) searchInput.value = '';
                if (searchClear) searchClear.classList.add('hidden');
                filteredRows = [...allRows];
                currentPage = 1;
                renderTable();
            }

            // Events
            if (searchInput) searchInput.addEventListener('input', applyFilter);
            if (searchClear) searchClear.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                applyFilter();
            });

            if (pageSizeSelect) pageSizeSelect.addEventListener('change', () => {
                const val = pageSizeSelect.value;
                pageSize = val === 'all' ? 0 : (parseInt(val, 10) || 10);
                currentPage = 1;
                renderTable();
            });

            dtController = { reinit, applyFilter, renderTable };
            window.dtController = dtController;

            // Initial render
            reinit();
        })();

        // ─── 3. DYNAMIC BATCH & CATEGORY CONTROLLERS (NO FULL PAGE RELOAD) ───
        const IGNORED_COLS = [
            'manager_administrasi', 'nip_manager_administrasi',
            'kepala_balai', 'nip_kepala_balai',
            'petugas_penerima', 'nip_petugas_penerima',
            'tanggal_spu', 'no_spu', 'nomor spu', 'nomor_spu',
            '_meta'
        ];

        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function buildTheadHtml(headers) {
            let html = `<tr class="bg-slate-50 text-slate-700 border-b border-slate-200">
                <th class="py-3 px-3.5 font-bold uppercase tracking-wider text-center w-12">No</th>`;

            headers.forEach(h => {
                const hLower = (h || '').trim().toLowerCase();
                if (hLower.includes('qr') || h.startsWith('_') || IGNORED_COLS.includes(hLower)) return;

                html += `<th class="py-3 px-3.5 font-bold uppercase tracking-wider text-slate-800 min-w-[180px]">`;
                if (h.includes(' - ')) {
                    const parts = h.split(' - ');
                    const mainH = (parts[0] || '').replace(/_/g, ' ');
                    const subH = (parts.slice(1).join(' - ') || '').replace(/_/g, ' ');
                    html += `<div class="text-[10px] text-slate-400 font-semibold normal-case tracking-normal">${escapeHtml(mainH)}</div>
                             <div class="text-xs font-bold text-slate-800">${escapeHtml(subH)}</div>`;
                } else {
                    html += escapeHtml(h.replace(/_/g, ' '));
                }
                html += `</th>`;
            });

            html += `
                <th class="py-3 px-4 font-bold uppercase tracking-wider text-center bg-blue-50/70 text-[#005baa] border-l border-blue-100 min-w-[120px]">
                    QR Code Ter-generate
                </th>
                <th class="py-3 px-4 font-bold uppercase tracking-wider text-center bg-slate-50 text-slate-700 border-l border-slate-200 min-w-[160px] sticky right-0 z-10 shadow-[-4px_0_8px_rgba(0,0,0,0.03)]">
                    Aksi
                </th>
            </tr>`;
            return html;
        }

        function buildTbodyHtml(items, headers) {
            let html = '';
            items.forEach((item, index) => {
                const scanUrl = item.target_url || (`/scan/${item.qr_token}`);
                const pdfUrl = `/item/${item.id}/pdf`;
                const qrImgUrl = item.qr_url || (`/qr/${item.qr_token}`);
                const displayTitle = item.display_title || (item.data ? (item.data['Nama Produk'] || item.data['NAMA PRODUK / SAMPEL'] || item.code || '') : (item.code || ''));
                const qrInfo = item.qr_content_display || item.qr_content || item.code || '-';

                html += `<tr class="dt-row hover:bg-blue-50/20 transition-colors" data-row-index="${index}">
                    <td class="dt-cell-no py-3 px-3.5 text-center text-slate-400 font-mono font-medium">
                        ${index + 1}
                    </td>`;

                headers.forEach(h => {
                    const hLower = (h || '').trim().toLowerCase();
                    if (hLower.includes('qr') || h.startsWith('_') || IGNORED_COLS.includes(hLower)) return;

                    let val = (item.data && item.data[h] !== undefined) ? item.data[h] : '-';
                    html += `<td class="dt-cell-data py-3 px-3.5 text-slate-900 font-medium max-w-sm">`;
                    if (typeof val === 'object' && val !== null) {
                        html += escapeHtml(JSON.stringify(val));
                    } else {
                        const valStr = String(val);
                        const vLower = valStr.toLowerCase();
                        if (vLower.includes('memenuhi syarat') || vLower === 'ms') {
                            html += `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">${escapeHtml(valStr)}</span>`;
                        } else if (vLower.includes('tms') || vLower.includes('tidak memenuhi')) {
                            html += `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-800">${escapeHtml(valStr)}</span>`;
                        } else {
                            html += escapeHtml(valStr);
                        }
                    }
                    html += `</td>`;
                });

                // QR Code column
                html += `
                    <td class="py-2.5 px-4 bg-blue-50/20 border-l border-blue-100 text-center">
                        <div class="flex items-center justify-center">
                            <button type="button" 
                                    class="js-btn-qr-modal p-1 bg-white rounded-xl border border-slate-200 hover:border-[#005baa] hover:shadow-md transition-all shrink-0 cursor-pointer" 
                                    title="Klik untuk melihat Pop-up QR Code"
                                    data-qr-token="${escapeHtml(item.qr_token || '')}"
                                    data-title="${escapeHtml(displayTitle)}"
                                    data-code="${escapeHtml(item.code || '')}"
                                    data-target-url="${escapeHtml(scanUrl)}"
                                    data-pdf-url="${escapeHtml(pdfUrl)}"
                                    data-info="${escapeHtml(qrInfo)}"
                                    data-qr-base64="${escapeHtml(item.qr_base64 || '')}">
                                <div class="w-14 h-14 flex items-center justify-center p-0.5">
                                    <img src="${escapeHtml(qrImgUrl)}" alt="QR Code" class="w-full h-full object-contain" loading="lazy" />
                                </div>
                            </button>
                        </div>
                    </td>`;

                // Action column
                html += `
                    <td class="py-2.5 px-4 bg-white border-l border-slate-200 sticky right-0 z-10 hover:bg-blue-50/30 transition-colors">
                        <div class="flex items-center justify-center gap-1.5 flex-wrap">
                            <button type="button"
                                    class="js-btn-qr-modal inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-[#005baa] hover:bg-[#004887] text-white shadow-2xs transition-colors cursor-pointer"
                                    title="Buka pop-up QR Code besar"
                                    data-qr-token="${escapeHtml(item.qr_token || '')}"
                                    data-title="${escapeHtml(displayTitle)}"
                                    data-code="${escapeHtml(item.code || '')}"
                                    data-target-url="${escapeHtml(scanUrl)}"
                                    data-pdf-url="${escapeHtml(pdfUrl)}"
                                    data-info="${escapeHtml(qrInfo)}"
                                    data-qr-base64="${escapeHtml(item.qr_base64 || '')}">
                                <span>Pop-up QR</span>
                            </button>

                            <a href="${escapeHtml(pdfUrl)}" 
                               target="_blank"
                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[11px] font-semibold bg-white hover:bg-rose-50 text-rose-700 border border-rose-200 shadow-2xs transition-colors"
                               title="Export dokumen PDF baris data ini">
                                <span>Export PDF</span>
                            </a>
                        </div>
                    </td>
                </tr>`;
            });
            return html;
        }

        function renderTableData(data) {
            const items = data.items || [];
            const headers = data.headers || [];
            const currentBatch = data.currentBatch || null;
            const siblingSheets = data.siblingSheets || [];
            const selectedBatchId = data.selectedBatchId || '';

            // 1. Update Title & Subtitle
            const badgeTotal = document.getElementById('dt-badge-total');
            if (badgeTotal) badgeTotal.innerText = `${items.length} Data`;

            const subEl = document.getElementById('dt-batch-subtitle');
            if (subEl) {
                if (currentBatch) {
                    let subHtml = `Sumber: <strong class="text-slate-700">${escapeHtml(currentBatch.filename || currentBatch.batch_id)}</strong>`;
                    if (currentBatch.sheet_name && currentBatch.sheet_name !== 'Sheet1') {
                        subHtml += ` &bull; Tab: <span class="text-[#005baa] font-semibold">${escapeHtml(currentBatch.sheet_name)}</span>`;
                    }
                    subEl.innerHTML = subHtml;
                } else {
                    subEl.innerHTML = '';
                }
            }

            // 2. Update Export & Delete Actions
            const btnExport = document.getElementById('btn-export-tabular');
            if (btnExport) {
                btnExport.href = `/batch/${encodeURIComponent(selectedBatchId)}/pdf-all`;
                btnExport.classList.toggle('hidden', items.length === 0);
            }
            const formDelete = document.getElementById('form-delete-batch');
            if (formDelete) {
                formDelete.action = `/batch/${encodeURIComponent(selectedBatchId)}`;
                formDelete.classList.toggle('hidden', !selectedBatchId);
            }

            // 3. Update Sibling Sheets
            const sibContainer = document.getElementById('sibling-sheets-container');
            const sibButtons = document.getElementById('sibling-sheets-buttons');
            const btnMerged = document.getElementById('btn-merged-pdf');

            if (siblingSheets && siblingSheets.length > 1) {
                if (sibContainer) sibContainer.classList.remove('hidden');
                if (sibButtons) {
                    sibButtons.innerHTML = siblingSheets.map(s => {
                        const isSel = s.batch_id === selectedBatchId;
                        return `<button type="button" onclick="onBatchSelectChange('${escapeHtml(s.batch_id)}')" class="js-sibling-tab-btn px-3 py-1 rounded-lg text-xs font-semibold border transition-all cursor-pointer ${isSel ? 'bg-[#005baa] text-white border-[#005baa]' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100'}" data-batch-id="${escapeHtml(s.batch_id)}">${escapeHtml(s.sheet_name || 'Tab')} (${s.total_items})</button>`;
                    }).join('');
                }
                if (btnMerged) {
                    if (currentBatch && currentBatch.file_group_id) {
                        btnMerged.href = `/file-group/${encodeURIComponent(currentBatch.file_group_id)}/pdf-merged`;
                        btnMerged.classList.remove('hidden');
                    } else {
                        btnMerged.classList.add('hidden');
                    }
                }
            } else {
                if (sibContainer) sibContainer.classList.add('hidden');
            }

            // 4. Update Controls, Empty Container, Table Content
            const dtControls = document.getElementById('dt-controls-container');
            if (dtControls) dtControls.classList.toggle('hidden', items.length === 0);

            const emptyContainer = document.getElementById('table-empty-container');
            const contentContainer = document.getElementById('table-content-container');

            if (items.length === 0) {
                if (emptyContainer) emptyContainer.classList.remove('hidden');
                if (contentContainer) contentContainer.classList.add('hidden');
            } else {
                if (emptyContainer) emptyContainer.classList.add('hidden');
                if (contentContainer) contentContainer.classList.remove('hidden');

                const tableThead = document.querySelector('#main-datatable thead');
                const tableBody = document.getElementById('dt-table-body');
                if (tableThead) tableThead.innerHTML = buildTheadHtml(headers);
                if (tableBody) tableBody.innerHTML = buildTbodyHtml(items, headers);

                if (window.dtController) {
                    window.dtController.reinit();
                }
            }
        }

        let isBatchLoading = false;
        async function loadBatchData(batchId) {
            if (!batchId || isBatchLoading) return;
            isBatchLoading = true;

            const tableEl = document.getElementById('main-datatable');
            if (tableEl) tableEl.style.opacity = '0.35';

            try {
                const response = await fetch('/api/pengujian?batch=' + encodeURIComponent(batchId), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!response.ok) throw new Error('HTTP ' + response.status);
                const data = await response.json();
                if (data && data.success) {
                    renderTableData(data);
                    try {
                        window.history.pushState(null, '', '/?batch=' + encodeURIComponent(batchId));
                    } catch (e) {}
                }
            } catch (err) {
                console.error('Gagal memuat batch data:', err);
            } finally {
                isBatchLoading = false;
                if (tableEl) tableEl.style.opacity = '1';
            }
        }
        window.loadBatchData = loadBatchData;

        function onBatchSelectChange(batchId) {
            const select = document.getElementById('batch-select');
            if (select && select.value !== batchId) {
                select.value = batchId;
            }
            loadBatchData(batchId);
        }
        window.onBatchSelectChange = onBatchSelectChange;

        window.currentCategory = 'ALL';
        function filterBatchesByCategory(targetCat) {
            window.currentCategory = targetCat;

            // 1. Update pill styling
            const pills = document.querySelectorAll('.js-cat-pill');
            pills.forEach(p => {
                if (p.getAttribute('data-cat') === targetCat) {
                    p.className = 'js-cat-pill active px-3 py-1 rounded-full text-xs font-bold transition-all bg-[#005baa] text-white shadow-xs cursor-pointer';
                } else {
                    p.className = 'js-cat-pill px-3 py-1 rounded-full text-xs font-bold transition-all bg-slate-100 text-slate-600 hover:bg-slate-200 cursor-pointer';
                }
            });

            // 2. Filter dropdown options
            const select = document.getElementById('batch-select');
            if (!select) return;

            let firstVisibleOpt = null;
            let currentSelectedIsVisible = false;

            Array.from(select.options).forEach(opt => {
                const optCat = opt.getAttribute('data-komoditi') || 'Lainnya';
                const isMatch = (targetCat === 'ALL' || optCat === targetCat);
                if (isMatch) {
                    opt.hidden = false;
                    opt.disabled = false;
                    opt.style.display = '';
                    if (!firstVisibleOpt) firstVisibleOpt = opt;
                    if (opt.value === select.value) currentSelectedIsVisible = true;
                } else {
                    opt.hidden = true;
                    opt.disabled = true;
                    opt.style.display = 'none';
                }
            });

            // 3. Auto-select first matching batch if current selection is not in the filtered category
            if (!currentSelectedIsVisible && firstVisibleOpt) {
                select.value = firstVisibleOpt.value;
                loadBatchData(firstVisibleOpt.value);
            }
        }
        window.filterBatchesByCategory = filterBatchesByCategory;

        function printBarcodeLabel() {
            const sampleCode = (document.getElementById('modal-sample-code').innerText || 'QR_Code').trim();
            const qrImg = document.getElementById('modal-qr-img');
            const sampleTitle = document.getElementById('modal-sample-title').innerText || 'Sampel Uji';
            const infoText = (document.getElementById('modal-qr-content').innerText || '').replace(/^Wilayah:\s*/gim, 'Lokasi Sampel: ');

            const printWindow = window.open('', '_blank', 'width=600,height=600');
            if (!printWindow) {
                alert('Harap izinkan pop-up pada browser untuk mencetak label barcode!');
                return;
            }

            printWindow.document.write(`<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cetak Label Barcode - ${sampleCode}</title>
    <style>
        @page { size: auto; margin: 3mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, Helvetica, sans-serif; background: #fff; color: #000; padding: 8px; display: flex; justify-content: center; }
        .label-card { width: 105mm; min-height: 52mm; border: 2px solid #000; border-radius: 6px; padding: 10px; background: #fff; }
        .label-header { display: flex; align-items: center; justify-content: space-between; border-bottom: 1.5px solid #000; padding-bottom: 5px; margin-bottom: 8px; }
        .header-title { font-size: 9.5pt; font-weight: bold; letter-spacing: 0.5px; text-transform: uppercase; }
        .header-sub { font-size: 7.5pt; color: #333; }
        .badge { font-size: 7.5pt; font-weight: bold; border: 1px solid #000; padding: 1px 5px; border-radius: 3px; }
        .label-body { display: flex; gap: 12px; align-items: flex-start; }
        .qr-box { width: 155px; text-align: center; flex-shrink: 0; }
        .qr-box img { width: 150px; height: 150px; display: block; margin: 0 auto; object-fit: contain; image-rendering: -webkit-optimize-contrast; image-rendering: crisp-edges; }
        .info-box { flex: 1; min-width: 0; }
        .code-title { font-family: monospace; font-size: 11pt; font-weight: bold; display: block; margin-bottom: 4px; word-break: break-all; border-bottom: 1px dashed #666; padding-bottom: 3px; }
        .sample-title { font-size: 8.5pt; font-weight: bold; line-height: 1.25; margin-bottom: 5px; }
        .info-text { font-size: 7.5pt; line-height: 1.35; color: #111; white-space: pre-line; max-height: 140px; overflow: hidden; word-break: break-word; }
        @media print { body { padding: 0; } .label-card { border-width: 1.5px; } }
    </style>
</head>
<body>
    <div class="label-card">
        <div class="label-header">
            <div>
                <div class="header-title">BALAI BESAR POM DI MATARAM</div>
                <div class="header-sub">Laboratorium Pengujian &bull; Kode Sampel</div>
            </div>
            <div class="badge">RESMI</div>
        </div>
        <div class="label-body">
            <div class="qr-box">
                <img src="${qrImg.src}" alt="QR" />
            </div>
            <div class="info-box">
                <div class="code-title">${sampleCode}</div>
                <div class="sample-title">${sampleTitle}</div>
                <div class="info-text">${infoText}</div>
            </div>
        </div>
    </div>
    ${'<'}script>
        window.onload = function() {
            setTimeout(function() { window.print(); }, 300);
        };
    ${'<'}/script>
</body>
</html>`);
            printWindow.document.close();
        }

        // ─── 4. POP-UP MODAL QR CODE BESAR ───
        const qrModal = document.getElementById('qr-modal');
        let currentRawQrContent = '';
        let currentTargetUrl = '';

        function openQrModal(qrToken, title, code, qrContent, scanUrl, pdfUrl, qrBase64) {
            const modal = document.getElementById('qr-modal');
            if (!modal) return;

            currentRawQrContent = (qrContent || code || '').replace(/^Wilayah:\s*/gim, 'Lokasi Sampel: ');
            currentTargetUrl = scanUrl || pdfUrl;

            const codeEl = document.getElementById('modal-sample-code');
            if (codeEl) codeEl.innerText = code || 'SMP';

            const titleEl = document.getElementById('modal-sample-title');
            if (titleEl) titleEl.innerText = title || 'Sampel Pengujian';

            const contentEl = document.getElementById('modal-qr-content');
            if (contentEl) contentEl.innerText = currentRawQrContent;
            
            const targetUrlEl = document.getElementById('modal-target-url');
            if (targetUrlEl) {
                targetUrlEl.innerText = currentTargetUrl;
                targetUrlEl.href = currentTargetUrl;
            }

            const btnPdf = document.getElementById('modal-btn-pdf');
            if (btnPdf) btnPdf.href = pdfUrl || currentTargetUrl;

            const qrImg = document.getElementById('modal-qr-img');
            if (qrImg) {
                const svgDataUri = qrBase64 ? ('data:image/svg+xml;base64,' + qrBase64) : ('/qr/' + qrToken);
                qrImg.src = svgDataUri;
            }

            modal.classList.remove('hidden');
        }

        // Attach global functions to window so inline onclick handlers never fail
        window.filterBatchesByCategory = filterBatchesByCategory;
        window.printBarcodeLabel = printBarcodeLabel;
        window.openQrModal = openQrModal;

        // Unduh QR Code dalam format PNG siap print (resolusi tinggi 800x800 dengan background putih)
        function downloadQrPng() {
            const qrImg = document.getElementById('modal-qr-img');
            if (!qrImg || !qrImg.src) return;

            const sampleCode = (document.getElementById('modal-sample-code').innerText || 'QR_Code').trim();
            const cleanFilename = 'QR-' + sampleCode.replace(/[^a-z0-9\.\-_]/gi, '_') + '.png';

            const canvas = document.createElement('canvas');
            const size = 800;
            const padding = 40;
            canvas.width = size;
            canvas.height = size;
            const ctx = canvas.getContext('2d');

            // Beri background putih solid agar saat dicetak tidak transparan atau menghitam
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, size, size);

            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = function() {
                ctx.drawImage(img, padding, padding, size - (padding * 2), size - (padding * 2));

                try {
                    canvas.toBlob(function(blob) {
                        if (blob) {
                            const url = URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = cleanFilename;
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                            setTimeout(() => URL.revokeObjectURL(url), 1000);
                        } else {
                            const dataUrl = canvas.toDataURL('image/png');
                            const a = document.createElement('a');
                            a.href = dataUrl;
                            a.download = cleanFilename;
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                        }
                    }, 'image/png', 1.0);
                } catch (e) {
                    const dataUrl = canvas.toDataURL('image/png');
                    const a = document.createElement('a');
                    a.href = dataUrl;
                    a.download = cleanFilename;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                }
            };
            img.src = qrImg.src;
        }

        function closeQrModal() {
            const modal = document.getElementById('qr-modal');
            if (modal) modal.classList.add('hidden');
            const qrImg = document.getElementById('modal-qr-img');
            if (qrImg) qrImg.src = '';
        }

        function copyQrContent() {
            if (!currentRawQrContent) return;
            navigator.clipboard.writeText(currentRawQrContent).then(() => {
                const textEl = document.getElementById('btn-copy-text');
                if (textEl) {
                    textEl.innerText = 'Tersalin!';
                    setTimeout(() => { textEl.innerText = 'Salin Rincian'; }, 2000);
                }
            });
        }

        function copyTargetUrl() {
            if (!currentTargetUrl) return;
            navigator.clipboard.writeText(currentTargetUrl).then(() => {
                const textEl = document.getElementById('btn-copy-url-text');
                if (textEl) {
                    textEl.innerText = 'Tersalin!';
                    setTimeout(() => { textEl.innerText = 'Salin Link'; }, 2000);
                }
            });
        }

        window.downloadQrPng = downloadQrPng;
        window.closeQrModal = closeQrModal;
        window.copyQrContent = copyQrContent;
        window.copyTargetUrl = copyTargetUrl;

        // Event delegation for opening QR Modal
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.js-btn-qr-modal');
            if (!btn) return;
            e.preventDefault();

            const ds = btn.dataset;
            openQrModal(
                ds.qrToken || '',
                ds.title || '',
                ds.code || '',
                ds.info || '',
                ds.targetUrl || '',
                ds.pdfUrl || '',
                ds.qrBase64 || ''
            );
        });

        // Close on ESC key or clicking outside backdrop
        window.addEventListener('keydown', (e) => {
            const modal = document.getElementById('qr-modal');
            if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
                closeQrModal();
            }
        });

        document.addEventListener('click', (e) => {
            const modal = document.getElementById('qr-modal');
            if (modal && e.target === modal) {
                closeQrModal();
            }
        });

        // ─── 4. PHOTO EXPORT MODAL LOGIC ───
        const photoModal = document.getElementById('photo-export-modal');
        const photoForm = document.getElementById('photo-export-form');
        const photoSubtitle = document.getElementById('photo-modal-subtitle');
        const photoGrid = document.getElementById('photo-preview-grid');
        const photoContainer = document.getElementById('photo-preview-container');

        function openPhotoModal(actionUrl, code, title) {
            if (!photoModal) return;
            photoForm.action = actionUrl;
            photoSubtitle.innerText = `${code} • ${title}`;
            photoGrid.innerHTML = '';
            photoContainer.classList.add('hidden');
            document.getElementById('photo-file-input').value = '';
            photoModal.classList.remove('hidden');
        }

        function closePhotoModal() {
            if (!photoModal) return;
            photoModal.classList.add('hidden');
        }

        function handlePhotoSelect(input) {
            if (!input.files || input.files.length === 0) {
                photoContainer.classList.add('hidden');
                photoGrid.innerHTML = '';
                return;
            }

            photoGrid.innerHTML = '';
            photoContainer.classList.remove('hidden');

            const defaultCaptions = [
                'Dokumentasi Tampak Depan Sampel',
                'Dokumentasi Label & No. Batch',
                'Dokumentasi Bentuk Sediaan',
                'Dokumentasi Hasil Pengujian / Reaksi'
            ];

            Array.from(input.files).slice(0, 4).forEach((file, idx) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const card = document.createElement('div');
                    card.className = 'border border-slate-200 rounded-xl p-2 bg-slate-50 flex flex-col gap-1.5';
                    card.innerHTML = `
                        <div class="h-24 bg-white rounded-lg border border-slate-200 overflow-hidden flex items-center justify-center">
                            <img src="${e.target.result}" class="max-h-full max-w-full object-contain" alt="Preview Foto">
                        </div>
                        <input type="text" name="captions[]" value="${defaultCaptions[idx] || ('Dokumentasi Foto ' + (idx + 1))}" 
                               placeholder="Keterangan Foto ${idx + 1}" 
                               class="w-full text-[11px] px-2 py-1 bg-white border border-slate-200 rounded-md focus:outline-none focus:border-emerald-600">
                    `;
                    photoGrid.appendChild(card);
                };
                reader.readAsDataURL(file);
            });
        }

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.js-btn-photo-modal');
            if (!btn) return;
            e.preventDefault();
            const ds = btn.dataset;
            openPhotoModal(ds.actionUrl, ds.code, ds.title);
        });

        if (photoModal) {
            photoModal.addEventListener('click', (e) => {
                if (e.target === photoModal) {
                    closePhotoModal();
                }
            });
        }
    </script>
</body>
</html>