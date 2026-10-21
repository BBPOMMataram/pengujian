<?php

namespace App\Http\Controllers;

use App\Exports\SamplePengujianExport;
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
                  ->orWhere('data', 'like', "%{$search}%");
            });
        }

        $items = $itemsQuery->orderBy('row_number', 'asc')->get();

        // Prepare QR codes and headers for each item
        $headers = [];
        foreach ($items as $item) {
            $targetUrl = route('pengujian.scan', $item->qr_token);
            $item->target_url = $targetUrl;
            
            // Generate small SVG for table column
            $item->qr_svg_small = (string) QrCode::size(70)->margin(1)->generate($targetUrl);
            
            // Generate large SVG for popup modal
            $item->qr_svg_large = (string) QrCode::size(240)->margin(2)->generate($targetUrl);
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
                ->with('success', "Berhasil mengunggah file \"{$originalName}\"! {$count} baris data berhasil diproses dengan QR Code siap scan.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }

    /**
     * Download the official sample Excel template.
     */
    public function downloadTemplate()
    {
        return Excel::download(new SamplePengujianExport, 'Contoh_Data_Pengujian_Laboratorium.xlsx');
    }

    /**
     * Quick-load sample data directly into the database.
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

            PengujianItem::create([
                'batch_id' => $batchId,
                'filename' => $filename,
                'row_number' => $index + 1,
                'code' => $data['kode_sampel'] ?? ('SMP-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT)),
                'name' => $data['nama_sampel'] ?? 'Sampel Pengujian',
                'qr_token' => Str::random(32),
                'data' => $data,
            ]);
            $count++;
        }

        return redirect()->route('pengujian.index', ['batch' => $batchId])
            ->with('success', "Berhasil memuat {$count} data sampel contoh laboratorium BPOM dengan QR Code!");
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
     * Generate PDF using barryvdh/laravel-dompdf.
     */
    public function pdf(int $id, Request $request)
    {
        $item = PengujianItem::findOrFail($id);
        $action = $request->query('download') ? 'attachment' : 'inline';

        return $this->generateDomPdf($item, $action);
    }

    /**
     * Helper to render DomPDF.
     */
    protected function generateDomPdf(PengujianItem $item, string $disposition = 'inline')
    {
        $targetUrl = route('pengujian.scan', $item->qr_token);
        $qrSvg = (string) QrCode::size(120)->margin(1)->generate($targetUrl);
        $qrBase64 = base64_encode($qrSvg);

        $pdf = Pdf::loadView('pdf.laporan-pengujian', [
            'item' => $item,
            'qrBase64' => $qrBase64,
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
        $targetUrl = route('pengujian.scan', $item->qr_token);

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
        $fpdf->SetFont('Arial', 'B', 10);
        $fpdf->SetTextColor(3, 66, 142);
        $fpdf->Cell(120, 5, 'KODE SAMPEL: ' . ($item->code ?? '-'), 0, 1);

        $fpdf->SetX(20);
        $fpdf->SetFont('Arial', '', 9);
        $fpdf->SetTextColor(15, 23, 42);
        $fpdf->Cell(120, 5, 'Nama Sampel: ' . ($item->name ?? '-'), 0, 1);

        $fpdf->SetX(20);
        $fpdf->SetFont('Arial', 'I', 8);
        $fpdf->SetTextColor(100, 116, 139);
        $fpdf->Cell(120, 4, 'Verifikasi Token: ' . $item->qr_token, 0, 1);

        $fpdf->SetY($curY + 23);
        $fpdf->Ln(4);

        // Section header
        $fpdf->SetFont('Arial', 'B', 10);
        $fpdf->SetTextColor(3, 66, 142);
        $fpdf->Cell(0, 6, 'RINCIAN DATA HASIL PENGUJIAN', 0, 1, 'L');
        $fpdf->Ln(1);

        // Table Header
        $fpdf->SetFillColor(3, 66, 142);
        $fpdf->SetTextColor(255, 255, 255);
        $fpdf->SetDrawColor(3, 66, 142);
        $fpdf->SetFont('Arial', 'B', 9);
        $fpdf->Cell(12, 7, 'No', 1, 0, 'C', true);
        $fpdf->Cell(65, 7, 'Parameter / Kolom', 1, 0, 'L', true);
        $fpdf->Cell(103, 7, 'Nilai Hasil Pengujian', 1, 1, 'L', true);

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
                $fpdf->Cell(65, 6, ucwords(str_replace(['_', '-'], ' ', (string)$key)), 1, 0, 'L', $bg);
                $valStr = is_array($val) ? json_encode($val) : (string)($val ?? '-');
                $fpdf->Cell(103, 6, substr($valStr, 0, 65), 1, 1, 'L', $bg);
            }
        }

        $fpdf->Ln(6);
        $fpdf->SetFont('Arial', 'I', 8);
        $fpdf->SetTextColor(100, 116, 139);
        $fpdf->MultiCell(0, 4, "Catatan: Dokumen laporan pengujian ini dibuat secara otomatis dengan plugin FPDF & Laravel. Pindai QR Code untuk memverifikasi keabsahan data.");

        $fpdf->Ln(8);
        $fpdf->SetFont('Arial', '', 9);
        $fpdf->SetTextColor(15, 23, 42);
        $fpdf->Cell(90, 5, 'Mengetahui, Penyelia', 0, 0, 'C');
        $fpdf->Cell(90, 5, 'Mataram, ' . date('d F Y'), 0, 1, 'C');
        $fpdf->Ln(15);
        $fpdf->SetFont('Arial', 'B', 9);
        $fpdf->Cell(90, 5, 'Apt. I Made Sujana, S.Farm.', 0, 0, 'C');
        $fpdf->Cell(90, 5, 'Apt. Lalu Naofal Arkan, S.Farm.', 0, 1, 'C');

        return response($fpdf->Output('S', 'LHP-FPDF-' . $item->code . '.pdf'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="LHP-FPDF-' . $item->code . '.pdf"',
        ]);
    }

    /**
     * Delete an entire batch of data.
     */
    public function destroyBatch(string $batchId)
    {
        $deleted = PengujianItem::where('batch_id', $batchId)->delete();

        return redirect()->route('pengujian.index')
            ->with('success', "Batch data ({$deleted} baris) berhasil dihapus.");
    }

    /**
     * Clear all records.
     */
    public function clearAll()
    {
        PengujianItem::truncate();

        return redirect()->route('pengujian.index')
            ->with('success', 'Semua data pengujian berhasil dikosongkan.');
    }
}
