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
        $this->app->singleton(
            \App\Contracts\CaptchaVerifier::class,
            fn() => new \App\Services\GoogleRecaptchaVerifier(
                (string) config('services.recaptcha.secret'),
                (float) config('services.recaptcha.min_score'),
            )
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
