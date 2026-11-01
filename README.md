# Raster

Rasterise views and components to images by simply adding a directive and fetching the URL. Automatic routing, scaling, caching, protection and preview mode. Zero configuration (unless you need it).

## Example

Add the `@raster` directive to a view:

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

Then fetch the URL with the `raster()` helper:

```blade
{{-- resources/views/blog/show.blade.php --}}
@push('head')
    <meta property="og:image" content="{{ raster('blog.hero', ['post' => $post]) }}">
@endpush
```

## Documentation

[Laravel Raster Documentation](https://jacksleight.com/code/laravel-raster)

## Sponsoring 

This package is completely free to use. However fixing bugs, adding features and helping users takes time and effort. If you find this useful and would like to support its development any [contribution](https://github.com/sponsors/jacksleight) would be greatly appreciated. Thanks! 🙂
