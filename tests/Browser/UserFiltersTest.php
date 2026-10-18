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

/**
 * Shows the advanced filters, which are hidden by default.
 */
function openAdvanced($page): void
{
    $page->click(FILTERS.' button[aria-label="Advanced"]');
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

test('the table shows just the global search by default', function () {
    $page = visit(route('users.index', absolute: false));

    $page->assertPresent(FILTERS.' input[name="search"]')
        ->assertPresent(FILTERS.' button[aria-label="Advanced"]')
        ->assertPresent(FILTERS.' button[aria-label="Refresh"]')
        ->assertPresent(FILTERS.' button[aria-label="Erase"]')
        ->assertMissing(FILTERS.' input[name="name"]')
        ->assertMissing(FILTERS.' input[name="email"]')
        ->assertMissing(FILTERS.' input[name="phone"]')
        ->assertMissing(FILTERS.' .p-multiselect')
        ->assertNoJavaScriptErrors();

    expectRows($page, 3);
});

test('the advanced toggle shows and hides the rest of the filters, keeping the search', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    openAdvanced($page);

    $page->assertPresent(FILTERS.' input[name="search"]')
        ->assertPresent(FILTERS.' input[name="name"]')
        ->assertPresent(FILTERS.' input[name="email"]')
        ->assertPresent(FILTERS.' input[name="phone"]')
        ->assertPresent(FILTERS.' [name="language"]')
        ->assertPresent(FILTERS.' [name="currency"]')
        ->assertPresent(FILTERS.' [name="verified"]')
        ->assertPresent(FILTERS.' .p-multiselect');

    openAdvanced($page);

    $page->assertPresent(FILTERS.' input[name="search"]')
        ->assertMissing(FILTERS.' input[name="name"]')
        ->assertMissing(FILTERS.' .p-multiselect')
        ->assertNoJavaScriptErrors();
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

    openAdvanced($page);

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

    openAdvanced($page);

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

    openAdvanced($page);

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

    openAdvanced($page);

    $page->click(FILTERS.' .p-multiselect');

    $options = $page->script("[...document.querySelectorAll('li[role=option]')].map(e => e.getAttribute('aria-label')).join(',')");
    $toggleAll = $page->script("document.querySelectorAll('.p-multiselect-header .p-checkbox').length");

    expect($options)->toBe('Admin,User')
        ->and($toggleAll)->toBe(0);
});

test('the boolean filter distinguishes yes from no', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    openAdvanced($page);

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

    openAdvanced($page);

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

    openAdvanced($page);

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

    openAdvanced($page);

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

    $page->assertPresent(FILTERS.' input[name="search"][placeholder="Buscar"]')
        ->assertPresent(FILTERS.' button[aria-label="Avanzado"]')
        ->assertSee('Filtrando por Nombre')
        ->assertPresent(FILTERS.' button[aria-label="Borrar"]')
        ->assertPresent(FILTERS.' button[aria-label="Refrescar"]')
        ->assertNoJavaScriptErrors();
});

test('the global search filters by several columns and writes it in the url', function () {
    $this->cara->forceFill(['email' => 'cara@searchable.test', 'phone' => '655443322'])->saveQuietly();

    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $page->type(FILTERS.' input[name="search"]', 'searchable')
        ->assertQueryStringHas('search', 'searchable');

    expectRows($page, 1);

    $page->assertSee('Cara Regular')
        ->assertDontSee('Bob Regular')
        ->fill(FILTERS.' input[name="search"]', '655443')
        ->assertQueryStringHas('search', '655443');

    expectRows($page, 1);

    $page->fill(FILTERS.' input[name="search"]', 'bob')
        ->assertQueryStringHas('search', 'bob');

    expectRows($page, 1);

    $page->assertSee('Bob Regular')
        ->assertNoJavaScriptErrors();
});

test('typing fast in the global search makes just one request', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $page->typeSlowly(FILTERS.' input[name="search"]', 'Regular', 40)
        ->assertQueryStringHas('search', 'Regular');

    expectRows($page, 2);

    expect(filteredRequests($page, 'search'))->toBe(1);
});

test('the global search is initialised from the url query', function () {
    $page = visit(route('users.index', ['search' => 'cara'], false));

    expectRows($page, 1);

    $page->assertValue(FILTERS.' input[name="search"]', 'cara')
        ->assertSee('Cara Regular')
        ->assertMissing(FILTERS.' input[name="name"]') // The advanced filters stay hidden
        ->assertNoJavaScriptErrors();
});

test('the global search keeps its value when the advanced filters are toggled', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $page->type(FILTERS.' input[name="search"]', 'Regular')
        ->assertQueryStringHas('search', 'Regular');

    openAdvanced($page);

    $page->assertValue(FILTERS.' input[name="search"]', 'Regular');

    openAdvanced($page);

    $page->assertValue(FILTERS.' input[name="search"]', 'Regular')
        ->assertQueryStringHas('search', 'Regular');

    expectRows($page, 2);
});

