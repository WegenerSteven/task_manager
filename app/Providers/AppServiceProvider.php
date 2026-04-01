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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Suppress swagger-php v6 strict warnings during doc generation
        if ($this->app->runningInConsole()) {
            set_error_handler(function ($errno, $errstr) {
                if (str_contains($errstr, 'Required @OA\PathItem() not found')) {
                    return true; // suppress this specific warning
                }
                return false; // let everything else through
            }, E_USER_WARNING);
        }
    }
}
