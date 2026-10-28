<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Extensions\SecureDatabaseSessionHandler;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\ConnectionInterface;

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
        if (config('app.env') === 'production' || str_contains(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Session::extend('secure_db', function ($app) {
            $table = $app['config']['session.table'];
            $lifetime = $app['config']['session.lifetime'];
            $connection = $app['db']->connection($app['config']['session.connection']);

            return new SecureDatabaseSessionHandler($connection, $table, $lifetime, $app);
        });

        // Session::extend('database', function ($app) {
        //     $table = $app['config']['session.table'];
        //     $lifetime = $app['config']['session.lifetime'];
        //     $connection = $app['db']->connection($app['config']['session.connection']);

        //     return new SecureDatabaseSessionHandler($connection, $table, $lifetime, $app);
        // });
    }
}
