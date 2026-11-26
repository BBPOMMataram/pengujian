<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class Controller
{
    /**
     * Helper pengiriman notifikasi ke portal pusat (Auth-BPOM -> In-App, Web Push, & Telegram)
     */
    protected function sendCentralNotification(array $payload): void
    {
        $authUrl = env('BACKEND_URL_AUTH');
        if (!$authUrl) {
            $isProd = (isset($_SERVER['HTTP_HOST']) && str_contains($_SERVER['HTTP_HOST'], 'bbpommataram.id')) ||
                      (env('APP_URL') && str_contains(env('APP_URL'), 'bbpommataram.id'));
            $authUrl = $isProd ? 'https://auth.bbpommataram.id' : 'http://localhost:8000';
        }

        try {
            $response = Http::timeout(5)
                ->post(rtrim($authUrl, '/') . '/api/notifications', $payload);
            if ($response->successful()) {
                return;
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim notifikasi pusat via HTTP: ' . $e->getMessage());
        }

        // Fallback: Simpan langsung ke database bbpo_auth jika berada di server MySQL yang sama
        try {
            DB::table('bbpo_auth.central_notifications')->insert([
                'target_user_id' => $payload['target_user_id'] ?? null,
                'module' => $payload['module'] ?? 'pengujian',
                'title' => $payload['title'] ?? 'Pemberitahuan',
                'message' => $payload['message'] ?? '',
                'action_url' => $payload['action_url'] ?? null,
                'icon' => $payload['icon'] ?? 'solar:bell-bing-bold',
                'type' => $payload['type'] ?? 'info',
                'target_group_jabatan' => $payload['target_group_jabatan'] ?? null,
                'target_role' => $payload['target_role'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $dbErr) {
            Log::warning('Gagal simpan notifikasi fallback DB: ' . $dbErr->getMessage());
        }
    }
}
