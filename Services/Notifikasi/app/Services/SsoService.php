<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SsoService
{
    public static function getToken()
    {
        $response = Http::post(
            'https://iae-sso.virtualfri.id/api/v1/auth/token',
            [
                'api_key' => env('IAE_API_KEY'),
                'nim' => env('IAE_NIM')
            ]
        );

        if (!$response->successful()) {
            throw new \Exception('Failed to get SSO token');
        }

        return $response->json('token');
    }
}