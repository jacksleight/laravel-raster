# Changelog

## 1.0.0 (Unreleased)

- [new] Brand new [documentation](https://jacksleight.com/code/laravel-raster)
- [break] Only option parameters are read from the URL, anything else is ignored and `cacheId` can no longer be set there
- [break] URL options take priority over option closures
- [break] Cache path defaults to `raster` when a cache disk is set
- [break] Preview mode no longer posts a `raster.loaded` message
- [fix] Signed URLs and unknown query parameters causing errors
- [fix] File option missing from generated URLs
- [fix] Boolean URL options treating `false` as true
- [fix] Cache flush failing with the default config
- [fix] Antlers views rendering through the Blade route, they now throw a helpful error
- PHP 8.2+ is now required

## 0.5.2 (2026-07-10)

- [fix] Escaped layout slot content

## 0.5.1 (2026-07-10)

- Laravel 13 support

## 0.5.0 (2025-07-07)

- [new] Add transparent option

## 0.4.2 (2025-02-26)

- Laravel 12 support

## 0.4.1 (2025-01-06)

- Browsershot 5 support

## 0.4.0 (2024-12-06)

- [new] File name option

## 0.3.1 (2024-12-04)

- [fix] View file paths with dots

## 0.3.0 (2024-11-25)

- [new] Disk based caching

## 0.2.0 (2024-11-22)

- [new] Global cache toggle
- [new] Cache key option

## 0.1.1 (2024-11-15)

- [fix] URL param overrides

## 0.1.0 (2024-11-15)

- 🚀 Initial release
