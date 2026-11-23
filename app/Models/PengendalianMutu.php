<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengendalianMutu extends Model
{
    use HasFactory;

    protected $table = 'pengendalian_mutus';

    protected $fillable = [
        'user_id',
        'tanggal_pengujian',
        'jenis_sampel',
        'parameter_pengujian',
        'metode_pengujian',
        'status_akhir',
        'catatan_evaluasi',
        'penyelia_id',
        'penyelia_nama',
        'penguji_nama',
        'penguji_nip',
        'penguji_signature_path',
    ];

    protected $casts = [
        'jenis_sampel' => 'array',
        'tanggal_pengujian' => 'date:Y-m-d',
    ];

    public function penyelia(): BelongsTo
    {
        return $this->belongsTo(MasterPenyelia::class, 'penyelia_id');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(PengendalianMutuChecklist::class, 'pengendalian_mutu_id');
    }
}
