<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The one place where users sign in and out on the web guard, whichever the way they come in: the login form, the
 * registration, or any other that is added. The guard renews the id of the session and its CSRF token on every
 * sign-in, so an id someone saw or planted before is worthless afterwards.
 */
class AuthService
{
    public function login(User $user, bool $remember = false): void
    {
        Auth::guard('web')->login($user, $remember);
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    public function attempt(array $credentials, bool $remember = false): bool
    {
        return Auth::guard('web')->attempt($credentials, $remember);
    }

    /**
     * Signs the user out and throws away the session, so nothing of it is left for the next one.
     */
    public function logout(Request $request): void
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
