<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\View;
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

                    public function asset(string $asset, ?string $buildDirectory = null): string
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
        Date::use(CarbonImmutable::class);

        Password::defaults(function () {
            return Password::min(8)->max(255);
        });

        View::composer([
            'components.layouts.header',
            'attendances.index',
            'attendances.show',
        ], function ($view) {
            $view->with('isAdmin', Auth::user()?->is_admin ?? false);
        });
    }
}
