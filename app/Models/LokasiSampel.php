<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LokasiSampel extends Model
{
    use HasFactory;

    protected $table = 'lokasi_sampel';

    protected $fillable = [
        'kode_lokasi',
        'name',
        'keterangan',
    ];

    /**
     * Cache array master kode_lokasi => name
     */
    protected static ?array $masterCache = null;

    public static function getMasterMap(): array
    {
        if (static::$masterCache === null) {
            try {
                if (Schema::hasTable('lokasi_sampel')) {
                    static::$masterCache = DB::table('lokasi_sampel')
                        ->pluck('name', 'kode_lokasi')
                        ->toArray();
                }
            } catch (\Throwable $e) {
                static::$masterCache = [];
            }

            if (empty(static::$masterCache)) {
                static::$masterCache = [
                    'K' => 'Kimia',
                    'M' => 'Mikro',
                    'R' => 'Retensi',
                    'A' => 'Arsip',
                    'P' => 'Penandaan',
                    'S' => 'Sisa Sampel',
                ];
            } else {
                static::$masterCache += [
                    'K' => 'Kimia',
                    'M' => 'Mikro',
                    'R' => 'Retensi',
                    'A' => 'Arsip',
                    'P' => 'Penandaan',
                    'S' => 'Sisa Sampel',
                ];
            }
        }

        return static::$masterCache;
    }

    /**
     * Decode Catatan containing codes like 'K2,M2,A1 Cluster B/ Compliance...' into official Lokasi Sampel string.
     * Example:
     * - 'K2,M2,A1 Cluster B/...' -> 'Kimia 2 Sampel, Mikro 2 Sampel dan Arsip 1 Sampel'
     * - 'K1 M0 R1' -> 'Kimia 1 Sampel dan Retensi 1 Sampel'
     * - Empty + Narkotika -> 'Kimia'
     * - Empty + Non-narkotika -> '-'
     */
    public static function parseCatatan(?string $catatan, ?string $jenisSampel = null, ?string $komoditi = null): string
    {
        $master = static::getMasterMap();

        $isNarkotika = false;
        if (!empty($jenisSampel) && preg_match('/narkotika|narkoba/i', $jenisSampel)) {
            $isNarkotika = true;
        } elseif (!empty($komoditi) && preg_match('/narkotika|narkoba/i', $komoditi)) {
            $isNarkotika = true;
        }

        // PENTING: Untuk kategori Narkotika, selalu pastikan masuk ke Laboratorium Kimia
        if ($isNarkotika) {
            return $master['K'] ?? 'Kimia';
        }

        $cleanCatatan = trim($catatan ?? '');

        // Abaikan artefak header tabel seperti TKMP / TKMPS
        if (preg_match('/^T\s*K\s*M\s*P\s*S?$/i', $cleanCatatan)) {
            $cleanCatatan = '';
        }

        // 1. Catatan kosong atau strip
        if ($cleanCatatan === '' || $cleanCatatan === '-') {
            return '-';
        }

        // 2. Cari kode lokasi huruf diikuti angka (misal K2, M2, A1, R1, P1, K1 M0 R1)
        // Pola: diawali batas kata / koma / spasi / tanda hubung, huruf K|M|R|A|P|S, titik dua/sama dengan opsional, dan angka
        $pattern = '/(?:^|[\s,;\/\-])([KMRAPS])\s*[:=]?\s*(\d+)(?=[\s,;\/\-]|$)/i';
        if (preg_match_all($pattern, $cleanCatatan, $matches, PREG_SET_ORDER)) {
            $parts = [];
            foreach ($matches as $m) {
                $code = strtoupper($m[1]);
                $count = (int)$m[2];
                if ($count > 0) {
                    $name = $master[$code] ?? $code;
                    $parts[] = "{$name} {$count} Sampel";
                }
            }

            if (!empty($parts)) {
                if (count($parts) === 1) {
                    return $parts[0];
                }
                if (count($parts) === 2) {
                    return $parts[0] . ' dan ' . $parts[1];
                }
                $last = array_pop($parts);
                return implode(', ', $parts) . ' dan ' . $last;
            }

            // Jika semua count bernilai 0 (misal K0 M0 R0)
            return '-';
        }

        // Kembalikan teks catatan apa adanya
        return $cleanCatatan;
    }

    /**
     * Extract count array for SPU T K M P S columns
     */
    public static function extractCounts(?string $catatan, ?string $jenisSampel = null, ?string $komoditi = null, ?int $totalDefault = null): array
    {
        $clean = trim($catatan ?? '');
        $pattern = '/(?:^|[\s,;\/\-])([KMRAPS])\s*[:=]?\s*(\d+)(?=[\s,;\/\-]|$)/i';
        
        $counts = [
            'T' => 0,
            'K' => '-',
            'M' => '-',
            'P' => '-',
            'S' => '0',
            'A' => '-',
            'R' => '-',
        ];

        $found = false;
        if (preg_match_all($pattern, $clean, $matches, PREG_SET_ORDER)) {
            $sum = 0;
            foreach ($matches as $m) {
                $code = strtoupper($m[1]);
                $c = (int)$m[2];
                $counts[$code] = ($c > 0) ? (string)$c : '-';
                $sum += $c;
                $found = true;
            }
            if ($sum > 0) {
                $counts['T'] = $sum;
            }
        }

        if (!$found) {
            $isNarkotika = (!empty($jenisSampel) && preg_match('/narkotika/i', $jenisSampel))
                || (!empty($komoditi) && preg_match('/narkotika/i', $komoditi));
            if ($isNarkotika) {
                $counts['T'] = $totalDefault ?: 1;
                $counts['K'] = '1';
            } else {
                $counts['T'] = $totalDefault ?: 1;
            }
        } elseif ($totalDefault !== null && $totalDefault > 0) {
            $counts['T'] = $totalDefault;
        }

        return $counts;
    }
}
