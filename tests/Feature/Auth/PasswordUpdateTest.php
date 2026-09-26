<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('password can be updated', function () {
    $user = User::factory()->create(['language' => 'en']);

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->put('/password', [
            'current_password' => config('app.default_password'),
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue()
        ->and(session('flash_message_data'))->toBe(['message' => 'User Updated', 'type' => 'success']);
});

test('correct password must be provided to update password', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->put('/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertSessionHasErrors('current_password')
        ->assertRedirect('/profile');
});
