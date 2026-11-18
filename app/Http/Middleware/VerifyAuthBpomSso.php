<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifikasi sesi SSO Auth-BPOM (cookie HttpOnly `access_token` atau Bearer token).
 * Mendukung verifikasi HS256 lokal (jika JWT_SECRET tersedia di .env)
 * maupun verifikasi otomatis ke Auth-BPOM (/api/me) tanpa konfigurasi .env tambahan.
 */
class VerifyAuthBpomSso
{
    public function handle(Request $request, Closure $next, ?string $requiredRole = null): Response
    {
        $token = $request->cookie('access_token') ?: $request->bearerToken();

        if (!$token) {
            return $this->unauthorizedResponse($request, 'Sesi login SSO BPOM tidak ditemukan. Silakan login terlebih dahulu.');
        }

        $userClaims = $this->verifyToken($token);
        if (!$userClaims) {
            return $this->unauthorizedResponse($request, 'Sesi login SSO BPOM tidak valid atau telah berakhir. Silakan login ulang.');
        }

        $roleLevel = $this->extractRoleLevel($userClaims);

        if ($requiredRole !== null) {
            $allowedRoles = array_map(
                fn ($r) => strtolower(trim($r)),
                explode('|', $requiredRole)
            );
            if (!in_array($roleLevel, $allowedRoles, true)) {
                return $this->forbiddenResponse($request, 'Akses ditolak: fitur ini hanya dapat diakses oleh Superadmin.');
            }
        }

        $request->attributes->set('sso_user', $userClaims);
        $request->attributes->set('sso_role', $roleLevel);

        return $next($request);
    }

    protected function verifyToken(string $token): ?array
    {
        // 1. Verifikasi JWT HS256 langsung jika JWT_SECRET dikonfigurasi
        $jwtSecret = trim((string) env('JWT_SECRET', ''));
        if ($jwtSecret !== '') {
            $decoded = $this->decodeHs256Jwt($token, $jwtSecret);
            if ($decoded !== null) {
                return $decoded;
            }
        }

        // 2. Fallback otomatis: verifikasi ke Auth-BPOM /api/me (dengan cache singkat 60 detik)
        $cacheKey = 'sso_bpom_user_' . hash('sha256', $token);
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $authUrl = trim((string) env('BACKEND_URL_AUTH', ''));
        if ($authUrl === '') {
            $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
            $appUrl = (string) env('APP_URL', '');
            $isProd = str_contains($host, 'bbpommataram.id') || str_contains($appUrl, 'bbpommataram.id');
            $authUrl = $isProd ? 'https://auth.bbpommataram.id' : 'http://localhost:8000';
        }

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Cookie' => 'access_token=' . $token,
                ])
                ->get(rtrim($authUrl, '/') . '/api/me');

            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data) && !empty($data['id'])) {
                    Cache::put($cacheKey, $data, now()->addSeconds(60));
                    return $data;
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('VerifyAuthBpomSso: gagal verifikasi ke Auth-BPOM: ' . $e->getMessage());
        }

        return null;
    }

    protected function decodeHs256Jwt(string $token, string $secret): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$headerB64, $payloadB64, $sigB64] = $parts;
        $header = json_decode($this->base64UrlDecode($headerB64), true);
        if (!is_array($header) || ($header['alg'] ?? '') !== 'HS256') {
            return null;
        }

        $expectedSig = hash_hmac('sha256', $headerB64 . '.' . $payloadB64, $secret, true);
        $actualSig = $this->base64UrlDecode($sigB64);
        if (!hash_equals($expectedSig, $actualSig)) {
            return null;
        }

        $payload = json_decode($this->base64UrlDecode($payloadB64), true);
        if (!is_array($payload)) {
            return null;
        }

        if (isset($payload['exp']) && time() >= (int) $payload['exp']) {
            return null;
        }

        return $payload;
    }

    protected function extractRoleLevel(array $claims): string
    {
        if (isset($claims['role'])) {
            if (is_string($claims['role'])) {
                return strtolower(trim($claims['role']));
            }
            if (is_array($claims['role']) && isset($claims['role']['level'])) {
                return strtolower(trim((string) $claims['role']['level']));
            }
        }

        return '';
    }

    protected function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/')) ?: '';
    }

    protected function unauthorizedResponse(Request $request, string $message): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 401);
        }

        return abort(401, $message);
    }

    protected function forbiddenResponse(Request $request, string $message): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 403);
        }

        return abort(403, $message);
    }
}
