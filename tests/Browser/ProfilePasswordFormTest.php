<?php

use App\Models\User;

test('a user can update their password and log in with the new one', function () {
    $user = User::factory()->create(['password' => 'OldSecret123**', 'language' => 'en']);

    $this->actingAs($user);

    $page = visit('/profile');

    $page->type('input[name="current_password"]', 'OldSecret123**')
        ->type('form:has(input[name="current_password"]) input[name="password"]', 'NewSecret123**')
        ->type('input[name="password_confirmation"]', 'NewSecret123**')
        ->click('form:has(input[name="current_password"]) button[type="submit"]')
        ->assertSee('User Updated')
        ->assertNoJavaScriptErrors();

    $this->post('/logout');
    $this->assertGuest();

    $page = visit('/login');

    $page->type('input[name="email"]', $user->email)
        ->type('input[name="password"]', 'NewSecret123**')
        ->click('button[type="submit"]')
        ->assertRoute('dashboard.show');
});

test('the password form shows a visible error for an incorrect current password', function () {
    $user = User::factory()->create(['password' => 'OldSecret123**', 'language' => 'en']);

    $this->actingAs($user);

    $page = visit('/profile');

    $page->type('input[name="current_password"]', 'WrongPassword**')
        ->type('form:has(input[name="current_password"]) input[name="password"]', 'NewSecret123**')
        ->type('input[name="password_confirmation"]', 'NewSecret123**')
        ->click('form:has(input[name="current_password"]) button[type="submit"]')
        ->assertPathIs('/profile')
        ->assertSee('The password is incorrect.')
        ->assertNoJavaScriptErrors();
});

test('the password form shows a visible error when the new passwords do not match', function () {
    $user = User::factory()->create(['password' => 'OldSecret123**', 'language' => 'en']);

    $this->actingAs($user);

    $page = visit('/profile');

    $page->type('input[name="current_password"]', 'OldSecret123**')
        ->type('form:has(input[name="current_password"]) input[name="password"]', 'NewSecret123**')
        ->type('input[name="password_confirmation"]', 'SomethingElse123**')
        ->click('form:has(input[name="current_password"]) button[type="submit"]')
        ->assertPathIs('/profile')
        ->assertSee('The password confirmation does not match.')
        ->assertNoJavaScriptErrors();
});

test('the password form exposes stable name and autocomplete attributes', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $page = visit('/profile');

    $page->assertAttribute('input[name="current_password"]', 'autocomplete', 'current-password')
        ->assertAttribute('form:has(input[name="current_password"]) input[name="password"]', 'autocomplete', 'new-password')
        ->assertAttribute('input[name="password_confirmation"]', 'autocomplete', 'new-password');
});
