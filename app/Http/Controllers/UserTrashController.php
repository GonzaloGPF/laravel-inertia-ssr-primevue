<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;

/**
 * The trash of the users, which is where the deleted ones are: they are taken out of it (update), or deleted for
 * good (destroy). Only the ones that are in the trash.
 */
class UserTrashController extends Controller
{
    public function update(User $user): RedirectResponse
    {
        Gate::authorize('restore', $user);
        abort_unless($user->trashed(), 404);

        $user->restore();

        $this->flashMessage(tAction('restored', 'user'));

        return Redirect::back();
    }

    /**
     * Deletes the user for good, with its files.
     */
    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('forceDelete', $user);
        abort_unless($user->trashed(), 404);

        $user->forceDelete();

        $this->flashMessage(tAction('force_deleted', 'user'));

        return Redirect::back();
    }
}
