<?php

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->authService = app(AuthService::class);
});

test('a user signs in on the web guard, and the session and the csrf token are renewed', function () {
    $this->startSession();
    $sessionId = session()->getId();
    $token = csrf_token();

    $this->authService->login($user = User::factory()->create());

    expect(Auth::guard('web')->id())->toBe($user->id)
        ->and(session()->getId())->not->toBe($sessionId)
        ->and(csrf_token())->not->toBe($token);
});

test('a user is remembered only when they ask for it', function (bool $remember) {
    $this->authService->login(User::factory()->create(), $remember);

    $cookies = collect(app('cookie')->getQueuedCookies())->map->getName();

    expect($cookies->contains(fn (string $name) => str_starts_with($name, 'remember_web_')))->toBe($remember);
})->with([
    'remembered' => true,
    'not remembered' => false,
]);

test('the credentials sign a user in only when they are right', function () {
    $user = User::factory()->create();

    expect($this->authService->attempt(['email' => $user->email, 'password' => 'wrong-password']))->toBeFalse();
    $this->assertGuest('web');

    expect($this->authService->attempt(['email' => $user->email, 'password' => config('app.default_password')]))->toBeTrue();
    $this->assertAuthenticatedAs($user, 'web');
});

test('logging out signs the user out and leaves nothing of the session', function () {
    $this->startSession();
    $this->authService->login(User::factory()->create());
    session()->put('something', 'private');
    $sessionId = session()->getId();
    $token = csrf_token();

    $request = Request::create('/');
    $request->setLaravelSession(session()->driver());
    $this->authService->logout($request);

    $this->assertGuest('web');
    expect(session()->has('something'))->toBeFalse()
        ->and(session()->getId())->not->toBe($sessionId)
        ->and(csrf_token())->not->toBeEmpty() // The next page needs one
        ->and(csrf_token())->not->toBe($token);
});
