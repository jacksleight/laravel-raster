<?php

namespace Tests;

use JackSleight\LaravelRaster\ServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [ServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('view.paths', [__DIR__.'/views']);
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
    }
}
