<?php

use App\Models\User;

test('the confirm password page loads without javascript errors', function () {
    $user = User::factory()->create(['language' => 'en']);

    $this->actingAs($user);

    $page = visit('/confirm-password');

    $page->assertSee('Confirm Password')
        ->assertNoSmoke();
});

test('a user can confirm their password through the browser and reach the dashboard', function () {
    $user = User::factory()->create(['password' => 'Secret123**']);

    $this->actingAs($user);

    $page = visit('/confirm-password');

    $page->type('input[name="password"]', 'Secret123**')
        ->click('button[type="submit"]')
        ->assertRoute('dashboard.show')
        ->assertNoJavaScriptErrors();
});

test('the confirm password form shows a visible error for an incorrect password', function () {
    $user = User::factory()->create(['password' => 'Secret123**', 'language' => 'en']);

    $this->actingAs($user);

    $page = visit('/confirm-password');

    $page->type('input[name="password"]', 'WrongPassword**')
        ->click('button[type="submit"]')
        ->assertPathIs('/confirm-password')
        ->assertSee('The password is incorrect.')
        ->assertNoJavaScriptErrors();
});

test('the confirm password form exposes a stable name and autocomplete attribute', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $page = visit('/confirm-password');

    $page->assertAttribute('input[name="password"]', 'autocomplete', 'current-password');
});
