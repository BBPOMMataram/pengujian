<?php

use App\Http\Controllers\PengujianController;
use Illuminate\Support\Facades\Route;

// Main Upload & Table View
Route::get('/', [PengujianController::class, 'index'])->name('pengujian.index');
Route::post('/upload', [PengujianController::class, 'upload'])->name('pengujian.upload');

// Template & Sample Data
Route::get('/download-template', [PengujianController::class, 'downloadTemplate'])->name('pengujian.template');
Route::post('/load-sample', [PengujianController::class, 'loadSampleData'])->name('pengujian.sample');

// Public Scan Endpoint (Outputs PDF when scanned with smartphone camera)
Route::get('/scan/{qrToken}', [PengujianController::class, 'scan'])->name('pengujian.scan');

// Direct PDF Generation Routes
Route::get('/item/{id}/pdf', [PengujianController::class, 'pdf'])->name('pengujian.pdf');
Route::get('/item/{id}/pdf-fpdf', [PengujianController::class, 'pdfFpdf'])->name('pengujian.pdf.fpdf');

// Data Cleanup
Route::delete('/batch/{batchId}', [PengujianController::class, 'destroyBatch'])->name('pengujian.batch.destroy');
Route::post('/clear-all', [PengujianController::class, 'clearAll'])->name('pengujian.clear');
