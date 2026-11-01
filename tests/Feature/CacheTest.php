<?php

use Illuminate\Support\Facades\Storage;
use JackSleight\LaravelRaster\Cache;

test('flush clears the cache with the default config', function () {
    $cache = app(Cache::class);
    $params = ['type' => 'png'];
    $cache->put('probe', '_', $params, 'image');

    $cache->flush();

    expect($cache->get('probe', '_', $params))->toBeNull();
});

test('flush leaves the rest of a shared disk alone', function () {
    Storage::fake('shared');
    config(['raster.cache.disk' => 'shared']);
    app()->forgetInstance(Cache::class);
    Storage::disk('shared')->put('other/file.txt', 'keep');
    $cache = app(Cache::class);
    $params = ['type' => 'png'];
    $cache->put('probe', '_', $params, 'image');

    $cache->flush();

    expect($cache->get('probe', '_', $params))->toBeNull();
    expect(Storage::disk('shared')->exists('other/file.txt'))->toBeTrue();
});
