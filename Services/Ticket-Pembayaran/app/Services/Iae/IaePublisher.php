<?php

namespace App\Services\Iae;

use Illuminate\Support\Facades\Http;

class IaePublisher
{
    public function __construct(private IaeTokenService $tokens) {}

    /**
     * Publish event JSON ke RabbitMQ pusat (iae.central.exchange).
     * Endpoint mewajibkan event dibungkus di field "message".
     */
    public function publish(array $event): array
    {
        $base  = rtrim(config('services.iae.sso_url'), '/');
        $token = $this->tokens->m2mToken();

        $response = Http::withToken($token)
            ->asJson()
            ->timeout(15)
            ->post($base.'/api/v1/messages/publish', [
                'message'     => $event,
                'routing_key' => $event['event_name'] ?? 'ticket.purchased',
            ]);

        return ['status' => $response->status(), 'body' => $response->json() ?? $response->body()];
    }
}
