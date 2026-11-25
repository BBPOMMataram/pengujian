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
        'penyelia_verified_at',
        'penyelia_verified_by',
        'penyelia_signature_path',
        'katim_id',
        'katim_nama',
        'katim_nip',
        'katim_verified_at',
        'katim_verified_by',
        'katim_signature_path',
        'penguji_nama',
        'penguji_nip',
        'penguji_signature_path',
    ];

    protected $casts = [
        'jenis_sampel' => 'array',
        'tanggal_pengujian' => 'date:Y-m-d',
        'penyelia_verified_at' => 'datetime',
        'katim_verified_at' => 'datetime',
    ];

    public function penyelia(): BelongsTo
    {
        return $this->belongsTo(MasterPenyelia::class, 'penyelia_id');
    }

    public function katim(): BelongsTo
    {
        return $this->belongsTo(MasterKatim::class, 'katim_id');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(PengendalianMutuChecklist::class, 'pengendalian_mutu_id');
    }
}
