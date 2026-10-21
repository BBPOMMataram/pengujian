<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SampleTwoColumnExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'Data Informasi Sampel',
            'QR Code (Biarkan Kosong)',
        ];
    }

    public function array(): array
    {
        return [
            ['BBPOM/LAB/2026/001 - Parasetamol Sirup 120mg/5ml (Batch A101)', ''],
            ['BBPOM/LAB/2026/002 - Amoksisilin Kapsul 500mg (Batch B204)', ''],
            ['BBPOM/LAB/2026/003 - Krim Pencerah Herbal Malam (Batch C309)', ''],
            ['BBPOM/LAB/2026/004 - Minuman Serbuk Rasa Jeruk (Batch D412)', ''],
            ['BBPOM/LAB/2026/005 - Jamu Pegal Linu Tradisional (Batch E515)', ''],
            ['BBPOM/LAB/2026/006 - Sirup Obat Batuk Dewasa (Batch F618)', ''],
            ['BBPOM/LAB/2026/007 - Kapsul Minyak Ikan Omega 3 (Batch G721)', ''],
            ['BBPOM/LAB/2026/008 - Makanan Ringan Ekstrudat Jagung (Batch H824)', ''],
        ];
    }
}

