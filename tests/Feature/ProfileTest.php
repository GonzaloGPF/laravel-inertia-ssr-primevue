<?php

use App\Enums\Currencies;
use App\Enums\Languages;
use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->put('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    expect($user->name)->toBe('Test User')
        ->and($user->email)->toBe('test@example.com')
        ->and($user->email_verified_at)->toBeNull();
});

test('profile language and currency preferences can be updated', function () {
    $user = User::factory()->create(['language' => Languages::EN, 'currency' => Currencies::DOLLAR]);

    $response = $this
        ->actingAs($user)
        ->put('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'language' => Languages::ES->value,
            'currency' => Currencies::EURO->value,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    expect($user->language)->toBe(Languages::ES)
        ->and($user->currency)->toBe(Currencies::EURO);
});

test('the authenticated user language is used for validation messages', function () {
    $user = User::factory()->create(['language' => Languages::ES]);

    $response = $this
        ->actingAs($user)
        ->put('/profile', [
            'name' => '',
            'email' => $user->email,
        ]);

    $response->assertSessionHasErrors('name');
    expect(session('errors')->get('name')[0])->toBe('El campo nombre es obligatorio.');
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->put('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => config('app.default_password'),
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/login');

    $this->assertGuest();
    expect($user->fresh()->deleted_at)->not->toBeNull();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect('/profile');

    expect($user->fresh())->not->toBeNull();
});
