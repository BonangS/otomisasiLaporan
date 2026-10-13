<?php

use App\Http\Controllers\ReportImageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ReportImageController::class, 'create'])->name('reports.create');
Route::post('/reports/convert', [ReportImageController::class, 'store'])->name('reports.convert');
