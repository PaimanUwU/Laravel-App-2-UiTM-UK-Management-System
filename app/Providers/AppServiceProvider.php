<?php

namespace App\Providers;

use App\Models\Patient;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

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
        Route::bind('patient', function ($value) {
            return Patient::where('patient_ID', $value)->firstOrFail();
        });
    }
}
