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

    protected string $path;

    protected BaseHandler $handler;

    /**
     * @var array<mixed>
     */
    protected array $data = [];

    protected ?Request $request;

    protected ?int $width;

    protected ?int $height;

    protected ?int $basis;

    protected int $scale = 1;

    protected string $type = 'png';

    protected bool $preview = false;

    protected bool $cache = false;

    protected ?int $cacheFor;

    protected string|array|null $cacheKey;

    protected static Closure $browsershot;

    protected static $extensions = [
        'blade.php' => BladeHandler::class,
    ];

    protected $route = 'laravel-raster.render';

    public static function make(string $name): static
    {
        return new static($name);
    }

    public static function makeFromRequest(?Request $request): static
    {
        return new static($request->route()->parameter('name'), $request);
    }

    /**
     * @param  array<mixed>  $data
     */
    protected function __construct(string $name, ?Request $request = null)
    {
        $this->name = $name;
        $this->request = $request;

        $this->path = View::getFinder()->find($this->name);

        $extension = Str::after($this->path, '.');
        if (! $handler = static::$extensions[$extension] ?? null) {
            throw new \Exception('Unsupported view type: '.$extension);
        }
        $this->handler = new $handler($this);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function handler(): BaseHandler
    {
        return $this->handler;
    }

    public function request(): Request
    {
        return $this->request;
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

    public function height(?int $height = null): static|int|null
    {
        if (func_num_args() > 0) {
            $this->height = $height;

            return $this;
        }

        return $this->height;
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

    public function cache(bool|int|string|array|null $cache = null): static|bool
    {
        if (func_num_args() > 0) {
            if (is_int($cache)) {
                $this->cacheFor($cache);
                $cache = true;
            } elseif (is_string($cache) || is_array($cache)) {
                $this->cacheKey($cache);
                $cache = true;
            }
            $this->cache = $cache ?? false;

            return $this;
        }

        return $this->cache;
    }

    public function cacheFor(?int $cacheFor = null): static|int
    {
        if (func_num_args() > 0) {
            $this->cacheFor = $cacheFor;

            return $this;
        }

        return $this->cacheFor;
    }

    public function cacheKey(string|array|null $cacheKey = null): static|string|array|null
    {
        if (func_num_args() > 0) {
            $this->cacheKey = $cacheKey;

            return $this;
        }

        return $this->cacheKey;
    }

    public function render(): string
    {
        if ($this->isAutomaticMode() && ! $this->hasFingerprint()) {
            throw new \Exception('View must implement raster');
        } elseif ($this->isManualMode() && $this->hasFingerprint()) {
            throw new \Exception('View must not implement raster');
        }

        $html = $this->renderHtml();

        if (! isset($this->width)) {
            throw new \Exception('Width must be set');
        }
        if ($this->type === 'pdf' && ! isset($this->height)) {
            throw new \Exception('Height must be set for PDF output');
        }

        if ($this->preview) {
            return $this->renderPreview($html);
        }

        $renderImage = fn () => $this->renderImage($html);

        if (config('raster.cache') && $this->cache) {
            $cacheKey = $this->generateCacheKey();
            $cacheStore = Cache::store(config('raster.cache_store'));
            if (isset($this->cacheFor)) {
                return $cacheStore->remember($cacheKey, $this->cacheFor, $renderImage);
            } else {
                return $cacheStore->rememberForever($cacheKey, $renderImage);
            }
        }

        return $renderImage();
    }

    /**
     * @param  array<mixed>  $data
     */
    protected function renderHtml(): string
    {
        $layout = config('raster.layout');

        View::share('raster', $this);
        $html = $this->renderView($layout, [], $this->renderView($this->name(), $this->data()));
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
        $callback = static::$browsershot ?? fn ($browsershot) => $browsershot;

        $raster = $callback(new Browsershot)
            ->setHtml($html)
            ->setOption('addStyleTag', json_encode(['content' => $this->makeStyle()]))
            ->showBackground();

        if ($this->type === 'pdf') {
            $width = ($this->width / 96 * 25.4) * $this->scale;
            $height = ($this->height / 96 * 25.4) * $this->scale;

            return $raster
                ->paperSize($width, $height)
                ->scale($this->scale)
                ->pages(1)
                ->pdf();
        }

        if (isset($this->height)) {
            $raster->windowSize($this->width, $this->height);
        } else {
            $raster->windowSize($this->width, 1)->fullPage();
        }

        return $raster
            ->deviceScaleFactor($this->scale)
            ->setScreenshotType($this->type)
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
        return $this->handler->hasFingerprint($this->path);
    }

    public function toResponse($request): Response
    {
        $data = $this->render();

        $mime = match (true) {
            $this->preview => 'text/html',
            $this->type === 'jpeg' => 'image/jpeg',
            $this->type === 'png' => 'image/png',
            $this->type === 'pdf' => 'application/pdf',
            default => throw new \Exception('Unsupported image type: '.$this->type),
        };

        return response($data)->header('Content-Type', $mime);
    }

    public function toUrl(): string
    {
        $params = $this->gatherParams();

        $defaults = (new \ReflectionClass($this))
            ->getDefaultProperties();
        $params = collect($params)
            ->filter(fn ($value, $key) => $value !== ($defaults[$key] ?? null))
            ->all();

        return config('raster.sign_urls')
            ? URL::signedRoute($this->route, $params)
            : route($this->route, $params);
    }

    protected function generateCacheKey(): string
    {
        if (isset($this->cacheKey)) {
            if (is_string($this->cacheKey)) {
                return $this->cacheKey;
            } else {
                $data = $this->cacheKey;
            }
        } else {
            $data = $this->gatherParams();
        }

        return 'laravel-raster.'.md5(serialize($data));
    }

    protected function gatherParams(): array
    {
        return [
            'name' => $this->name,
            'data' => app('url')->formatParameters($this->data),
            'width' => $this->width ?? null,
            'height' => $this->height ?? null,
            'basis' => $this->basis ?? null,
            'scale' => $this->scale,
            'type' => $this->type,
            'preview' => $this->preview(),
        ];
    }

    public function __toString(): string
    {
        return $this->toUrl();
    }

    public function isAutomaticMode(): bool
    {
        return isset($this->request);
    }

    public function isManualMode(): bool
    {
        return ! isset($this->request);
    }

    public static function browsershot(Closure $browsershot): void
    {
        static::$browsershot = $browsershot;
    }

    public static function extension(string $extension, string $class): void
    {
        static::$extensions[$extension] = $class;
    }
}