test('the global search combines with the advanced filters', function () {
    $this->bob->forceFill(['role' => Roles::ADMIN])->saveQuietly();

    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $page->type(FILTERS.' input[name="search"]', 'Regular')
        ->assertQueryStringHas('search', 'Regular');

    expectRows($page, 2);

    openAdvanced($page);

    $page->click(FILTERS.' .p-multiselect')
        ->click('li[aria-label="Admin"]');

    expectRows($page, 1);

    $page->assertSee('Bob Regular')
        ->assertQueryStringHas('search', 'Regular')
        ->assertNoJavaScriptErrors();

    expect(urlSearch($page))->toBe('search=Regular&role[]=admin');
});

test('the advanced filters are open when the url has some of them', function () {
    $page = visit(route('users.index', ['role' => ['user']], false));

    expectRows($page, 2);

    $page->assertPresent(FILTERS.' input[name="name"]')
        ->assertPresent(FILTERS.' .p-multiselect');
});

test('the filters in use are listed while the advanced filters are hidden', function () {
    $page = visit(route('users.index', ['search' => 'bob', 'role' => ['user']], false));

    expectRows($page, 1);

    openAdvanced($page); // Hidden again

    $page->assertMissing(FILTERS.' input[name="name"]')
        ->assertSee('Filtering by Role') // The search is not listed: it is there to be seen
        ->assertDontSee('Filtering by Search')
        ->assertNoJavaScriptErrors();
});

test('changing the global search goes back to the first page', function () {
    User::factory(30)->create(['name' => 'Extra Person']);

    $page = visit(route('users.index', ['page' => 2, 'perPage' => 10], false));

    expectRows($page, 10);

    $page->type(FILTERS.' input[name="search"]', 'Extra')
        ->assertQueryStringHas('search', 'Extra')
        ->assertQueryStringMissing('page')
        ->assertQueryStringHas('perPage', '10');

    expectRows($page, 10);
});

test('the erase button also clears the global search', function () {
    $page = visit(route('users.index', ['search' => 'cara'], false));

    expectRows($page, 1);

    $page->click(FILTERS.' button[aria-label="Erase"]')
        ->assertQueryStringMissing('search')
        ->assertValue(FILTERS.' input[name="search"]', '')
        ->assertNoJavaScriptErrors();

    expectRows($page, 3);
});

test('the global search follows the url when going back in the browser', function () {
    User::factory(30)->create(['name' => 'Extra Person']);

    $page = visit(route('users.index', absolute: false));

    expectRows($page, 25);

    $page->type(FILTERS.' input[name="search"]', 'Extra')
        ->assertQueryStringHas('search', 'Extra');

    expectRows($page, 25);

    $page->click('.p-datatable-paginator-bottom .p-paginator-next')
        ->assertQueryStringHas('page', '2');

    expectRows($page, 5);

    $page->fill(FILTERS.' input[name="search"]', 'Cara')
        ->assertQueryStringHas('search', 'Cara');

    expectRows($page, 1);

    $page->script('window.history.back()');

    expectRows($page, 25);

    $page->assertValue(FILTERS.' input[name="search"]', 'Extra')
        ->assertNoJavaScriptErrors();
});

