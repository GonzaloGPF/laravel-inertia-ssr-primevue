<?php

namespace App\Http\Controllers;

use App\Http\Requests\LanguageRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class LanguageController extends Controller
{
    /**
     * The language of who is not logged is kept in the session, and the one of a user is theirs, so it is also
     * saved in their profile.
     */
    public function update(LanguageRequest $request): RedirectResponse
    {
        $language = $request->validated('language');

        $request->session()->put('language', $language);
        $request->user()?->update(['language' => $language]);

        return Redirect::back();
    }
}
