<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{

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
        /** @var User $user */
        $user = User::create(array_merge($request->validated(), [
            'password' => config('app.default_password'),
        ]));

        $this->flashMessage(tAction('created', 'user'));

        return Redirect::route('users.edit', $user);
    }

    public function edit(User $user): Response
    {
        return Inertia::render('Users/UserEdit', [
            'user' => fn() => $user,
        ]);
    }

    public function update(User $user, UserRequest $request): RedirectResponse
    {
        $user->update($request->validated());

        $this->flashMessage(tAction('updated', 'user'));

        return Redirect::route('users.edit', $user);
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        $this->flashMessage(tAction('deleted', 'user'));

        return Redirect::route('users.index');
    }
}
