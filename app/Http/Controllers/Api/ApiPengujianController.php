<?php

namespace App\Http\Controllers\Api;

use App\Exports\SamplePengujianExport;
use App\Exports\SampleTwoColumnExport;
use App\Http\Controllers\Controller;
use App\Imports\PengujianImport;
use App\Imports\PengujianMultipleSheetsImport;
use App\Models\PengujianItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ApiPengujianController extends Controller
{
    /**
     * Get items, batch list, headers, and metadata for Next.js frontend.
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
            \Illuminate\Support\Facades\Log::error('API DB Index error: ' . $e->getMessage());
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
            \Illuminate\Support\Facades\Log::error('API Items query error: ' . $e->getMessage());
            $items = collect();
        }

        // Prepare QR codes and headers for each item
        $headers = [];
        foreach ($items as $item) {
            $targetUrl = route('pengujian.scan', $item->qr_token);
            $item->target_url = $targetUrl;
            
            // The QR code encodes the direct URL pointing to the row's PDF export
            $contentToEncode = $targetUrl;
            $item->qr_content_display = $item->qr_content ?: ($item->code ?: $targetUrl);

            // Generate small SVG for table column
            $item->qr_svg_small = (string) QrCode::size(68)->margin(1)->generate($contentToEncode);
            
            // Generate large SVG for popup modal
            $item->qr_svg_large = (string) QrCode::size(260)->margin(2)->generate($contentToEncode);
            $item->qr_base64 = base64_encode($item->qr_svg_large);

            if (is_array($item->data)) {
                foreach (array_keys($item->data) as $h) {
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

        return response()->json([
            'success' => true,
            'items' => $items,
            'batches' => $batches,
            'selectedBatchId' => $selectedBatchId,
            'currentBatch' => $currentBatch,
            'siblingSheets' => $siblingSheets,
            'headers' => $headers,
            'totalOverall' => $totalOverall,
            'dbError' => $dbError,
        ]);
    }

    /**
     * Handle Excel / CSV / PDF upload.
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

                return response()->json([
                    'success' => true,
                    'message' => "Dokumen PDF \"{$originalFilename}\" berhasil diimpor! Total {$count} baris data berhasil diekstraksi ke tabel & QR Code ter-generate otomatis.",
                    'batch_id' => $fileGroupId,
                    'count' => $count,
                ]);
            } catch (\Throwable $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses file PDF: ' . $e->getMessage(),
                ], 422);
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
                Excel::import(new PengujianMultipleSheetsImport($fileGroupId, $originalFilename, $sheetNames), $file);
                
                $firstSheetBatch = $fileGroupId . '__' . Str::slug($sheetNames[0]);
                $count = PengujianItem::where('file_group_id', $fileGroupId)->count();

                return response()->json([
                    'success' => true,
                    'message' => "File \"{$originalFilename}\" berhasil diimpor! Terdeteksi " . count($sheetNames) . " Tab/Sheet terpisah (Total {$count} baris data).",
                    'batch_id' => $firstSheetBatch,
                    'file_group_id' => $fileGroupId,
                    'count' => $count,
                ]);
            } else {
                $sheetName = $sheetNames[0] ?? 'Sheet1';
                Excel::import(new PengujianImport($fileGroupId, $originalFilename, $sheetName, $fileGroupId), $file);

                $count = PengujianItem::where('batch_id', $fileGroupId)->count();

                return response()->json([
                    'success' => true,
                    'message' => "File \"{$originalFilename}\" berhasil diimpor! Total {$count} baris data berhasil dimuat & QR Code ter-generate otomatis.",
                    'batch_id' => $fileGroupId,
                    'count' => $count,
                ]);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses file: ' . $e->getMessage(),
            ], 422);
        }
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

        return response()->json([
            'success' => true,
            'message' => "Berhasil memuat {$count} data contoh format 2-Kolom! QR code otomatis ter-generate dari isi kolom sebelahnya.",
            'batch_id' => $batchId,
            'count' => $count,
        ]);
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

        return response()->json([
            'success' => true,
            'message' => "Berhasil memuat {$count} data sampel contoh laboratorium BPOM dengan QR Code!",
            'batch_id' => $batchId,
            'count' => $count,
        ]);
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
                'Komoditi' => 'Narkotika',
                'Bentuk Sediaan' => 'Solid',
                'Netto' => '0,07 gram',
                'Tempat Sampling' => 'Kepolisian Resort Lombok Timur',
                'Pengirim' => 'MUH. BAGUS HARFIAN (01081068)',
                'Tanggal Diterima' => '2026-10-20',
                'No Surat Petugas Sampling' => 'B/1551/X/RES.4.2/2026',
                'No Billing' => '820261020575208',
                'Keterangan Sampling' => 'Plastik transparan bersegel yang dijahit benang warna merah dalam amplop coklat berlak segel barang bukti',
            ],
            [
                'No' => '2',
                'Kode Sampel' => '26.117.11.16.05.0713.K',
                'QR Code' => '(Otomatis Di-generate)',
                'Nama Produk / Sampel' => 'Kristal Putih Transparan diduga Shabu an Tsk ANDI GALIB Bin H. IMAM AS',
                'Komoditi' => 'Narkotika',
                'Bentuk Sediaan' => 'Solid',
                'Netto' => '0,07 gram',
                'Tempat Sampling' => 'Kepolisian Resort Lombok Timur',
                'Pengirim' => 'MUH. BAGUS HARFIAN (01081068)',
                'Tanggal Diterima' => '2026-10-20',
                'No Surat Petugas Sampling' => 'B/1558/X/RES.4.2/2026',
                'No Billing' => '820261020575296',
                'Keterangan Sampling' => 'Plastik transparan bersegel yang dijahit benang warna merah dalam amplop coklat berlak segel barang bukti',
            ]
        ];

        $now = now();
        foreach ($samples as $index => $data) {
            $qrLines = [
                "Kode Sampel: " . $data['Kode Sampel'],
                "Nama Produk: " . $data['Nama Produk / Sampel'],
                "Jenis / Komoditi: " . $data['Komoditi'],
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

        return response()->json([
            'success' => true,
            'message' => "Berhasil memuat contoh tabel hasil ekstraksi Dokumen PDF Surat Permintaan Uji (SPU) BBPOM!",
            'batch_id' => $batchId,
            'count' => count($samples),
        ]);
    }

    /**
     * Delete an entire batch.
     */
    public function destroyBatch(string $batchId)
    {
        $deleted = PengujianItem::where('batch_id', $batchId)->delete();

        return response()->json([
            'success' => true,
            'message' => "File / Batch data ({$deleted} baris) berhasil dihapus.",
        ]);
    }

    /**
     * Delete all records in the database.
     */
    public function clearAll()
    {
        $count = PengujianItem::count();
        PengujianItem::truncate();

        return response()->json([
            'success' => true,
            'message' => "Seluruh data pengujian ({$count} data) berhasil dikosongkan.",
        ]);
    }
}
