<?php

use App\Enums\Currencies;
use App\Enums\Languages;
use App\Enums\Roles;
use App\Models\User;

const DETAIL = '.p-card';
const INFO_CARD = '.p-card-content';

beforeEach(function () {
    $this->admin = User::factory()->admin()->create([
        'name' => 'Ada Admin',
        'language' => Languages::EN,
    ]);

    $this->bob = User::factory()->create([
        'name' => 'Bob Regular',
        'email' => 'bob@example.com',
        'phone' => '611223344',
        'age' => 45,
        'role' => Roles::USER,
        'language' => Languages::EN,
        'currency' => Currencies::EURO,
    ]);

    // Listed first, as the newest one
    $this->cara = User::factory()->create([
        'name' => 'Cara Regular',
        'email' => 'cara@example.com',
        'phone' => '655443322',
        'age' => 20,
        'role' => Roles::USER,
        'language' => Languages::ES,
        'currency' => Currencies::DOLLAR,
    ]);

    $this->actingAs($this->admin);
});

test('the view action opens the detail of the user of its row', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $page->click(rowAction(1, 'View'))
        ->assertPathIs(route('users.show', $this->cara, false))
        ->assertPresent(INFO_CARD)
        ->assertSee('Cara Regular')
        ->assertNoJavaScriptErrors();

    expect(infoPairs($page, 0, INFO_CARD))->toBe('Name|Cara Regular|Email|cara@example.com|Phone|655443322|Age|20|Role|User|Language|Spanish|Currency|Dollar');
});

test('each row opens its own user', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $page->click(rowAction(2, 'View'))
        ->assertPathIs(route('users.show', $this->bob, false))
        ->assertSee('bob@example.com')
        ->assertDontSee('cara@example.com');
});

test('the detail can be opened directly with its url', function () {
    $page = visit(route('users.show', $this->bob, false));

    $page->assertPresent(INFO_CARD)
        ->assertSee('Bob Regular')
        ->assertSee('bob@example.com')
        ->assertNoJavaScriptErrors();

    expect(infoPairs($page, 0, INFO_CARD))->toContain('Phone|611223344|Age|45');
});

test('the detail is translated', function () {
    $this->admin->update(['language' => Languages::ES]);

    $page = visit(route('users.show', $this->cara, false));

    $page->assertPresent(INFO_CARD)
        ->assertSee('Cara Regular')
        ->assertPresent(DETAIL.' button[aria-label="Regresar"]')
        ->assertNoJavaScriptErrors();

    expect(infoPairs($page, 0, INFO_CARD))->toBe('Nombre|Cara Regular|Correo Electrónico|cara@example.com|Teléfono|655443322|Edad|20|Rol|Usuario|Lenguaje|Español|Moneda|Dólar');
});

test('the back button goes to the list of users', function () {
    $page = visit(route('users.show', $this->bob, false));

    $page->assertSee('Bob Regular')
        ->click(DETAIL.' button[aria-label="Back"]')
        ->assertPathIs(route('users.index', absolute: false))
        ->assertNoJavaScriptErrors();

    expectRows($page, 3);
});

test('going from one user to another never shows the data of the previous one', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $page->click(rowAction(1, 'View')) // Cara
        ->assertSee('cara@example.com')
        ->click(DETAIL.' button[aria-label="Back"]');

    expectRows($page, 3);

    $page->click(rowAction(2, 'View')) // Bob
        ->assertPresent(INFO_CARD)
        ->assertSee('bob@example.com')
        ->assertDontSee('cara@example.com')
        ->assertNoJavaScriptErrors();

    expect(infoPairs($page, 0, INFO_CARD))->toContain('Name|Bob Regular|Email|bob@example.com');
});

test('a user without optional data is shown with dashes', function () {
    $this->bob->forceFill(['phone' => null, 'age' => null])->saveQuietly();

    $page = visit(route('users.show', $this->bob, false));

    $page->assertPresent(INFO_CARD)
        ->assertSee('Bob Regular');

    expect(infoPairs($page, 0, INFO_CARD))->toContain('Phone|-|Age|-');
});

test('a regular user cannot open the detail of a user', function () {
    $this->actingAs($this->bob);

    $page = visit(route('users.show', $this->cara, false));

    $page->assertPathIs('/')
        ->assertDontSee('cara@example.com');
});

test('the detail sets the title of the page with the name of the user', function () {
    $page = visit(route('users.show', $this->bob, false));

    $page->assertPresent(INFO_CARD)
        ->assertTitleContains('Bob Regular')
        ->assertNoJavaScriptErrors();
});

test('the back button is placed before the title, in the toolbar of the card', function () {
    $page = visit(route('users.show', $this->bob, false));

    $page->assertPresent(INFO_CARD);

    $positions = $page->script("(() => { const card = document.querySelector('.p-card-title'); const back = card.querySelector('button').getBoundingClientRect(); const title = [...card.querySelectorAll('div')].find(div => div.textContent.trim() === 'Bob Regular').getBoundingClientRect(); return JSON.stringify({ backFirst: back.right <= title.left + 1, sameLine: Math.abs((back.top + back.bottom) / 2 - (title.top + title.bottom) / 2) < 12 }) })()");

    expect(json_decode($positions, true))->toBe(['backFirst' => true, 'sameLine' => true]);
});

test('the actions of the toolbar are icon only buttons by default', function () {
    $page = visit(route('users.show', $this->bob, false));

    $page->assertPresent(INFO_CARD);

    $back = $page->script("(() => { const button = document.querySelector('.p-card-title button'); const rect = button.getBoundingClientRect(); return JSON.stringify({ iconOnly: button.classList.contains('p-button-icon-only'), label: button.querySelectorAll('.p-button-label').length, title: button.getAttribute('title'), ariaLabel: button.getAttribute('aria-label'), squared: Math.abs(rect.width - rect.height) < 0.5 }) })()");

    expect(json_decode($back, true))->toBe([
        'iconOnly' => true,
        'label' => 0,
        'title' => 'Back',
        'ariaLabel' => 'Back',
        'squared' => true,
    ]);
});
