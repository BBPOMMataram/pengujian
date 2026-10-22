<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('lokasi_sampel')) {
            Schema::create('lokasi_sampel', function (Blueprint $table) {
                $table->id();
                $table->string('kode_lokasi')->unique();
                $table->string('name');
                $table->string('keterangan')->nullable();
                $table->timestamps();
            });
        } elseif (!Schema::hasColumn('lokasi_sampel', 'keterangan')) {
            Schema::table('lokasi_sampel', function (Blueprint $table) {
                $table->string('keterangan')->nullable()->after('name');
            });
        }

        // Seed master data default: Kimia, Mikro, Retensi
        $defaults = [
            [
                'kode_lokasi' => 'K',
                'name' => 'Kimia',
                'keterangan' => 'Laboratorium Pengujian Kimia',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_lokasi' => 'M',
                'name' => 'Mikro',
                'keterangan' => 'Laboratorium Pengujian Mikrobiologi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_lokasi' => 'R',
                'name' => 'Retensi',
                'keterangan' => 'Penyimpanan Sampel Retensi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($defaults as $item) {
            DB::table('lokasi_sampel')->updateOrInsert(
                ['kode_lokasi' => $item['kode_lokasi']],
                ['name' => $item['name'], 'keterangan' => $item['keterangan'], 'updated_at' => now()]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lokasi_sampel');
    }
};
