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
        'file_group_id',
        'filename',
        'sheet_name',
        'row_number',
        'qr_token',
        'qr_content',
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
     * Get dynamic sample title/name from data.
     */
    public function getDisplayTitleAttribute(): string
    {
        if (is_array($this->data)) {
            foreach ($this->data as $k => $v) {
                $key = strtolower((string)$k);
                if (str_contains($key, 'nama') || str_contains($key, 'sampel') || str_contains($key, 'produk') || str_contains($key, 'item')) {
                    if (!empty($v) && !is_array($v)) {
                        return (string)$v;
                    }
                }
            }
            // fallback to first non-empty text value that is not QR
            foreach ($this->data as $k => $v) {
                $key = strtolower((string)$k);
                if (!str_contains($key, 'qr') && !empty($v) && !is_array($v)) {
                    return (string)$v;
                }
            }
        }
        return 'Baris #' . $this->row_number;
    }

    /**
     * Get sample code dynamically from data or fallback.
     */
    public function getCodeAttribute(): string
    {
        if (is_array($this->data)) {
            foreach ($this->data as $k => $v) {
                $key = strtolower((string)$k);
                if (str_contains($key, 'kode') || str_contains($key, 'code') || str_contains($key, 'no') || str_contains($key, 'id')) {
                    if (!empty($v) && !is_array($v)) {
                        return (string)$v;
                    }
                }
            }
        }
        return 'SMP-' . str_pad((string)$this->row_number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Alias for display_title.
     */
    public function getNameAttribute(): string
    {
        return $this->display_title;
    }

    /**
     * Get the full URL that the QR Code points to.
     */
    public function getQrTargetUrlAttribute(): string
    {
        return route('pengujian.scan', $this->qr_token);
    }
}
