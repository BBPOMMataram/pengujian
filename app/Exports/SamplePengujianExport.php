<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SamplePengujianExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'No',
            'Kode Sampel',
            'Nama Sampel',
            'Kategori Produk',
            'Parameter Uji',
            'Metode Analisis',
            'Hasil Uji',
            'Syarat / Batas',
            'Kesimpulan',
            'Tanggal Uji',
            'Nama Analis',
        ];
    }

    public function array(): array
    {
        return [
            [
                '1',
                'SMP-2026-001',
                'Parasetamol Sirup 120mg/5ml',
                'Obat Bebas',
                'Kadar Parasetamol & Uji Cemaran EG/DEG',
                'KCKT / HPLC',
                '99.8% (EG/DEG Tidak Terdeteksi)',
                '90.0% - 110.0% (EG/DEG < 0.1%)',
                'Memenuhi Syarat (MS)',
                '2026-10-21',
                'Apt. Lalu Naofal Arkan, S.Farm.',
            ],
            [
                '2',
                'SMP-2026-002',
                'Amoksisilin Kapsul 500mg',
                'Obat Keras',
                'Disolusi & Penetapan Kadar',
                'Spektrofotometri UV-Vis',
                '98.4%',
                '90.0% - 120.0%',
                'Memenuhi Syarat (MS)',
                '2026-10-21',
                'Siti Aminah, S.Si.',
            ],
            [
                '3',
                'SMP-2026-003',
                'Krim Pencerah Wajah Herbal',
                'Kosmetik',
                'Identifikasi Merkuri (Hg) & Hidrokuinon',
                'AAS & KLT',
                'Negatif (Tidak Terdeteksi)',
                'Bebas Merkuri & Hidrokuinon',
                'Memenuhi Syarat (MS)',
                '2026-10-20',
                'Budi Santoso, A.Md.AK',
            ],
            [
                '4',
                'SMP-2026-004',
                'Kapsul Jamu Pegal Linu Tradisional',
                'Obat Bahan Alam',
                'Uji BKO (Dexamethasone & Paracetamol)',
                'KCKT Diode Array',
                'Negatif BKO',
                'Bebas Bahan Kimia Obat (BKO)',
                'Memenuhi Syarat (MS)',
                '2026-10-19',
                'Apt. Lalu Naofal Arkan, S.Farm.',
            ],
            [
                '5',
                'SMP-2026-005',
                'Minuman Sari Buah Apel Kemasan',
                'Pangan Olahan',
                'Uji Angka Lempeng Total (ALT) & E. Coli',
                'Mikrobiologi SNI',
                '< 10 Koloni/ml (Negatif E. Coli)',
                'Maks. 1x10^4 Koloni/ml',
                'Memenuhi Syarat (MS)',
                '2026-10-18',
                'Dewi Lestari, S.Si.',
            ],
        ];
    }
}
