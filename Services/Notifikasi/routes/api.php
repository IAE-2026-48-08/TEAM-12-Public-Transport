<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DelayController;
use App\Services\SsoService;

Route::middleware(['iae.jwt', 'iae.auth'])->prefix('v1')->group(function () {

    Route::get('/delays', [DelayController::class, 'index']);

    Route::post('/delays/notifications', [DelayController::class, 'sendNotification']);

    Route::get('/delays/{id}', [DelayController::class, 'show']);

    Route::post('/delays', [DelayController::class, 'store']);

});

Route::get('/test-sso', function () {
    return [
        'token' => SsoService::getToken()
    ];
});