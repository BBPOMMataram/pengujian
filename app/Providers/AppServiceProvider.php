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
        // Fallback autoloader for Smalot\PdfParser if vendor/smalot is missing on production/shared hosting servers
        spl_autoload_register(function ($class) {
            $prefix = 'Smalot\\PdfParser\\';
            if (str_starts_with($class, $prefix)) {
                $relative = substr($class, strlen($prefix));
                $file = app_path('Support/PdfParser/' . str_replace('\\', '/', $relative) . '.php');
                if (file_exists($file)) {
                    require_once $file;
                }
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
