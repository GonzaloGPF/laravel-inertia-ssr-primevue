<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => config('app.default_password'),
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard.show', absolute: false));
});

test('logging in renews the session of whoever was there before', function () {
    $user = User::factory()->create();
    $this->startSession();
    $sessionId = session()->getId();

    $this->post('/login', ['email' => $user->email, 'password' => config('app.default_password')]);

    expect(session()->getId())->not->toBe($sessionId);
});

test('users are remembered when the login form asks for it', function (bool $remember) {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => config('app.default_password'),
        'remember' => $remember,
    ]);

    expect(collect($response->headers->getCookies())->contains(fn ($cookie) => str_starts_with($cookie->getName(), 'remember_web_')))->toBe($remember);
})->with([
    'remembered' => true,
    'not remembered' => false,
]);

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('logging out throws away the session', function () {
    $this->actingAs(User::factory()->create())
        ->withSession(['something' => 'private'])
        ->post('/logout');

    expect(session()->has('something'))->toBeFalse();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
