<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

test('the verify email page loads without javascript errors', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user);

    $page = visit('/verify-email');

    $page->assertSee('Verify')
        ->assertNoSmoke();
});

test('an already verified user is redirected away from the verify email page', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $page = visit('/verify-email');

    $page->assertRoute('dashboard.show');
});

test('resending the verification email shows a visible confirmation and sends the notification', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create(['language' => 'en']);

    $this->actingAs($user);

    $page = visit('/verify-email');

    $page->click('button[type="submit"]')
        ->assertSee('A new verification link has been sent to the email address you provided during registration.')
        ->assertNoJavaScriptErrors();

    Notification::assertSentTo($user, VerifyEmailNotification::class);
});

test('clicking a valid verification link shows a visible success message', function () {
    $user = User::factory()->unverified()->create(['language' => 'en']);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $this->actingAs($user);

    $page = visit($verificationUrl);

    $page->assertRoute('dashboard.show')
        ->assertSee('Email verified')
        ->assertNoJavaScriptErrors();
});

test('the logout button on the verify email page logs the user out', function () {
    $user = User::factory()->unverified()->create(['language' => 'en']);

    $this->actingAs($user);

    $page = visit('/verify-email');

    $page->assertSee('Logout')
        ->click('Logout')
        ->assertRoute('root')
        ->assertNoJavaScriptErrors();

    $this->assertGuest();
});
