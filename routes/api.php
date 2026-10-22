<?php

use App\Http\Controllers\Api\ApiPengujianController;
use Illuminate\Support\Facades\Route;

Route::prefix('pengujian')->group(function () {
    Route::get('/', [ApiPengujianController::class, 'index'])->name('api.pengujian.index');
    Route::post('/upload', [ApiPengujianController::class, 'upload'])->name('api.pengujian.upload');
    Route::post('/load-sample-2col', [ApiPengujianController::class, 'loadSampleTwoColumn'])->name('api.pengujian.sample.2col');
    Route::post('/load-sample-full', [ApiPengujianController::class, 'loadSampleData'])->name('api.pengujian.sample.full');
    Route::post('/load-sample-pdf', [ApiPengujianController::class, 'loadSamplePdfSpu'])->name('api.pengujian.sample.pdf');
    Route::delete('/batch/{batchId}', [ApiPengujianController::class, 'destroyBatch'])->name('api.pengujian.batch.destroy');
    Route::post('/batch/{batchId}/delete', [ApiPengujianController::class, 'destroyBatch'])->name('api.pengujian.batch.destroy.post');
    Route::post('/clear-all', [ApiPengujianController::class, 'clearAll'])->name('api.pengujian.clear');
});
