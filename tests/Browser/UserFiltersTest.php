<?php

use App\Enums\Languages;
use App\Enums\Roles;
use App\Models\User;

const FILTERS = '[role="search"]';

/**
 * Number of requests made by the table to get the users filtered by the given param.
 */
function filteredRequests($page, string $param): int
{
    return (int) $page->script("performance.getEntriesByType('resource').filter(entry => entry.name.includes('/admin/users') && entry.name.includes('$param=')).length");
}

/**
 * The decoded search of the current url, as the table writes it.
 */
function urlSearch($page): string
{
    return (string) $page->script('decodeURIComponent(location.search.slice(1))');
}

beforeEach(function () {
    $this->admin = User::factory()->admin()->create([
        'name' => 'Ada Admin',
        'email' => 'ada@example.com',
        'language' => Languages::EN,
    ]);

    // Users are listed from the newest to the oldest: Cara, Bob and Ada
    $this->bob = User::factory()->create(['name' => 'Bob Regular', 'role' => Roles::USER, 'email_verified_at' => now()]);
    $this->cara = User::factory()->create(['name' => 'Cara Regular', 'role' => Roles::USER, 'email_verified_at' => null]);

    $this->actingAs($this->admin);
});

test('the table renders the filters', function () {
    $page = visit(route('users.index', absolute: false));

    $page->assertSee('Filters')
        ->assertPresent(FILTERS.' input[name="name"]')
        ->assertPresent(FILTERS.' input[name="email"]')
        ->assertPresent(FILTERS.' input[name="phone"]')
        ->assertPresent(FILTERS.' [name="language"]')
        ->assertPresent(FILTERS.' [name="currency"]')
        ->assertPresent(FILTERS.' [name="verified"]')
        ->assertPresent(FILTERS.' .p-multiselect')
        ->assertPresent(FILTERS.' button[aria-label="Refresh"]')
        ->assertPresent(FILTERS.' button[aria-label="Erase"]')
        ->assertNoJavaScriptErrors();

    expectRows($page, 3);
});

test('the filters are empty by default, without any boolean filter selected', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $page->assertQueryStringMissing('verified')
        ->assertQueryStringMissing('name')
        ->assertDontSee('Filtering by');
});

test('typing in a filter filters the users and writes it in the url', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $page->type(FILTERS.' input[name="name"]', 'Cara')
        ->assertQueryStringHas('name', 'Cara')
        ->assertSee('Filtering by Name')
        ->assertNoJavaScriptErrors();

    expectRows($page, 1);

    $page->assertSee('Cara Regular')
        ->assertDontSee('Bob Regular');
});

test('typing fast in a filter makes just one request', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $page->typeSlowly(FILTERS.' input[name="name"]', 'Regular', 40)
        ->assertQueryStringHas('name', 'Regular');

    expectRows($page, 2);

    expect(filteredRequests($page, 'name'))->toBe(1);
});

test('the filters are initialised from the url query', function () {
    $page = visit(route('users.index', [
        'name' => 'Cara',
        'role' => ['user'],
        'verified' => 0,
    ], false));

    expectRows($page, 1);

    $page->assertValue(FILTERS.' input[name="name"]', 'Cara')
        ->assertSeeIn(FILTERS.' [name="verified"]', 'No')
        ->assertSee('Cara Regular')
        ->assertSee('Filtering by Name, Role, Verified')
        ->assertNoJavaScriptErrors();
});

test('a multiple filter sends all the selected values', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $page->click(FILTERS.' .p-multiselect')
        ->click('li[aria-label="Admin"]')
        ->assertQueryStringHas('role');

    expect(urlSearch($page))->toBe('role[]=admin');

    expectRows($page, 1);

    $page->assertSee('Ada Admin')
        ->click('li[aria-label="User"]')
        ->assertNoJavaScriptErrors();

    expectRows($page, 3);

    expect(urlSearch($page))->toBe('role[]=admin&role[]=user');
});

