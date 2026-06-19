<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\ScheduleController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::middleware(['iae.jwt'])->prefix('v1')->group(function () {
    Route::get('/schedules', [ScheduleController::class, 'index']); // [cite: 56]
    Route::post('/schedules', [ScheduleController::class, 'store']); // [cite: 58]
    Route::get('/schedules/{id}', [ScheduleController::class, 'show']); // [cite: 57]
});