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

    public function test_spu_pdf_renders_three_official_signatories_and_spu_layout()
    {
        $this->post('/load-sample-pdf');
        $item = PengujianItem::first();

        // 1. Assert item has official SPU metadata & 3 signatories (stored in _meta or data)
        $meta = $item->data['_meta'] ?? $item->data;
        $this->assertArrayHasKey('petugas_penerima', $meta);
        $this->assertEquals('Nirmala Nurhasanah, STP', $meta['petugas_penerima']);
        $this->assertEquals('199205172023212054', $meta['nip_petugas_penerima']);

        $this->assertArrayHasKey('manager_administrasi', $meta);
        $this->assertEquals('Basuki Murdi Hartono, SH.', $meta['manager_administrasi']);
        $this->assertEquals('197904052005011002', $meta['nip_manager_administrasi']);

        $this->assertArrayHasKey('kepala_balai', $meta);
        $this->assertEquals('Yogi Abaso Mataram, S.Si., Apt', $meta['kepala_balai']);
        $this->assertEquals('198101242009121002', $meta['nip_kepala_balai']);

        // 2. Render HTML view of SPU PDF and assert exact 3 signatories
        $html = view('pdf.laporan-pengujian', [
            'item' => $item,
            'qrBase64' => base64_encode('<svg></svg>'),
            'orientation' => 'landscape',
            'printedAt' => now()->format('d/m/Y H:i'),
        ])->render();

        // Signatory 1: Pemohon / Pengirim Sampel (Petugas Penerima Sampel)
        $this->assertStringContainsString('Pemohon / Pengirim Sampel', $html);
        $this->assertStringContainsString('(Petugas Penerima Sampel)', $html);
        $this->assertStringContainsString('Nirmala Nurhasanah, STP', $html);
        $this->assertStringContainsString('199205172023212054', $html);

        // Signatory 2: Penerima Sampel (Manager Administrasi)
        $this->assertStringContainsString('Penerima Sampel', $html);
        $this->assertStringContainsString('(Manager Administrasi)', $html);
        $this->assertStringContainsString('Basuki Murdi Hartono, SH.', $html);
        $this->assertStringContainsString('197904052005011002', $html);

        // Signatory 3: Mengetahui (Kepala Balai)
        $this->assertStringContainsString('Mengetahui', $html);
        $this->assertStringContainsString('(Kepala Balai)', $html);
        $this->assertStringContainsString('Yogi Abaso Mataram, S.Si., Apt', $html);
        $this->assertStringContainsString('198101242009121002', $html);

        // Assert SPU Sections
        $this->assertStringContainsString('DAFTAR LAMPIRAN SAMPEL', $html);
        $this->assertStringContainsString('LABEL KODE SAMPEL', $html);
        $this->assertStringContainsString('BALAI BESAR POM DI MATARAM', $html);

        // 3. Test actual PDF binary stream endpoint
        $pdfResponse = $this->get("/item/{$item->id}/pdf");
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('content-type', 'application/pdf');
    }

    public function test_spu_batch_pdf_renders_spu_format_and_decoded_catatan()
    {
        $this->post('/load-sample-pdf');
        $item = PengujianItem::first();

        // Uji parsing kode K2,M2,A1 dengan teks cluster di belakangnya
        $sampleCatatan = 'K2,M2,A1 Cluster B/ Compliance Importir/ Uji Keamanan dan Mutu';
        $decodedLokasi = \App\Models\LokasiSampel::parseCatatan($sampleCatatan, 'Kosmetika', 'Kosmetika');
        $this->assertEquals('Kimia 2 Sampel, Mikro 2 Sampel dan Arsip 1 Sampel', $decodedLokasi);

        // Uji ekstrasi jumlah sampel T K M P S
        $counts = \App\Models\LokasiSampel::extractCounts($sampleCatatan, 'Kosmetika', 'Kosmetika');
        $this->assertEquals(5, $counts['T']);
        $this->assertEquals('2', $counts['K']);
        $this->assertEquals('2', $counts['M']);
        $this->assertEquals('1', $counts['A']);

        // Update item data dengan catatan baru untuk memverifikasi rendering view PDF
        $itemData = $item->data;
        $itemData['Catatan'] = $sampleCatatan;
        $item->data = $itemData;
        $item->save();

        $batchResponse = $this->get("/batch/{$item->batch_id}/pdf-all");
        $batchResponse->assertStatus(200);
        $batchResponse->assertHeader('content-type', 'application/pdf');

        $items = PengujianItem::where('batch_id', $item->batch_id)->get();
        $html = view('pdf.batch-pengujian', [
            'items' => $items,
            'headers' => ['No', 'Kode Sampel', 'Identitas Sample', 'Keterangan Sampling', 'Jumlah Sampel', 'Rekomendasi Uji', 'No. Surat Petugas Sampling', 'Catatan'],
            'batchId' => $item->batch_id,
            'filename' => $item->filename,
            'sheetName' => $item->sheet_name,
            'orientation' => 'landscape',
        ])->render();

        // Check SPU columns & decoded catatan
        $this->assertStringContainsString('Identitas Sample', $html);
        $this->assertStringContainsString('Keterangan Tambahan', $html);
        $this->assertStringContainsString('Catatan', $html);
        $this->assertStringContainsString('Kimia 2 Sampel, Mikro 2 Sampel dan Arsip 1 Sampel', $html);

        // Check signatories in batch PDF
        $this->assertStringContainsString('Nirmala Nurhasanah, STP', $html);
        $this->assertStringContainsString('Basuki Murdi Hartono, SH.', $html);
        $this->assertStringContainsString('Yogi Abaso Mataram, S.Si., Apt', $html);

        // Verify that internal metadata fields do not appear as table headers
        $this->assertStringNotContainsString('<th>manager_administrasi</th>', $html);
        $this->assertStringNotContainsString('<th>kepala_balai</th>', $html);
    }
}
