---
title: Caching
order: 60
---

# Caching

Generating an image takes a little while, so once you're happy with the output you'll probably want to cache it. Caching is off by default per view and enabled with the `cache` option, either in the directive or by chaining `->cache(true)` on to the object.

[TOC]

---

## Options

* **cache (bool, false)**  
  Enable caching of generated images.
* **cacheId (string, '_')**  
  Cache identifier, see [clearing the cache](#clearing-the-cache) below. This one can't be passed as a URL parameter.

Cached files are keyed on every option and data parameter, so a change to the URL produces a new image. File paths will use this pattern:

```
[cache_path]/[view_name]/[cache_id]/[params_hash].[extension]
```

---

## Storage

By default the cache will be stored locally in `storage/app/raster`. You can store it on any filesystem disk instead by setting the `RASTER_CACHE_DISK` and `RASTER_CACHE_PATH` env vars:

```env
RASTER_CACHE_DISK=s3
RASTER_CACHE_PATH=raster
```

You can globally disable caching by setting the `RASTER_CACHE_ENABLED` env var to `false`, which is useful locally while you're working on a view.

---

## Clearing the Cache

There's no expiry, cached images live until you remove them. The `cacheId` option exists so that you can group the images for a record together and clear them when it changes:

```blade
@raster(
    width: 1000,
    cache: true,
    cacheId: fn ($post) => $post->id,
    data: fn ($post) => [
        'post' => Post::find((int) $post),
    ],
)
```

The `cacheId` closure receives the resolved view data rather than the URL parameters, so `$post` here is the model, see [option closures](automatic-mode#option-closures).

Then use the `Cache` class to forget a view's images by ID, or flush the lot:

```php
use JackSleight\LaravelRaster\Cache;

app(Cache::class)->forget('blog.hero', $post->id);
app(Cache::class)->forget(['blog.hero', 'blog.card'], $post->id);
app(Cache::class)->flush();
```

A model observer's `saved` method is a good place for the first one.
