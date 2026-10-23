<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    private const AVATAR_FIELDS = ['avatar', 'remove_avatar'];

    public function show(User $user): Response
    {
        return Inertia::render('Users/UserShow', ['user' => $user]);
    }

    public function index(): Response
    {
        return $this->renderCollection('Users/UserIndex', User::class);
    }

    public function create(): Response
    {
        return Inertia::render('Users/UserCreate');
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $user = User::create(array_merge($request->safe()->except(self::AVATAR_FIELDS), [
            'password' => config('app.default_password'),
        ]));

        $user->syncFile('avatar', $request->file('avatar'), $request->boolean('remove_avatar'));

        $this->flashMessage(tAction('created', 'user'));

        return Redirect::back();
    }

    public function edit(User $user): Response
    {
        return Inertia::render('Users/UserEdit', [
            'user' => fn () => $user,
        ]);
    }

    public function update(User $user, UserRequest $request): RedirectResponse
    {
        $user->update($request->safe()->except(self::AVATAR_FIELDS));

        $user->syncFile('avatar', $request->file('avatar'), $request->boolean('remove_avatar'));

        $this->flashMessage(tAction('updated', 'user'));

        return Redirect::back();
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $user->delete();

        $this->flashMessage(tAction('deleted', 'user'));

        return Redirect::back();
    }
}
