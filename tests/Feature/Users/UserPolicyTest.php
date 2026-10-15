<?php

use App\Enums\Roles;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->regular = User::factory()->create(['role' => Roles::USER]);
    $this->other = User::factory()->create(['role' => Roles::USER]);
});

it('lets everybody view users', function () {
    expect($this->admin->can('viewAny', User::class))->toBeTrue()
        ->and($this->regular->can('viewAny', User::class))->toBeTrue()
        ->and($this->admin->can('view', $this->other))->toBeTrue()
        ->and($this->regular->can('view', $this->other))->toBeTrue();
});

it('lets only admins create users', function () {
    expect($this->admin->can('create', User::class))->toBeTrue()
        ->and($this->regular->can('create', User::class))->toBeFalse();
});

it('lets admins update any user, and the rest just themselves', function () {
    expect($this->admin->can('update', $this->other))->toBeTrue()
        ->and($this->admin->can('update', $this->admin))->toBeTrue()
        ->and($this->regular->can('update', $this->regular))->toBeTrue()
        ->and($this->regular->can('update', $this->other))->toBeFalse();
});

it('lets only admins delete other users', function () {
    expect($this->admin->can('delete', $this->other))->toBeTrue()
        ->and($this->regular->can('delete', $this->other))->toBeFalse();
});

it('lets nobody delete themselves', function () {
    expect($this->admin->can('delete', $this->admin))->toBeFalse()
        ->and($this->regular->can('delete', $this->regular))->toBeFalse();
});

it('explains why a user cannot delete themselves', function () {
    $response = Gate::forUser($this->admin)->inspect('delete', $this->admin);

    expect($response->denied())->toBeTrue()
        ->and($response->message())->toBe(trans('exceptions.cannot_delete_yourself'));
});

it('lets only admins restore and force delete users', function () {
    expect($this->admin->can('restore', $this->other))->toBeTrue()
        ->and($this->admin->can('forceDelete', $this->other))->toBeTrue()
        ->and($this->regular->can('restore', $this->other))->toBeFalse()
        ->and($this->regular->can('forceDelete', $this->other))->toBeFalse();
});
