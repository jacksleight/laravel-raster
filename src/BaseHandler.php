<?php

namespace JackSleight\LaravelRaster;

abstract class BaseHandler
{
    protected Raster $raster;

    public function __construct(Raster $raster)
    {
        $this->raster = $raster;
    }

    abstract public function hasFingerprint(): bool;
}
