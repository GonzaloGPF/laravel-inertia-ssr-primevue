<?php

use App\Enums\Languages;
use App\Models\User;

it('keeps in the session the language that someone who is not logged chooses', function () {
    $this->from('/about')
        ->put(route('language.update'), ['language' => 'es'])
        ->assertRedirect('/about')
        ->assertSessionHas('language', 'es');
});

it('saves in the profile the language that a user chooses, besides keeping it in the session', function () {
    $user = $this->signIn(User::factory()->create(['language' => Languages::EN]));

    $this->put(route('language.update'), ['language' => 'es'])
        ->assertSessionHas('language', 'es');

    expect($user->fresh()->language)->toBe(Languages::ES);
});

it('does not take a language that is not one of the languages', function (mixed $language) {
    $user = $this->signIn(User::factory()->create(['language' => Languages::EN]));

    $this->put(route('language.update'), ['language' => $language])
        ->assertSessionHasErrors('language')
        ->assertSessionMissing('language');

    expect($user->fresh()->language)->toBe(Languages::EN);
})->with([
    'another language' => 'fr',
    'nothing' => '',
    'a list' => [['es']],
]);

it('shows the pages in the language chosen in the session', function () {
    $this->withSession(['language' => 'es'])
        ->get('/')
        ->assertSee('<html lang="es"', false);
});

it('shows the pages in the language of the application until one is chosen', function () {
    config(['app.locale' => 'en']);

    $this->get('/')->assertSee('<html lang="en"', false);
});

it('shows the pages to a user in the language of their profile, not in the one of the session', function () {
    $this->signIn(User::factory()->create(['language' => Languages::EN]));

    $this->withSession(['language' => 'es'])
        ->get('/about')
        ->assertSee('<html lang="en"', false);
});

it('gives who registers the language that was in use', function (?string $chosen, string $expected) {
    config(['app.locale' => 'en']);

    $this->withSession(array_filter(['language' => $chosen]))
        ->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

    expect(User::firstWhere('email', 'test@example.com')->language->value)->toBe($expected);
})->with([
    'the one chosen' => ['es', 'es'],
    'another one chosen' => ['en', 'en'],
    'the one of the application, when none was chosen' => [null, 'en'],
]);
