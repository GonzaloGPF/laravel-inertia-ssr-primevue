<?php

use App\Models\User;

test('the registration page loads without javascript errors', function () {
    $page = visit('/register');

    $page->assertSee('Register')
        ->assertNoSmoke();
});

test('a user can register through the browser and reach the dashboard', function () {
    $page = visit('/register');

    $page->type('input[name="name"]', 'Jane Doe')
        ->type('input[name="email"]', 'jane@example.com')
        ->type('input[name="password"]', 'Secret123**')
        ->type('input[name="password_confirmation"]', 'Secret123**')
        ->click('button[type="submit"]')
        ->assertRoute('dashboard.show')
        ->assertNoJavaScriptErrors();

    expect(User::where('email', 'jane@example.com')->exists())->toBeTrue();
});

test('the registration form shows a visible error when the passwords do not match', function () {
    $page = visit('/register');

    $page->type('input[name="name"]', 'Jane Doe')
        ->type('input[name="email"]', 'jane@example.com')
        ->type('input[name="password"]', 'Secret123**')
        ->type('input[name="password_confirmation"]', 'SomethingElse123**')
        ->click('button[type="submit"]')
        ->assertPathIs('/register')
        ->assertSee('The password confirmation does not match.')
        ->assertNoJavaScriptErrors();

    expect(User::where('email', 'jane@example.com')->exists())->toBeFalse();
});

test('the registration form exposes stable name and autocomplete attributes', function () {
    $page = visit('/register');

    $page->assertAttribute('input[name="name"]', 'autocomplete', 'name')
        ->assertAttribute('input[name="email"]', 'autocomplete', 'username')
        ->assertAttribute('input[name="password"]', 'autocomplete', 'new-password')
        ->assertAttribute('input[name="password_confirmation"]', 'autocomplete', 'new-password');
});
