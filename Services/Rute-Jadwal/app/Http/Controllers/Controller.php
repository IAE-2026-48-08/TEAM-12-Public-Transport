<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    description: "Dokumentasi API untuk Layanan Rute & Jadwal (Tugas 2 IAE)",
    title: "Rute & Jadwal Service API"
)]
#[OA\Server(
    url: "http://localhost",
    description: "Local Docker Server"
)]
#[OA\SecurityScheme(
    securityScheme: "ApiKeyAuth",
    type: "apiKey",
    in: "header",
    name: "X-IAE-KEY",
    description: "Masukkan NIM Anda (contoh: 102022430022) sebagai API Key"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "Token JWT dari SSO IAE. Ambil lewat POST /api/v1/auth/token."
)]
abstract class Controller
{
    
}