<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RabbitMqService
{
    public static function publish($queueName, $message)
    {
       
        $url = 'http://guest:guest@localhost:15672/api/exchanges/%2f/amq.default/publish';

        $response = Http::post($url, [
            'routing_key' => $queueName,
            'payload'     => json_encode($message),
            'payload_encoding' => 'string',
            'properties'  => []
        ]);

        if (!$response->successful()) {
            throw new \Exception('Gagal kirim ke RabbitMQ via HTTP: ' . $response->body());
        }

        return true;
    }
}