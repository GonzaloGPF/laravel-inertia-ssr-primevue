<?php

test('the homepage loads without javascript errors', function () {
    $page = visit('/');

    $page->assertSee('This is the root page')
        ->assertNoSmoke();
});
