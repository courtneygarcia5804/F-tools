<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageConvertController;
use App\Http\Controllers\InvoiceController;

// Main upload page
Route::get('/', [ImageConvertController::class , 'index'])->name('image.index');

// Invoice Generator
Route::get('/invoice', [InvoiceController::class , 'index'])->name('invoice.index');

// Upload endpoint
Route::post('/upload', [ImageConvertController::class , 'upload'])->name('image.upload');

// Preview converted image
Route::get('/preview/{filename}', [ImageConvertController::class , 'preview'])->name('image.preview');

// Download single file
Route::get('/download/{filename}', [ImageConvertController::class , 'download'])->name('image.download');

// Download all as ZIP
Route::post('/download-all', [ImageConvertController::class , 'downloadAll'])->name('image.downloadAll');

// Cleanup session files
Route::post('/cleanup', [ImageConvertController::class , 'cleanup'])->name('image.cleanup');
