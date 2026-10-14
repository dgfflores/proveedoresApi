<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\BarcodeGeneratorInterface;
use App\Services\RealBarcodeGenerator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BarcodeGeneratorInterface::class, RealBarcodeGenerator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
