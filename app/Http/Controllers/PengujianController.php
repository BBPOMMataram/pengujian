<?php

namespace App\Http\Controllers;

use App\Exports\SamplePengujianExport;
use App\Exports\SampleTwoColumnExport;
use App\Imports\PengujianImport;
use App\Models\PengujianItem;
use Barryvdh\DomPDF\Facade\Pdf;
use FPDF;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PengujianController extends Controller
{
    /**
     * Display the main web page with upload form, stats, and data table.
     */
    public function index(Request $request)
    {
        // Get list of distinct batches
        $batches = PengujianItem::select('batch_id', 'filename', 'created_at')
            ->selectRaw('count(*) as total_items')
            ->groupBy('batch_id', 'filename', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

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
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('qr_content', 'like', "%{$search}%")
                  ->orWhere('data', 'like', "%{$search}%");
            });
        }

        $items = $itemsQuery->orderBy('row_number', 'asc')->get();

        // Prepare QR codes and headers for each item
        $headers = [];
        foreach ($items as $item) {
            $targetUrl = route('pengujian.scan', $item->qr_token);
            $item->target_url = $targetUrl;
            
            // The QR code contains the information from the adjacent column!
            $contentToEncode = $item->qr_content ?: ($item->code ?: $targetUrl);
            $item->qr_content_display = $contentToEncode;

            // Generate small SVG for table column (di kolom sampingnya)
            $item->qr_svg_small = (string) QrCode::size(68)->margin(1)->generate($contentToEncode);
            
            // Generate large SVG for popup modal
            $item->qr_svg_large = (string) QrCode::size(250)->margin(2)->generate($contentToEncode);
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

        return view('pengujian.index', [
            'items' => $items,
            'batches' => $batches,
            'selectedBatchId' => $selectedBatchId,
            'currentBatch' => $currentBatch,
            'headers' => $headers,
            'totalOverall' => PengujianItem::count(),
        ]);
    }

    /**
     * Handle Excel upload using Maatwebsite\Excel.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ], [
            'excel_file.required' => 'Silakan pilih file Excel atau CSV terlebih dahulu.',
            'excel_file.mimes' => 'Format file harus berupa Excel (.xlsx, .xls) atau .csv.',
            'excel_file.max' => 'Ukuran file maksimal 20 MB.',
        ]);

        $file = $request->file('excel_file');
        $originalName = $file->getClientOriginalName();
        $batchId = 'BATCH-' . date('Ymd-His') . '-' . Str::upper(Str::random(4));

        try {
            Excel::import(new PengujianImport($batchId, $originalName), $file);
            $count = PengujianItem::where('batch_id', $batchId)->count();

            return redirect()->route('pengujian.index', ['batch' => $batchId])
                ->with('success', "Berhasil mengunggah file \"{$originalName}\"! {$count} baris data berhasil dipetakan dengan QR Code di kolom sampingnya.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
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
        $headings = $export->headings();
        $rows = $export->array();

        $batchId = 'SAMPLE-2COL-' . date('Ymd-His');
        $filename = 'Contoh_2_Kolom_Data_dan_QR.xlsx';

        $count = 0;
        foreach ($rows as $index => $rowValues) {
            $dataVal = $rowValues[0] ?? "Sampel #" . ($index + 1);

            $data = [
                'Data Informasi' => $dataVal,
                'QR Code' => '(Otomatis Di-generate)',
            ];

            PengujianItem::create([
                'batch_id' => $batchId,
                'filename' => $filename,
                'row_number' => $index + 1,
                'code' => 'SMP-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'name' => $dataVal,
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

        $batchId = 'SAMPLE-FULL-' . date('Ymd-His');
        $filename = 'Contoh_Lengkap_Pengujian_Laboratorium.xlsx';

        $count = 0;
        foreach ($rows as $index => $rowValues) {
            $data = [];
            foreach ($headings as $i => $h) {
                $data[$h] = $rowValues[$i] ?? null;
            }

            $code = $data['Kode Sampel'] ?? ('SMP-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT));
            $name = $data['Nama Sampel'] ?? 'Sampel Pengujian';
            $qrContent = "{$code} | {$name} | Hasil: " . ($data['Kesimpulan'] ?? 'MS');

            PengujianItem::create([
                'batch_id' => $batchId,
                'filename' => $filename,
                'row_number' => $index + 1,
                'code' => $code,
                'name' => $name,
                'qr_token' => Str::random(32),
                'qr_content' => $qrContent,
                'data' => $data,
            ]);
            $count++;
        }

        return redirect()->route('pengujian.index', ['batch' => $batchId])
            ->with('success', "Berhasil memuat {$count} data sampel contoh multi-kolom laboratorium BPOM dengan QR Code!");
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
     * Generate PDF using barryvdh/laravel-dompdf for single row.
     */
    public function pdf(int $id, Request $request)
    {
        $item = PengujianItem::findOrFail($id);
        $action = $request->query('download') ? 'attachment' : 'inline';

        return $this->generateDomPdf($item, $action);
    }

    /**
     * Batch export: export all items in a batch to a combined PDF.
     */
    public function pdfBatch(string $batchId, Request $request)
    {
        $items = PengujianItem::where('batch_id', $batchId)->orderBy('row_number', 'asc')->get();
        if ($items->isEmpty()) {
            return redirect()->route('pengujian.index')->with('error', 'Batch data tidak ditemukan.');
        }

        foreach ($items as $item) {
            $contentToEncode = $item->qr_content ?: ($item->code ?: route('pengujian.scan', $item->qr_token));
            $qrSvg = (string) QrCode::size(120)->margin(1)->generate($contentToEncode);
            $item->qr_base64 = base64_encode($qrSvg);
        }

        $pdf = Pdf::loadView('pdf.batch-pengujian', [
            'items' => $items,
            'batchId' => $batchId,
            'filename' => $items->first()->filename ?? 'Batch_Pengujian',
        ]);

        $pdf->setPaper('A4', 'portrait');

        $downloadFilename = 'LHP-Batch-' . Str::slug($batchId) . '.pdf';
        return $pdf->download($downloadFilename);
    }

    /**
     * Helper to render DomPDF for a single row.
     */
    protected function generateDomPdf(PengujianItem $item, string $disposition = 'inline')
    {
        $targetUrl = route('pengujian.scan', $item->qr_token);
        
        // The QR code contains the information from the adjacent column!
        $contentToEncode = $item->qr_content ?: ($item->code ?: $targetUrl);
        $qrSvg = (string) QrCode::size(120)->margin(1)->generate($contentToEncode);
        $qrBase64 = base64_encode($qrSvg);

        $pdf = Pdf::loadView('pdf.laporan-pengujian', [
            'item' => $item,
            'qrBase64' => $qrBase64,
            'qrContent' => $contentToEncode,
            'targetUrl' => $targetUrl,
        ]);

        $pdf->setPaper('A4', 'portrait');

        $filename = 'LHP-' . Str::slug($item->code ?: 'sampel') . '.pdf';

        if ($disposition === 'attachment') {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    /**
     * Generate PDF using FPDF plugin.
     */
    public function pdfFpdf(int $id)
    {
        $item = PengujianItem::findOrFail($id);
        $contentToEncode = $item->qr_content ?: ($item->code ?: route('pengujian.scan', $item->qr_token));

        $fpdf = new FPDF('P', 'mm', 'A4');
        $fpdf->AddPage();
        $fpdf->SetAutoPageBreak(true, 15);

        // Header BPOM
        $fpdf->SetFont('Arial', 'B', 12);
        $fpdf->SetTextColor(3, 66, 142);
        $fpdf->Cell(0, 6, 'BADAN PENGAWAS OBAT DAN MAKANAN', 0, 1, 'C');
        $fpdf->SetFont('Arial', 'B', 13);
        $fpdf->SetTextColor(15, 23, 42);
        $fpdf->Cell(0, 6, 'BALAI BESAR POM DI MATARAM - LABORATORIUM PENGUJIAN', 0, 1, 'C');
        $fpdf->SetFont('Arial', '', 8);
        $fpdf->SetTextColor(100, 116, 139);
        $fpdf->Cell(0, 4, 'Jl. Cendrawasih No. 84, Cakranegara, Kota Mataram, NTB 83239 | Telp: (0370) 621926', 0, 1, 'C');
        $fpdf->Ln(2);

        // Double line
        $fpdf->SetDrawColor(3, 66, 142);
        $fpdf->SetLineWidth(0.8);
        $fpdf->Line(15, $fpdf->GetY(), 195, $fpdf->GetY());
        $fpdf->SetLineWidth(0.3);
        $fpdf->Line(15, $fpdf->GetY() + 1.2, 195, $fpdf->GetY() + 1.2);
        $fpdf->Ln(7);

        // Title
        $fpdf->SetFont('Arial', 'B', 13);
        $fpdf->SetTextColor(15, 23, 42);
        $fpdf->Cell(0, 7, 'LAPORAN HASIL PENGUJIAN SAMPEL (FPDF)', 0, 1, 'C');
        $fpdf->SetFont('Arial', '', 9);
        $fpdf->SetTextColor(71, 85, 105);
        $fpdf->Cell(0, 5, 'Nomor Dokumen: LHP/FPDF/' . date('Y') . '/' . str_pad($item->id, 5, '0', STR_PAD_LEFT), 0, 1, 'C');
        $fpdf->Ln(4);

        // Sample Info Box
        $fpdf->SetFillColor(240, 247, 255);
        $fpdf->SetDrawColor(191, 219, 254);
        $fpdf->Rect(15, $fpdf->GetY(), 180, 26, 'DF');

        $curY = $fpdf->GetY() + 3;
        $fpdf->SetXY(20, $curY);
        $fpdf->SetFont('Arial', 'B', 11);
        $fpdf->SetTextColor(3, 66, 142);
        $fpdf->Cell(170, 6, $item->name ?: 'Sampel Pengujian', 0, 1);

        $fpdf->SetX(20);
        $fpdf->SetFont('Arial', '', 9);
        $fpdf->SetTextColor(51, 65, 85);
        $fpdf->Cell(170, 5, 'Kode Sampel: ' . ($item->code ?: '-') . '   |   Baris: #' . $item->row_number, 0, 1);

        $fpdf->SetX(20);
        $fpdf->SetFont('Arial', 'I', 8);
        $fpdf->SetTextColor(100, 116, 139);
        $fpdf->Cell(170, 5, 'Isi QR Code: ' . substr($contentToEncode, 0, 80), 0, 1);

        $fpdf->Ln(12);

        // Column Mapping Table
        $fpdf->SetFont('Arial', 'B', 10);
        $fpdf->SetTextColor(3, 66, 142);
        $fpdf->Cell(0, 6, 'PEMETAAN DATA PER KOLOM EXCEL:', 0, 1);
        $fpdf->Ln(1);

        // Table Header
        $fpdf->SetFillColor(3, 66, 142);
        $fpdf->SetTextColor(255, 255, 255);
        $fpdf->SetFont('Arial', 'B', 9);
        $fpdf->Cell(12, 7, 'No', 1, 0, 'C', true);
        $fpdf->Cell(65, 7, 'Kolom Excel', 1, 0, 'L', true);
        $fpdf->Cell(103, 7, 'Nilai Informasi', 1, 1, 'L', true);

        // Table Rows
        $fpdf->SetTextColor(15, 23, 42);
        $fpdf->SetFont('Arial', '', 8.5);
        $no = 1;
        $fill = false;
        if (is_array($item->data)) {
            foreach ($item->data as $k => $v) {
                $fpdf->SetFillColor(248, 250, 252);
                $fpdf->Cell(12, 6, (string)$no++, 1, 0, 'C', $fill);
                $fpdf->Cell(65, 6, substr(str_replace(['_', '-'], ' ', (string)$k), 0, 35), 1, 0, 'L', $fill);
                
                $vStr = is_array($v) ? json_encode($v) : (string)($v ?? '-');
                $fpdf->Cell(103, 6, substr($vStr, 0, 60), 1, 1, 'L', $fill);
                $fill = !$fill;
            }
        }

        $fpdf->Ln(15);
        $fpdf->SetFont('Arial', '', 8.5);
        $fpdf->SetTextColor(100, 116, 139);
        $fpdf->Cell(0, 5, 'Dicetak melalui Sistem Pengujian Laboratorium BBPOM di Mataram pada ' . date('d/m/Y H:i') . ' WITA', 0, 1, 'C');

        return response($fpdf->Output('S', 'LHP-FPDF-' . ($item->code ?: 'sampel') . '.pdf'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="LHP-FPDF-' . ($item->code ?: 'sampel') . '.pdf"');
    }

    /**
     * Delete an entire batch.
     */
    public function destroyBatch(string $batchId)
    {
        $deleted = PengujianItem::where('batch_id', $batchId)->delete();

        return redirect()->route('pengujian.index')
            ->with('success', "Batch \"{$batchId}\" ({$deleted} data) berhasil dihapus.");
    }

    /**
     * Clear all data in database.
     */
    public function clearAll()
    {
        PengujianItem::truncate();

        return redirect()->route('pengujian.index')
            ->with('success', 'Semua data pengujian laboratorium berhasil dikosongkan.');
    }
}
