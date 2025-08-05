<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SlideshowController;


// Routes untuk slideshow dengan keamanan tambahan
Route::middleware(['slideshow.security'])->group(function () {
    Route::get('/', function () {
        return view('slideshow.optimized');
    })->name('slideshow.optimized');
    Route::get('/slideshow/media', [SlideshowController::class, 'getMediaWithCache'])->name('slideshow.media');
});
