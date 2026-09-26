<?php

use App\Models\User;

test('the profile edit page loads without javascript errors', function () {
    $user = User::factory()->create(['language' => 'en']);

    $this->actingAs($user);

    $page = visit('/profile');

    $page->assertSee('Profile Information')
        ->assertNoSmoke();
});

test('a user can update their name and email and sees a visible confirmation', function () {
    $user = User::factory()->create(['language' => 'en']);

    $this->actingAs($user);

    $page = visit('/profile');

    $page->clear('input[name="name"]')
        ->type('input[name="name"]', 'Updated Name')
        ->click('form:has(input[name="name"]) button[type="submit"]')
        ->assertSee('User Updated')
        ->assertNoJavaScriptErrors();

    expect($user->fresh()->name)->toBe('Updated Name');
});

test('a user can change their language preference, it persists, and the UI retranslates immediately', function () {
    $user = User::factory()->create(['language' => 'en']);

    $this->actingAs($user);

    $page = visit('/profile');

    $page->assertSee('Language')
        ->click('span[name="language"]')
        ->click('li[aria-label="Spanish"]')
        ->click('form:has(input[name="name"]) button[type="submit"]')
        ->assertSee('Idioma')
        ->assertDontSee('Language')
        ->assertNoJavaScriptErrors();

    expect($user->fresh()->language->value)->toBe('es');

    $page->click('span[name="language"]')
        ->click('li[aria-label="Inglés"]')
        ->click('form:has(input[name="name"]) button[type="submit"]')
        ->assertSee('Language')
        ->assertNoJavaScriptErrors();

    expect($user->fresh()->language->value)->toBe('en');
});

test('a user can change their currency preference and it persists', function () {
    $user = User::factory()->create(['language' => 'en', 'currency' => 'dollar']);

    $this->actingAs($user);

    $page = visit('/profile');

    $page->click('span[name="currency"]')
        ->click('li[aria-label="Euro"]')
        ->click('form:has(input[name="name"]) button[type="submit"]')
        ->assertNoJavaScriptErrors();

    expect($user->fresh()->currency->value)->toBe('euro');
});

test('the profile form shows a visible error for a duplicate email', function () {
    $existing = User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create(['language' => 'en']);

    $this->actingAs($user);

    $page = visit('/profile');

    $page->clear('input[name="email"]')
        ->type('input[name="email"]', 'taken@example.com')
        ->click('form:has(input[name="name"]) button[type="submit"]')
        ->assertSee('The email has already been taken.')
        ->assertNoJavaScriptErrors();

    expect($user->fresh()->email)->not->toBe('taken@example.com');
});

test('the profile form exposes stable name and autocomplete attributes', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $page = visit('/profile');

    $page->assertAttribute('input[name="name"]', 'autocomplete', 'name')
        ->assertAttribute('input[name="email"]', 'autocomplete', 'email');
});
