<?php

namespace App\Services\Iae;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class IaeTokenService
{
    public function m2mToken(): string
    {
        return Cache::remember('iae_m2m_token', 3000, function () {
            $base = rtrim(config('services.iae.sso_url'), '/');

            $res = Http::asJson()->timeout(10)
                ->post($base.'/api/v1/auth/token', [
                    'api_key' => config('services.iae.api_key'),
                    'nim'     => config('services.iae.nim'),
                ])->throw()->json();

            return $res['token'] ?? $res['data']['token'] ?? null;
        });
    }
}