test('a multiple filter offers just its options, without a select all', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $page->click(FILTERS.' .p-multiselect');

    $options = $page->script("[...document.querySelectorAll('li[role=option]')].map(e => e.getAttribute('aria-label')).join(',')");
    $toggleAll = $page->script("document.querySelectorAll('.p-multiselect-header .p-checkbox').length");

    expect($options)->toBe('Admin,User')
        ->and($toggleAll)->toBe(0);
});

test('the boolean filter distinguishes yes from no', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $page->click(FILTERS.' [name="verified"]')
        ->click('li[aria-label="No"]')
        ->assertQueryStringHas('verified', '0');

    expectRows($page, 1);

    $page->assertSee('Cara Regular');
});

test('the select filters filter by their value', function () {
    $this->cara->forceFill(['language' => Languages::ES])->saveQuietly();
    $this->bob->forceFill(['language' => Languages::EN])->saveQuietly();

    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $page->click(FILTERS.' [name="language"]')
        ->click('li[aria-label="Spanish"]')
        ->assertQueryStringHas('language', 'es');

    expectRows($page, 1);

    $page->assertSee('Cara Regular');
});

test('changing a filter goes back to the first page and keeps the page size', function () {
    User::factory(30)->create(['name' => 'Extra Person']);

    $page = visit(route('users.index', ['page' => 2, 'perPage' => 10], false));

    expectRows($page, 10);

    $page->type(FILTERS.' input[name="name"]', 'Extra')
        ->assertQueryStringHas('name', 'Extra')
        ->assertQueryStringMissing('page')
        ->assertQueryStringHas('perPage', '10');

    expectRows($page, 10);
});

test('the erase button clears the filters and the url', function () {
    $page = visit(route('users.index', ['name' => 'Cara', 'perPage' => 10], false));

    expectRows($page, 1);

    $page->click(FILTERS.' button[aria-label="Erase"]')
        ->assertQueryStringMissing('name')
        ->assertQueryStringHas('perPage', '10')
        ->assertValue(FILTERS.' input[name="name"]', '')
        ->assertDontSee('Filtering by')
        ->assertNoJavaScriptErrors();

    expectRows($page, 3);
});

test('the refresh button requests the users again', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    User::factory()->create(['name' => 'Late Arrival']);

    $page->click(FILTERS.' button[aria-label="Refresh"]')
        ->assertSee('Late Arrival');

    expectRows($page, 4);
});

test('the filters follow the url when going back in the browser', function () {
    User::factory(30)->create(['name' => 'Extra Person']);

    $page = visit(route('users.index', absolute: false));

    expectRows($page, 25);

    $page->type(FILTERS.' input[name="name"]', 'Extra')
        ->assertQueryStringHas('name', 'Extra');

    expectRows($page, 25);

    $page->click('.p-datatable-paginator-bottom .p-paginator-next')
        ->assertQueryStringHas('page', '2');

    expectRows($page, 5);

    $page->fill(FILTERS.' input[name="name"]', 'Cara')
        ->assertQueryStringHas('name', 'Cara');

    expectRows($page, 1);

    // Back to the previous entry, which was filtered by Extra
    $page->script('window.history.back()');

    expectRows($page, 25);

    $page->assertValue(FILTERS.' input[name="name"]', 'Extra')
        ->assertQueryStringHas('name', 'Extra')
        ->assertNoJavaScriptErrors();
});

test('the filters are translated', function () {
    $this->admin->update(['language' => Languages::ES]);

    $page = visit(route('users.index', ['name' => 'Cara'], false));

    $page->assertSee('Filtros')
        ->assertSee('Filtrando por Nombre')
        ->assertPresent(FILTERS.' button[aria-label="Borrar"]')
        ->assertPresent(FILTERS.' button[aria-label="Refrescar"]')
        ->assertNoJavaScriptErrors();
});
