<?php

namespace App\Http\Middleware;

use App\Models\User;
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
                $url = rtrim(config('services.iae.sso_url', 'https://iae-sso.virtualfri.id'), '/').'/api/v1/auth/jwks';

                return Http::timeout(10)->get($url)->throw()->json();
            });

            $decoded = JWT::decode($token, JWK::parseKeySet($jwks, 'RS256'));
        } catch (ExpiredException $e) {
            return $this->unauthorized('Token sudah kedaluwarsa, ambil token baru.');
        } catch (\Throwable $e) {
            return $this->unauthorized('Token tidak valid.');
        }

        $claims  = (array) $decoded;
        $email   = $claims['email'] ?? ($claims['sub'] ?? null);
        $name    = $claims['name'] ?? ($claims['username'] ?? 'SSO User');
        $role    = $claims['role'] ?? ($claims['roles'][0] ?? 'warga');

        // SSO User Mapping ke tabel users lokal
        if ($email) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => bcrypt('SSO_DUMMY_PASSWORD_' . uniqid()),
                    'role' => $role
                ]
            );
        }

        $request->attributes->set('iae_claims', $claims);
        $request->attributes->set('iae_subject', $email);
        $request->attributes->set('iae_role', $role);

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
