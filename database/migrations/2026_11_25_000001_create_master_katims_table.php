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
        Schema::create('master_katims', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('auth_user_id')->nullable()->index();
            $table->string('nama');
            $table->string('nip', 50)->nullable();
            $table->string('jabatan', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_katims');
    }
};
