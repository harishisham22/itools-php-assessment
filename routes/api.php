<?php

use App\Http\Controllers\BuildingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TariffController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('reports', [ReportController::class, 'summary'])->name('api.reports.index');
Route::get('reports/by-month', [ReportController::class, 'byMonth'])->name('api.reports.by-month');
Route::get('reports/by-building-type', [ReportController::class, 'byBuildingType'])->name('api.reports.by-building-type');
Route::get('reports/by-state', [ReportController::class, 'byState'])->name('api.reports.by-state');

Route::resource('buildings', BuildingController::class)->names('api.buildings');
Route::resource('tariffs', TariffController::class)->names('api.tariffs');
