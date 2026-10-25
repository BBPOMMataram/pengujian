<?php

namespace App\Http\Controllers;

use App\Exports\SamplePengujianExport;
use App\Exports\SampleTwoColumnExport;
use App\Imports\PengujianImport;
use App\Imports\PengujianMultipleSheetsImport;
use App\Models\PengujianItem;
use Barryvdh\DomPDF\Facade\Pdf;
use FPDF;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PengujianController extends Controller
{
    /**
     * Display the main web page with upload form, stats, and data table.
     */
    public function index(Request $request)
    {
        $dbError = null;
        try {
            // Auto run migrations if pengujian_items table does not exist
            if (!\Illuminate\Support\Facades\Schema::hasTable('pengujian_items')) {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            }

            // Get list of distinct batches grouped strictly by batch_id, file_group_id, filename, sheet_name
            $batches = PengujianItem::select('batch_id', 'file_group_id', 'filename', 'sheet_name')
                ->selectRaw('MIN(created_at) as created_at, count(*) as total_items')
                ->groupBy('batch_id', 'file_group_id', 'filename', 'sheet_name')
                ->orderByDesc('created_at')
                ->get();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('DB Index error: ' . $e->getMessage());
            $dbError = $e->getMessage();
            $batches = collect();
        }

        $selectedBatchId = $request->query('batch');

        if (!$selectedBatchId && $batches->isNotEmpty()) {
            $selectedBatchId = $batches->first()->batch_id;
        }

        $itemsQuery = PengujianItem::query();
        if ($selectedBatchId) {
            $itemsQuery->where('batch_id', $selectedBatchId);
        }

        if ($search = $request->query('q')) {
            $itemsQuery->where(function ($q) use ($search) {
                $q->where('qr_content', 'like', "%{$search}%")
                  ->orWhere('data', 'like', "%{$search}%");
            });
        }

        try {
            $items = $itemsQuery->orderBy('row_number', 'asc')->get();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Items query error: ' . $e->getMessage());
            $items = collect();
        }

        // Prepare QR codes and headers for each item
        $headers = [];
        foreach ($items as $item) {
            $targetUrl = route('pengujian.scan', $item->qr_token);
            $item->target_url = $targetUrl;
            
            // The QR code encodes the direct URL pointing to the row's PDF export!
            $contentToEncode = $targetUrl;
            $item->qr_content_display = $item->qr_content ?: ($item->code ?: $targetUrl);

            // Generate small SVG for table column (di kolom sampingnya)
            $item->qr_svg_small = (string) QrCode::size(68)->margin(1)->generate($contentToEncode);
            
            // Generate large SVG for popup modal
            $item->qr_svg_large = (string) QrCode::size(260)->margin(2)->generate($contentToEncode);
            $item->qr_base64 = base64_encode($item->qr_svg_large);

            if (is_array($item->data)) {
                $ignoredKeys = [
                    'manager_administrasi', 'nip_manager_administrasi',
                    'kepala_balai', 'nip_kepala_balai',
                    'petugas_penerima', 'nip_petugas_penerima',
                    'tanggal_spu', 'no_spu', 'nomor spu', 'nomor_spu',
                    '_meta'
                ];

                foreach (array_keys($item->data) as $h) {
                    $hLower = strtolower(trim($h));
                    if (str_starts_with($h, '_') || in_array($hLower, $ignoredKeys)) {
                        continue;
                    }
                    if (!in_array($h, $headers)) {
                        $headers[] = $h;
                    }
                }
            }
        }

        $currentBatch = $batches->firstWhere('batch_id', $selectedBatchId);

        // Find sibling sheets if this file had multiple tabs
        $siblingSheets = collect();
        if ($currentBatch && $currentBatch->file_group_id) {
            $siblingSheets = $batches->where('file_group_id', $currentBatch->file_group_id);
        }

        $totalOverall = 0;
        try {
            $totalOverall = PengujianItem::count();
        } catch (\Throwable $e) {
            $totalOverall = 0;
        }

        try {
            return view('pengujian.index', [
                'items' => $items,
                'batches' => $batches,
                'selectedBatchId' => $selectedBatchId,
                'currentBatch' => $currentBatch,
                'siblingSheets' => $siblingSheets,
                'headers' => $headers,
                'totalOverall' => $totalOverall,
                'dbError' => $dbError,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Blade View Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response('<div style="font-family: sans-serif; padding: 24px; color: #b91c1c;"><h2>Kendala Tampilan (View Error)</h2><p>' . htmlspecialchars($e->getMessage()) . '</p><pre style="background: #f1f5f9; padding: 12px; border-radius: 8px; color: #334155;">' . htmlspecialchars($e->getFile() . ':' . $e->getLine()) . "\n" . htmlspecialchars($e->getTraceAsString()) . '</pre></div>', 500);
        }
    }

    /**
     * Handle Excel / CSV / PDF upload with multi-sheet detection and trailing empty row trimming.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv,pdf|max:25600',
        ], [
            'excel_file.required' => 'Silakan pilih file Excel, CSV, atau PDF terlebih dahulu.',
            'excel_file.mimes' => 'Format file harus berupa Excel (.xlsx, .xls), .csv, atau .pdf.',
            'excel_file.max' => 'Ukuran file maksimal 25 MB.',
        ]);

        $file = $request->file('excel_file');
        $originalFilename = $file->getClientOriginalName();
        $fileGroupId = 'FILE-' . date('Ymd-His') . '-' . Str::random(4);
        $extension = strtolower($file->getClientOriginalExtension());

        // Handle PDF import
        if ($extension === 'pdf') {
            try {
                $pdfService = new \App\Services\PdfImportService();
                $count = $pdfService->import($file->getRealPath(), $fileGroupId, $originalFilename);

                return redirect()->route('pengujian.index', ['batch' => $fileGroupId])
                    ->with('success', "Dokumen PDF \"{$originalFilename}\" berhasil diimpor! Total {$count} baris data berhasil diekstraksi ke tabel & QR Code ter-generate otomatis.");
            } catch (\Throwable $e) {
                return redirect()->back()
                    ->with('error', 'Gagal memproses file PDF: ' . $e->getMessage());
            }
        }

        // Check for multiple sheets in Excel files
        $sheetNames = ['Worksheet'];
        if (in_array($extension, ['xlsx', 'xls'])) {
            try {
                $reader = IOFactory::createReaderForFile($file->getRealPath());
                $sheetNames = $reader->listWorksheetNames($file->getRealPath());
            } catch (\Throwable $e) {
                $sheetNames = ['Worksheet'];
            }
        }

        try {
            if (count($sheetNames) > 1) {
                // Multi-tab Excel file: import each tab separately
                Excel::import(new PengujianMultipleSheetsImport($fileGroupId, $originalFilename, $sheetNames), $file);
                
                $firstSheetBatch = $fileGroupId . '__' . Str::slug($sheetNames[0]);
                $count = PengujianItem::where('file_group_id', $fileGroupId)->count();

                return redirect()->route('pengujian.index', ['batch' => $firstSheetBatch])
                    ->with('success', "File \"{$originalFilename}\" berhasil diimpor! Terdeteksi " . count($sheetNames) . " Tab/Sheet terpisah (Total {$count} baris data). Anda dapat beralih antar tab atau mengunduh laporan gabungan.");
            } else {
                // Single tab Excel or CSV file
                $sheetName = $sheetNames[0] ?? 'Sheet1';
                Excel::import(new PengujianImport($fileGroupId, $originalFilename, $sheetName, $fileGroupId), $file);

                $count = PengujianItem::where('batch_id', $fileGroupId)->count();

                return redirect()->route('pengujian.index', ['batch' => $fileGroupId])
                    ->with('success', "File \"{$originalFilename}\" berhasil diimpor! Total {$count} baris data berhasil dimuat & QR Code ter-generate otomatis.");
            }
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    /**
     * Download the sample template (default 2-kolom).
     */
    public function downloadTemplate()
    {
        return $this->downloadTemplateTwoColumn();
    }

    /**
     * Download template minimal 2-kolom (Kolom Data & Kolom Kosong untuk QR Code).
     */
    public function downloadTemplateTwoColumn()
    {
        return Excel::download(new SampleTwoColumnExport, 'Template_2_Kolom_Data_dan_QR_Kosong.xlsx');
    }

    /**
     * Download template multi-kolom lengkap laboratorium.
     */
    public function downloadTemplateFull()
    {
        return Excel::download(new SamplePengujianExport, 'Template_Lengkap_Pengujian_Laboratorium.xlsx');
    }

    /**
     * Quick-load sample data: 2-Kolom (Data + QR Code).
     */
    public function loadSampleTwoColumn()
    {
        $export = new SampleTwoColumnExport();
        $rows = $export->array();

        $batchId = 'SAMPLE-2COL-' . date('Ymd-His');
        $filename = 'Contoh_2_Kolom_Data_dan_QR.xlsx';

        $count = 0;
        foreach ($rows as $index => $rowValues) {
            $dataVal = $rowValues[0] ?? ("Sampel #" . ($index + 1));

            $data = [
                'Data Informasi' => $dataVal,
                'QR Code' => '(Otomatis Di-generate)',
            ];

            PengujianItem::create([
                'batch_id' => $batchId,
                'file_group_id' => $batchId,
                'filename' => $filename,
                'sheet_name' => 'Data Sampel 2-Kolom',
                'row_number' => $index + 1,
                'qr_token' => Str::random(32),
                'qr_content' => $dataVal,
                'data' => $data,
            ]);
            $count++;
        }

        return redirect()->route('pengujian.index', ['batch' => $batchId])
            ->with('success', "Berhasil memuat {$count} data contoh format 2-Kolom! QR code otomatis ter-generate dari isi kolom sebelahnya.");
    }

    /**
     * Quick-load sample data: Multi-kolom pengujian laboratorium.
     */
    public function loadSampleData()
    {
        $export = new SamplePengujianExport();
        $headings = $export->headings();
        $rows = $export->array();

        $batchId = 'SAMPLE-' . date('Ymd-His');
        $filename = 'Contoh_Data_Pengujian_Laboratorium.xlsx';

        $count = 0;
        foreach ($rows as $index => $rowValues) {
            $data = [];
            foreach ($headings as $i => $h) {
                $slug = Str::slug($h, '_');
                $data[$slug] = $rowValues[$i] ?? null;
            }

            $primaryVal = $data['kode_sampel'] ?? ($data['nama_sampel'] ?? ('SMP-' . str_pad((string)($index + 1), 4, '0', STR_PAD_LEFT)));

            PengujianItem::create([
                'batch_id' => $batchId,
                'file_group_id' => $batchId,
                'filename' => $filename,
                'sheet_name' => 'Uji Laboratorium',
                'row_number' => $index + 1,
                'qr_token' => Str::random(32),
                'qr_content' => $primaryVal . ' - ' . ($data['nama_sampel'] ?? ''),
                'data' => $data,
            ]);
            $count++;
        }

        return redirect()->route('pengujian.index', ['batch' => $batchId])
            ->with('success', "Berhasil memuat {$count} data sampel contoh laboratorium BPOM dengan QR Code!");
    }

    /**
     * Quick-load sample data: Format Dokumen SPU PDF BBPOM (Surat Permintaan Uji & Lampiran).
     */
    public function loadSamplePdfSpu()
    {
        $batchId = 'SAMPLE-SPU-PDF-' . date('Ymd-His');
        $filename = 'Contoh_Surat_Permintaan_Uji_SPU.pdf';

        $samples = [
            [
                'No' => '1',
                'Kode Sampel' => '26.117.11.16.05.0712.K',
                'QR Code' => '(Otomatis Di-generate)',
                'Nama Produk / Sampel' => 'Kristal Putih Transparan diduga Shabu an Tsk ALFAN AHMADI Bin SAIPUDIN',
                'NIE' => '-',
                'Jenis Sampel' => 'Narkotika - Pihak Ke 3 - Kepolisian',
                'Komoditi' => 'Narkotika',
                'Lokasi Sampel' => 'Kimia 1 Sampel dan Retensi 1 Sampel',
                'Catatan' => 'K1 M0 R1',
                'Bentuk Sediaan' => 'Solid',
                'Netto' => '0,07 gram',
                'Tempat Sampling' => 'Kepolisian Resort Lombok Timur',
                'Pengirim' => 'MUH. BAGUS HARFIAN (01081068)',
                'Tanggal Diterima' => '2026-10-20',
                'No Surat Petugas Sampling' => 'B/1551/X/RES.4.2/2026',
                'No Billing' => '820261020575208',
                'Keterangan Sampling' => 'Plastik transparan bersegel yang dijahit benang warna merah dalam amplop coklat berlak segel barang bukti',
                'Komposisi' => 'Metamfetamina (Shabu)',
                '_meta' => [
                    'no_spu' => 'SPU.117.05.26.10.16.0044',
                    'anggaran' => 'Pihak Ke 3 - Kepolisian',
                    'asal_sampling' => 'Kepolisian',
                    'tanggal_spu' => '20 Oktober 2026',
                    'petugas_penerima' => 'Nirmala Nurhasanah, STP',
                    'nip_petugas_penerima' => '199205172023212054',
                    'manager_administrasi' => 'Basuki Murdi Hartono, SH.',
                    'nip_manager_administrasi' => '197904052005011002',
                    'kepala_balai' => 'Yogi Abaso Mataram, S.Si., Apt',
                    'nip_kepala_balai' => '198101242009121002',
                ],
            ],
            [
                'No' => '2',
                'Kode Sampel' => '26.117.11.16.05.0713.K',
                'QR Code' => '(Otomatis Di-generate)',
                'Nama Produk / Sampel' => 'Kristal Putih Transparan diduga Shabu an Tsk ANDI GALIB Bin H. IMAM AS',
                'NIE' => '-',
                'Jenis Sampel' => 'Narkotika - Pihak Ke 3 - Kepolisian',
                'Komoditi' => 'Narkotika',
                'Lokasi Sampel' => 'Kimia',
                'Catatan' => '-',
                'Bentuk Sediaan' => 'Solid',
                'Netto' => '0,07 gram',
                'Tempat Sampling' => 'Kepolisian Resort Lombok Timur',
                'Pengirim' => 'MUH. BAGUS HARFIAN (01081068)',
                'Tanggal Diterima' => '2026-10-20',
                'No Surat Petugas Sampling' => 'B/1558/X/RES.4.2/2026',
                'No Billing' => '820261020575296',
                'Keterangan Sampling' => 'Plastik transparan bersegel yang dijahit benang warna merah dalam amplop coklat berlak segel barang bukti',
                'Komposisi' => 'Metamfetamina (Shabu)',
                '_meta' => [
                    'no_spu' => 'SPU.117.05.26.10.16.0044',
                    'anggaran' => 'Pihak Ke 3 - Kepolisian',
                    'asal_sampling' => 'Kepolisian',
                    'tanggal_spu' => '20 Oktober 2026',
                    'petugas_penerima' => 'Nirmala Nurhasanah, STP',
                    'nip_petugas_penerima' => '199205172023212054',
                    'manager_administrasi' => 'Basuki Murdi Hartono, SH.',
                    'nip_manager_administrasi' => '197904052005011002',
                    'kepala_balai' => 'Yogi Abaso Mataram, S.Si., Apt',
                    'nip_kepala_balai' => '198101242009121002',
                ],
            ],
            [
                'No' => '3',
                'Kode Sampel' => '26.117.11.16.05.0714.T',
                'QR Code' => '(Otomatis Di-generate)',
                'Nama Produk / Sampel' => 'Jamu Tradisional Herbal Temulawak Asli',
                'NIE' => 'TR123456789',
                'Jenis Sampel' => 'Obat Traditional (OT) - Rutin',
                'Komoditi' => 'Obat Tradisional',
                'Lokasi Sampel' => '-',
                'Catatan' => '-',
                'Bentuk Sediaan' => 'Cairan Obat Dalam',
                'Netto' => '150 ml',
                'Tempat Sampling' => 'Pasar Tradisional Cakranegara',
                'Pengirim' => 'Petugas Sampling BBPOM',
                'Tanggal Diterima' => '2026-10-21',
                'No Surat Petugas Sampling' => 'SP/117/X/2026',
                'No Billing' => '820261020575300',
                'Keterangan Sampling' => 'Botol kemasan asli bersegel utuh',
                'Komposisi' => 'Ekstrak Curcuma xanthorrhiza Rhizoma',
                '_meta' => [
                    'no_spu' => 'SPU.117.05.26.10.16.0044',
                    'anggaran' => 'DIPA BBPOM di Mataram',
                    'asal_sampling' => 'Balai Besar POM di Mataram',
                    'tanggal_spu' => '21 Oktober 2026',
                    'petugas_penerima' => 'Nirmala Nurhasanah, STP',
                    'nip_petugas_penerima' => '199205172023212054',
                    'manager_administrasi' => 'Basuki Murdi Hartono, SH.',
                    'nip_manager_administrasi' => '197904052005011002',
                    'kepala_balai' => 'Yogi Abaso Mataram, S.Si., Apt',
                    'nip_kepala_balai' => '198101242009121002',
                ],
            ]
        ];

        $now = now();
        foreach ($samples as $index => $data) {
            $qrLines = [
                "Kode Sampel: " . $data['Kode Sampel'],
                "Nama Produk: " . $data['Nama Produk / Sampel'],
                "Jenis Sampel: " . $data['Jenis Sampel'],
                "NIE: " . $data['NIE'],
                "Lokasi Sampel: " . $data['Lokasi Sampel'],
                "Bentuk Sediaan: " . $data['Bentuk Sediaan'],
                "Netto: " . $data['Netto'],
                "Tempat Sampling: " . $data['Tempat Sampling'],
                "Pengirim: " . $data['Pengirim'],
                "No Surat: " . $data['No Surat Petugas Sampling'],
            ];
            $qrContent = implode("\n", $qrLines);

            PengujianItem::create([
                'batch_id' => $batchId,
                'file_group_id' => $batchId,
                'filename' => $filename,
                'sheet_name' => 'Dokumen SPU',
                'row_number' => $index + 1,
                'qr_token' => Str::random(32),
                'qr_content' => $qrContent,
                'data' => $data,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return redirect()->route('pengujian.index', ['batch' => $batchId])
            ->with('success', "Berhasil memuat contoh tabel hasil ekstraksi Dokumen PDF Surat Permintaan Uji (SPU) BBPOM!");
    }

    /**
     * Public QR Code scan target: outputs PDF directly to the scanner's phone or browser.
     */
    public function scan(string $qrToken)
    {
        $item = PengujianItem::where('qr_token', $qrToken)->firstOrFail();

        return $this->generateDomPdf($item, 'inline');
    }

    /**
     * Render SVG QR code directly on demand for modal popup or external image tag.
     */
    public function qrImage(string $qrToken)
    {
        $item = PengujianItem::where('qr_token', $qrToken)->firstOrFail();
        $targetUrl = route('pengujian.scan', $item->qr_token);
        $svg = (string) QrCode::size(260)->margin(2)->generate($targetUrl);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Generate PDF using DomPDF per row.
     */
    public function pdf(int $id)
    {
        $item = PengujianItem::findOrFail($id);

        return $this->generateDomPdf($item, 'inline');
    }

    /**
     * Helper to render single item DomPDF view.
     */
    protected function generateDomPdf(PengujianItem $item, string $disposition = 'inline')
    {
        $targetUrl = route('pengujian.scan', $item->qr_token);
        
        $qrSvg = QrCode::size(140)->margin(1)->generate($targetUrl);
        $qrBase64 = base64_encode($qrSvg);

        $dataArr = is_array($item->data) ? $item->data : [];
        $isSpu = ($item->sheet_name === 'Dokumen SPU')
            || isset($dataArr['Kode Sampel'])
            || isset($dataArr['kode_sampel'])
            || isset($dataArr['no_spu'])
            || str_contains(strtolower($item->filename ?? ''), 'spu');

        $orientation = request()->query('orientation') ?: ($isSpu ? 'landscape' : 'portrait');

        // Proses lampiran foto dokumentasi jika ada
        $photos = [];
        if (request()->hasFile('photos')) {
            $uploadedPhotos = request()->file('photos');
            if (!is_array($uploadedPhotos)) {
                $uploadedPhotos = [$uploadedPhotos];
            }
            $captions = request()->input('captions', []);
            foreach ($uploadedPhotos as $idx => $photo) {
                if ($photo && $photo->isValid()) {
                    $mime = $photo->getMimeType();
                    $base64 = base64_encode(file_get_contents($photo->getRealPath()));
                    $photos[] = [
                        'src' => "data:{$mime};base64,{$base64}",
                        'caption' => !empty($captions[$idx]) ? $captions[$idx] : ('Dokumentasi Foto ' . ($idx + 1)),
                    ];
                }
            }
        }

        $pdf = Pdf::loadView('pdf.laporan-pengujian', [
            'item' => $item,
            'qrBase64' => $qrBase64,
            'isSpu' => $isSpu,
            'orientation' => $orientation,
            'photos' => $photos,
            'printedAt' => now()->format('d/m/Y H:i'),
        ]);

        $pdf->setPaper('A4', $orientation);

        $filename = 'LHP-' . Str::slug($item->display_title ?: ('sampel-' . $item->id)) . '.pdf';

        if ($disposition === 'attachment') {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    /**
     * Generate Tabular or Vertical Batch PDF (All items in a single sheet / batch).
     * If headers (columns) > 15: switches automatically to Vertical Per-Row layout (Kolom A: Header, Kolom B: Data)
     * exactly like the BBPOM official screenshot.
     * If headers <= 15: renders as Tabular Grid (Landscape if > 5 columns, Portrait if <= 5 columns).
     */
    public function pdfBatch(Request $request, string $batchId)
    {
        $items = PengujianItem::where('batch_id', $batchId)->orderBy('row_number', 'asc')->get();

        if ($items->isEmpty()) {
            return redirect()->back()->with('error', 'Batch data tidak ditemukan.');
        }

        // Collect distinct headers from this batch
        $headers = [];
        foreach ($items as $item) {
            $targetUrl = route('pengujian.scan', $item->qr_token);
            $qrSvg = QrCode::size(100)->margin(1)->generate($targetUrl);
            $item->qr_base64 = base64_encode($qrSvg);

            if (is_array($item->data)) {
                $ignoredKeys = [
                    'manager_administrasi', 'nip_manager_administrasi',
                    'kepala_balai', 'nip_kepala_balai',
                    'petugas_penerima', 'nip_petugas_penerima',
                    'tanggal_spu', 'no_spu', 'nomor spu', 'nomor_spu',
                    '_meta'
                ];

                foreach (array_keys($item->data) as $h) {
                    $hLower = strtolower(trim($h));
                    if (str_starts_with($h, '_') || in_array($hLower, $ignoredKeys)) {
                        continue;
                    }
                    if (!in_array($h, $headers)) {
                        $headers[] = $h;
                    }
                }
            }
        }

        $firstItem = $items->first();
        $filename = $firstItem->filename ?: 'Batch Pengujian';
        $sheetName = $firstItem->sheet_name ?: 'Data Pengujian';
        $firstData = ($firstItem && is_array($firstItem->data)) ? $firstItem->data : [];
        $firstMeta = $firstData['_meta'] ?? [];

        $isSpu = ($firstItem && $firstItem->sheet_name === 'Dokumen SPU')
            || isset($firstData['Kode Sampel'])
            || isset($firstData['kode_sampel'])
            || isset($firstData['no_spu'])
            || isset($firstMeta['no_spu'])
            || str_contains(strtolower($filename ?? ''), 'spu');

        // Layout handling:
        // Jika SPU: selalu gunakan format SPU Landscape 3 kolom (No, Identitas Sampel, Lokasi Sampel)
        // kecuali user secara eksplisit meminta layout=vertical lewat URL
        $requestedLayout = strtolower($request->query('layout', ''));
        if ($requestedLayout === 'vertical') {
            // FORMAT VERTICAL PER-BARIS: Kolom A = Parameter, Kolom B = Nilai Data (Sesuai Gambar Lampiran)
            $pdf = Pdf::loadView('pdf.batch-vertical', [
                'items' => $items,
                'headers' => $headers,
                'batchId' => $batchId,
                'filename' => $filename,
                'sheetName' => $sheetName,
                'printedAt' => now()->format('d/m/Y H:i'),
            ]);

            $pdf->setPaper('A4', 'portrait');

            return $pdf->stream('LHP-' . Str::slug($filename) . '-perbaris.pdf');
        }

        // FORMAT TABULAR GRID / SPU KESELURUHAN (Landscape)
        $colCount = count($headers);
        $requestedOrientation = strtolower($request->query('orientation', ''));
        if (in_array($requestedOrientation, ['portrait', 'landscape'])) {
            $orientation = $requestedOrientation;
        } else {
            $orientation = $isSpu ? 'landscape' : (($colCount > 5) ? 'landscape' : 'portrait');
        }

        // Auto font scaling to ensure table does not swell or overflow
        if ($colCount <= 4) {
            $fontSize = '8.5pt';
            $headerFontSize = '8pt';
        } elseif ($colCount <= 7) {
            $fontSize = '7.5pt';
            $headerFontSize = '7.5pt';
        } elseif ($colCount <= 10) {
            $fontSize = '7pt';
            $headerFontSize = '7pt';
        } else {
            $fontSize = '6.5pt';
            $headerFontSize = '6.5pt';
        }

        $spuNum = $firstMeta['no_spu'] ?? $firstData['no_spu'] ?? $firstData['Nomor SPU'] ?? 'SPU.117.05.26.10.16.0044';
        $komoditi = $firstMeta['komoditi'] ?? $firstData['komoditi'] ?? $firstData['Komoditi'] ?? 'Narkotika';
        $anggaran = $firstMeta['anggaran'] ?? $firstData['anggaran'] ?? $firstData['Anggaran'] ?? 'Pihak Ke 3 - Kepolisian';
        $asalSampling = $firstMeta['asal_sampling'] ?? $firstData['asal_sampling'] ?? $firstData['Asal Sampling'] ?? 'Kepolisian';

        $pdf = Pdf::loadView('pdf.batch-pengujian', [
            'items' => $items,
            'headers' => $headers,
            'isSpu' => $isSpu,
            'spuNum' => $spuNum,
            'komoditi' => $komoditi,
            'anggaran' => $anggaran,
            'asalSampling' => $asalSampling,
            'orientation' => $orientation,
            'fontSize' => $fontSize,
            'headerFontSize' => $headerFontSize,
            'batchId' => $batchId,
            'filename' => $filename,
            'sheetName' => $sheetName,
            'printedAt' => now()->format('d/m/Y H:i'),
        ]);

        $pdf->setPaper('A4', $orientation);

        return $pdf->stream('SPU-' . Str::slug($filename) . '-keseluruhan.pdf');
    }

    /**
     * Generate Merged PDF combining all tabs / sheets from the same uploaded Excel file.
     */
    public function pdfMergedFileGroup(Request $request, string $fileGroupId)
    {
        $allItems = PengujianItem::where('file_group_id', $fileGroupId)
            ->orWhere('batch_id', $fileGroupId)
            ->orderBy('batch_id', 'asc')
            ->orderBy('row_number', 'asc')
            ->get();

        if ($allItems->isEmpty()) {
            return redirect()->back()->with('error', 'File data pengujian tidak ditemukan.');
        }

        // Group items by batch_id / sheet_name
        $grouped = $allItems->groupBy('batch_id');
        $sheetGroups = [];
        $maxColsOverall = 0;

        foreach ($grouped as $batchId => $groupItems) {
            $headers = [];
            foreach ($groupItems as $item) {
                $targetUrl = route('pengujian.scan', $item->qr_token);
                $qrSvg = QrCode::size(90)->margin(1)->generate($targetUrl);
                $item->qr_base64 = base64_encode($qrSvg);

                if (is_array($item->data)) {
                    foreach (array_keys($item->data) as $h) {
                        if (!in_array($h, $headers)) {
                            $headers[] = $h;
                        }
                    }
                }
            }

            $groupColCount = count($headers);
            if ($groupColCount > $maxColsOverall) {
                $maxColsOverall = $groupColCount;
            }

            // Jika sheet ini memiliki lebih dari 15 kolom, ubah ke layout vertical per-baris
            $groupLayout = ($groupColCount > 15) ? 'vertical' : 'tabular';

            $firstInGroup = $groupItems->first();
            $sheetGroups[] = [
                'batch_id' => $batchId,
                'sheet_name' => $firstInGroup->sheet_name ?: 'Data Pengujian',
                'headers' => $headers,
                'items' => $groupItems,
                'layout' => $groupLayout,
            ];
        }

        $orientation = ($maxColsOverall > 5) ? 'landscape' : 'portrait';

        if ($maxColsOverall <= 4) {
            $fontSize = '8.5pt';
            $headerFontSize = '8pt';
        } elseif ($maxColsOverall <= 7) {
            $fontSize = '7.5pt';
            $headerFontSize = '7.5pt';
        } else {
            $fontSize = '7pt';
            $headerFontSize = '6.5pt';
        }

        $filename = $allItems->first()->filename ?: 'Laporan_Gabungan';

        $pdf = Pdf::loadView('pdf.merged-workbook', [
            'sheetGroups' => $sheetGroups,
            'totalItems' => $allItems->count(),
            'filename' => $filename,
            'orientation' => $orientation,
            'fontSize' => $fontSize,
            'headerFontSize' => $headerFontSize,
            'printedAt' => now()->format('d/m/Y H:i'),
        ]);

        $pdf->setPaper('A4', $orientation);

        return $pdf->stream('Laporan-Gabungan-Semua-Tab-' . Str::slug($filename) . '.pdf');
    }

    /**
     * Alternative PDF generation using FPDF library per row.
     */
    public function pdfFpdf(int $id)
    {
        $item = PengujianItem::findOrFail($id);

        $fpdf = new FPDF('P', 'mm', 'A4');
        $fpdf->AddPage();
        $fpdf->SetAutoPageBreak(true, 15);

        // Header Balai
        $fpdf->SetFont('Arial', 'B', 13);
        $fpdf->SetTextColor(3, 66, 142);
        $fpdf->Cell(0, 7, 'BALAI BESAR PENGAWAS OBAT DAN MAKANAN DI MATARAM', 0, 1, 'C');
        $fpdf->SetFont('Arial', '', 8.5);
        $fpdf->SetTextColor(100, 116, 139);
        $fpdf->Cell(0, 5, 'Jl. Majapahit No. 147A, Mataram - Nusa Tenggara Barat | Telp: (0370) 621926', 0, 1, 'C');
        $fpdf->SetDrawColor(3, 66, 142);
        $fpdf->SetLineWidth(0.6);
        $fpdf->Line(15, 27, 195, 27);
        $fpdf->Ln(7);

        // Document Title
        $fpdf->SetFont('Arial', 'B', 11);
        $fpdf->SetTextColor(15, 23, 42);
        $fpdf->Cell(0, 7, 'LAPORAN HASIL PENGUJIAN SAMPEL (FPDF)', 0, 1, 'C');
        $fpdf->SetFont('Arial', '', 9);
        $fpdf->SetTextColor(71, 85, 105);
        $fpdf->Cell(0, 5, 'Nomor Dokumen: LHP/FPDF/' . date('Y') . '/' . str_pad((string)$item->id, 5, '0', STR_PAD_LEFT), 0, 1, 'C');
        $fpdf->Ln(4);

        // Sample Info Box
        $fpdf->SetFillColor(241, 245, 249);
        $fpdf->SetDrawColor(203, 213, 225);
        $fpdf->SetLineWidth(0.2);
        $curY = $fpdf->GetY();
        $fpdf->Rect(15, $curY, 180, 24, 'DF');

        $fpdf->SetXY(20, $curY + 2);
        $fpdf->SetFont('Arial', 'B', 10);
        $fpdf->SetTextColor(3, 66, 142);
        $fpdf->Cell(120, 5, 'ID SAMPEL: #' . $item->id . ' (Baris ' . $item->row_number . ')', 0, 1);

        $fpdf->SetX(20);
        $fpdf->SetFont('Arial', '', 9);
        $fpdf->SetTextColor(15, 23, 42);
        $fpdf->Cell(120, 5, 'Nama / Keterangan: ' . ($item->display_title ?? '-'), 0, 1);

        $fpdf->SetX(20);
        $fpdf->SetFont('Arial', 'I', 8);
        $fpdf->SetTextColor(100, 116, 139);
        $fpdf->Cell(120, 5, 'Batch: ' . $item->batch_id . ' | File: ' . ($item->filename ?: '-'), 0, 1);

        $fpdf->SetY($curY + 28);

        // Table Header
        $fpdf->SetFillColor(3, 66, 142);
        $fpdf->SetTextColor(255, 255, 255);
        $fpdf->SetDrawColor(3, 66, 142);
        $fpdf->SetFont('Arial', 'B', 9);
        $fpdf->Cell(12, 7, 'No', 1, 0, 'C', true);
        $fpdf->Cell(65, 7, 'Parameter / Kolom', 1, 0, 'L', true);
        $fpdf->Cell(103, 7, 'Nilai Informasi Hasil Pengujian', 1, 1, 'L', true);

        // Table Rows
        $fpdf->SetFont('Arial', '', 8.5);
        $fpdf->SetTextColor(30, 41, 59);
        $fpdf->SetDrawColor(203, 213, 225);

        $no = 1;
        if (is_array($item->data)) {
            foreach ($item->data as $key => $val) {
                $bg = ($no % 2 === 0);
                $fpdf->SetFillColor(248, 250, 252);
                $fpdf->Cell(12, 6, (string)$no++, 1, 0, 'C', $bg);
                $fpdf->Cell(65, 6, substr(ucwords(str_replace(['_', '-'], ' ', (string)$key)), 0, 35), 1, 0, 'L', $bg);
                $valStr = is_array($val) ? json_encode($val) : (string)($val ?? '-');
                $fpdf->Cell(103, 6, substr($valStr, 0, 65), 1, 1, 'L', $bg);
            }
        }

        $fpdf->Ln(6);
        $fpdf->SetFont('Arial', 'I', 8);
        $fpdf->SetTextColor(100, 116, 139);
        $fpdf->MultiCell(0, 4, "Catatan: Dokumen laporan pengujian ini dibuat secara otomatis dengan sistem laboratorium BPOM (FPDF + Laravel). Pindai QR Code untuk memverifikasi keabsahan data.");

        $fpdf->Ln(8);
        $fpdf->SetFont('Arial', '', 9);
        $fpdf->SetTextColor(15, 23, 42);
        $fpdf->Cell(90, 5, 'Mengetahui, Penyelia', 0, 0, 'C');
        $fpdf->Cell(90, 5, 'Mataram, ' . date('d F Y'), 0, 1, 'C');
        $fpdf->Ln(15);
        $fpdf->SetFont('Arial', 'B', 9);
        $fpdf->Cell(90, 5, 'Apt. I Made Sujana, S.Farm.', 0, 0, 'C');
        $fpdf->Cell(90, 5, 'Apt. Lalu Naofal Arkan, S.Farm.', 0, 1, 'C');

        return response($fpdf->Output('S', 'LHP-FPDF-' . Str::slug($item->display_title ?: ('sampel-' . $item->id)) . '.pdf'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="LHP-FPDF-' . Str::slug($item->display_title ?: ('sampel-' . $item->id)) . '.pdf"',
        ]);
    }

    /**
     * Delete an entire batch.
     */
    public function destroyBatch(string $batchId)
    {
        $deleted = PengujianItem::where('batch_id', $batchId)->delete();

        return redirect()->route('pengujian.index')
            ->with('success', "File / Batch data ({$deleted} baris) berhasil dihapus.");
    }

    /**
     * Delete all records in the database.
     */
    public function clearAll()
    {
        $count = PengujianItem::count();
        PengujianItem::truncate();

        return redirect()->route('pengujian.index')
            ->with('success', "Seluruh data pengujian ({$count} data) berhasil dikosongkan.");
    }
}
