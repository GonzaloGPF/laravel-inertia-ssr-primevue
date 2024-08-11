<?php

use App\Enums\Roles;
use App\Models\User;

beforeEach(function () {
    create(User::class, [], 3); // Extra users
});

it('can filter users by role', function () {
    User::query()->update(['role' => Roles::USER]);

    create(User::class, ['role' => Roles::ADMIN]);

    $data = User::filter(['role' => [Roles::ADMIN->value]])->get();

    expect($data)->toHaveCount(1);
});

it('can filter users by verified', function () {
    User::query()->update(['email_verified_at' => null]);

    create(User::class, ['email_verified_at' => today()]);

    $data = User::filter(['verified' => true])->get();
    expect($data)->toHaveCount(1);

    $data = User::filter(['verified' => false])->get();
    expect($data)->toHaveCount(3);
});

it('can filter users by email', function () {
    create(User::class, ['email' => 'test@mail.com']);

    $data = User::filter(['email' => 'test'])->get();

    expect($data)->toHaveCount(1);
});

it('can filter users by phone', function () {
    create(User::class, ['phone' => '666666666']);

    $data = User::filter(['phone' => '666666'])->get();

    expect($data)->toHaveCount(1);
});
