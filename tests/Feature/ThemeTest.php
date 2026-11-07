<?php

it('sets the theme before the page is painted, and the html has no theme of its own', function () {
    $html = $this->get('/')->assertSuccessful()->getContent();

    expect($html)->toContain("localStorage.getItem('theme')")
        ->and(strpos($html, "localStorage.getItem('theme')"))->toBeLessThan(strpos($html, '<script type="module"')) // Before the scripts of the application
        ->and($html)->not->toMatch('/<html[^>]*class=/');
});
