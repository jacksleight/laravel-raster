<?php

namespace JackSleight\LaravelRaster;

abstract class BaseHandler
{
    protected Raster $raster;

    public function __construct(Raster $raster)
    {
        $this->raster = $raster;
    }

    /**
     * @param  array<mixed>  $data
     */
    abstract public function renderHtml(): string;

    abstract public function hasFingerprint(): bool;

    abstract public function injectParams(array $params): array;
}
