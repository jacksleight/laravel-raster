<?php

test('preview mode returns the html without a script', function () {
    $this->get('/raster/probe')
        ->assertOk()
        ->assertSee('<style>', false)
        ->assertDontSee('postMessage', false);
});
