<?php

namespace JackSleight\LaravelRaster;

use Closure;
use Illuminate\Support\Str;

class BladeHandler extends BaseHandler
{
    public function hasFingerprint(): bool
    {
        $compiler = app('blade.compiler');
        $string = $compiler->compileString(file_get_contents($this->raster->path()));

        return Str::contains($string, static::uniqueString());
    }

    /**
     * @param  array<mixed>  $args
     * @return array<mixed>
     */
    public function injectParams(...$params): array
    {
        if (! $this->raster->isAutomaticMode()) {
            return [];
        }

        $input = $this->raster->request()->all();
        $merged = collect([...$params, ...$input]);
        collect($params)
            ->keys()
            ->unshift('data')
            ->unique()
            ->each(function ($name) use ($params, $input, &$merged) {
                if ($params[$name] instanceof Closure) {
                    $pass = $name === 'data' ? $input : $merged;
                    $merged[$name] = app()->call($params[$name], $pass['data'] ?? []);
                }
            });

        $merged->each(fn ($value, $name) => $this->raster->{$name}($value));

        return $this->raster->data();
    }

    protected static function uniqueString(): string
    {
        return '__raster_'.hash('sha1', __FILE__).'__';
    }

    public static function compile(string $expression): string
    {
        $uniqueString = static::uniqueString();

        return "<?php
/* {$uniqueString} */
if ((\$raster ?? null) instanceof \JackSleight\LaravelRaster\Raster) {
    \$__raster_data = \$raster->handler()->injectParams({$expression});
    extract(\$__raster_data);
    unset(\$__raster_data);
}
?>";
    }
}
