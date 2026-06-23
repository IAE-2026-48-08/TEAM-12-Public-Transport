<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;

Route::middleware(['iae.jwt', 'iae.auth'])->prefix('v1')->group(function () {
    Route::get('/tickets', [TicketController::class, 'index']);       // Collection
    Route::get('/tickets/{id}', [TicketController::class, 'show']);   // Resource
    Route::post('/tickets', [TicketController::class, 'store']);      // Action
});
