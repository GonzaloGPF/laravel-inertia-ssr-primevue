<?php

use App\Models\User;

test('a guest sees an Enter trigger that opens Login and Register options', function () {
    $page = visit('/');

    $page->assertSee('Enter')
        ->assertDontSee('Login')
        ->assertDontSee('Register')
        ->click('Enter')
        ->assertSee('Login')
        ->assertSee('Register')
        ->assertNoJavaScriptErrors();
});

test('a guest can navigate to the login page from the menu', function () {
    $page = visit('/');

    $page->click('Enter')
        ->click('Login')
        ->assertRoute('login');
});

test('a guest can navigate to the register page from the menu', function () {
    $page = visit('/');

    $page->click('Enter')
        ->click('Register')
        ->assertRoute('register');
});

test('a logged in user sees their name and can open a menu with profile and logout options', function () {
    $user = User::factory()->create(['name' => 'Jane Doe', 'language' => 'en']);

    $this->actingAs($user);

    $page = visit('/');

    $page->assertSee('Jane Doe')
        ->assertDontSee('Enter')
        ->click('Jane Doe')
        ->assertSee('Profile')
        ->assertSee('Logout')
        ->assertNoJavaScriptErrors();
});

test('a logged in user can navigate to their profile from the menu', function () {
    $user = User::factory()->create(['name' => 'Jane Doe', 'language' => 'en']);

    $this->actingAs($user);

    $page = visit('/');

    $page->click('Jane Doe')
        ->click('li[aria-label="Profile"]')
        ->assertRoute('profile.edit');
});

test('a logged in user can log out from the menu', function () {
    $user = User::factory()->create(['name' => 'Jane Doe', 'language' => 'en']);

    $this->actingAs($user);

    $page = visit('/');

    $page->click('Jane Doe')
        ->click('Logout')
        ->assertRoute('root')
        ->assertNoJavaScriptErrors();

    $this->assertGuest();
});
