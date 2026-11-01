<?php

use JackSleight\LaravelRaster\Raster;

test('unset options read as null', function () {
    $raster = Raster::make('plain');

    expect($raster->width())->toBeNull()
        ->and($raster->height())->toBeNull()
        ->and($raster->basis())->toBeNull()
        ->and($raster->file())->toBeNull()
        ->and($raster->request())->toBeNull();
});

test('null options fall back to their defaults', function () {
    $raster = Raster::make('plain')
        ->cacheId(null)
        ->file(null);

    expect($raster->cacheId())->toBe('_')
        ->and($raster->file())->toBeNull();
});

test('scale below one throws', function () {
    expect(fn () => Raster::make('plain')->scale(0))
        ->toThrow(Exception::class, 'Cannot rasterise [plain], scale must be a whole number of 1 or more');
});

test('scale of one or more is accepted', function () {
    expect(Raster::make('plain')->scale(2)->scale())->toBe(2);
});