describe('range filters', function () {
    beforeEach(function () {
        // Ada is 50, Bob 30 and Cara 20. Bob was created in 2020, the rest now
        $this->admin->forceFill(['age' => 50])->saveQuietly();
        $this->bob->forceFill(['age' => 30, 'created_at' => '2020-06-15 12:00:00'])->saveQuietly();
        $this->cara->forceFill(['age' => 20])->saveQuietly();
    });

    test('the age and creation date ranges are advanced filters', function () {
        $page = visit(route('users.index', absolute: false));

        expectRows($page, 3);

        $page->assertMissing(FILTERS.' input[name="min_age"]');

        openAdvanced($page);

        $page->assertPresent(FILTERS.' input[name="min_age"][placeholder="Min"]')
            ->assertPresent(FILTERS.' input[name="max_age"][placeholder="Max"]')
            ->assertPresent(FILTERS.' .p-datepicker input')
            ->assertSee('Age')
            ->assertSee('Created At')
            ->assertNoJavaScriptErrors();
    });

    test('the age range filters by its min and max', function () {
        $page = visit(route('users.index', absolute: false));

        expectRows($page, 3);

        openAdvanced($page);

        $page->type(FILTERS.' input[name="min_age"]', '25')
            ->assertQueryStringHas('min_age', '25')
            ->assertQueryStringMissing('max_age');

        expectRows($page, 2); // Ada and Bob

        $page->type(FILTERS.' input[name="max_age"]', '40')
            ->assertQueryStringHas('max_age', '40');

        expectRows($page, 1);

        $page->assertSee('Bob Regular')
            ->assertSee('Filtering by Age')
            ->assertNoJavaScriptErrors();

        expect(urlSearch($page))->toBe('min_age=25&max_age=40');
    });

    test('the age range is initialised from the url query', function () {
        $page = visit(route('users.index', ['min_age' => 25, 'max_age' => 40], false));

        expectRows($page, 1);

        $page->assertSee('Bob Regular')
            ->assertValue(FILTERS.' input[name="min_age"]', '25') // The advanced filters are open
            ->assertValue(FILTERS.' input[name="max_age"]', '40')
            ->assertNoJavaScriptErrors();
    });

    test('the creation date range filters by its dates and shows them', function () {
        $page = visit(route('users.index', ['min_created_at' => '2020-01-01', 'max_created_at' => '2020-12-31'], false));

        expectRows($page, 1);

        $page->assertSee('Bob Regular')
            ->assertValue(FILTERS.' .p-datepicker input', '2020-01-01 - 2020-12-31')
            ->assertNoJavaScriptErrors();
    });

    test('picking dates in the calendar writes the creation date range in the url', function () {
        $page = visit(route('users.index', absolute: false));

        expectRows($page, 3);

        openAdvanced($page);

        $page->click(FILTERS.' .p-datepicker input');

        $clickDay = fn (int $position) => $page->script("[...document.querySelectorAll('.p-datepicker-day')].filter(day => ! day.closest('td').classList.contains('p-datepicker-other-month'))[$position].click()");

        $clickDay(4);
        $clickDay(11);

        $page->assertQueryStringHas('min_created_at')
            ->assertQueryStringHas('max_created_at')
            ->assertNoJavaScriptErrors();

        expect(urlSearch($page))->toMatch('/^min_created_at=\d{4}-\d{2}-\d{2}&max_created_at=\d{4}-\d{2}-\d{2}$/');
    });

    test('the erase button clears the ranges', function () {
        $page = visit(route('users.index', [
            'min_age' => 25,
            'max_age' => 40,
            'min_created_at' => '2020-01-01',
            'max_created_at' => '2020-12-31',
        ], false));

        expectRows($page, 1);

        $page->click(FILTERS.' button[aria-label="Erase"]')
            ->assertQueryStringMissing('min_age')
            ->assertQueryStringMissing('max_age')
            ->assertQueryStringMissing('min_created_at')
            ->assertQueryStringMissing('max_created_at')
            ->assertValue(FILTERS.' input[name="min_age"]', '')
            ->assertValue(FILTERS.' input[name="max_age"]', '')
            ->assertValue(FILTERS.' .p-datepicker input', '')
            ->assertNoJavaScriptErrors();

        expectRows($page, 3);
    });

    test('the ranges follow the url when going back in the browser', function () {
        User::factory(30)->create(['age' => 60]);

        $page = visit(route('users.index', ['min_age' => 25], false));

        expectRows($page, 25);

        $page->click('.p-datatable-paginator-bottom .p-paginator-next')
            ->assertQueryStringHas('page', '2');

        expectRows($page, 7); // Ada, Bob and the 30 new ones are 32

        $page->fill(FILTERS.' input[name="min_age"]', '55') // Replaces this entry, going back to the first page
            ->assertQueryStringHas('min_age', '55');

        expectRows($page, 25);

        // Back to the previous entry, which was filtered by 25
        $page->script('window.history.back()');

        expectRows($page, 25);

        $page->assertValue(FILTERS.' input[name="min_age"]', '25')
            ->assertQueryStringHas('min_age', '25')
            ->assertNoJavaScriptErrors();
    });
});

test('an age range with a zero is shown, as zero is a value', function () {
    $page = visit(route('users.index', ['min_age' => 0, 'max_age' => 200], false));

    expectRows($page, 3);

    $page->assertValue(FILTERS.' input[name="min_age"]', '0')
        ->assertValue(FILTERS.' input[name="max_age"]', '200')
        ->assertNoJavaScriptErrors();
});

test('the advanced filters fade in and out instead of appearing at once', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    // Records every animation class given to something inside the filters
    $page->script("window.__animations = []; new MutationObserver(mutations => mutations.forEach(mutation => { const classes = mutation.target.className || ''; if (typeof classes === 'string' && classes.includes('animate-')) { window.__animations.push(classes) } })).observe(document.querySelector('[role=\"search\"]'), { attributes: true, subtree: true })");

    $animations = fn () => (string) $page->script("window.__animations.join(' | ')");

    openAdvanced($page);

    $page->assertPresent(FILTERS.' input[name="name"]');

    expect($animations())->toContain('animate-fadein')
        ->and($animations())->not->toContain('animate-fadeout');

    openAdvanced($page);

    $page->assertMissing(FILTERS.' input[name="name"]') // Removed once it has faded out
        ->assertPresent(FILTERS.' input[name="search"]')
        ->assertNoJavaScriptErrors();

    expect($animations())->toContain('animate-fadeout');
});

test('the filters are inside the header of the table, below its title', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $page->assertPresent('.p-datatable-header [role="search"]')
        ->assertPresent('.p-datatable-header [role="search"] input[name="search"]');

    $titleBeforeFilters = $page->script("(() => { const header = document.querySelector('.p-datatable-header'); const title = header.querySelector('h2'); const filters = header.querySelector('[role=search]'); return !!(title.compareDocumentPosition(filters) & Node.DOCUMENT_POSITION_FOLLOWING) && title.getBoundingClientRect().bottom <= filters.getBoundingClientRect().top })()");

    expect($titleBeforeFilters)->toBeTrue();
});
