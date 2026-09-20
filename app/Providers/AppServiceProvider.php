<?php
namespace App\Providers;

use App\Models\Perumahaan;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }

        // Superadmin bypass all gates
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('Superadmin') ? true : null;
        });

        // Kirim semua perumahaan ke header
        View::composer('layouts.app', function ($view) {
            $view->with('perumahaan', Perumahaan::all());
        });
    }
}
