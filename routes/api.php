<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\ReservationController;

// Resource Routes
Route::post('/resource', [ResourceController::class, 'store']);
Route::get('/resource/get-resources', [ResourceController::class, 'index']);
Route::get('/resource/get-resources-info', [ResourceController::class, 'info']);
Route::get('/resource/get-resrouce-by-id/{id}', [ResourceController::class, 'show']); // Typo as per spec

// Reservation Routes
Route::get('/reservation/get-reservations', [ReservationController::class, 'index']);
Route::get('/reservation/get-reservation/{id}', [ReservationController::class, 'show']);
Route::get('/reservation/get-reservation-by-resource/{resourceId}', [ReservationController::class, 'getByResource']);
Route::post('/reservation/create-reservation', [ReservationController::class, 'store']);
Route::post('/reservation/ongoing-reservation', [ReservationController::class, 'ongoing']);
Route::post('/reservation/cancel-reservation', [ReservationController::class, 'cancel']);
Route::post('/reservation/done-reservation', [ReservationController::class, 'done']);
Route::post('/reservation/move-reservation', [ReservationController::class, 'move']);
