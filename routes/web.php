<?php

use App\Http\Controllers\PengujianController;
use Illuminate\Support\Facades\Route;

// Main Upload & Table View
Route::get('/', [PengujianController::class, 'index'])->name('pengujian.index');
Route::post('/upload', [PengujianController::class, 'upload'])->name('pengujian.upload');

// Templates (2-Column & Multi-Column)
Route::get('/download-template', [PengujianController::class, 'downloadTemplateTwoColumn'])->name('pengujian.template');
Route::get('/download-template-2col', [PengujianController::class, 'downloadTemplateTwoColumn'])->name('pengujian.template.2col');
Route::get('/download-template-full', [PengujianController::class, 'downloadTemplateFull'])->name('pengujian.template.full');

// Sample Data Loaders
Route::post('/load-sample', [PengujianController::class, 'loadSampleTwoColumn'])->name('pengujian.sample');
Route::post('/load-sample-2col', [PengujianController::class, 'loadSampleTwoColumn'])->name('pengujian.sample.2col');
Route::post('/load-sample-full', [PengujianController::class, 'loadSampleData'])->name('pengujian.sample.full');

// Public Scan Endpoint
Route::get('/scan/{qrToken}', [PengujianController::class, 'scan'])->name('pengujian.scan');
Route::get('/qr/{qrToken}', [PengujianController::class, 'qrImage'])->name('pengujian.qr');

// PDF Generation Routes (Per-Row & Batch & Merged File Groups)
Route::get('/item/{id}/pdf', [PengujianController::class, 'pdf'])->name('pengujian.pdf');
Route::get('/item/{id}/pdf-fpdf', [PengujianController::class, 'pdfFpdf'])->name('pengujian.pdf.fpdf');
Route::get('/batch/{batchId}/pdf-all', [PengujianController::class, 'pdfBatch'])->name('pengujian.batch.pdf');
Route::get('/file-group/{fileGroupId}/pdf-merged', [PengujianController::class, 'pdfMergedFileGroup'])->name('pengujian.filegroup.pdf');

// Data Cleanup
Route::delete('/batch/{batchId}', [PengujianController::class, 'destroyBatch'])->name('pengujian.batch.destroy');
Route::post('/batch/{batchId}/delete', [PengujianController::class, 'destroyBatch'])->name('pengujian.batch.destroy.post');
Route::post('/clear-all', [PengujianController::class, 'clearAll'])->name('pengujian.clear');
