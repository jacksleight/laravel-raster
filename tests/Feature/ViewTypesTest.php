<?php

use Illuminate\Support\Facades\View;
use JackSleight\LaravelRaster\BladeHandler;
use JackSleight\LaravelRaster\Raster;

class OtherRaster extends Raster
{
    protected static $extensions = [];
}

beforeEach(function () {
    View::addExtension('antlers.html', 'file');
});

test('antlers views point at statamic raster', function () {
    expect(fn () => Raster::make('antlers-view'))
        ->toThrow(Exception::class, 'Statamic Raster');
});

test('handlers registered on a subclass do not apply to raster', function () {
    OtherRaster::extension('antlers.html', BladeHandler::class);

    expect(fn () => Raster::make('antlers-view'))
        ->toThrow(Exception::class, 'Statamic Raster');
    expect(OtherRaster::make('antlers-view')->handler())
        ->toBeInstanceOf(BladeHandler::class);
});
