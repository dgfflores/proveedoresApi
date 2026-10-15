<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
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
