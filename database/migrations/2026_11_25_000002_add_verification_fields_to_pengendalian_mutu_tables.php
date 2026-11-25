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
        Schema::table('master_penyelias', function (Blueprint $table) {
            $table->unsignedBigInteger('auth_user_id')->nullable()->index()->after('id');
        });

        Schema::table('pengendalian_mutus', function (Blueprint $table) {
            $table->timestamp('penyelia_verified_at')->nullable()->after('penyelia_nama');
            $table->unsignedBigInteger('penyelia_verified_by')->nullable()->after('penyelia_verified_at');
            $table->string('penyelia_signature_path')->nullable()->after('penyelia_verified_by');

            $table->foreignId('katim_id')->nullable()->after('penyelia_signature_path')->constrained('master_katims')->nullOnDelete();
            $table->string('katim_nama')->nullable()->after('katim_id');
            $table->string('katim_nip', 50)->nullable()->after('katim_nama');
            $table->timestamp('katim_verified_at')->nullable()->after('katim_nip');
            $table->unsignedBigInteger('katim_verified_by')->nullable()->after('katim_verified_at');
            $table->string('katim_signature_path')->nullable()->after('katim_verified_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengendalian_mutus', function (Blueprint $table) {
            $table->dropForeign(['katim_id']);
            $table->dropColumn([
                'penyelia_verified_at',
                'penyelia_verified_by',
                'penyelia_signature_path',
                'katim_id',
                'katim_nama',
                'katim_nip',
                'katim_verified_at',
                'katim_verified_by',
                'katim_signature_path',
            ]);
        });

        Schema::table('master_penyelias', function (Blueprint $table) {
            $table->dropColumn('auth_user_id');
        });
    }
};
