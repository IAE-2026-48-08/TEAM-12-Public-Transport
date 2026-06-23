<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Notification Delay Service API",
    description: "API untuk Service Notifikasi Delay"
)]
#[OA\Server(
    url: "http://localhost",
    description: "Local API Gateway Server"
)]
#[OA\SecurityScheme(
    securityScheme: "ApiKeyAuth",
    type: "apiKey",
    in: "header",
    name: "X-IAE-KEY",
    description: "Masukkan API Key (NIM: 102022400154)"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "Masukkan Token JWT SSO untuk mengakses endpoint"
)]
class OpenApi
{
}