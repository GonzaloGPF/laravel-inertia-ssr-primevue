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

/**
 * Waits for the given amount of rows to be expanded, as the closing of a row is animated.
 */
function expectExpansions($page, int $expected): void
{
    $count = -1;

    for ($attempt = 0; $attempt < 30; $attempt++) {
        $count = expansions($page);

        if ($count === $expected) {
            break;
        }

        usleep(100_000);
    }

    expect($count)->toBe($expected);
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

    expectExpansions($page, 1);
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

test('an expanded row opens growing its height, as it fades in', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    // Samples, frame by frame, the height and the opacity of the row while it opens
    $samples = json_decode($page->script("new Promise(resolve => { const samples = []; const start = performance.now(); document.querySelector('.p-datatable-row-toggle-button').click(); const tick = () => { const row = document.querySelector('.p-datatable-row-expansion'); if (row) { samples.push({ height: row.getBoundingClientRect().height, opacity: Number(getComputedStyle(row.querySelector('.animate-expand')).opacity) }) } if (performance.now() - start < 700) { requestAnimationFrame(tick) } else { resolve(JSON.stringify(samples)) } }; requestAnimationFrame(tick) })"), true);

    $heights = array_column($samples, 'height');
    $opacities = array_column($samples, 'opacity');
    $natural = end($heights);

    expect($samples)->not->toBeEmpty()
        ->and($heights[0])->toBeLessThan($natural / 2) // It starts almost closed
        ->and($heights)->toBe(collect($heights)->sort()->values()->all()) // And only grows
        ->and(count(array_unique($heights)))->toBeGreaterThan(3) // Through several heights, not at once
        ->and($opacities[0])->toBeLessThan(1)
        ->and(end($opacities))->toEqual(1);
});

test('an expanded row closes shrinking its height, as it fades out, and is then removed', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    toggleRow($page, 0);

    $page->assertPresent(INFO);

    usleep(600_000); // Its opening has ended

    $samples = json_decode($page->script("new Promise(resolve => { const samples = []; const start = performance.now(); document.querySelector('.p-datatable-row-toggle-button').click(); const tick = () => { const row = document.querySelector('.p-datatable-row-expansion'); samples.push(row ? { height: row.getBoundingClientRect().height, opacity: Number(getComputedStyle(row.querySelector('.animate-collapse')).opacity) } : null); if (performance.now() - start < 700) { requestAnimationFrame(tick) } else { resolve(JSON.stringify(samples)) } }; requestAnimationFrame(tick) })"), true);

    $closing = array_values(array_filter($samples));
    $heights = array_column($closing, 'height');
    $opacities = array_column($closing, 'opacity');

    expect($closing)->not->toBeEmpty()
        ->and(max($heights))->toBeGreaterThan(80) // It starts open
        ->and($heights)->toBe(collect($heights)->sortDesc()->values()->all()) // And only shrinks
        ->and(count(array_unique($heights)))->toBeGreaterThan(3) // Through several heights, not at once
        ->and($opacities)->toBe(collect($opacities)->sortDesc()->values()->all())
        ->and(min($heights))->toBeLessThan(max($heights) / 2) // Until it is almost closed, depending on the frames drawn
        ->and(end($samples))->toBeNull(); // And then the row is removed

    expect(expansions($page))->toBe(0);
});

test('the toggle says what it does and a closed row can be opened again', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $state = fn () => (string) $page->script("(() => { const button = document.querySelector('.p-datatable-row-toggle-button'); return button.getAttribute('aria-label') + ':' + button.getAttribute('aria-expanded') })()");

    expect($state())->toBe('Expand:false');

    toggleRow($page, 0);

    $page->assertPresent(INFO);

    expect($state())->toBe('Collapse:true');

    toggleRow($page, 0);
    expectExpansions($page, 0);

    expect($state())->toBe('Expand:false');

    toggleRow($page, 0);

    $page->assertPresent(INFO)
        ->assertNoJavaScriptErrors();
});

test('clicking a row that is closing does not reopen it', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    toggleRow($page, 0);

    $page->assertPresent(INFO);

    usleep(600_000);

    $page->script("(() => { const button = document.querySelector('.p-datatable-row-toggle-button'); button.click(); button.click() })()");

    expectExpansions($page, 0);
});
