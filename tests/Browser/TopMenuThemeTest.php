<?php

use App\Enums\Languages;
use App\Models\User;

const THEME_BUTTON = '[aria-label="Theme"]';

/**
 * The theme the page is in: if it is the dark one, what the user chose, the icon of the button and the color of the menu.
 *
 * @return array{dark: bool, stored: ?string, icon: ?string, menu: string}
 */
function themeState($page): array
{
    // The button is there once the page is mounted
    for ($attempt = 0; $attempt < 50 && ! $page->script("!!document.querySelector('[aria-label=\"Theme\"]')"); $attempt++) {
        usleep(100_000);
    }

    return json_decode((string) $page->script(<<<'JS'
        JSON.stringify({
            dark: document.documentElement.classList.contains('app-dark'),
            stored: localStorage.getItem('theme'),
            icon: document.querySelector('[aria-label="Theme"] .material-symbols-outlined')?.textContent.trim() ?? null,
            menu: getComputedStyle(document.querySelector('.p-menubar')).backgroundColor,
        })
    JS), true);
}

/**
 * If a color, as the browser gives it (rgb), is a dark one.
 */
function isDarkColor(string $color): bool
{
    preg_match_all('/\d+/', $color, $channels);

    return array_sum(array_slice($channels[0], 0, 3)) / 3 < 128;
}

/**
 * Chooses a theme in the menu of the button.
 */
function chooseTheme($page, string $theme): void
{
    $page->click(THEME_BUTTON)
        ->click('li[aria-label="'.$theme.'"]');
}

test('the theme is the one of the system until another is chosen', function () {
    $light = themeState(visit('/'));
    $dark = themeState(visit('/')->inDarkMode());

    expect($light['dark'])->toBeFalse()
        ->and($light['stored'])->toBeNull()
        ->and($light['icon'])->toBe('contrast') // The one of the system
        ->and(isDarkColor($light['menu']))->toBeFalse()
        ->and($dark['dark'])->toBeTrue()
        ->and($dark['stored'])->toBeNull()
        ->and(isDarkColor($dark['menu']))->toBeTrue();
});

test('choosing the dark theme in a light system, and it is kept', function () {
    $page = visit('/');

    chooseTheme($page, 'Dark');

    $chosen = themeState($page);

    expect($chosen['dark'])->toBeTrue()
        ->and($chosen['stored'])->toBe('dark')
        ->and($chosen['icon'])->toBe('dark_mode')
        ->and(isDarkColor($chosen['menu']))->toBeTrue();

    $page->refresh();

    $kept = themeState($page);

    expect($kept['dark'])->toBeTrue()
        ->and($kept['icon'])->toBe('dark_mode')
        ->and(isDarkColor($kept['menu']))->toBeTrue();

    $page->assertNoJavaScriptErrors();
});

test('choosing the light theme in a dark system, and it is kept', function () {
    $page = visit('/')->inDarkMode();

    chooseTheme($page, 'Light');

    $chosen = themeState($page);

    expect($chosen['dark'])->toBeFalse()
        ->and($chosen['stored'])->toBe('light')
        ->and($chosen['icon'])->toBe('light_mode')
        ->and(isDarkColor($chosen['menu']))->toBeFalse();

    $page->refresh();

    expect(themeState($page))->dark->toBeFalse()
        ->icon->toBe('light_mode');
});

test('the system theme is followed again when it is chosen after another', function (bool $systemIsDark) {
    $page = $systemIsDark ? visit('/')->inDarkMode() : visit('/');

    chooseTheme($page, $systemIsDark ? 'Light' : 'Dark');
    chooseTheme($page, 'System');

    expect(themeState($page))->dark->toBe($systemIsDark)
        ->stored->toBe('system')
        ->icon->toBe('contrast');
})->with(['a dark system' => true, 'a light system' => false]);

test('a theme that is not valid in the storage is the one of the system', function () {
    $page = visit('/');

    $page->script("localStorage.setItem('theme', 'purple')");
    $page->refresh();

    expect(themeState($page))->dark->toBeFalse()
        ->icon->toBe('contrast');
});

test('the dark variants of Tailwind follow the theme chosen, as the components of PrimeVue do', function () {
    $this->actingAs(User::factory()->create(['language' => Languages::EN]));

    $page = visit(route('profile.edit', absolute: false));

    // The title of the danger zone is text-red-600 in the light theme, and dark:text-red-400 in the dark one
    $color = fn () => (string) $page->script("getComputedStyle(document.querySelector('.p-card-title .text-red-600')).color");

    $light = $color();

    chooseTheme($page, 'Dark');

    $dark = $light;

    for ($attempt = 0; $attempt < 30 && $dark === $light; $attempt++) {
        usleep(100_000);
        $dark = $color();
    }

    expect($dark)->not->toBe($light);

    chooseTheme($page, 'Light');

    expect($color())->toBe($light);
});

test('the theme is in the menu of the users who are logged too, next to theirs', function () {
    $this->actingAs(User::factory()->admin()->create(['name' => 'Jane Doe', 'language' => Languages::EN]));

    $page = visit('/');

    chooseTheme($page, 'Dark');

    expect(themeState($page))->dark->toBeTrue();

    $page->click('Jane Doe')
        ->assertSee('Profile')
        ->assertNoJavaScriptErrors();
});

test('the theme is translated', function () {
    $this->actingAs(User::factory()->create(['language' => Languages::ES]));

    $page = visit('/');

    $page->click('[aria-label="Tema"]')
        ->assertPresent('li[aria-label="Claro"]')
        ->assertPresent('li[aria-label="Oscuro"]')
        ->assertPresent('li[aria-label="Sistema"]')
        ->assertNoJavaScriptErrors();
});
