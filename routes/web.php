<?php

use App\Http\Controllers\Tools\ImageToPdfController;
use App\Http\Controllers\Tools\ImageToTextController;
use App\Http\Controllers\Tools\PdfMergeController;
use App\Http\Controllers\Tools\PdfSplitController;
use App\Http\Controllers\Tools\RemoveBackgroundController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Max AI
|--------------------------------------------------------------------------
| Menu utama: Home - Proses Gambar - Proses PDF - Tool Lainnya
*/

Route::view('/', 'home.index')->name('home');

Route::prefix('tools')->name('tools.')->group(function () {

    // ===== Proses Gambar =====
    Route::get('remove-background', [RemoveBackgroundController::class, 'create'])
        ->name('remove-background');
    Route::post('remove-background', [RemoveBackgroundController::class, 'store'])
        ->name('remove-background.process');
    Route::delete('remove-background/{processedImage}', [RemoveBackgroundController::class, 'destroy'])
        ->name('remove-background.destroy');

    Route::get('image-to-pdf', [ImageToPdfController::class, 'create'])
        ->name('image-to-pdf');
    Route::post('image-to-pdf', [ImageToPdfController::class, 'store'])
        ->name('image-to-pdf.process');

    Route::get('image-to-text', [ImageToTextController::class, 'create'])
        ->name('image-to-text');
    Route::post('image-to-text', [ImageToTextController::class, 'store'])
        ->name('image-to-text.process');

    // ===== Proses PDF =====
    Route::get('merge-pdf', [PdfMergeController::class, 'create'])
        ->name('merge-pdf');
    Route::post('merge-pdf', [PdfMergeController::class, 'store'])
        ->name('merge-pdf.process');

    Route::get('split-pdf', [PdfSplitController::class, 'create'])
        ->name('split-pdf');
    Route::post('split-pdf', [PdfSplitController::class, 'store'])
        ->name('split-pdf.process');
});
