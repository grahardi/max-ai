<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Member\MemberFileController;
use App\Http\Controllers\Tools\EncryptionController;
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
| Menu utama: Home - Proses Gambar - Proses PDF - Enkripsi - Tool Lainnya
| Plus: Member Area (auth + file manager)
*/

Route::view('/', 'home.index')->name('home');

// ===== Auth =====
Route::middleware('guest')->group(function () {
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});
Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ===== Member Area =====
Route::prefix('member')->name('member.')->middleware('auth')->group(function () {
    Route::get('/', [MemberFileController::class, 'index'])->name('dashboard');
    Route::post('upload', [MemberFileController::class, 'upload'])->name('upload');
    Route::get('download/{memberFile}', [MemberFileController::class, 'download'])->name('download');
    Route::delete('{memberFile}', [MemberFileController::class, 'destroy'])->name('destroy');
});

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

    // ===== Enkripsi =====
    Route::get('encrypt/bcrypt', [EncryptionController::class, 'bcryptForm'])
        ->name('encrypt.bcrypt');
    Route::post('encrypt/bcrypt', [EncryptionController::class, 'bcryptProcess'])
        ->name('encrypt.bcrypt.process');

    Route::get('encrypt/base64', [EncryptionController::class, 'base64Form'])
        ->name('encrypt.base64');
    Route::post('encrypt/base64', [EncryptionController::class, 'base64Process'])
        ->name('encrypt.base64.process');

    Route::get('encrypt/sha256', [EncryptionController::class, 'sha256Form'])
        ->name('encrypt.sha256');
    Route::post('encrypt/sha256', [EncryptionController::class, 'sha256Process'])
        ->name('encrypt.sha256.process');

    Route::get('encrypt/md5', [EncryptionController::class, 'md5Form'])
        ->name('encrypt.md5');
    Route::post('encrypt/md5', [EncryptionController::class, 'md5Process'])
        ->name('encrypt.md5.process');
});
