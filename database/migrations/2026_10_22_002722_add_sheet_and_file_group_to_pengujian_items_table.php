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
        Schema::table('pengujian_items', function (Blueprint $table) {
            $table->string('file_group_id')->nullable()->index()->after('batch_id');
            $table->string('sheet_name')->nullable()->after('filename');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengujian_items', function (Blueprint $table) {
            $table->dropColumn(['file_group_id', 'sheet_name']);
        });
    }
};
