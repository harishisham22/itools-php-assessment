<?php

use App\Http\Controllers\BuildingController;
use App\Http\Controllers\TariffController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::resource('buildings', BuildingController::class);
Route::resource('tariffs', TariffController::class);