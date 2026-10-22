<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorium Pengujian BBPOM - Upload Excel & Generator QR Code PDF</title>
    <!-- Tailwind CSS CDN -->
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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex flex-col md:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#005baa] to-blue-600 flex items-center justify-center text-white font-extrabold text-sm shadow-md shadow-blue-500/20">
                    BPOM
                </div>
                <div>
                    <h1 class="text-base sm:text-lg font-bold text-slate-900 leading-tight">
                        Laboratorium Pengujian BBPOM di Mataram
                    </h1>
                    <p class="text-xs text-slate-500">
                        Unggah Excel &bull; Otomatis Generate QR Code &bull; Pop-up Detail &bull; Export PDF Per Baris
                    </p>
                </div>
            </div>

            <!-- Header Quick Actions -->
            <div class="flex items-center gap-2 flex-wrap justify-end">
                <!-- Dropdown Download Template -->
                <div class="relative inline-block text-left" id="template-dropdown-wrapper">
                    <button type="button" onclick="toggleTemplateMenu()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors shadow-2xs">
                        <span class="material-symbols-outlined text-[17px]">download</span>
                        <span>Download Template</span>
                        <span class="material-symbols-outlined text-[14px]">expand_more</span>
                    </button>
                    <div id="template-menu" class="hidden absolute right-0 mt-1 w-64 rounded-xl bg-white shadow-lg border border-slate-200 py-1.5 z-40 text-xs">
                        <a href="{{ route('pengujian.template.2col') }}" class="flex items-center gap-2 px-3 py-2 text-slate-700 hover:bg-emerald-50 hover:text-emerald-800">
                            <span class="material-symbols-outlined text-emerald-600 text-[18px]">table_rows</span>
                            <div>
                                <p class="font-bold">Template 2 Kolom</p>
                                <p class="text-[10px] text-slate-500">1 Kolom Data &amp; 1 Kolom Kosong QR Code</p>
                            </div>
                        </a>
                        <div class="border-t border-slate-100 my-1"></div>
                        <a href="{{ route('pengujian.template.full') }}" class="flex items-center gap-2 px-3 py-2 text-slate-700 hover:bg-blue-50 hover:text-blue-800">
                            <span class="material-symbols-outlined text-blue-600 text-[18px]">view_column</span>
                            <div>
                                <p class="font-bold">Template Multi-Kolom</p>
                                <p class="text-[10px] text-slate-500">Format lengkap sampel laboratorium</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Quick Load Sample 2-Kolom -->
                <form action="{{ route('pengujian.sample.2col') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-50 text-[#005baa] border border-blue-200 hover:bg-blue-100 transition-colors shadow-2xs" title="Muat contoh format 1 kolom data & 1 kolom kosong">
                        <span class="material-symbols-outlined text-[17px]">science</span>
                        <span>Contoh 2-Kolom</span>
                    </button>
                </form>

                <!-- Quick Load Sample Multi-Kolom -->
                <form action="{{ route('pengujian.sample.full') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition-colors shadow-2xs" title="Muat data contoh laboratorium lengkap">
                        <span class="material-symbols-outlined text-[17px]">lab_research</span>
                        <span>Contoh Lengkap</span>
                    </button>
                </form>

                <!-- Quick Load Sample PDF SPU -->
                <form action="{{ route('pengujian.sample.pdf') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100 transition-colors shadow-2xs" title="Muat contoh ekstraksi dari Dokumen PDF Surat Permintaan Uji (SPU)">
                        <span class="material-symbols-outlined text-[17px]">picture_as_pdf</span>
                        <span>Contoh SPU (PDF)</span>
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
                    <span class="text-xs text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full font-medium ml-1">
                        Total <strong>{{ number_format($totalOverall, 0, ',', '.') }}</strong> Data
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
                <span class="material-symbols-outlined text-emerald-600 mt-0.5 shrink-0">check_circle</span>
                <div class="flex-1 font-medium">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-start gap-3 shadow-2xs">
                <span class="material-symbols-outlined text-rose-600 mt-0.5 shrink-0">error</span>
                <div class="flex-1 font-medium">{{ session('error') }}</div>
            </div>
        @endif

        @if(!empty($dbError))
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm flex items-start gap-3 shadow-2xs">
                <span class="material-symbols-outlined text-amber-600 mt-0.5 shrink-0">warning</span>
                <div class="flex-1">
                    <strong class="font-bold">Pemberitahuan Status Database:</strong>
                    <div class="text-xs font-mono mt-1 break-all">{{ $dbError }}</div>
                    <div class="text-xs mt-1 text-amber-700">Database sedang disiapkan atau dimigrasi ulang secara otomatis. Silakan muat contoh atau unggah file.</div>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-2xs">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- SECTION 1: UPLOAD CARD (Excel / CSV / PDF) -->
        <section class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-6 sm:p-7">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#005baa]">upload_file</span>
                            Unggah Dokumen Data Pengujian (Excel / CSV / PDF)
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                            Unggah file <strong>Excel (.xlsx, .xls)</strong>, <strong>CSV</strong>, atau dokumen <strong>PDF (Surat Permintaan Uji / SPU)</strong>. Sistem otomatis mengekstrak seluruh data sampel ke dalam tabel dan menghasilkan <strong>QR Code tepat di kolom sampingnya</strong>.
                        </p>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-500 bg-slate-50 px-3 py-2 rounded-xl border border-slate-200 shrink-0">
                        <span class="material-symbols-outlined text-[#005baa] text-[18px]">info</span>
                        <span>Mendukung: <strong>.xlsx, .xls, .csv, .pdf</strong></span>
                    </div>
                </div>

                <form action="{{ route('pengujian.upload') }}" method="POST" enctype="multipart/form-data" class="mt-5">
                    @csrf
                    <div id="dropzone" class="border-2 border-dashed border-slate-300 hover:border-[#005baa] rounded-2xl p-6 sm:p-8 text-center transition-all cursor-pointer bg-slate-50/50 hover:bg-blue-50/20 group">
                        <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls,.csv,.pdf" class="hidden" required onchange="handleFileSelected(this)">
                        
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-blue-100/70 text-[#005baa] flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined text-3xl">cloud_upload</span>
                        </div>

                        <div id="file-label" class="space-y-1">
                            <p class="text-sm font-semibold text-slate-700">
                                Klik untuk memilih file Excel, CSV, atau Dokumen PDF atau seret &amp; letakkan ke sini
                            </p>
                            <p class="text-xs text-slate-400">
                                Mendukung Excel (1 kolom data &amp; 1 kolom kosong / multi-kolom) serta Dokumen PDF resmi BBPOM (Surat Permintaan Uji / SPU)
                            </p>
                        </div>

                        <div id="file-selected-name" class="hidden mt-3 text-xs sm:text-sm font-semibold text-[#005baa] bg-blue-100/60 inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full">
                            <span class="material-symbols-outlined text-base">description</span>
                            <span id="selected-filename-text">-</span>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div class="text-xs text-slate-500 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-emerald-600 text-[18px]">verified</span>
                            <span>QR Code otomatis berisikan informasi teks kolom sebelahnya &amp; dapat diekspor ke PDF per baris.</span>
                        </div>

                        <button type="submit" id="btn-submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-[#005baa] hover:bg-[#004887] active:scale-98 text-white font-semibold text-sm shadow-md shadow-blue-500/20 transition-all">
                            <span class="material-symbols-outlined text-[19px]">qr_code_scanner</span>
                            <span>Proses &amp; Generate QR Code</span>
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- SECTION 2: BATCH SELECTION & FILTER -->
        @if($batches->isNotEmpty())
            <div class="space-y-3">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200/80 shadow-2xs">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Pilih File/Batch:</span>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            @foreach($batches as $b)
                                <a href="{{ route('pengujian.index', ['batch' => $b->batch_id]) }}" 
                                   class="px-3 py-1 rounded-lg text-xs font-medium border transition-colors {{ $selectedBatchId === $b->batch_id ? 'bg-[#005baa] text-white border-[#005baa] shadow-2xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                                    {{ $b->filename ?: $b->batch_id }} 
                                    @if(!empty($b->sheet_name) && $b->sheet_name !== 'Sheet1')
                                        <span class="opacity-80">[{{ $b->sheet_name }}]</span>
                                    @endif
                                    ({{ $b->total_items }} data)
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Search and Batch Actions -->
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($selectedBatchId && $items->isNotEmpty())
                            <!-- Export PDF Tabular (Otomatis Landscape/Portrait) -->
                            <a href="{{ route('pengujian.batch.pdf', $selectedBatchId) }}" 
                               target="_blank"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition shadow-2xs"
                               title="Unduh seluruh data baris dalam tab ini ke dokumen PDF (otomatis Landscape jika kolom banyak)">
                                <span class="material-symbols-outlined text-[16px]">picture_as_pdf</span>
                                <span>Export PDF Tabular</span>
                            </a>

                            <form action="{{ route('pengujian.batch.destroy', $selectedBatchId) }}" method="POST" onsubmit="return confirm('Hapus file / batch ini?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50 border border-rose-200 transition" title="Hapus Batch Ini">
                                    <span class="material-symbols-outlined text-[15px]">delete</span>
                                    <span>Hapus</span>
                                </button>
                            </form>
                        @endif

                        <form action="{{ route('pengujian.index') }}" method="GET" class="flex items-center gap-2">
                            <input type="hidden" name="batch" value="{{ $selectedBatchId }}">
                            <div class="relative">
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari data/informasi..." 
                                       class="pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-300 focus:outline-none focus:border-[#005baa] focus:ring-1 focus:ring-[#005baa] w-48 sm:w-60">
                                <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[16px]">search</span>
                            </div>
                            @if(request('q'))
                                <a href="{{ route('pengujian.index', ['batch' => $selectedBatchId]) }}" class="text-xs text-rose-600 hover:underline">Reset</a>
                            @endif
                        </form>
                    </div>
                </div>

                <!-- SUB-BAR JIKA FILE MEMILIKI BEBERAPA TAB / SHEETS -->
                @if($siblingSheets->count() > 1)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-blue-50/80 border border-blue-200 p-3 rounded-xl shadow-2xs">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-bold text-blue-900 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[17px] text-[#005baa]">tab</span>
                                Tab Dalam File Ini:
                            </span>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                @foreach($siblingSheets as $sheet)
                                    <a href="{{ route('pengujian.index', ['batch' => $sheet->batch_id]) }}" 
                                       class="px-3 py-1 rounded-lg text-xs font-semibold border transition-all {{ $selectedBatchId === $sheet->batch_id ? 'bg-[#005baa] text-white border-[#005baa] shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                                        {{ $sheet->sheet_name ?: 'Tab' }} ({{ $sheet->total_items }} data)
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <!-- Tombol Export PDF Gabungan Seluruh Tab -->
                        @if($currentBatch && $currentBatch->file_group_id)
                            <a href="{{ route('pengujian.filegroup.pdf', $currentBatch->file_group_id) }}" 
                               target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition shrink-0"
                               title="Unduh satu laporan PDF gabungan yang menyatukan seluruh tab dari file ini">
                                <span class="material-symbols-outlined text-[16px]">merge</span>
                                <span>Export Gabungan (Semua Tab ke 1 PDF)</span>
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        <!-- SECTION 3: DATA TABLE WITH QR CODE COLUMN & ACTIONS -->
        <section class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#005baa]">table_chart</span>
                        Tabel Data Pengujian &amp; QR Code
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-100 text-[#005baa]">
                            {{ $items->count() }} Baris
                        </span>
                    </h3>
                    @if($currentBatch)
                        <p class="text-xs text-slate-500 mt-0.5">
                            File: <strong class="text-slate-700">{{ $currentBatch->filename }}</strong>
                            @if(!empty($currentBatch->sheet_name) && $currentBatch->sheet_name !== 'Sheet1')
                                &bull; Tab: <span class="px-2 py-0.5 rounded-md bg-blue-100 text-[#005baa] font-bold">{{ $currentBatch->sheet_name }}</span>
                            @endif
                            &bull; Diunggah pada {{ \Carbon\Carbon::parse($currentBatch->created_at)->format('d/m/Y H:i') }} WITA
                        </p>
                    @endif
                </div>

                <div class="flex items-center gap-3 text-xs text-slate-500">
                    <span class="flex items-center gap-1.5">
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        Kolom QR Code otomatis terisi dari info kolom sebelahnya
                    </span>
                </div>
            </div>

            @if($items->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                        <span class="material-symbols-outlined text-3xl">inventory_2</span>
                    </div>
                    <h4 class="text-sm font-semibold text-slate-700">Belum ada data yang ditampilkan</h4>
                    <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                        Silakan unggah file Excel Anda di atas (bisa 1 kolom data &amp; 1 kolom kosong) atau coba dengan tombol contoh di bawah.
                    </p>
                    <div class="mt-5 flex flex-wrap items-center justify-center gap-2.5">
                        <a href="{{ route('pengujian.template.2col') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-xs">
                            <span class="material-symbols-outlined text-[17px]">download</span>
                            Download Template 2-Kolom
                        </a>
                        <form action="{{ route('pengujian.sample.2col') }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-[#005baa] text-white hover:bg-[#004887] transition shadow-xs">
                                <span class="material-symbols-outlined text-[17px]">play_arrow</span>
                                Muat Contoh 2-Kolom Sekarang
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-100/90 text-slate-700 border-b border-slate-200">
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

                                <!-- GENERATED QR CODE COLUMN (DI KOLOM SAMPINGNYA) -->
                                <th class="py-3 px-4 font-bold uppercase tracking-wider text-center bg-blue-50/90 text-[#005baa] border-l border-blue-200 min-w-[130px]">
                                    QR Code Ter-generate
                                </th>

                                <!-- AKSI PER BARIS DATA -->
                                <th class="py-3 px-4 font-bold uppercase tracking-wider text-center bg-slate-100 text-slate-700 border-l border-slate-200 min-w-[170px] sticky right-0 z-10 shadow-[-4px_0_8px_rgba(0,0,0,0.03)]">
                                    Aksi Baris Data
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($items as $index => $item)
                                <tr class="hover:bg-blue-50/20 transition-colors group">
                                    <td class="py-3 px-3.5 text-center text-slate-400 font-mono font-medium">
                                        {{ $index + 1 }}
                                    </td>

                                    <!-- DYNAMIC DATA CELLS -->
                                    @foreach($headers as $h)
                                        @if(!str_contains(strtolower($h), 'qr'))
                                            <td class="py-3 px-3.5 text-slate-900 font-medium max-w-sm">
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

                                    <!-- KOLOM SAMPINGNYA: QR CODE IMAGE -->
                                    <td class="py-2.5 px-4 bg-blue-50/30 border-l border-blue-100 text-center">
                                        <div class="flex items-center justify-center">
                                            <button type="button" 
                                                    class="js-btn-qr-modal p-1 bg-white rounded-xl border border-slate-200 hover:border-[#005baa] hover:shadow-md transition-all group/qr shrink-0" 
                                                    title="Klik untuk melihat Pop-up QR Code"
                                                    data-qr-token="{{ $item->qr_token }}"
                                                    data-title="{{ $item->display_title }}"
                                                    data-code="{{ $item->code }}"
                                                    data-target-url="{{ route('pengujian.scan', $item->qr_token) }}"
                                                    data-pdf-url="{{ route('pengujian.pdf', $item->id) }}"
                                                    data-fpdf-url="{{ route('pengujian.pdf.fpdf', $item->id) }}"
                                                    data-info="{{ $item->qr_content ?: ($item->code ?: '-') }}"
                                                    data-qr-base64="{{ $item->qr_base64 }}">
                                                <div class="w-14 h-14 flex items-center justify-center p-0.5">
                                                    {!! $item->qr_svg_small !!}
                                                </div>
                                            </button>
                                        </div>
                                    </td>

                                    <!-- KOLOM AKSI: POP-UP QR & EXPORT PDF PER BARIS -->
                                    <td class="py-2.5 px-4 bg-white border-l border-slate-200 sticky right-0 z-10 group-hover:bg-blue-50/30 transition-colors">
                                        <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                            <!-- Tombol Pop-up QR -->
                                            <button type="button"
                                                    class="js-btn-qr-modal inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[11px] font-bold bg-[#005baa] hover:bg-[#004887] text-white shadow-2xs transition-colors"
                                                    title="Buka pop-up QR Code besar"
                                                    data-qr-token="{{ $item->qr_token }}"
                                                    data-title="{{ $item->display_title }}"
                                                    data-code="{{ $item->code }}"
                                                    data-target-url="{{ route('pengujian.scan', $item->qr_token) }}"
                                                    data-pdf-url="{{ route('pengujian.pdf', $item->id) }}"
                                                    data-fpdf-url="{{ route('pengujian.pdf.fpdf', $item->id) }}"
                                                    data-info="{{ $item->qr_content ?: ($item->code ?: '-') }}"
                                                    data-qr-base64="{{ $item->qr_base64 }}">
                                                <span class="material-symbols-outlined text-[15px]">qr_code_2</span>
                                                <span>Pop-up QR</span>
                                            </button>

                                            <!-- Tombol Export PDF Per Baris -->
                                            <a href="{{ route('pengujian.pdf', $item->id) }}" 
                                               target="_blank"
                                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[11px] font-semibold bg-white hover:bg-rose-50 text-rose-700 border border-rose-200 shadow-2xs transition-colors"
                                               title="Export dokumen PDF pemetaan per baris data ini">
                                                <span class="material-symbols-outlined text-rose-600 text-[15px]">picture_as_pdf</span>
                                                <span>Export PDF</span>
                                            </a>
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

    <!-- POPUP MODAL QR CODE BESAR -->
    <div id="qr-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden transition-opacity">
        <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all text-slate-800">
            
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-[#005baa] via-blue-700 to-indigo-700 p-5 text-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-white backdrop-blur-xs">
                        <span class="material-symbols-outlined text-2xl">qr_code_2</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-base leading-tight">Pop-up QR Code Sampel</h3>
                        <p class="text-xs text-blue-100 mt-0.5">Pindai QR untuk langsung membuka Laporan Dokumen PDF baris ini</p>
                    </div>
                </div>
                <button type="button" onclick="closeQrModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition text-lg font-bold">
                    &times;
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 text-center space-y-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
                
                <!-- Sample Header & Code -->
                <div>
                    <span id="modal-sample-code" class="inline-block px-3 py-1 rounded-full text-xs font-extrabold bg-blue-100 text-[#005baa] tracking-wide font-mono">
                        SMP-0001
                    </span>
                    <h4 id="modal-sample-title" class="text-base font-bold text-slate-900 mt-1.5 leading-snug">
                        Nama Sampel Pengujian
                    </h4>
                </div>

                <!-- Big QR Code Container (Local High-Res SVG) -->
                <div class="p-4 bg-slate-50 rounded-2xl border-2 border-dashed border-blue-200 inline-block shadow-inner">
                    <div id="modal-qr-container" class="w-56 h-56 mx-auto flex items-center justify-center bg-white p-2 rounded-xl shadow-xs">
                        <img id="modal-qr-img" src="" alt="QR Code" class="w-full h-full object-contain" />
                    </div>
                    <p class="text-[11px] text-slate-500 font-medium mt-2 flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-[15px] text-emerald-600">smartphone</span>
                        Pindai dengan kamera HP untuk membuka Laporan PDF
                    </p>
                </div>

                <!-- Card: Link Target Dokumen PDF -->
                <div class="bg-blue-50/70 border border-blue-200 rounded-2xl p-3 text-left space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#005baa] flex items-center gap-1">
                            <span class="material-symbols-outlined text-[15px]">link</span>
                            Link Target QR Code (File Export PDF):
                        </span>
                        <button type="button" onclick="copyTargetUrl()" id="btn-copy-url" class="inline-flex items-center gap-1 text-[11px] font-bold text-[#005baa] hover:underline">
                            <span class="material-symbols-outlined text-[14px]">content_copy</span>
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
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1">
                            <span class="material-symbols-outlined text-emerald-600 text-[16px]">fact_check</span>
                            Rincian Data Sampel:
                        </span>
                        <button type="button" onclick="copyQrContent()" id="btn-copy" class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-600 hover:text-[#005baa]">
                            <span class="material-symbols-outlined text-[14px]">content_copy</span>
                            <span id="btn-copy-text">Salin Rincian</span>
                        </button>
                    </div>
                    <div id="modal-qr-content" class="text-xs font-mono text-slate-800 bg-white p-2.5 rounded-xl border border-slate-200 break-words whitespace-pre-line select-all font-medium leading-relaxed max-h-36 overflow-y-auto custom-scrollbar">
                        -
                    </div>
                </div>

                <!-- Action Buttons in Modal -->
                <div class="grid grid-cols-2 gap-2.5 pt-1">
                    <a id="modal-btn-download-svg" href="#" download="qr-code.svg" class="inline-flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs transition-all border border-slate-200">
                        <span class="material-symbols-outlined text-base">download</span>
                        <span>Unduh QR (SVG)</span>
                    </a>

                    <a id="modal-btn-pdf" href="#" target="_blank" class="inline-flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-[#005baa] hover:bg-[#004887] text-white font-bold text-xs shadow-md shadow-blue-500/20 transition-all">
                        <span class="material-symbols-outlined text-base">picture_as_pdf</span>
                        <span>Buka PDF Baris Ini</span>
                    </a>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="bg-slate-50 px-6 py-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span class="text-[11px]">Dapat dipindai langsung menggunakan kamera smartphone</span>
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

        if (dropzone && fileInput) {
            dropzone.addEventListener('click', () => fileInput.click());

            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('border-[#005baa]', 'bg-blue-50/40');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('border-[#005baa]', 'bg-blue-50/40');
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
        }

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

        // Dropdown toggle
        function toggleTemplateMenu() {
            const menu = document.getElementById('template-menu');
            if (menu) {
                menu.classList.toggle('hidden');
            }
        }

        window.addEventListener('click', (e) => {
            const wrapper = document.getElementById('template-dropdown-wrapper');
            const menu = document.getElementById('template-menu');
            if (wrapper && !wrapper.contains(e.target) && menu) {
                menu.classList.add('hidden');
            }
        });

        // Pop-up Modal Handlers
        const modal = document.getElementById('qr-modal');
        let currentRawQrContent = '';
        let currentTargetUrl = '';

        function openQrModal(qrToken, title, code, qrContent, scanUrl, pdfUrl, fpdfUrl, qrBase64) {
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

            // Render high-res SVG directly from server base64 or fallback to /qr/ route
            const qrImg = document.getElementById('modal-qr-img');
            const svgDataUri = qrBase64 ? ('data:image/svg+xml;base64,' + qrBase64) : ('/qr/' + qrToken);
            qrImg.src = svgDataUri;

            // Setup download link for SVG
            const downloadBtn = document.getElementById('modal-btn-download-svg');
            downloadBtn.href = svgDataUri;
            downloadBtn.download = 'QR-' + (code || 'code').replace(/[^a-z0-9]/gi, '_') + '.svg';

            modal.classList.remove('hidden');
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
                    setTimeout(() => {
                        textEl.innerText = 'Salin Rincian';
                    }, 2000);
                }
            });
        }

        function copyTargetUrl() {
            if (!currentTargetUrl) return;
            navigator.clipboard.writeText(currentTargetUrl).then(() => {
                const textEl = document.getElementById('btn-copy-url-text');
                if (textEl) {
                    textEl.innerText = 'Tersalin!';
                    setTimeout(() => {
                        textEl.innerText = 'Salin Link';
                    }, 2000);
                }
            });
        }

        // Event delegation for opening QR Modal - safe from quote/newline escaping bugs
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
                ds.fpdfUrl || '',
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
