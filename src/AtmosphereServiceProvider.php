<?php

namespace Freesgen\Atmosphere;

use Illuminate\Support\ServiceProvider;

class AtmosphereServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerRoutes();

        $this->loadMigrationsFrom(
            __DIR__.'/../database/migrations'
        );

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations')
        ], 'atmosphere-migrations');
    }

        /**
     * Register the package routes.
     *
     * @return void
     */
    private function registerRoutes()
    {
        $this->loadRoutesFrom(__DIR__ . '/Domains/Admin/routes.php');
    }

}
