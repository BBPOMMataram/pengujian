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
        Schema::create('pengujian_items', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id')->index();
            $table->string('filename')->nullable();
            $table->integer('row_number')->default(1);
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->string('qr_token', 64)->unique();
            $table->json('data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengujian_items');
    }
};
