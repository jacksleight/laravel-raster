---
title: Introduction
order: 10
---

# Introduction

Rasterise views and components to images by simply adding a directive and fetching the URL. Automatic routing, scaling, caching, protection and preview mode. Zero configuration (unless you need it).

It's useful for things like:

* Generating Open Graph and social sharing images from your own templates
* Rendering charts, badges or cards that need to be embedded as images
* Producing single page PDFs from a view
* Any time you'd rather build an image with HTML and CSS than an image library

## How It Works

Raster renders a view inside a layout, hands the HTML to a headless browser via [Puppeteer](https://pptr.dev/), and returns the screenshot as the response. There are two ways to use it:

* **[Automatic mode](automatic-mode)** is the quick way. You add the `@raster` directive to a view, and Raster provides the route, the URL helper and the parameter handling for you.
* **[Manual mode](manual-mode)** is for when you'd like to own the routing yourself. Your route builds a `Raster` object and returns it as the response.

Either way the [options](options) are the same, as are [caching](caching), [security](security) and the Browsershot [customisation](advanced) hooks.

## Compatibility

Raster works with Antlers views too, when used with the [Statamic Raster](https://github.com/jacksleight/statamic-raster/tree/main/docs) addon, which builds on this package and adds a few Statamic specific features.
