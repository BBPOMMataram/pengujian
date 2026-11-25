<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterKatim extends Model
{
    use HasFactory;

    protected $table = 'master_katims';

    protected $fillable = [
        'auth_user_id',
        'nama',
        'nip',
        'jabatan',
        'is_active',
    ];

    protected $casts = [
        'auth_user_id' => 'string',
        'is_active' => 'boolean',
    ];

    public function pengendalianMutus(): HasMany
    {
        return $this->hasMany(PengendalianMutu::class, 'katim_id');
    }
}