<?php

use App\Models\User;
use App\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

test('the forgot password page loads without javascript errors', function () {
    $page = visit('/forgot-password');

    $page->assertSee('Forgot Password?')
        ->assertNoSmoke();
});

test('requesting a reset link shows a visible confirmation and sends the notification', function () {
    Notification::fake();

    $user = User::factory()->create();

    $page = visit('/forgot-password');

    $page->type('input[name="email"]', $user->email)
        ->click('button[type="submit"]')
        ->assertSee('We have emailed your password reset link.')
        ->assertNoJavaScriptErrors();

    Notification::assertSentTo($user, ResetPassword::class);
});

test('the forgot password form shows a visible error for an unknown email', function () {
    $page = visit('/forgot-password');

    $page->type('input[name="email"]', 'nobody@example.com')
        ->click('button[type="submit"]')
        ->assertSee("We can't find a user with that email address.")
        ->assertNoJavaScriptErrors();
});

test('the forgot password form exposes a stable name and autocomplete attribute', function () {
    $page = visit('/forgot-password');

    $page->assertAttribute('input[name="email"]', 'autocomplete', 'username');
});
