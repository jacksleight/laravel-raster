# Raster

Easily rasterize views and components to images by dropping in a directive and fetching a URL. Automatic scaling, caching, protection and preview mode. Zero configuration.

## Installation

Run the following command from your project root:

```bash
composer require jacksleight/laravel-raster
```

This package uses [Puppeteer](https://pptr.dev/) via [spatie/browsershot](https://spatie.be/docs/browsershot/v4/introduction) under the hood, you will also need follow the necessary Puppeteer [installation steps](https://spatie.be/docs/browsershot/v4/requirements) for your system. I can't help with Puppeteer issues, sorry.

If you need to customise the config you can publish it with:

```bash
php artisan vendor:publish --tag="raster-config"
```

## Usage

### Layout Setup

The views will be rendered inside a layout view where you can load any required CSS and other assets. By default it looks for a component called `layouts.raster`, but you can configure it in the config file.

```blade
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Raster</title>
        @vite(['resources/css/app.css'])
    </head>
    <body class="bg-black text-white">
        {{ $slot }}
    </body>
</html>
```

### Simple Mode (Automatic Routing)

To make a view rasterizeable simply implement the `@raster` directive and then generate a URL to your image using the `raster()` helper. The data closure receives any parameters passed in the URL and should return an array of data to pass to the view.

```blade
{{-- resources/views/blog/hero.blade.php --}}
@raster(
    width: 1000,
    data: fn ($post) => [
        'post' => Post::find((int) $post),
    ],
)
<div>
    <svg>...</svg>
    <h1>{{ $post->title }}</h1>
    <p>{{ $post->date }}</p>
</div>
```

```blade
{{-- resources/views/blog/show.blade.php --}}
@push('head')
    <meta property="og:image" content="{{ raster('blog.hero', ['post' => $post]) }}">
@endpush
```

You can set [options](#options) with the directive or through the URL by chaining methods on to the helper. The options passed in the URL take priority over options set in the directive.

When the view is rendered during normal non-raster requests the directive does nothing.

> [!IMPORTANT] 
> Views rasterised using simple mode must implement the raster directive.

### Advanced Mode (Manual Routing)

If you would like more control over the routing and how the requests are handled you can define your own routes that return raster responses and then generate a URL to your image using the usual `route()` helper.

```blade
{{-- resources/views/blog/hero.blade.php --}}
<div>
    <svg>...</svg>
    <h1>{{ $post->title }}</h1>
    <p>{{ $post->date }}</p>
</div>
```

```php
/* routes/web.php */
use JackSleight\LaravelRaster\Raster;

Route::get('/blog/{post}/hero', function (Post $post) {
    return (new Raster('blog.hero', ['post' => $post]))->width(1000);
})->name('blog.hero');
```

```blade
{{-- resources/views/layout.blade.php --}}
<meta property="og:image" content="{{ route('blog.hero', ['post' => $post]) }}">
```

> [!IMPORTANT] 
> Views rasterised using advanced mode must not implement the raster directive.

## Customising Rasterized Views

If you would like to make changes to the view based on whether or not it's being rasterized you can check for the `$raster` variable:

```blade
<div {{ $attributes->class([
    'rounded-none' => $raster ?? null,
]) }}>
</div>
```

## Options

The following options can be set with the directive or by chaining methods on to the helper:

* **width (int)**  
  Width of the generated image.
* **basis (int)**  
  [Viewport basis](#viewport-basis) of the generated image. 
* **scale (int, 1)**  
  Scale factor of the generated image (1, 2 or 3).
* **type (string, png)**  
  Type of the generated image (`png` or `jpeg`).
* **preview (bool, false)**  
  Enable [preview mode](#preview-mode).
* **cache (bool|int, false)**  
  Cache image generation, `true` for forever or `int` for seconds.  
  Cannot be set from a route parameter.

```php
raster('blog.hero', ['post' => $post])
    ->width(1000)
    ->basis(700)
    ->scale(2)
    ->type('jpeg')
    ->preview(true);
```

## Viewport Basis

When the basis option is set the image will be generated as if the viewport was that width, but the final image will match the desired width. Here's an example of how that affects output:

![Viewport Basis](https://jacksleight.dev/assets/packages/laravel-raster/viewport-basis.jpg)

## Preview Mode

In preview mode the HTML will be returned from the response but with all the appropriate scaling applied. This gives you a 1:1 preview without the latency that comes from generating the actual image.

## Security & URL Signing

Only views that implement the `@raster` directive can be rasterized in simple mode, an error will be thrown before execution if they don't. It's also recommended to enable URL signing on production to ensure they can't be tampered with. You can do this by setting the `RASTER_SIGN_URLS` .env var to `true`.

## Customising Browsershot

If you need to customise the Browsershot instance you can pass a closure to `Raster::browsershot()` in a service provider:

```php
use JackSleight\LaravelRaster\Raster;

Raster::browsershot(fn ($browsershot) => $browsershot
    ->setOption('args', ['--disable-web-security'])
    ->waitUntilNetworkIdle()
);
```

## Sponsoring 

This package is completely free to use. However fixing bugs, adding features and helping users takes time and effort. If you find this addon useful and would like to support its development any [contribution](https://github.com/sponsors/jacksleight) would be greatly appreciated. Thanks! 🙂
