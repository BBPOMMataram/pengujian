<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorium Pengujian - BBPOM di Mataram</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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

    <!-- HEADER SEDERHANA & BERSIH -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-700 to-indigo-700 flex items-center justify-center text-white font-extrabold text-sm shadow-md shadow-blue-500/20">
                    BPOM
                </div>
                <div>
                    <h1 class="text-base sm:text-lg font-bold text-slate-900 leading-tight">
                        Laboratorium Pengujian BPOM Mataram
                    </h1>
                </div>
            </div>

            @if($totalOverall > 0)
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full font-medium">
                        Total <strong>{{ number_format($totalOverall, 0, ',', '.') }}</strong> Data
                    </span>
                </div>
            @endif
        </div>
    </header>

    <!-- KONTEN UTAMA -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full flex-1 space-y-6">

        <!-- NOTIFIKASI SUKSES / ERROR -->
        @if(session('success'))
            <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 flex items-center gap-3 shadow-xs">
                <span class="material-symbols-outlined text-emerald-600 text-xl shrink-0">check_circle</span>
                <div class="flex-1 text-xs sm:text-sm text-emerald-800 font-medium">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-xl bg-rose-50 border border-rose-200 p-4 flex items-center gap-3 shadow-xs">
                <span class="material-symbols-outlined text-rose-600 text-xl shrink-0">error</span>
                <div class="flex-1 text-xs sm:text-sm text-rose-800 font-medium">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-xl bg-rose-50 border border-rose-200 p-4 shadow-xs text-xs sm:text-sm text-rose-800">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- BAGIAN 1: UPLOAD FILE & PROSES -->
        <section class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 sm:p-6">
            <form action="{{ route('pengujian.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h2 class="text-sm sm:text-base font-bold text-slate-900 flex items-center gap-2">
                                <span class="material-symbols-outlined text-blue-600 text-xl">upload_file</span>
                                Unggah File Data Pengujian
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Format file yang didukung: Excel (<strong>.xlsx</strong>, <strong>.xls</strong>) atau <strong>.csv</strong>
                            </p>
                        </div>
                    </div>

                    <!-- Area Dropzone -->
                    <div id="dropzone" class="border-2 border-dashed border-slate-300 hover:border-blue-500 rounded-xl p-5 sm:p-6 text-center transition-all cursor-pointer bg-slate-50/50 hover:bg-blue-50/20 group">
                        <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls,.csv" class="hidden" required onchange="handleFileSelected(this)">
                        
                        <div class="w-12 h-12 mx-auto rounded-xl bg-blue-100/70 text-blue-600 flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                            <span class="material-symbols-outlined text-2xl">cloud_upload</span>
                        </div>

                        <div id="file-label" class="space-y-0.5">
                            <p class="text-xs sm:text-sm font-semibold text-slate-700">
                                Klik untuk memilih file atau seret & letakkan file ke sini
                            </p>
                            <p class="text-[11px] text-slate-400">
                                Maksimal ukuran: 20 MB
                            </p>
                        </div>

                        <div id="file-selected-name" class="hidden mt-2 text-xs font-semibold text-blue-700 bg-blue-100/80 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg">
                            <span class="material-symbols-outlined text-sm">description</span>
                            <span id="selected-filename-text">-</span>
                        </div>
                    </div>

                    <!-- Tombol Proses -->
                    <div class="flex items-center justify-end">
                        <button type="submit" id="btn-submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-98 text-white font-semibold text-xs sm:text-sm shadow-md shadow-blue-500/20 transition-all">
                            <span class="material-symbols-outlined text-lg">play_arrow</span>
                            <span>Proses & Tampilkan Data</span>
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <!-- BAGIAN 2: DAFTAR FILE YANG SUDAH DIUNGGAH & HAPUS FILE -->
        @if($batches->isNotEmpty())
            <section class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 sm:p-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm">folder_open</span>
                        File yang Pernah Dikirim:
                    </span>
                    <span class="text-xs text-slate-400">
                        Klik nama file untuk melihat data, atau ikon hapus untuk membuang file
                    </span>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    @foreach($batches as $b)
                        @php
                            $isActive = ($selectedBatchId === $b->batch_id);
                        @endphp
                        <div class="inline-flex items-center rounded-xl border text-xs transition-all {{ $isActive ? 'bg-blue-50 border-blue-300 text-blue-900 shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100' }}">
                            <!-- Link Pilih File -->
                            <a href="{{ route('pengujian.index', ['batch' => $b->batch_id]) }}" 
                               class="inline-flex items-center gap-1.5 px-3 py-2 font-medium">
                                <span class="material-symbols-outlined text-sm {{ $isActive ? 'text-blue-600' : 'text-slate-400' }}">
                                    {{ str_ends_with(strtolower($b->filename), '.csv') ? 'table_view' : 'description' }}
                                </span>
                                <span class="max-w-[200px] truncate font-semibold" title="{{ $b->filename ?: $b->batch_id }}">
                                    {{ $b->filename ?: $b->batch_id }}
                                </span>
                                <span class="text-[11px] px-1.5 py-0.5 rounded-md {{ $isActive ? 'bg-blue-200/70 text-blue-800' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $b->total_items }} data
                                </span>
                            </a>

                            <!-- Tombol Hapus File / Batch -->
                            <form action="{{ route('pengujian.batch.destroy', $b->batch_id) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus file \'{{ addslashes($b->filename ?: $b->batch_id) }}\' beserta seluruh {{ $b->total_items }} baris datanya?')" 
                                  class="border-l {{ $isActive ? 'border-blue-200' : 'border-slate-200' }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-r-xl transition-colors flex items-center justify-center" 
                                        title="Hapus file ini">
                                    <span class="material-symbols-outlined text-base">delete</span>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- BAGIAN 3: TABEL DATA DINAMIS -->
        <section class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <!-- Header Tabel -->
            <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-lg">table_chart</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm sm:text-base font-bold text-slate-900">
                                Data Pengujian
                            </h3>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">
                                {{ $items->count() }} Baris
                            </span>
                        </div>
                        @if($currentBatch)
                            <p class="text-xs text-slate-500 mt-0.5">
                                File: <strong class="text-slate-700">{{ $currentBatch->filename }}</strong>
                                &bull; Diunggah: {{ \Carbon\Carbon::parse($currentBatch->created_at)->format('d/m/Y H:i') }}
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Pencarian Sederhana -->
                @if($batches->isNotEmpty())
                    <form action="{{ route('pengujian.index') }}" method="GET" class="flex items-center gap-2">
                        <input type="hidden" name="batch" value="{{ $selectedBatchId }}">
                        <div class="relative">
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari isi data..." 
                                   class="pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 w-44 sm:w-56">
                            <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                        </div>
                        @if(request('q'))
                            <a href="{{ route('pengujian.index', ['batch' => $selectedBatchId]) }}" class="text-xs text-rose-600 hover:underline">Reset</a>
                        @endif
                    </form>
                @endif
            </div>

            <!-- Isi Tabel -->
            @if($items->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-14 h-14 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                        <span class="material-symbols-outlined text-2xl">inventory_2</span>
                    </div>
                    <h4 class="text-sm font-semibold text-slate-700">Belum ada data yang ditampilkan</h4>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        Silakan pilih dan unggah file Excel atau CSV pada bagian atas untuk mulai memproses data dan membuat QR code.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-100/90 text-slate-700 border-b border-slate-200">
                                <!-- KOLOM NOMOR -->
                                <th class="py-3 px-3.5 font-bold uppercase tracking-wider text-center w-12 text-slate-500">
                                    No
                                </th>

                                <!-- KOLOM DINAMIS DARI FILE YANG DIUNGGAH -->
                                @foreach($headers as $h)
                                    @if(strtolower(trim($h)) !== 'no')
                                        <th class="py-3 px-3.5 font-semibold text-slate-700 uppercase tracking-wider min-w-[130px] whitespace-nowrap">
                                            {{ ucwords(str_replace(['_', '-'], ' ', (string)$h)) }}
                                        </th>
                                    @endif
                                @endforeach

                                <!-- KOLOM AKSI (HANYA TOMBOL BUKA QR, TIDAK MENAMPILKAN GAMBAR QR LANGSUNG) -->
                                <th class="py-3 px-4 font-bold uppercase tracking-wider text-center bg-blue-50/90 text-blue-900 border-l border-blue-200 min-w-[110px] sticky right-0 z-10 shadow-[-4px_0_8px_rgba(0,0,0,0.02)]">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($items as $index => $item)
                                @php
                                    // Ambil label judul sampel untuk modal secara dinamis
                                    $sampleTitle = $item->display_title;
                                @endphp
                                <tr class="hover:bg-blue-50/40 transition-colors group">
                                    <!-- Nomor Urut -->
                                    <td class="py-2.5 px-3.5 text-center text-slate-400 font-mono">
                                        {{ $index + 1 }}
                                    </td>

                                    <!-- Nilai Data Dinamis -->
                                    @foreach($headers as $h)
                                        @if(strtolower(trim($h)) !== 'no')
                                            @php
                                                $val = $item->data[$h] ?? '-';
                                                $strVal = is_array($val) ? json_encode($val) : (string)$val;
                                            @endphp
                                            <td class="py-2.5 px-3.5 text-slate-700 max-w-xs truncate" title="{{ $strVal }}">
                                                @if(is_array($val))
                                                    {{ json_encode($val) }}
                                                @else
                                                    @if(str_contains(strtolower($val), 'memenuhi syarat') || strtolower(trim($val)) === 'ms')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                                            {{ $val }}
                                                        </span>
                                                    @elseif(str_contains(strtolower($val), 'tidak memenuhi') || strtolower(trim($val)) === 'tms')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-800">
                                                            {{ $val }}
                                                        </span>
                                                    @else
                                                        {{ $val !== '' ? $val : '-' }}
                                                    @endif
                                                @endif
                                            </td>
                                        @endif
                                    @endforeach

                                    <!-- KOLOM AKSI: HANYA TOMBOL BUKA QR (BERSIH & RAMPING) -->
                                    <td class="py-2 px-3.5 bg-blue-50/40 border-l border-blue-100 text-center sticky right-0 z-10 group-hover:bg-blue-50/70 transition-colors">
                                        <button type="button" 
                                                onclick="openQrModal('{{ $item->qr_token }}', '{{ addslashes($sampleTitle) }}', '{{ route('pengujian.pdf', $item->id) }}', '{{ route('pengujian.scan', $item->qr_token) }}')" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-600 hover:bg-blue-700 active:scale-95 text-white shadow-xs transition-all"
                                                title="Buka QR Code Sampel Ini">
                                            <span class="material-symbols-outlined text-[15px]">qr_code_2</span>
                                            <span>Buka QR</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

    </main>

    <!-- FOOTER SEDERHANA -->
    <footer class="bg-white border-t border-slate-200 py-3.5 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} Laboratorium Pengujian Balai Besar POM di Mataram
        </div>
    </footer>

    <!-- POPUP MODAL QR CODE -->
    <div id="qr-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden transition-all">
        <div class="relative w-full max-w-sm bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all text-slate-800 animate-in fade-in zoom-in-95 duration-150">
            
            <!-- Header Modal -->
            <div class="bg-gradient-to-r from-blue-700 to-indigo-700 px-5 py-4 text-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center text-white">
                        <span class="material-symbols-outlined text-lg">qr_code_2</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm leading-tight">QR Code Verifikasi</h3>
                        <p class="text-[11px] text-blue-100">Scan untuk melihat dokumen PDF resmi</p>
                    </div>
                </div>
                <button type="button" onclick="closeQrModal()" class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition text-lg leading-none">
                    &times;
                </button>
            </div>

            <!-- Isi Modal -->
            <div class="p-5 text-center space-y-3.5">
                
                <!-- Info Sampel -->
                <div>
                    <h4 id="modal-sample-title" class="text-sm font-bold text-slate-900 mt-1 max-w-[280px] mx-auto truncate">
                        -
                    </h4>
                </div>

                <!-- Kotak QR Code -->
                <div class="p-3 bg-slate-50 rounded-2xl border-2 border-dashed border-blue-200 inline-block shadow-inner">
                    <div class="w-52 h-52 mx-auto flex items-center justify-center bg-white p-2 rounded-xl shadow-xs">
                        <img id="modal-qr-img" src="" alt="QR Code" class="w-full h-full object-contain" />
                    </div>
                </div>

                <!-- Petunjuk Scan -->
                <p class="text-xs text-slate-500">
                    Arahkan kamera smartphone ke QR Code di atas untuk membuka dokumen PDF resmi pengujian.
                </p>

                <!-- Tombol Aksi di Modal -->
                <div class="pt-1 flex flex-col gap-2">
                    <a id="modal-btn-pdf" href="#" target="_blank" class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition-all">
                        <span class="material-symbols-outlined text-base">picture_as_pdf</span>
                        <span>Buka Dokumen PDF Resmi</span>
                    </a>

                    <button type="button" onclick="closeQrModal()" class="w-full py-2 px-4 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPT INTERAKSI -->
    <script>
        // File Drag & Drop
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

        // Modal QR Code
        const modal = document.getElementById('qr-modal');

        function openQrModal(qrToken, title, pdfUrl, scanUrl) {
            document.getElementById('modal-sample-title').innerText = title;
            document.getElementById('modal-btn-pdf').href = pdfUrl;

            // Load QR Code SVG lokal dari route Laravel
            document.getElementById('modal-qr-img').src = '/qr/' + qrToken;

            modal.classList.remove('hidden');
        }

        function closeQrModal() {
            modal.classList.add('hidden');
            document.getElementById('modal-qr-img').src = '';
        }

        // Tutup modal dengan tombol Escape atau klik luar
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
