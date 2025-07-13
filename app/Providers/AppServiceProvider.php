<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Media;
use App\Observers\MediaObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Daftarkan MediaObserver
        Media::observe(MediaObserver::class);
    }
}
