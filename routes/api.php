<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\ReservationController;

// Facility Routes
Route::post('/facility', [FacilityController::class, 'store']);
Route::get('/facility/get-facilities', [FacilityController::class, 'index']);
Route::get('/facility/get-facilities-info', [FacilityController::class, 'info']);
Route::get('/facility/get-facility-by-id/{id}', [FacilityController::class, 'show']);

// Reservation Routes
Route::get('/reservation/get-reservations', [ReservationController::class, 'index']);
Route::get('/reservation/get-reservation/{id}', [ReservationController::class, 'show']);
Route::get('/reservation/get-reservation-by-facility/{facilityId}', [ReservationController::class, 'getByFacility']);
Route::post('/reservation/create-reservation', [ReservationController::class, 'store']);
Route::post('/reservation/ongoing-reservation', [ReservationController::class, 'ongoing']);
Route::post('/reservation/cancel-reservation', [ReservationController::class, 'cancel']);
Route::post('/reservation/done-reservation', [ReservationController::class, 'done']);
Route::post('/reservation/move-reservation', [ReservationController::class, 'move']);
