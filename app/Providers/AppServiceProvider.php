<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Activation;
use App\Observers\ActivationObserver;

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
        Activation::observe(ActivationObserver::class);
    }
}
