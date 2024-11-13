<?php

namespace JackSleight\LaravelRaster;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/raster.php' => config_path('raster.php'),
        ], 'raster-config');

        $this->mergeConfigFrom(
            __DIR__.'/../config/raster.php', 'raster'
        );

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        Blade::directive('raster', function ($expression) {
            return BladeHandler::compile($expression);
        });
    }
}
