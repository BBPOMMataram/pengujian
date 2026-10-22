<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LokasiSampel extends Model
{
    use HasFactory;

    protected $table = 'lokasi_sampel';

    protected $fillable = [
        'kode_lokasi',
        'name',
        'keterangan',
    ];
}

