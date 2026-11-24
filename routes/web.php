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
Route::post('/load-sample-pdf', [PengujianController::class, 'loadSamplePdfSpu'])->name('pengujian.sample.pdf');

// Public Scan Endpoint
Route::get('/scan/{qrToken}', [PengujianController::class, 'scan'])->name('pengujian.scan');
Route::get('/qr/{qrToken}', [PengujianController::class, 'qrImage'])->name('pengujian.qr');

// PDF Generation Routes (Per-Row & Batch & Merged File Groups)
Route::get('/item/{id}/pdf', [PengujianController::class, 'pdf'])->name('pengujian.pdf');
Route::post('/item/{id}/pdf', [PengujianController::class, 'pdf'])->name('pengujian.pdf.post');
Route::get('/item/{id}/pdf-fpdf', [PengujianController::class, 'pdfFpdf'])->name('pengujian.pdf.fpdf');
Route::get('/batch/{batchId}/pdf-all', [PengujianController::class, 'pdfBatch'])->name('pengujian.batch.pdf');
Route::get('/file-group/{fileGroupId}/pdf-merged', [PengujianController::class, 'pdfMergedFileGroup'])->name('pengujian.filegroup.pdf');

// Data Cleanup (Khusus Superadmin)
Route::delete('/batch/{batchId}', [PengujianController::class, 'destroyBatch'])->middleware('sso.auth:superadmin')->name('pengujian.batch.destroy');
Route::post('/batch/{batchId}/delete', [PengujianController::class, 'destroyBatch'])->middleware('sso.auth:superadmin')->name('pengujian.batch.destroy.post');
Route::post('/clear-all', [PengujianController::class, 'clearAll'])->middleware('sso.auth:superadmin')->name('pengujian.clear');


// Diagnostic routes
Route::get('/debug-log', function () {
    $logPath = storage_path('logs/laravel.log');
    if (!file_exists($logPath)) {
        return response('No log file found at ' . $logPath, 200, ['Content-Type' => 'text/plain']);
    }
    $lines = file($logPath);
    $lastLines = array_slice($lines, -150);
    return response(implode('', $lastLines), 200, ['Content-Type' => 'text/plain']);
});

Route::get('/debug-db', function () {
    try {
        $connection = config('database.default');
        if ($connection === 'sqlite') {
            $tables = \Illuminate\Support\Facades\DB::select("SELECT name FROM sqlite_master WHERE type='table'");
            $tableNames = array_column($tables, 'name');
        } else {
            $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
            $tableNames = array_map('current', json_decode(json_encode($tables), true));
        }

        return response()->json([
            'status' => 'DB connected',
            'connection' => $connection,
            'database' => \Illuminate\Support\Facades\DB::connection()->getDatabaseName(),
            'tables' => $tableNames,
            'app_key' => config('app.key') ? 'set' : 'not set',
            'storage_writable' => is_writable(storage_path()),
            'views_writable' => is_writable(storage_path('framework/views')),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
});

Route::get('/run-migrate', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = \Illuminate\Support\Facades\Artisan::output();

        return response()->json([
            'status' => 'success',
            'connection' => config('database.default'),
            'database' => \Illuminate\Support\Facades\DB::connection()->getDatabaseName(),
            'migrate' => $migrateOutput,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'error' => $e->getMessage(),
        ], 500);
    }
});
