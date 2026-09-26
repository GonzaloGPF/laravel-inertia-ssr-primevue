<?php

use App\Models\User;

test('the login page loads without javascript errors', function () {
    $page = visit('/login');

    $page->assertSee('Login')
        ->assertNoSmoke();
});

test('a user can log in through the browser and reach the dashboard', function () {
    User::factory()->create([
        'email' => 'jane@example.com',
        'password' => 'Secret123**',
    ]);

    $page = visit('/login');

    $page->type('input[name="email"]', 'jane@example.com')
        ->type('input[name="password"]', 'Secret123**')
        ->click('button[type="submit"]')
        ->assertRoute('dashboard.show')
        ->assertNoJavaScriptErrors();
});

test('the login form shows a visible error for invalid credentials', function () {
    User::factory()->create([
        'email' => 'jane@example.com',
        'password' => 'Secret123**',
    ]);

    $page = visit('/login');

    $page->type('input[name="email"]', 'jane@example.com')
        ->type('input[name="password"]', 'WrongPassword**')
        ->click('button[type="submit"]')
        ->assertPathIs('/login')
        ->assertSee('These credentials do not match our records.')
        ->assertNoJavaScriptErrors();
});

test('the login page links to the forgot password page', function () {
    $page = visit('/login');

    $page->click('Forgot Password?')
        ->assertRoute('password.request');
});

test('the login page links to the registration page', function () {
    $page = visit('/login');

    $page->assertSee("Don't have an account?")
        ->click('Create an account here')
        ->assertRoute('register');
});

test('the login form exposes stable name and autocomplete attributes', function () {
    $page = visit('/login');

    $page->assertAttribute('input[name="email"]', 'autocomplete', 'username')
        ->assertAttribute('input[name="password"]', 'autocomplete', 'current-password');
});
