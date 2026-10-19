<?php

use App\Enums\Currencies;
use App\Enums\Languages;
use App\Enums\Roles;
use App\Models\User;

const INFO = '.p-datatable-row-expansion';

/**
 * Expands, or collapses, the row in the given position. The rows are listed from the newest to the oldest.
 */
function toggleRow($page, int $position): void
{
    $page->click(".p-datatable-row-toggle-button >> nth=$position");
}

function expansions($page): int
{
    return (int) $page->script("document.querySelectorAll('.p-datatable-row-expansion').length");
}

beforeEach(function () {
    $this->admin = User::factory()->admin()->create([
        'name' => 'Ada Admin',
        'language' => Languages::EN,
    ]);

    $this->bob = User::factory()->create(['name' => 'Bob Regular', 'email' => 'bob@example.com']);

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

test('the rows have a column to expand them, before the id', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $columns = $page->script("(() => { const headers = [...document.querySelectorAll('.p-datatable-thead th')]; return JSON.stringify({ total: headers.length, first: headers[0].innerText.trim(), second: headers[1].innerText.trim(), toggles: document.querySelectorAll('.p-datatable-row-toggle-button').length }) })()");

    expect(json_decode($columns, true))->toBe(['total' => 8, 'first' => '', 'second' => 'ID', 'toggles' => 3]);

    expect(expansions($page))->toBe(0);
});

test('expanding a row shows all the information of its user', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    toggleRow($page, 0);

    $page->assertPresent(INFO)
        ->assertNoJavaScriptErrors();

    expect(infoPairs($page))->toBe('Name|Cara Regular|Email|cara@example.com|Phone|655443322|Age|20|Role|User|Language|Spanish|Currency|Dollar');
});

test('the information is translated', function () {
    $this->admin->update(['language' => Languages::ES]);

    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    toggleRow($page, 0);

    $page->assertPresent(INFO);

    expect(infoPairs($page))->toBe('Nombre|Cara Regular|Correo Electrónico|cara@example.com|Teléfono|655443322|Edad|20|Rol|Usuario|Lenguaje|Español|Moneda|Dólar');
});

test('each row expands on its own', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    toggleRow($page, 1); // Bob

    $page->assertPresent(INFO);

    expect(expansions($page))->toBe(1)
        ->and(infoPairs($page))->toContain('Bob Regular|Email|bob@example.com')
        ->and(infoPairs($page))->not->toContain('Cara');

    toggleRow($page, 0); // Cara, with Bob still expanded

    expect(expansions($page))->toBe(2);

    toggleRow($page, 1); // Collapses Cara, the second one is now the row of Bob

    expect(expansions($page))->toBe(1);
});

test('the empty values are shown with a dash', function () {
    $this->bob->forceFill(['phone' => null, 'age' => null, 'language' => null, 'currency' => null])->saveQuietly();

    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    toggleRow($page, 1);

    $page->assertPresent(INFO);

    expect(infoPairs($page))->toBe('Name|Bob Regular|Email|bob@example.com|Phone|-|Age|-|Role|'.infoRole($this->bob).'|Language|-|Currency|-');
});

test('the values are shown as text, never as html', function () {
    $this->cara->forceFill(['name' => '<b>Bold</b> <img src=x onerror="window.__xss = true">'])->saveQuietly();

    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    toggleRow($page, 0);

    $page->assertPresent(INFO)
        ->assertNoJavaScriptErrors();

    $rendered = $page->script("(() => ({ markup: document.querySelectorAll('.p-datatable-row-expansion b, .p-datatable-row-expansion img').length, xss: window.__xss === true }))()");

    // The model capitalizes the words of the names
    expect(infoPairs($page))->toContain('<B>Bold</B> <Img Src=X Onerror="Window.__Xss = True">')
        ->and($rendered)->toBe(['markup' => 0, 'xss' => false]);
});

test('an expanded row shows the new data when its user is edited', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    toggleRow($page, 0);

    $page->assertPresent(INFO)
        ->click(rowAction(1, 'Edit'))
        ->fill('[data-pc-name="dialog"] input[name="name"]', 'Cara Edited')
        ->click('[data-pc-name="dialog"] button[type="submit"]')
        ->assertDontSee('Edit User');

    $page->assertSee('Cara Edited');

    expect(expansions($page))->toBe(1)
        ->and(infoPairs($page))->toContain('Name|Cara Edited');
});

/**
 * The role as the info shows it.
 */
function infoRole(User $user): string
{
    return $user->role === Roles::ADMIN ? 'Admin' : 'User';
}
