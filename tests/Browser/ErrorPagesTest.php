<?php

use App\Enums\Languages;
use App\Enums\Roles;
use App\Models\User;

const ERROR_PAGE = '[data-error-page]';

test('a regular user that opens an admin page is told that it is forbidden, in the layout of the application', function () {
    $this->actingAs(User::factory()->create(['name' => 'Jane Doe', 'role' => Roles::USER, 'language' => Languages::EN]));

    $page = visit(route('users.index', absolute: false));

    $page->assertPresent(ERROR_PAGE)
        ->assertSee('Forbidden')
        ->assertSee('403')
        ->assertSee('You do not have permission to see this page.')
        ->assertSee('Jane Doe') // The menu of the application knows who is logged
        ->assertTitleContains('403')
        ->assertPathIs('/admin/users')
        ->assertNoJavaScriptErrors();
});

test('a page that does not exist shows the not found page, and the way out goes home', function () {
    $page = visit('/nothing-here');

    $page->assertPresent(ERROR_PAGE)
        ->assertSee('Not Found')
        ->assertSee('404')
        ->assertSee('The page you are looking for does not exist, or it has been moved.')
        ->click('Home')
        ->assertRoute('root')
        ->assertNoJavaScriptErrors();
});

test('the error page can go back to where the user was', function () {
    $page = visit('/');

    $page->script("window.location.assign('/nothing-here')");

    $page->assertPresent(ERROR_PAGE)
        ->click('Back')
        ->assertRoute('root')
        ->assertNoJavaScriptErrors();
});

test('the error page is translated to the language of the user', function () {
    $this->actingAs(User::factory()->create(['role' => Roles::USER, 'language' => Languages::ES]));

    $page = visit(route('users.index', absolute: false));

    $page->assertPresent(ERROR_PAGE)
        ->assertSee('Prohibido')
        ->assertSee('No tienes permiso para ver esta página.')
        ->assertSee('Regresar')
        ->assertSee('Inicio')
        ->assertNoJavaScriptErrors();
});

test('the error page fits a small screen', function () {
    $page = visit('/nothing-here')->resize(400, 800);

    $page->assertPresent(ERROR_PAGE);

    $overflow = $page->script('document.documentElement.scrollWidth - window.innerWidth');

    expect($overflow)->toBeLessThanOrEqual(0);
});
