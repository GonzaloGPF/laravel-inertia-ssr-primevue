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

it('sorts users by id descending by default, whatever their creation date', function () {
    $oldest = User::query()->oldest('id')->first();
    $oldest->forceFill(['created_at' => now()->addYear()])->saveQuietly();

    $ids = User::filter([])->pluck('id')->all();

    expect($ids)->toBe(User::query()->orderByDesc('id')->pluck('id')->all());
});

it('sorts users by the given sort input instead of the default order', function () {
    $names = User::filter(['sort' => 'name|asc'])->pluck('name')->all();

    expect($names)->toBe(User::query()->orderBy('name')->pluck('name')->all());
});

it('falls back to the default order when the sort input is malformed', function () {
    $ids = User::filter(['sort' => 'name'])->pluck('id')->all();

    expect($ids)->toBe(User::query()->orderByDesc('id')->pluck('id')->all());
});
