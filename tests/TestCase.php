<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Helper for signing in a user. If no user is provided it will log in an Admin user.
     *
     * @param  null  $user
     */
    protected function signIn($user = null, $attributes = [], ?string $guard = null): User
    {
        /** @var User $user */
        $user = $user ?: User::factory()->admin()->create($attributes);

        $this->actingAs($user, $guard);

        return $user;
    }

    protected function logout(?string $guard = null): void
    {
//        User::flushEventListeners();

        auth($guard)->logout();
    }
}
