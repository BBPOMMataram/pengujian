<?php

namespace Tests\Feature;

use App\Models\PengujianItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengujianTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_page_is_accessible()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Laboratorium Pengujian BBPOM di Mataram');
    }

    public function test_load_sample_data_creates_items()
    {
        $response = $this->post('/load-sample');
        $response->assertRedirect();

        $this->assertDatabaseCount('pengujian_items', 8);

        $item = PengujianItem::first();
        $this->assertNotNull($item->qr_token);
        $this->assertNotEmpty($item->data);
    }

    public function test_load_sample_pdf_creates_items()
    {
        $response = $this->post('/load-sample-pdf');
        $response->assertRedirect();

        $item = PengujianItem::first();
        $this->assertNotNull($item->qr_token);
        $this->assertNotEmpty($item->data);
        $this->assertStringContainsString('Kode Sampel:', $item->qr_content);
    }

    public function test_index_displays_table_with_qr_code_column()
    {
        $this->post('/load-sample');

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('QR Code Ter-generate', false);
        $response->assertSee('Pop-up QR');
        $response->assertSee('Export PDF');
    }

    public function test_dompdf_generation_works()
    {
        $this->post('/load-sample');
        $item = PengujianItem::first();

        $response = $this->get("/item/{$item->id}/pdf");
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_fpdf_generation_works()
    {
        $this->post('/load-sample');
        $item = PengujianItem::first();

        $response = $this->get("/item/{$item->id}/pdf-fpdf");
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_qr_scan_endpoint_streams_pdf()
    {
        $this->post('/load-sample');
        $item = PengujianItem::first();

        $response = $this->get("/scan/{$item->qr_token}");
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_modal_attributes_and_qr_target_url()
    {
        $this->post('/load-sample');
        $item = PengujianItem::latest('id')->first();

        $response = $this->get('/?batch=' . $item->batch_id);
        $response->assertStatus(200);
        $response->assertSee('js-btn-qr-modal');
        $response->assertSee('data-target-url=', false);
        $response->assertSee('/scan/' . $item->qr_token);
    }

    public function test_excel_template_download()
    {
        $response = $this->get('/download-template');
        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
    }

    public function test_pdf_view_renders_spu_berita_acara_and_omits_qr_code()
    {
        $this->post('/load-sample');
        $item = PengujianItem::first();

        // Render blade view directly to inspect HTML content before PDF conversion
        $singleHtml = view('pdf.laporan-pengujian', [
            'item' => $item,
            'printedAt' => now()->format('d/m/Y H:i'),
        ])->render();

        $this->assertStringContainsString('SURAT PERMINTAAN UJI', $singleHtml);
        $this->assertStringContainsString('SPU.117.05.26.10.16.0036', $singleHtml);
        $this->assertStringContainsString('Komoditi', $singleHtml);
        $this->assertStringContainsString('Anggaran', $singleHtml);
        $this->assertStringContainsString('Asal Sampling', $singleHtml);
        $this->assertStringNotContainsString('(Otomatis Di-generate)', $singleHtml);
        $this->assertStringNotContainsString('data:image/svg+xml;base64', $singleHtml);

        // Batch Tabular View
        $items = PengujianItem::where('batch_id', $item->batch_id)->get();
        $batchHtml = view('pdf.batch-pengujian', [
            'items' => $items,
            'headers' => array_keys($item->data),
            'filename' => $item->filename,
            'batchId' => $item->batch_id,
            'printedAt' => now()->format('d/m/Y H:i'),
        ])->render();

        $this->assertStringContainsString('SURAT PERMINTAAN UJI', $batchHtml);
        $this->assertStringContainsString('SPU.117.05.26.10.16.0036', $batchHtml);
        $this->assertStringNotContainsString('<th class="col-qr"', $batchHtml);
        $this->assertStringNotContainsString('QR Code', $batchHtml);
    }

    public function test_spu_pdf_extraction_nie_lokasi_and_jenis_sampel_mapping()
    {
        $service = new \App\Services\PdfImportService();

        // 1. Test parsing K1 M0 R1 to Lokasi Sampel (0 is omitted)
        $lokasi1 = $service->parseLokasiSampel('K1 M0 R1', 'Narkotika');
        $this->assertEquals('Kimia 1 Sampel dan Retensi 1 Sampel', $lokasi1);

        $lokasi2 = $service->parseLokasiSampel('K2 M1 R0', 'Obat Tradisional');
        $this->assertEquals('Kimia 2 Sampel dan Mikro 1 Sampel', $lokasi2);

        // 2. Test empty catatan: if Narkotika -> Kimia, if other -> '-'
        $lokasiNarkotikaEmpty = $service->parseLokasiSampel('', 'Narkotika - Pihak Ke 3 - Kepolisian');
        $this->assertEquals('Kimia', $lokasiNarkotikaEmpty);

        $lokasiOtherEmpty = $service->parseLokasiSampel('', 'Obat Traditional (OT) - Rutin', 'Obat Tradisional');
        $this->assertEquals('-', $lokasiOtherEmpty);

        // 3. Test mapping Jenis Sampel to base Komoditi
        $komoditi1 = $service->mapKomoditi('Obat Traditional (OT) - Rutin');
        $this->assertEquals('Obat Tradisional', $komoditi1);

        $komoditi2 = $service->mapKomoditi('Narkotika - Pihak Ke 3 - Kepolisian');
        $this->assertEquals('Narkotika', $komoditi2);

        // 4. Test load sample PDF creates NIE, Lokasi Sampel, and full Jenis Sampel
        $this->post('/load-sample-pdf');
        $item = PengujianItem::first();

        $this->assertNotNull($item);
        $this->assertArrayHasKey('NIE', $item->data);
        $this->assertEquals('-', $item->data['NIE']);
        $this->assertArrayHasKey('Jenis Sampel', $item->data);
        $this->assertEquals('Narkotika - Pihak Ke 3 - Kepolisian', $item->data['Jenis Sampel']);
        $this->assertArrayHasKey('Lokasi Sampel', $item->data);
        $this->assertEquals('Kimia 1 Sampel dan Retensi 1 Sampel', $item->data['Lokasi Sampel']);

        // Check QR content contains NIE and Lokasi Sampel
        $this->assertStringContainsString('NIE: -', $item->qr_content);
        $this->assertStringContainsString('Jenis Sampel: Narkotika - Pihak Ke 3 - Kepolisian', $item->qr_content);
        $this->assertStringContainsString('Lokasi Sampel: Kimia 1 Sampel dan Retensi 1 Sampel', $item->qr_content);

        // Check sample 2 (Catatan: - & Narkotika -> Lokasi Sampel: Kimia)
        $item2 = PengujianItem::skip(1)->first();
        $this->assertNotNull($item2);
        $this->assertEquals('Kimia', $item2->data['Lokasi Sampel']);
        $this->assertStringContainsString('Lokasi Sampel: Kimia', $item2->qr_content);

        // Check sample 3 (Catatan: - & Obat Tradisional -> Lokasi Sampel: -)
        $item3 = PengujianItem::skip(2)->first();
        $this->assertNotNull($item3);
        $this->assertEquals('-', $item3->data['Lokasi Sampel']);
        $this->assertStringContainsString('Lokasi Sampel: -', $item3->qr_content);
    }
}
