---
title: Options
order: 50
---

# Options

The following options can be set with the directive or by chaining methods on to the object. Either `width` or `basis` must be set.

* **width (int)**  
  Width of the generated image.
* **height (int, auto)**  
  Height of the generated image. When not set the image will be as tall as the content.
* **basis (int)**  
  [Viewport basis](#viewport-basis) of the generated image.
* **scale (int, 1)**  
  Scale of the generated image, for retina output.
* **type (string, png)**  
  Type of the generated image (`png`, `jpeg` or `pdf`).
* **transparent (bool, false)**  
  Omit the page background, for PNG images with transparency. Your layout will need to leave the background unset too.
* **file (string)**  
  File name of the response, excluding extension. Defaults to the view name.
* **data (array)**  
  Array of data to pass to the view.
* **preview (bool, false)**  
  Enable [preview mode](#preview-mode).
* **cache (bool, false)**  
  Enable [caching](caching) of generated images.
* **cacheId (string, '_')**  
  [Cache](caching) identifier.

With PDF output a height is required, it will only contain one page, and dimensions are still pixels not mm/inches. If you're looking to generate actual documents from views I highly recommend checking out [spatie/laravel-pdf](https://github.com/spatie/laravel-pdf).

[TOC]

---

## Viewport Basis

When the basis option is set the image will be generated as if the viewport was that width, but the final image will match the desired width. Here's an example of how that affects output:

![Viewport Basis](https://jacksleight.com/assets/packages/laravel-raster/viewport-basis.jpg)

This works by adjusting the root font size, so it relies on your layout being sized in `rem` units. It's a handy way to render a design built for one width at another without touching the CSS, and if you only set `basis` the width defaults to match it.

---

## Preview Mode

In preview mode the HTML will be returned from the response but with all the appropriate scaling applied. This gives you a 1:1 preview without the latency that comes from generating the actual image, which is much quicker when you're building the view.

Enable it by chaining `->preview(true)` on to the helper, or adding `?preview=1` to a URL.

---

## Customising Rasterised Views

If you would like to make changes to the view based on whether or not it's being rasterised you can check for the `$raster` variable, which is the `Raster` object during a raster request and unset otherwise:

```blade
<div {{ $attributes->class([
    'rounded-none' => $raster ?? null,
]) }}>
</div>
```

It's available in the layout as well, so you can load different assets or set a background when rasterising.
