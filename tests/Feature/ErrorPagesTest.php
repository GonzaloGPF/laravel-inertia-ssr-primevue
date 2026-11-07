<?php

use App\Enums\Roles;
use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Testing\AssertableInertia as Assert;

// How the page of Inertia starts: the pages of Laravel, with their source code on, may mention data-page
const INERTIA_PAGE = '<script data-page="app" type="application/json">';

beforeEach(function () {
    config(['app.debug' => false]);

    Route::middleware('web')->group(function () {
        Route::get('/_test/boom', fn () => throw new RuntimeException('boom'));
        Route::get('/_test/status/{status}', fn (int $status) => abort($status));
        Route::post('/_test/expired', fn () => throw new TokenMismatchException);
    });
});

it('shows the error page of the statuses that have one', function (string $url, int $status) {
    $this->get($url)
        ->assertStatus($status)
        ->assertSee(INERTIA_PAGE, false) // What the tests that look for no page rely on
        ->assertInertia(fn (Assert $page) => $page
            ->component('Error/ErrorPage', false)
            ->where('status', $status));
})->with([
    'forbidden' => ['/_test/status/403', 403],
    'a page that does not exist' => ['/nothing-here', 404],
    'too many requests' => ['/_test/status/429', 429],
    'an exception' => ['/_test/boom', 500],
    'a server error' => ['/_test/status/500', 500],
    'not available' => ['/_test/status/503', 503],
]);

it('forbids with the error page, and the menu of the application knows who is logged', function () {
    $user = $this->signIn(User::factory()->create(['role' => Roles::USER]));

    $this->get(route('users.index'))
        ->assertForbidden()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Error/ErrorPage', false)
            ->where('status', 403)
            ->where('auth.user.id', $user->id)
            ->has('ziggy'));
});

it('answers the visits of Inertia with the error page too', function () {
    $version = (string) app(HandleInertiaRequests::class)->version(Request::create('/'));

    $this->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => $version])
        ->get('/nothing-here')
        ->assertNotFound()
        ->assertHeader('X-Inertia', 'true')
        ->assertJsonPath('component', 'Error/ErrorPage')
        ->assertJsonPath('props.status', 404);
});

it('leaves the errors of the requests that ask for JSON as JSON', function () {
    $this->getJson('/nothing-here')
        ->assertNotFound()
        ->assertJsonStructure(['message'])
        ->assertJsonMissingPath('component');

    $this->getJson('/_test/status/403')
        ->assertForbidden()
        ->assertJsonMissingPath('component');
});

it('leaves the server errors to the page of Laravel when debugging, as it tells what happened', function () {
    config(['app.debug' => true]);

    $this->get('/_test/boom')
        ->assertStatus(500)
        ->assertDontSee(INERTIA_PAGE, false)
        ->assertSee('boom');
});

it('leaves the statuses that have no page as they are', function () {
    $this->get('/_test/status/418')
        ->assertStatus(418)
        ->assertDontSee(INERTIA_PAGE, false);
});

it('goes back to the page that expired, with a notice, so it can be tried again', function () {
    $this->from('/about')
        ->post('/_test/expired')
        ->assertRedirect('/about')
        ->assertSessionHas('flash_message_data.type', 'warning')
        ->assertSessionHas('flash_message_data.message', trans('exceptions.page_expired'));
});

it('falls back to the page of Laravel when the error page cannot be built', function () {
    Inertia::share('explode', fn () => throw new RuntimeException('the shared data failed too'));

    $this->get('/nothing-here')
        ->assertNotFound()
        ->assertDontSee(INERTIA_PAGE, false);
});
