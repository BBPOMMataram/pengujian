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
                <h2 class="text-base font-bold text-slate-900">
                    Unggah Dokumen Pengujian (Excel / CSV / PDF)
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Mendukung file Excel (.xlsx, .xls), CSV, atau PDF Surat Permintaan Uji (SPU). Sistem otomatis memproses data dan menghasilkan QR Code untuk verifikasi.
                </p>
            </div>

            <form action="{{ route('pengujian.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- HALO ANIMATED DROPZONE CONTAINER -->
                <div class="halo-container">
                    <!-- Rotating Glowing Conic Trail in BBPOM Colors -->
                    <div class="halo-trail"></div>
                    <!-- Blurred Glowing Blobs -->
                    <div class="halo-blob-1"></div>
                    <div class="halo-blob-2"></div>

                    <!-- Inner Frosted Glass Card -->
                    <div id="halo-dropzone" class="halo-inner p-6 sm:p-8 text-center">
                        <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls,.csv,.pdf" class="hidden" required>

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
                                    Pilih atau seret file ke area ini
                                </p>
                                <p class="text-xs text-slate-400 mt-1">
                                    Format didukung: <strong>.xlsx, .xls, .csv, atau .pdf</strong> (Maksimal 25 MB)
                                </p>
                            </div>
                        </div>

                        <!-- SELECTED STATE: FILE DETAILS & REMOVE BUTTON -->
                        <div id="dropzone-selected" class="hidden py-1">
                            <div class="inline-flex items-center gap-3 bg-white p-3 rounded-xl border border-slate-200 shadow-sm">
                                <div id="selected-icon" class="w-10 h-10 rounded-lg bg-blue-50 text-[#005baa] flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div class="text-left pr-2">
                                    <p id="selected-name" class="text-xs sm:text-sm font-bold text-slate-900 truncate max-w-xs sm:max-w-md">-</p>
                                    <p id="selected-size" class="text-[11px] text-slate-400 mt-0.5">-</p>
                                </div>
                                <button type="button" id="btn-remove-file" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-500 flex items-center justify-center transition" title="Batalkan pilihan file">
                                    &times;
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Action -->
                <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="text-xs text-slate-500">
                        File yang diunggah akan langsung dipetakan ke tabel di bawah ini.
                    </div>
                    <button type="submit" id="btn-submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-[#005baa] hover:bg-[#004887] text-white font-semibold text-xs sm:text-sm shadow-sm transition">
                        <span>Proses &amp; Generate QR Code</span>
                    </button>
                </div>
            </form>
        </section>

        <!-- SECTION 2: BATCH SELECTION & FILTER -->
        @if($batches->isNotEmpty())
            <div class="space-y-3">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">File / Batch:</span>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            @foreach($batches as $b)
                                <a href="{{ route('pengujian.index', ['batch' => $b->batch_id]) }}" 
                                   class="px-3 py-1 rounded-lg text-xs font-medium border transition-colors {{ $selectedBatchId === $b->batch_id ? 'bg-[#005baa] text-white border-[#005baa] shadow-2xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                                    {{ $b->filename ?: $b->batch_id }} 
                                    @if(!empty($b->sheet_name) && $b->sheet_name !== 'Sheet1')
                                        <span class="opacity-80">[{{ $b->sheet_name }}]</span>
                                    @endif
                                    ({{ $b->total_items }})
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Batch Export and Delete Actions -->
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($selectedBatchId && $items->isNotEmpty())
                            <a href="{{ route('pengujian.batch.pdf', $selectedBatchId) }}" 
                               target="_blank"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition shadow-2xs"
                               title="Unduh seluruh data dalam format Surat Permintaan Uji PDF">
                                <span>Export PDF Tabular</span>
                            </a>

                            <form action="{{ route('pengujian.batch.destroy', $selectedBatchId) }}" method="POST" onsubmit="return confirm('Hapus batch ini?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50 border border-rose-200 transition">
                                    <span>Hapus</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- SUB-BAR JIKA FILE MEMILIKI BEBERAPA TAB / SHEETS -->
                @if($siblingSheets->count() > 1)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-blue-50/80 border border-blue-200 p-3 rounded-xl shadow-2xs">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-bold text-blue-900">
                                Tab File Ini:
                            </span>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                @foreach($siblingSheets as $sheet)
                                    <a href="{{ route('pengujian.index', ['batch' => $sheet->batch_id]) }}" 
                                       class="px-3 py-1 rounded-lg text-xs font-semibold border transition-all {{ $selectedBatchId === $sheet->batch_id ? 'bg-[#005baa] text-white border-[#005baa]' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                                        {{ $sheet->sheet_name ?: 'Tab' }} ({{ $sheet->total_items }})
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        @if($currentBatch && $currentBatch->file_group_id)
                            <a href="{{ route('pengujian.filegroup.pdf', $currentBatch->file_group_id) }}" 
                               target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-[#005baa] hover:bg-[#004887] text-white shadow-xs transition shrink-0">
                                <span>Export Gabungan (Semua Tab ke 1 PDF)</span>
                            </a>
                        @endif
                    </div>
                @endif
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
                    @if($currentBatch)
                        <p class="text-xs text-slate-500 mt-0.5">
                            Sumber: <strong class="text-slate-700">{{ $currentBatch->filename }}</strong>
                            @if(!empty($currentBatch->sheet_name) && $currentBatch->sheet_name !== 'Sheet1')
                                &bull; Tab: <span class="text-[#005baa] font-semibold">{{ $currentBatch->sheet_name }}</span>
                            @endif
                        </p>
                    @endif
                </div>

                @if($items->isNotEmpty())
                    <!-- Live Search & Page Size Selector -->
                    <div class="flex items-center gap-2.5 flex-wrap">
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
                @endif
            </div>

            @if($items->isEmpty())
                <div class="p-12 text-center">
                    <p class="text-sm font-semibold text-slate-700">Belum ada data pengujian yang dimuat</p>
                    <p class="text-xs text-slate-400 mt-1">Silakan unggah file Excel, CSV, atau Dokumen PDF pada formulir di atas.</p>
                </div>
            @else
                <div class="overflow-x-auto custom-scrollbar">
                    <table id="main-datatable" class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-700 border-b border-slate-200">
                                <th class="py-3 px-3.5 font-bold uppercase tracking-wider text-center w-12">No</th>
                                
                                <!-- DYNAMIC DATA HEADERS FROM EXCEL -->
                                @foreach($headers as $h)
                                    @if(!str_contains(strtolower($h), 'qr'))
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
                                        @if(!str_contains(strtolower($h), 'qr'))
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
                                                    {!! $item->qr_svg_small !!}
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
            @endif
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
                <div class="grid grid-cols-2 gap-2.5 pt-1">
                    <button type="button" id="modal-btn-download-png" onclick="downloadQrPng()" class="inline-flex items-center justify-center py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs transition-all border border-slate-200" title="Unduh gambar QR Code berformat PNG siap cetak / print">
                        <span>Unduh QR (PNG)</span>
                    </button>

                    <a id="modal-btn-pdf" href="#" target="_blank" class="inline-flex items-center justify-center py-2.5 px-3 rounded-xl bg-[#005baa] hover:bg-[#004887] text-white font-bold text-xs shadow-md shadow-blue-500/20 transition-all">
                        <span>Buka PDF Baris Ini</span>
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

    <!-- CLIENT-SIDE LOGIC: HALO DROPZONE & DYNAMIC DATATABLE -->
    <script>
        // ─── 1. HALO DROPZONE HANDLERS ───
        const dropzone = document.getElementById('halo-dropzone');
        const fileInput = document.getElementById('excel_file');
        const idleState = document.getElementById('dropzone-idle');
        const selectedState = document.getElementById('dropzone-selected');
        const selectedName = document.getElementById('selected-name');
        const selectedSize = document.getElementById('selected-size');
        const btnRemove = document.getElementById('btn-remove-file');

        if (dropzone && fileInput) {
            dropzone.addEventListener('click', (e) => {
                if (e.target.closest('#btn-remove-file')) return;
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
                    fileInput.files = dt.files;
                    updateFileDisplay();
                }
            });

            fileInput.addEventListener('change', updateFileDisplay);
        }

        function updateFileDisplay() {
            if (fileInput.files && fileInput.files[0]) {
                const file = fileInput.files[0];
                selectedName.innerText = file.name;
                selectedSize.innerText = (file.size / 1024).toFixed(1) + ' KB';
                idleState.classList.add('hidden');
                selectedState.classList.remove('hidden');
            } else {
                idleState.classList.remove('hidden');
                selectedState.classList.add('hidden');
            }
        }

        if (btnRemove) {
            btnRemove.addEventListener('click', (e) => {
                e.stopPropagation();
                fileInput.value = '';
                updateFileDisplay();
            });
        }

        // ─── 2. DYNAMIC DATATABLE (SEARCH & PAGINATION) ───
        (function() {
            const tableBody = document.getElementById('dt-table-body');
            if (!tableBody) return;

            const allRows = Array.from(tableBody.querySelectorAll('.dt-row'));
            const searchInput = document.getElementById('dt-search-input');
            const searchClear = document.getElementById('dt-search-clear');
            const pageSizeSelect = document.getElementById('dt-page-size');
            const paginationContainer = document.getElementById('dt-pagination-container');
            const dtStart = document.getElementById('dt-start');
            const dtEnd = document.getElementById('dt-end');
            const dtTotal = document.getElementById('dt-total');

            let currentPage = 1;
            let pageSize = parseInt(pageSizeSelect.value, 10) || 10;
            let filteredRows = [...allRows];

            function applyFilter() {
                const query = (searchInput.value || '').trim().toLowerCase();
                if (query) {
                    searchClear.classList.remove('hidden');
                    filteredRows = allRows.filter(row => {
                        const text = row.innerText.toLowerCase();
                        return text.includes(query);
                    });
                } else {
                    searchClear.classList.add('hidden');
                    filteredRows = [...allRows];
                }
                currentPage = 1;
                renderTable();
            }

            function renderTable() {
                const total = filteredRows.length;
                const effectivePageSize = pageSize === 0 ? total : pageSize;
                const totalPages = Math.ceil(total / effectivePageSize) || 1;

                if (currentPage > totalPages) currentPage = totalPages;
                if (currentPage < 1) currentPage = 1;

                const startIndex = (currentPage - 1) * effectivePageSize;
                const endIndex = Math.min(startIndex + effectivePageSize, total);

                // Hide all rows first
                allRows.forEach(r => r.style.display = 'none');

                // Show only paginated filtered rows
                for (let i = startIndex; i < endIndex; i++) {
                    filteredRows[i].style.display = '';
                }

                // Update info text
                if (total === 0) {
                    dtStart.innerText = '0';
                    dtEnd.innerText = '0';
                    dtTotal.innerText = '0';
                } else {
                    dtStart.innerText = (startIndex + 1).toString();
                    dtEnd.innerText = endIndex.toString();
                    dtTotal.innerText = total.toString();
                }

                renderPaginationButtons(totalPages);
            }

            function renderPaginationButtons(totalPages) {
                paginationContainer.innerHTML = '';
                if (totalPages <= 1) return;

                // Previous Button
                const prevBtn = document.createElement('button');
                prevBtn.type = 'button';
                prevBtn.className = 'px-2.5 py-1 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition';
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
                    pageBtn.className = 'px-2.5 py-1 rounded-lg border text-xs font-semibold transition ' + 
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
                nextBtn.className = 'px-2.5 py-1 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition';
                nextBtn.innerText = 'Selanjutnya';
                nextBtn.disabled = currentPage === totalPages;
                nextBtn.onclick = () => { if (currentPage < totalPages) { currentPage++; renderTable(); } };
                paginationContainer.appendChild(nextBtn);
            }

            // Events
            searchInput.addEventListener('input', applyFilter);
            searchClear.addEventListener('click', () => {
                searchInput.value = '';
                applyFilter();
            });

            pageSizeSelect.addEventListener('change', () => {
                const val = pageSizeSelect.value;
                pageSize = val === 'all' ? 0 : (parseInt(val, 10) || 10);
                currentPage = 1;
                renderTable();
            });

            // Initial render
            renderTable();
        })();

        // ─── 3. POP-UP MODAL QR CODE BESAR ───
        const modal = document.getElementById('qr-modal');
        let currentRawQrContent = '';
        let currentTargetUrl = '';

        function openQrModal(qrToken, title, code, qrContent, scanUrl, pdfUrl, qrBase64) {
            currentRawQrContent = qrContent || code;
            currentTargetUrl = scanUrl || pdfUrl;

            document.getElementById('modal-sample-code').innerText = code || 'SMP';
            document.getElementById('modal-sample-title').innerText = title || 'Sampel Pengujian';
            document.getElementById('modal-qr-content').innerText = currentRawQrContent;
            
            const targetUrlEl = document.getElementById('modal-target-url');
            if (targetUrlEl) {
                targetUrlEl.innerText = currentTargetUrl;
                targetUrlEl.href = currentTargetUrl;
            }

            document.getElementById('modal-btn-pdf').href = pdfUrl || currentTargetUrl;

            const qrImg = document.getElementById('modal-qr-img');
            const svgDataUri = qrBase64 ? ('data:image/svg+xml;base64,' + qrBase64) : ('/qr/' + qrToken);
            qrImg.src = svgDataUri;

            modal.classList.remove('hidden');
        }

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
            modal.classList.add('hidden');
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
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeQrModal();
            }
        });

        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeQrModal();
                }
            });
        }
    </script>
</body>
</html>