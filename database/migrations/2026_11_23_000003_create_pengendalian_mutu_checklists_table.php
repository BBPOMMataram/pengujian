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
        Schema::create('pengendalian_mutu_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengendalian_mutu_id')->constrained('pengendalian_mutus')->cascadeOnDelete();
            $table->string('section_title');
            $table->string('item_key', 100)->index();
            $table->string('item_name');
            $table->text('criteria')->nullable();
            $table->string('status', 20)->nullable(); // ya / tidak / na
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengendalian_mutu_checklists');
    }
};
