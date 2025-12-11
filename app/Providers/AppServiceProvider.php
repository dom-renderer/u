<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        \Illuminate\Pagination\Paginator::useBootstrap();        

        if (env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
        }

        if (!defined('APP_NAME')) {
            define('APP_NAME', 'Teapost');
        }
    }
}
