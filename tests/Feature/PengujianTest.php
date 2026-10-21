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
        $response->assertSee('Laboratorium Pengujian BPOM Mataram');
    }

    public function test_load_sample_data_creates_items()
    {
        $response = $this->post('/load-sample');
        $response->assertRedirect();

        $this->assertDatabaseCount('pengujian_items', 5);

        $item = PengujianItem::first();
        $this->assertNotNull($item->qr_token);
        $this->assertNotEmpty($item->data);
    }

    public function test_index_displays_table_with_qr_code_column()
    {
        $this->post('/load-sample');

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('QR Code & Aksi', false);
        $response->assertSee('Lihat QR');
        $response->assertSee('Buka PDF');
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

    public function test_excel_template_download()
    {
        $response = $this->get('/download-template');
        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
    }
}
