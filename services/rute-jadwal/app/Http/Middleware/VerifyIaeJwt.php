<?php

namespace App\Http\Middleware;

use App\Models\SsoUser;
use Closure;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class VerifyIaeJwt
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return $this->unauthorized('Token tidak ada. Sertakan header: Authorization: Bearer <token>.');
        }

        try {
            $jwks = Cache::remember('iae_jwks', 3600, function () {
                $url = rtrim(config('services.iae.sso_url'), '/').'/api/v1/auth/jwks';

                return Http::timeout(10)->get($url)->throw()->json();
            });

            $decoded = JWT::decode($token, JWK::parseKeySet($jwks, 'RS256'));
        } catch (ExpiredException $e) {
            return $this->unauthorized('Token sudah kedaluwarsa, ambil token baru.');
        } catch (\Throwable $e) {
            return $this->unauthorized('Token tidak valid.');
        }

        $claims  = (array) $decoded;
        $subject = $claims['sub'] ?? $claims['email'] ?? null;
        $roles   = $claims['roles'] ?? [];

        // MODUL 1: petakan user ke tabel roles lokal
        $ssoUser = null;
        if ($subject) {
            $ssoUser = SsoUser::updateOrCreate(
                ['sso_subject' => $subject],
                ['roles' => $roles, 'last_login_at' => now()],
            );
        }

        $request->attributes->set('iae_claims', $claims);
        $request->attributes->set('iae_subject', $subject);
        $request->attributes->set('iae_roles', $roles);
        $request->attributes->set('sso_user', $ssoUser);

        return $next($request);
    }

    private function unauthorized(string $message): Response
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'errors'  => null,
        ], 401);
    }
}