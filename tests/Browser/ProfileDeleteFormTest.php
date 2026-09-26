<?php

use App\Models\User;

test('clicking delete opens a confirmation dialog with a password field', function () {
    $user = User::factory()->create(['language' => 'en']);

    $this->actingAs($user);

    $page = visit('/profile');

    $page->click('button[aria-label="Delete"]')
        ->assertSee('Are you sure you want to delete your account?')
        ->assertSee('Once your account is deleted, all of its resources and data will be permanently deleted.')
        ->assertNoJavaScriptErrors();
});

test('cancelling the dialog does not delete the account', function () {
    $user = User::factory()->create(['language' => 'en']);

    $this->actingAs($user);

    $page = visit('/profile');

    $page->click('button[aria-label="Delete"]')
        ->click('button[aria-label="Cancel"]')
        ->assertDontSee('Are you sure you want to delete your account?')
        ->assertNoJavaScriptErrors();

    expect($user->fresh())->not->toBeNull();
});

test('a user can delete their account with the correct password', function () {
    $user = User::factory()->create(['password' => 'Secret123**', 'language' => 'en']);

    $this->actingAs($user);

    $page = visit('/profile');

    $page->click('button[aria-label="Delete"]')
        ->type('[data-pc-name="dialog"] input[name="password"]', 'Secret123**')
        ->click('[data-pc-name="dialog"] button[aria-label="Delete"]')
        ->assertRoute('login')
        ->assertSee('User Deleted')
        ->assertNoJavaScriptErrors();

    $this->assertGuest();
    expect($user->fresh()->deleted_at)->not->toBeNull();
});

test('the delete dialog shows a visible error for an incorrect password', function () {
    $user = User::factory()->create(['password' => 'Secret123**', 'language' => 'en']);

    $this->actingAs($user);

    $page = visit('/profile');

    $page->click('button[aria-label="Delete"]')
        ->type('[data-pc-name="dialog"] input[name="password"]', 'WrongPassword**')
        ->click('[data-pc-name="dialog"] button[aria-label="Delete"]')
        ->assertSee('The password is incorrect.')
        ->assertNoJavaScriptErrors();

    $this->assertAuthenticated();
    expect($user->fresh()->deleted_at)->toBeNull();
});
