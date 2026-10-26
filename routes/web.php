<?php

use App\Http\Controllers\BuildingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TariffController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');
Route::get('dashboard', DashboardController::class)->name('dashboard');

Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

Route::resource('buildings', BuildingController::class)->except('show');
Route::resource('tariffs', TariffController::class)->except('show');

require __DIR__.'/settings.php';
