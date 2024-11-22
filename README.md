# Raster

Rasterise views and components to images by simply adding a directive and fetching the URL. Automatic routing, scaling, caching, protection and preview mode. Zero configuration (unless you need it).

## Installation

Run the following command from your project root:

```bash
composer require jacksleight/laravel-raster
```

This package uses [Puppeteer](https://pptr.dev/) via [spatie/browsershot](https://spatie.be/docs/browsershot/v4/introduction) under the hood, you will also need follow the necessary Puppeteer [installation steps](https://spatie.be/docs/browsershot/v4/requirements) for your system. I can't help with Puppeteer issues or rendering inconsistencies, sorry.

If you need to customise the config you can publish it with:

```bash
php artisan vendor:publish --tag="raster-config"
```

## Usage

### Layout Setup

The views will be rendered inside a layout view where you can load any required CSS and other assets. By default this is a component called `layouts.raster`, but you can change it in the config file.

```blade
{{-- resources/views/components/layouts/raster.blade.php --}}
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

### Automatic Mode

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
> Views rasterised using automatic mode must implement the raster directive.

### Manual Mode

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
    return Raster::make('blog.hero', ['post' => $post])->width(1000);
})->name('blog.hero');
```

```blade
{{-- resources/views/layout.blade.php --}}
<meta property="og:image" content="{{ route('blog.hero', ['post' => $post]) }}">
```

> [!IMPORTANT] 
> Views rasterised using manual mode must not implement the raster directive.

## Customising Rasterised Views

If you would like to make changes to the view based on whether or not it's being rasterised you can check for the `$raster` variable:

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
* **height (int, auto)**  
  Height of the generated image.
* **basis (int)**  
  [Viewport basis](#viewport-basis) of the generated image. 
* **scale (int, 1)**  
  Scale of the generated image.
* **type (string, png)**  
  Type of the generated image (`png`, `jpeg` or `pdf`).
* **data (array)**  
  Array of data to pass to the view.
* **preview (bool, false)**  
  Enable [preview mode](#preview-mode).

With PDF output a height is required, it will only contain one page, and dimensions are still pixels not mm/inches. If you're looking to generate actual documents from views I highly recommend checking out [spatie/laravel-pdf](https://github.com/spatie/laravel-pdf).

# Caching

* **cache (bool, false)**  
  Enable caching of image generation.
* **cacheFor (int)**  
  Cache time to live in seconds.
* **cacheKey (string|array)**  
  Cache key. If an array is provided that will hashed to generate a key.

The `cache` option can also be used as a shortcut. Pass an integer to enable caching and set the for value, pass a string or array to enable caching and set the key value.

Cache options cannot be passed as URL parameters.

## Viewport Basis

When the basis option is set the image will be generated as if the viewport was that width, but the final image will match the desired width. Here's an example of how that affects output:

![Viewport Basis](https://jacksleight.dev/assets/packages/laravel-raster/viewport-basis.jpg)

## Preview Mode

In preview mode the HTML will be returned from the response but with all the appropriate scaling applied. This gives you a 1:1 preview without the latency that comes from generating the actual image.

## Security & URL Signing

Only views that implement the `@raster` directive can be rasterised in automatic mode, an error will be thrown before execution if they don't. It's also recommended to enable URL signing on production to ensure they can't be tampered with. You can do this by setting the `RASTER_SIGN_URLS` .env var to `true`.

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

This package is completely free to use. However fixing bugs, adding features and helping users takes time and effort. If you find this useful and would like to support its development any [contribution](https://github.com/sponsors/jacksleight) would be greatly appreciated. Thanks! 🙂
