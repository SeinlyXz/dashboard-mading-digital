<?php

use App\Http\Controllers\GetMediaAction;
use Illuminate\Support\Facades\Route;

Route::get('media', GetMediaAction::class)
    ->name('media.get')
    ->withoutMiddleware(['throttle'])
    ->defaults('limit', 10)
    ->defaults('page', 1);
