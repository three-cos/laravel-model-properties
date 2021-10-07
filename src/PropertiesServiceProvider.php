<?php

namespace Wardenyarn\Properties;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Wardenyarn\Properties\Console\FillProperties;

class PropertiesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('model-properties.php'),
            ], 'config');

            $this->publishMigrations();

            $this->commands([
                FillProperties::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'model-properties');
    }

    protected function publishMigrations()
    {
        if (! class_exists('CreatePropertiesTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_properties_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_properties_table.php'),
            ], 'migrations');
        }
    }
}
