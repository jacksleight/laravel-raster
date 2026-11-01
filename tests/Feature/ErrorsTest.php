<?php

use JackSleight\LaravelRaster\Raster;

test('automatic mode views must implement raster', function () {
    $this->withoutExceptionHandling();

    expect(fn () => $this->get('/raster/plain'))
        ->toThrow(Exception::class, 'Cannot rasterise [plain], views used in automatic mode must implement raster.');
});

test('manual mode views must not implement raster', function () {
    expect(fn () => Raster::make('probe')->render())
        ->toThrow(Exception::class, 'Cannot rasterise [probe], views used in manual mode must not implement raster.');
});

test('width or basis must be set', function () {
    expect(fn () => Raster::make('plain')->render())
        ->toThrow(Exception::class, 'Cannot rasterise [plain], width or basis must be set.');
});

test('pdf output needs a height', function () {
    expect(fn () => Raster::make('plain')->width(100)->type('pdf')->render())
        ->toThrow(Exception::class, 'Cannot rasterise [plain], height must be set for PDF output.');
});

test('type must be supported', function () {
    expect(fn () => Raster::make('plain')->type('gif'))
        ->toThrow(Exception::class, 'Cannot rasterise [plain], type must be png, jpeg or pdf.');
});

test('unsupported types from the url are rejected before caching', function () {
    $this->withoutExceptionHandling();

    expect(fn () => $this->get('/raster/probe?type=gif&cache=1'))
        ->toThrow(Exception::class, 'Cannot rasterise [probe], type must be png, jpeg or pdf.');
});
