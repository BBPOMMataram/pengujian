<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pengendalian_mutus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->date('tanggal_pengujian')->index();
            $table->json('jenis_sampel');
            $table->string('parameter_pengujian');
            $table->string('metode_pengujian', 50); // molekuler / kultur
            $table->string('status_akhir', 50);    // valid / invalid
            $table->text('catatan_evaluasi')->nullable();
            $table->foreignId('penyelia_id')->nullable()->constrained('master_penyelias')->nullOnDelete();
            $table->string('penyelia_nama');
            $table->string('penguji_nama');
            $table->string('penguji_nip', 50)->nullable();
            $table->string('penguji_signature_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengendalian_mutus');
    }
};
