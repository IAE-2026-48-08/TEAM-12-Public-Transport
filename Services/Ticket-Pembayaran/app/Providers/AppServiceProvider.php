<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Tickets Service API Documentation",
 *      description="L5 Swagger OpenApi API Documentation for Tickets Microservice",
 *      @OA\Contact(
 *          email="bayu@example.com"
 *      )
 * )
 *
 * @OA\Server(
 *      url="http://localhost:8000/api",
 *      description="Local Tickets Service API Server"
 * )
 *
 * @OA\SecurityScheme(
 *      securityScheme="ApiKeyAuth",
 *      type="apiKey",
 *      in="header",
 *      name="X-IAE-KEY",
 *      description="Enter NIM Key to access the endpoints"
 * )
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
