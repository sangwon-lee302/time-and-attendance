<?php

namespace App\Providers;

use Illuminate\Foundation\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('testing')) {
            $this->app->singleton(Vite::class, function () {
                return new class
                {
                    public function __invoke($entrypoints, $buildDirectory = 'build')
                    {
                        return '';
                    }

                    public function __toString()
                    {
                        return '';
                    }
                };
            });
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Password::defaults(function () {
            return Password::min(8)->max(255);
        });
    }
}
