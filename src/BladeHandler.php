<?php

namespace JackSleight\LaravelRaster;

use Closure;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\View\ComponentAttributeBag;
use Illuminate\View\ComponentSlot;

class BladeHandler extends BaseHandler
{
    /**
     * @param  array<mixed>  $data
     */
    public function renderHtml(): string
    {
        $layout = config('raster.layout');

        View::share('raster', $this->raster);
        $html = $this->renderView($layout, [], $this->renderView($this->raster->name(), $this->raster->data()));
        View::share('raster', null);

        return $html;
    }

    /**
     * @param  array<mixed>  $data
     */
    protected function renderView(string $name, array $data, ?string $slot = null): string
    {
        if (Str::before($name, '.') === 'components') {
            return Blade::render(<<<'HTML'
                <x-dynamic-component :component="$name" :attributes="$data">
                    {{ $slot }}
                </x-dynamic-component>
            HTML, [
                'name' => Str::after($name, 'components.'),
                'data' => new ComponentAttributeBag($data),
                'slot' => new ComponentSlot($slot ?? ''),
            ]);
        }

        if ($slot) {
            return Blade::render(<<<'HTML'
                @extends($name, $data)
                @section('slot', $slot)
            HTML, [
                'name' => $name,
                'data' => $data,
            ]);
        }

        return view($name, $data)->render();
    }

    public function hasFingerprint(): bool
    {
        $compiler = app('blade.compiler');
        $string = $compiler->compileString(file_get_contents($this->raster->path()));

        return Str::contains($string, static::uniqueString());
    }

    protected static function uniqueString(): string
    {
        return '__raster_'.hash('sha1', __FILE__).'__';
    }

    /**
     * @param  array<mixed>  $args
     * @return array<mixed>
     */
    public function resolveData(mixed $data, array $input): array
    {
        if ($data instanceof Closure) {
            $data = app()->call($data, $input);
        }

        return $data;
    }

    public static function compile(string $expression): string
    {
        $uniqueString = static::uniqueString();

        return "<?php
/* {$uniqueString} */
if ((\$raster ?? null) instanceof \JackSleight\LaravelRaster\Raster) {
    \$__raster_data = \$raster->inject({$expression});
    extract(\$__raster_data);
    unset(\$__raster_data);
}
?>";
    }
}
