<?php

namespace JackSleight\LaravelRaster;

use Closure;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\View\ComponentAttributeBag;
use Illuminate\View\ComponentSlot;
use Spatie\Browsershot\Browsershot;
use Stringable;

class Raster implements Responsable, Stringable
{
    protected string $name;

    /**
     * @var array<mixed>
     */
    protected array $data = [];

    protected ?Request $request;

    protected ?int $width;

    protected ?int $basis;

    protected int $scale = 1;

    protected string $type = 'png';

    protected bool $preview = false;

    protected bool|int $cache = false;

    protected static Closure $browsershot;

    /**
     * @param  array<mixed>  $data
     */
    public function __construct(string $name, array $data = [], ?Request $request = null)
    {
        $this->name = $name;
        $this->data = $data;
        $this->request = $request;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @param  array<mixed>|null  $data
     * @return static|array<mixed>
     */
    public function data(?array $data = null): static|array
    {
        if (func_num_args() > 0) {
            $this->data = $data ?? [];

            return $this;
        }

        return $this->data;
    }

    public function width(?int $width = null): static|int|null
    {
        if (func_num_args() > 0) {
            $this->width = $width;

            return $this;
        }

        return $this->width;
    }

    public function basis(?int $basis = null): static|int|null
    {
        if (func_num_args() > 0) {
            $this->basis = $basis;

            return $this;
        }

        return $this->basis;
    }

    public function scale(?int $scale = null): static|int
    {
        if (func_num_args() > 0) {
            $this->scale = $scale ?? 1;

            return $this;
        }

        return $this->scale;
    }

    public function type(?string $type = null): static|string
    {
        if (func_num_args() > 0) {
            $this->type = $type ?? 'png';

            return $this;
        }

        return $this->type;
    }

    public function preview(?bool $preview = null): static|bool
    {
        if (func_num_args() > 0) {
            $this->preview = $preview ?? false;

            return $this;
        }

        return $this->preview;
    }

    public function cache(bool|int|null $cache = null): static|bool|int|null
    {
        if (func_num_args() > 0) {
            $this->cache = $cache ?? false;

            return $this;
        }

        return $this->cache;
    }

    public function render(): string
    {
        if ($this->isAutomaticMode() && ! $this->hasFingerprint()) {
            throw new \Exception('View must implement raster directive');
        } elseif ($this->isManualMode() && $this->hasFingerprint()) {
            throw new \Exception('View must not implement raster directive');
        }

        $html = $this->renderHtml();

        if (! isset($this->width)) {
            throw new \Exception('Width must be set');
        }

        if ($this->preview) {
            return $this->renderPreview($html);
        }

        $renderImage = fn () => $this->renderImage($html);

        $cacheKey = $this->cacheKey();
        $cacheStore = Cache::store(config('raster.cache_store'));
        if ($this->cache === true) {
            return $cacheStore->rememberForever($cacheKey, $renderImage);
        } elseif (is_int($this->cache)) {
            return $cacheStore->remember($cacheKey, $this->cache, $renderImage);
        }

        return $renderImage();
    }

    protected function renderHtml(): string
    {
        $layout = config('raster.layout');

        View::share('raster', $this);
        $html = $this->renderView($layout, [], $this->renderView($this->name, $this->data));
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

    protected function renderPreview(string $html): string
    {
        return $html.'<style>'.$this->makeStyle(true).'</style>';
    }

    protected function renderImage(string $html): string
    {
        $browsershot = static::$browsershot ?? fn ($browsershot) => $browsershot;

        return $browsershot(new Browsershot)
            ->setHtml($html)
            ->setOption('addStyleTag', json_encode(['content' => $this->makeStyle()]))
            ->deviceScaleFactor($this->scale)
            ->windowSize($this->width, 1)
            ->setScreenshotType($this->type)
            ->showBackground()
            ->fullPage()
            ->screenshot();
    }

    public function makeStyle(bool $preview = false): string
    {
        $fontSize = isset($this->basis)
            ? 16 * $this->width / $this->basis
            : 16;

        return collect([
            ':root { font-size: '.$fontSize.'px; }',
        ])->when($preview, fn ($style) => $style->merge([
            ':root { min-height: 100vh; display: flex; }',
            'body { width: '.$this->width.'px; margin: auto; }',
        ]))->join(' ');
    }

    protected function hasFingerprint(): bool
    {
        $path = View::getFinder()->find($this->name);

        if (! Str::endsWith($path, '.blade.php')) {
            throw new \Exception('View must be a blade file');
        }

        $compiler = app('blade.compiler');
        $string = $compiler->compileString(file_get_contents($path));

        return Str::contains($string, static::fingerprint());
    }

    public function toResponse($request): Response
    {
        $data = $this->render();

        $mime = match (true) {
            $this->preview => 'text/html',
            $this->type === 'jpeg' => 'image/jpeg',
            $this->type === 'png' => 'image/png',
            default => throw new \Exception('Unsupported image type: '.$this->type),
        };

        return response($data)->header('Content-Type', $mime);
    }

    public function toUrl(): string
    {
        $params = [
            'name' => $this->name,
            'data' => app('url')->formatParameters($this->data),
            'width' => $this->width ?? null,
            'basis' => $this->basis ?? null,
            'scale' => $this->scale,
            'type' => $this->type,
            'preview' => $this->preview(),
        ];

        $defaults = (new \ReflectionClass($this))
            ->getDefaultProperties();
        $params = collect($params)
            ->filter(fn ($value, $key) => $value !== ($defaults[$key] ?? null))
            ->all();

        return config('raster.sign_urls')
            ? URL::signedRoute('laravel-raster.render', $params)
            : route('laravel-raster.render', $params);
    }

    protected function cacheKey(): string
    {
        $params = [
            'name' => $this->name,
            'data' => app('url')->formatParameters($this->data),
            'width' => $this->width ?? null,
            'basis' => $this->basis ?? null,
            'scale' => $this->scale,
            'type' => $this->type,
            'preview' => $this->preview(),
        ];

        return 'laravel-raster.'.md5(serialize($params));
    }

    public function __toString(): string
    {
        return $this->toUrl();
    }

    /**
     * @param  array<mixed>  $args
     * @return array<mixed>
     */
    public function inject(...$args): array
    {
        if (! isset($this->request)) {
            return [];
        }

        $input = $this->request->all();
        $params = collect($input)
            ->merge($args)
            ->only([
                'data',
                'width',
                'basis',
                'scale',
                'type',
                'preview',
                'cache',
            ]);

        $data = $params['data'] ?? null;
        if ($data instanceof Closure) {
            $params['data'] = app()->call($data, $input['data'] ?? []);
        }

        $params->each(fn ($value, $name) => $this->{$name}($value));

        return $this->data;
    }

    protected function isAutomaticMode(): bool
    {
        return isset($this->request);
    }

    protected function isManualMode(): bool
    {
        return ! isset($this->request);
    }

    public static function fingerprint(): string
    {
        return '__raster_'.hash('sha1', __FILE__).'__';
    }

    public static function compile(string $expression): string
    {
        $fingerprint = static::fingerprint();

        return "<?php
/* {$fingerprint} */
if ((\$raster ?? null) instanceof \JackSleight\LaravelRaster\Raster) {
    \$__raster_data = \$raster->inject({$expression});
    extract(\$__raster_data);
    unset(\$__raster_data);
}
?>";
    }

    public static function browsershot(Closure $browsershot): void
    {
        static::$browsershot = $browsershot;
    }
}
