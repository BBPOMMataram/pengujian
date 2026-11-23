<?php

namespace Database\Seeders;

use App\Models\MasterPenyelia;
use Illuminate\Database\Seeder;

class MasterPenyeliaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $penyelias = [
            [
                'nama' => 'IGM Aryama Jelantik, S.Si',
                'jabatan' => 'Penyelia Mikrobiologi',
                'is_active' => true,
            ],
            [
                'nama' => 'Haryoudi Valorischa, S.Si',
                'jabatan' => 'Penyelia Mikrobiologi',
                'is_active' => true,
            ],
        ];

        foreach ($penyelias as $data) {
            MasterPenyelia::firstOrCreate(
                ['nama' => $data['nama']],
                $data
            );
        }
    }
}
