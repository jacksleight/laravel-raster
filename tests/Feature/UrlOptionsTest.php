<?php

test('file is included in generated urls', function () {
    expect((string) raster('probe')->file('share'))
        ->toContain('file=share');
});
