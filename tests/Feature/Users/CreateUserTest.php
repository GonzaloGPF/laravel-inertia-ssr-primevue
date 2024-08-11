<?php

use App\Enums\Currencies;
use App\Enums\Languages;
use App\Enums\Roles;
use App\Models\User;

beforeEach(function () {
    $this->user = $this->signIn();
});

it('wont let access to create if not logged', function () {
    $this->logout();

    $this->getJson(route('users.create'))
        ->assertRedirect();
});

it('will not allow access to create if you are not an admin', function () {
    $this->user
        ->forceFill(['role' => Roles::USER])
        ->saveQuietly();

    $this->get(route('users.create'))
        ->assertRedirect();
});

it('will allow access to create if its admin', function () {
    $this->get(route('users.create'))
        ->assertSuccessful();
});

it('let admin create users', function () {
    $this->post(route('users.store'), [
        'name' => 'Some Name',
        'email' => 'some@email.com',
        'phone' => '666666666',
        'role' => Roles::USER->value,
        'language' => Languages::EN->value,
        'currency' => Currencies::EURO->value,
    ])->assertRedirect();

    /** @var User $user */
    $user = User::firstWhere('email', 'some@email.com');

    expect($user->name)->toEqual('Some Name')
        ->and($user->email)->toEqual('some@email.com')
        ->and($user->phone)->toEqual('666666666')
        ->and($user->role->value)->toEqual('user')
        ->and($user->language->value)->toEqual('en')
        ->and($user->currency->value)->toEqual('euro');
});
