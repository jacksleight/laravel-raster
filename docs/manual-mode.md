---
title: Manual Mode
order: 40
---

# Manual Mode

If you would like more control over the routing and how the requests are handled you can define your own routes that return raster responses, and then generate a URL to your image using the usual `route()` helper.

The view itself is just a view, with no directive:

```blade
{{-- resources/views/blog/hero.blade.php --}}
<div>
    <svg>...</svg>
    <h1>{{ $post->title }}</h1>
    <p>{{ $post->date }}</p>
</div>
```

The route builds a `Raster` object with `Raster::make()`, sets the [options](options) by chaining methods on to it, and returns it. It's a responsable object, so Laravel takes care of the rest:

```php
/* routes/web.php */
use JackSleight\LaravelRaster\Raster;

Route::get('/blog/{post}/hero', function (Post $post) {
    return Raster::make('blog.hero')
        ->data(['post' => $post])
        ->width(1000);
})->name('blog.hero');
```

```blade
{{-- resources/views/layout.blade.php --}}
<meta property="og:image" content="{{ route('blog.hero', ['post' => $post]) }}">
```

Because you own the route you can do whatever you like in it, apply your own middleware, look up the data however suits, or decide the options based on the request.

:::warning
Views rasterised using manual mode must not implement the raster directive, an error will be thrown if they do.
:::

## Rendering Directly

If you want the image data rather than a response, call `render()` instead of returning the object. It returns the raw PNG, JPEG or PDF data, which you could then save to disk or attach to an email:

```php
$data = Raster::make('blog.hero')
    ->data(['post' => $post])
    ->width(1000)
    ->render();

Storage::put("heroes/{$post->id}.png", $data);
```
