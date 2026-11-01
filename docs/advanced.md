---
title: Advanced
order: 80
---

# Advanced

[TOC]

---

## Customising Browsershot

If you need to customise the Browsershot instance you can pass a closure to `Raster::browsershot()` in a service provider's `boot()` method:

```php
use JackSleight\LaravelRaster\Raster;

Raster::browsershot(fn ($browsershot) => $browsershot
    ->setOption('args', ['--disable-web-security'])
    ->waitUntilNetworkIdle()
);
```

This is where you'd point Browsershot at a specific Node or Chromium binary, adjust timeouts, or wait for assets to finish loading. See the [Browsershot docs](https://spatie.be/docs/browsershot/v4/introduction) for everything available.

---

## Custom Renderers

Raster renders views with Blade. If you'd like to render them some other way, pass a closure to `Raster::renderer()`. It receives the view name, the data array and the rendered inner view when rendering the layout, and should return the HTML:

```php
use JackSleight\LaravelRaster\Raster;

Raster::renderer(fn ($name, $data, $slot) => ...);
```

---

## Custom View Types

Views are matched to a handler by file extension, and only `blade.php` is supported out of the box. To support another template language register a handler for its extension:

```php
use JackSleight\LaravelRaster\Raster;

Raster::extension('twig', TwigHandler::class);
```

A handler extends `JackSleight\LaravelRaster\BaseHandler` and implements `hasFingerprint()`, which checks whether a view has opted in to automatic mode. This is how the [Statamic Raster](https://jacksleight.com/code/statamic-raster) addon adds Antlers support.
