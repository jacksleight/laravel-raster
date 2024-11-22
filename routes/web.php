<?php

use Illuminate\Http\Request;
use JackSleight\LaravelRaster\Raster;

Route::group(['as' => 'laravel-raster.'], function () {
    $route = config('raster.route');
    Route::get($route.'/{name}', function (Request $request) {
        if (config('raster.sign_urls') && ! $request->hasValidSignature()) {
            abort(401);
        }

        return Raster::makeFromRequest($request);
    })->name('render');
});
