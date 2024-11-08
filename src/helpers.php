<?php

use JackSleight\LaravelRaster\Raster;

if (! function_exists('raster')) {
    /**
     * @param  array<mixed>  $data
     */
    function raster(string $name, array $data = []): Raster
    {
        return new Raster($name, $data);
    }
}
