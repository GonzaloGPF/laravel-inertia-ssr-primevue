<?php

use App\Enums\Languages;
use App\Models\User;

/**
 * The language the page is in, as the `html` element says it.
 */
function pageLanguage($page): string
{
    return (string) $page->script('document.documentElement.lang');
}

/**
 * Chooses a language in the menu of the button, which is named in the language of the page.
 */
function chooseLanguage($page, string $language, string $button = 'Language'): void
{
    $page->click('[aria-label="'.$button.'"]')
        ->click('li[aria-label="'.$language.'"]');
}

/**
 * Waits for the page to be in the given language.
 */
function expectPageLanguage($page, string $expected): void
{
    $language = '';

    for ($attempt = 0; $attempt < 50 && $language !== $expected; $attempt++) {
        $language = pageLanguage($page);
        usleep(100_000);
    }

    expect($language)->toBe($expected);
}

test('a guest chooses a language, the page is translated at once, and it is kept', function () {
    config(['app.locale' => 'en']);

    $page = visit('/');

    $page->assertSee('Enter');

    chooseLanguage($page, 'Spanish');

    $page->assertSee('Entrar');

    expectPageLanguage($page, 'es');

    $page->refresh();

    $page->assertSee('Entrar')
        ->assertNoJavaScriptErrors();

    expect(pageLanguage($page))->toBe('es');

    chooseLanguage($page, 'Inglés', 'Lenguaje'); // The names are in the language of the page

    $page->assertSee('Enter');

    expectPageLanguage($page, 'en');
});

test('a page that is not the first one is translated too', function () {
    config(['app.locale' => 'en']);

    $page = visit('/login');

    $page->assertSee('Email');

    chooseLanguage($page, 'Spanish');

    $page->assertSee('Correo Electrónico')
        ->assertNoJavaScriptErrors();
});

test('a user chooses a language, and it is saved in their profile', function () {
    $user = User::factory()->create(['name' => 'Jane Doe', 'language' => Languages::EN]);
    $this->actingAs($user);

    $page = visit('/');

    $page->click('Jane Doe')
        ->assertSee('Profile');

    chooseLanguage($page, 'Spanish');

    expectPageLanguage($page, 'es');

    for ($attempt = 0; $attempt < 30 && $user->fresh()->language !== Languages::ES; $attempt++) {
        usleep(100_000);
    }

    expect($user->fresh()->language)->toBe(Languages::ES);

    $page->refresh();

    expect(pageLanguage($page))->toBe('es');

    $page->click('Jane Doe')
        ->assertSee('Perfil')
        ->assertNoJavaScriptErrors();
});

test('the languages are named in the language of the page, and the one in use is marked', function () {
    config(['app.locale' => 'en']);

    $page = visit('/');

    $page->click('[aria-label="Language"]')
        ->assertPresent('li[aria-label="English"]')
        ->assertPresent('li[aria-label="Spanish"]');

    expect($page->script("document.querySelector('.p-menu-item.font-semibold, li.font-semibold')?.getAttribute('aria-label')"))->toBe('English');

    $page->click('li[aria-label="Spanish"]');

    expectPageLanguage($page, 'es');

    $page->click('[aria-label="Lenguaje"]')
        ->assertPresent('li[aria-label="Español"]')
        ->assertPresent('li[aria-label="Inglés"]');

    expect($page->script("document.querySelector('li.font-semibold')?.getAttribute('aria-label')"))->toBe('Español');
});

test('the language is not lost when the theme is chosen, and the other way round', function () {
    config(['app.locale' => 'en']);

    $page = visit('/');

    chooseLanguage($page, 'Spanish');

    expectPageLanguage($page, 'es');

    $page->click('[aria-label="Tema"]')
        ->click('li[aria-label="Oscuro"]');

    expect($page->script("document.documentElement.classList.contains('app-dark')"))->toBeTrue()
        ->and(pageLanguage($page))->toBe('es');

    $page->assertNoJavaScriptErrors();
});

test('each menu of the top bar has its own id', function () {
    config(['app.locale' => 'en']);

    $page = visit('/');

    $page->assertSee('Enter');

    $menuId = fn () => (string) $page->script("[...document.querySelectorAll('[role=\"menu\"]')].at(-1)?.closest('[id]')?.id");

    $page->click('[aria-label="Language"]');
    $languageMenu = $menuId();

    // The menu of the language is closing while the one of the theme opens
    $page->click('[aria-label="Theme"]');
    usleep(500_000);
    $themeMenu = $menuId();

    expect($languageMenu)->not->toBeEmpty()
        ->and($themeMenu)->not->toBeEmpty()
        ->and($languageMenu)->not->toBe($themeMenu);
});
