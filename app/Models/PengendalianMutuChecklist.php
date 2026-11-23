<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengendalianMutuChecklist extends Model
{
    use HasFactory;

    protected $table = 'pengendalian_mutu_checklists';

    protected $fillable = [
        'pengendalian_mutu_id',
        'section_title',
        'item_key',
        'item_name',
        'criteria',
        'status',
        'note',
    ];

    public function pengendalianMutu(): BelongsTo
    {
        return $this->belongsTo(PengendalianMutu::class, 'pengendalian_mutu_id');
    }
}
