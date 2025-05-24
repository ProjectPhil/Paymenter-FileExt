<?php
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::post('/filament/upload', App\Http\Controllers\Filament\FileUploadController::class)
        ->name('filament.upload');
}); 