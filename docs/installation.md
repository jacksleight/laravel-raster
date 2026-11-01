---
title: Installation
order: 20
---

# Installation

Run the following command from your project root:

```bash
composer require jacksleight/laravel-raster
```

This package uses [Puppeteer](https://pptr.dev/) via [spatie/browsershot](https://spatie.be/docs/browsershot/v4/introduction) under the hood, you will also need to follow the necessary Puppeteer [installation steps](https://spatie.be/docs/browsershot/v4/requirements) for your system. I can't help with Puppeteer issues or rendering inconsistencies, sorry.

## Configuration

If you need to customise the config you can publish it with:

```bash
php artisan vendor:publish --tag="raster-config"
```

Then open `config/raster.php` to make your changes. The options are:

* **route (string, raster)**  
  The path prefix for the automatic mode route, so images are served from `/raster/[view-name]`.
* **layout (string, components.layouts.raster)**  
  The layout view to render rasterised views inside.
* **sign_urls (bool, false)**  
  Whether generated URLs are [signed](security), set with the `RASTER_SIGN_URLS` env var.
* **cache (array)**  
  The [cache](caching) settings, set with the `RASTER_CACHE_*` env vars.

## Layout Setup

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

The layout doesn't have to be a component. If you point the config at a plain view Raster will render it with `@extends`, so output the rasterised view with `@yield('slot')` instead.
