<?php

use App\Http\Controllers\Api\ApiPengujianController;
use App\Http\Controllers\Api\ApiPengendalianMutuController;
use Illuminate\Support\Facades\Route;

Route::prefix('pengujian')->middleware('sso.auth')->group(function () {
    Route::get('/', [ApiPengujianController::class, 'index'])->name('api.pengujian.index');
    Route::post('/upload', [ApiPengujianController::class, 'upload'])->name('api.pengujian.upload');
    Route::post('/load-sample-2col', [ApiPengujianController::class, 'loadSampleTwoColumn'])->name('api.pengujian.sample.2col');
    Route::post('/load-sample-full', [ApiPengujianController::class, 'loadSampleData'])->name('api.pengujian.sample.full');
    Route::post('/load-sample-pdf', [ApiPengujianController::class, 'loadSamplePdfSpu'])->name('api.pengujian.sample.pdf');
    Route::delete('/batch/{batchId}', [ApiPengujianController::class, 'destroyBatch'])
        ->middleware('sso.auth:superadmin')
        ->name('api.pengujian.batch.destroy');
    Route::post('/batch/{batchId}/delete', [ApiPengujianController::class, 'destroyBatch'])
        ->middleware('sso.auth:superadmin')
        ->name('api.pengujian.batch.destroy.post');
    Route::post('/clear-all', [ApiPengujianController::class, 'clearAll'])
        ->middleware('sso.auth:superadmin')
        ->name('api.pengujian.clear');

    // Pengendalian Mutu Mikrobiologi
    Route::get('/pengendalian-mutu/penyelia', [ApiPengendalianMutuController::class, 'getPenyeliaList'])->name('api.pengendalian-mutu.penyelia');
    Route::get('/pengendalian-mutu/master-penyelia', [ApiPengendalianMutuController::class, 'getAllMasterPenyelia'])->name('api.pengendalian-mutu.master-penyelia.index');
    Route::post('/pengendalian-mutu/master-penyelia', [ApiPengendalianMutuController::class, 'storeMasterPenyelia'])->name('api.pengendalian-mutu.master-penyelia.store');
    Route::put('/pengendalian-mutu/master-penyelia/{id}', [ApiPengendalianMutuController::class, 'updateMasterPenyelia'])->name('api.pengendalian-mutu.master-penyelia.update');
    Route::delete('/pengendalian-mutu/master-penyelia/{id}', [ApiPengendalianMutuController::class, 'destroyMasterPenyelia'])->name('api.pengendalian-mutu.master-penyelia.destroy');

    Route::get('/pengendalian-mutu', [ApiPengendalianMutuController::class, 'index'])->name('api.pengendalian-mutu.index');
    Route::post('/pengendalian-mutu', [ApiPengendalianMutuController::class, 'store'])->name('api.pengendalian-mutu.store');
    Route::get('/pengendalian-mutu/{id}', [ApiPengendalianMutuController::class, 'show'])->name('api.pengendalian-mutu.show');
    Route::delete('/pengendalian-mutu/{id}', [ApiPengendalianMutuController::class, 'destroy'])->name('api.pengendalian-mutu.destroy');
});
