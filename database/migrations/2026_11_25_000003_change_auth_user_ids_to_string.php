<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_katims', function (Blueprint $table) {$table->string('auth_user_id', 64)->nullable()->change();
        });

        Schema::table('master_penyelias', function (Blueprint $table) {$table->string('auth_user_id', 64)->nullable()->change();
        });

        Schema::table('pengendalian_mutus', function (Blueprint $table) {$table->string('user_id', 64)->nullable()->change();
            $table->string('penyelia_verified_by', 64)->nullable()->change();$table->string('katim_verified_by', 64)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('master_katims', function (Blueprint $table) {$table->unsignedBigInteger('auth_user_id')->nullable()->change();
        });

        Schema::table('master_penyelias', function (Blueprint $table) {$table->unsignedBigInteger('auth_user_id')->nullable()->change();
        });

        Schema::table('pengendalian_mutus', function (Blueprint $table) {$table->unsignedBigInteger('user_id')->nullable()->change();
            $table->unsignedBigInteger('penyelia_verified_by')->nullable()->change();$table->unsignedBigInteger('katim_verified_by')->nullable()->change();
        });
    }
};