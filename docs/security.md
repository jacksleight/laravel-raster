---
title: Security
order: 70
---

# Security

Rendering arbitrary views to images on request is the kind of thing that needs a couple of guards, and Raster has two.

## View Protection

Only views that implement the `@raster` directive can be rasterised in automatic mode, an error will be thrown before execution if they don't. That means adding the package doesn't expose every view in your project, just the ones you've opted in.

## URL Signing

It's also recommended to enable URL signing on production to ensure the options and data in a URL can't be tampered with. You can do this by setting the `RASTER_SIGN_URLS` env var to `true`:

```env
RASTER_SIGN_URLS=true
```

Once enabled the `raster()` helper generates signed URLs, and the automatic mode route returns a 401 for any request without a valid signature. Manual mode routes are yours, so protect those however you would any other route.
