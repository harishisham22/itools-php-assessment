<?php

use App\Http\Controllers\BuildingController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::inertia('dashboard', 'Dashboard')->name('dashboard');
Route::resource('buildings', BuildingController::class)->except('show');

require __DIR__ . '/settings.php';
