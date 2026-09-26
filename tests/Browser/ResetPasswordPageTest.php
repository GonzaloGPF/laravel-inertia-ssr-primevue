<?php

use App\Models\User;
use Illuminate\Support\Facades\Password;

test('the reset password page loads without javascript errors', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $page = visit(route('password.reset', ['token' => $token, 'email' => $user->email]));

    $page->assertSee('Reset Password')
        ->assertNoSmoke();
});

test('the reset password page pre-fills and disables the email field', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $page = visit(route('password.reset', ['token' => $token, 'email' => $user->email]));

    $page->assertValue('input[name="email"]', $user->email)
        ->assertDisabled('input[name="email"]');
});

test('a user can reset their password through the browser and log in with it', function () {
    $user = User::factory()->create(['password' => 'OldSecret123**']);
    $token = Password::createToken($user);

    $page = visit(route('password.reset', ['token' => $token, 'email' => $user->email]));

    $page->type('input[name="new_password"]', 'NewSecret123**')
        ->type('input[name="new_password_confirmation"]', 'NewSecret123**')
        ->click('button[type="submit"]')
        ->assertRoute('login')
        ->assertSee('Your password has been reset.')
        ->assertNoJavaScriptErrors();

    $page->type('input[name="email"]', $user->email)
        ->type('input[name="password"]', 'NewSecret123**')
        ->click('button[type="submit"]')
        ->assertRoute('dashboard.show');
});

test('the reset password form shows a visible error when the passwords do not match', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $page = visit(route('password.reset', ['token' => $token, 'email' => $user->email]));

    $page->type('input[name="new_password"]', 'NewSecret123**')
        ->type('input[name="new_password_confirmation"]', 'SomethingElse123**')
        ->click('button[type="submit"]')
        ->assertRoute('password.reset', ['token' => $token])
        ->assertSee('The password confirmation does not match.')
        ->assertNoJavaScriptErrors();
});

test('the reset password form shows a visible error for an invalid token', function () {
    $user = User::factory()->create();

    $page = visit(route('password.reset', ['token' => 'not-a-real-token', 'email' => $user->email]));

    $page->type('input[name="new_password"]', 'NewSecret123**')
        ->type('input[name="new_password_confirmation"]', 'NewSecret123**')
        ->click('button[type="submit"]')
        ->assertSee('This password reset token is invalid.')
        ->assertNoJavaScriptErrors();
});

test('the reset password form exposes stable name and autocomplete attributes', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $page = visit(route('password.reset', ['token' => $token, 'email' => $user->email]));

    $page->assertAttribute('input[name="email"]', 'autocomplete', 'username')
        ->assertAttribute('input[name="new_password"]', 'autocomplete', 'new-password')
        ->assertAttribute('input[name="new_password_confirmation"]', 'autocomplete', 'new-password');
});
