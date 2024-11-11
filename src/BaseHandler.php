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

    abstract public function resolveData(mixed $resolver, array $data): array;
}
