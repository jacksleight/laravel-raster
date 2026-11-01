<?php

use Illuminate\Testing\TestResponse;

function state(TestResponse $response): array
{
    preg_match('#<script id="state" type="application/json">(.*?)</script>#s', $response->assertOk()->getContent(), $match);

    return json_decode($match[1], true);
}

test('unknown query parameters are ignored', function () {
    expect(state($this->get('/raster/probe?foo=1&token=abc&utm_source=x')))
        ->label->toBe('default');
});

test('signed urls are accepted when signing is enabled', function () {
    config(['raster.sign_urls' => true]);

    expect(state($this->get((string) raster('probe', ['label' => 'signed']))))
        ->label->toBe('signed');
});

test('unsigned urls are rejected when signing is enabled', function () {
    config(['raster.sign_urls' => true]);

    $this->get('/raster/probe')->assertUnauthorized();
});

test('url options are applied', function () {
    expect(state($this->get('/raster/probe?width=300&file=share&data[label]=hi')))
        ->width->toBe(300)
        ->file->toBe('share')
        ->label->toBe('hi');
});

test('cache id cannot be set from the url', function () {
    expect(state($this->get('/raster/probe?cacheId=evil')))
        ->cacheId->toBe('_');
});

test('file is included in generated urls', function () {
    expect((string) raster('probe')->file('share'))
        ->toContain('file=share');
});
