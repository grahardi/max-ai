<?php

use App\Http\Controllers\Tools\RemoveBackgroundController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Max AI
|--------------------------------------------------------------------------
| Landing page berisi katalog "AI Tools". Setiap tool punya prefix /tools/{slug}.
| Tool pertama: Remove Background.
*/

Route::view('/', 'home.index')->name('home');

Route::prefix('tools')->name('tools.')->group(function () {
    Route::get('remove-background', [RemoveBackgroundController::class, 'create'])
        ->name('remove-background');

    Route::post('remove-background', [RemoveBackgroundController::class, 'store'])
        ->name('remove-background.process');

    Route::delete('remove-background/{processedImage}', [RemoveBackgroundController::class, 'destroy'])
        ->name('remove-background.destroy');
});
