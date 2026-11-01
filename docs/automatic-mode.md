---
title: Automatic Mode
order: 30
---

# Automatic Mode

In automatic mode Raster handles the routing for you. You add the `@raster` directive to a view to make it rasterisable, then generate a URL to the image with the `raster()` helper.

[TOC]

---

## The Directive

Add the `@raster` directive to the top of the view, and set any [options](options) you need as named arguments. The `data` closure receives any parameters passed in the URL and should return an array of data to pass to the view:

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

The closure is called through the container, so you can type-hint any services you need alongside the URL parameters. A plain `data` array works too when there's nothing to look up.

When the view is rendered during normal non-raster requests the directive does nothing, so the same view can be used both ways.

:::warning
Views rasterised using automatic mode must implement the raster directive, an error will be thrown if they don't. Views used in [manual mode](manual-mode) must not.
:::

---

## The URL Helper

Generate a URL to the image with the `raster()` helper, passing the view name and the data parameters for the closure:

```blade
{{-- resources/views/blog/show.blade.php --}}
@push('head')
    <meta property="og:image" content="{{ raster('blog.hero', ['post' => $post]) }}">
@endpush
```

The data is formatted the same way route parameters are, so a model is passed as its route key. That's why the closure above receives the post's ID rather than the post itself.

The helper returns a `Raster` object that casts to the URL, so you can override options by chaining methods on to it. Options passed in the URL take priority over options set in the directive:

```blade
{{ raster('blog.hero', ['post' => $post])->width(1200)->type('jpeg') }}
```

---

## Option Closures

Any of the other options can be a closure as well, which is handy when an option depends on the data. These closures run after `data` and receive the *resolved* view data rather than the URL parameters, so a model looked up in `data` arrives as the model:

```blade
@raster(
    width: 1000,
    height: fn ($post) => $post->is_tall ? 1000 : 500,
    cacheId: fn ($post) => $post->id,
    data: fn ($post) => [
        'post' => Post::find((int) $post),
    ],
)
```

---

## Components

Views under `components` are rendered as components rather than plain views, with the data passed as attributes, so `raster('components.cards.hero')` renders `<x-cards.hero>`.
