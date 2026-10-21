<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PengujianItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'filename',
        'row_number',
        'code',
        'name',
        'qr_token',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($item) {
            if (empty($item->qr_token)) {
                $item->qr_token = Str::random(32);
            }
        });
    }

    /**
     * Get the full URL that the QR Code points to.
     */
    public function getQrTargetUrlAttribute(): string
    {
        return route('pengujian.scan', $this->qr_token);
    }
}
