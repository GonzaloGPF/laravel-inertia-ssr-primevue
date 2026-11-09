<?php

test('the footer shows the name of the app from the configuration', function () {
    config(['app.name' => 'Acme Panel']);

    $page = visit('/');

    $page->assertSee('This is the root page')
        ->assertNoJavaScriptErrors();

    expect($page->script("[...document.querySelectorAll('.p-menubar')].at(-1).textContent.trim()"))
        ->toContain('Acme Panel')
        ->not->toContain('Boilerplate');
});
