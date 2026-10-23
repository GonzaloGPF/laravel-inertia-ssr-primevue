<?php

use App\Enums\Languages;
use App\Enums\Roles;
use App\Models\User;

/**
 * Selector of the header of a column of the table, by its position: the expander, ID, Name, Email, Age, Role, Language and Actions.
 */
function sortHeader(int $column): string
{
    return ".p-datatable-thead th:nth-child($column)";
}

/**
 * Names of the users, in the order they are listed.
 */
function listedNames($page): string
{
    return (string) $page->script("[...document.querySelectorAll('.p-datatable-tbody > tr')].map(row => row.children[2].querySelector(':scope > div > span').textContent.trim()).join(',')");
}

/**
 * Waits for the users to be listed in the given order.
 */
function expectNames($page, string $expected): void
{
    $names = '';

    for ($attempt = 0; $attempt < 50; $attempt++) {
        $names = listedNames($page);

        if ($names === $expected) {
            break;
        }

        usleep(100_000);
    }

    expect($names)->toBe($expected);
}

function urlSort($page): string
{
    return (string) $page->script('decodeURIComponent(location.search.slice(1))');
}

const NAME = 3;
const AGE = 5;
const ROLE = 6;
const ACTIONS = 8;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create([
        'name' => 'Ada Admin',
        'language' => Languages::EN,
        'age' => 30,
    ]);

    // Listed from the newest to the oldest: Cara, Bob and Ada
    $this->bob = User::factory()->create(['name' => 'Bob Regular', 'role' => Roles::USER, 'age' => 50]);
    $this->cara = User::factory()->create(['name' => 'Cara Regular', 'role' => Roles::USER, 'age' => 20]);

    $this->actingAs($this->admin);
});

test('every column with a field is sortable, but the actions', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $sortable = $page->script("document.querySelectorAll('.p-datatable-thead th[data-p-sortable-column=\"true\"]').length");
    $actionsSortable = $page->script("document.querySelector('.p-datatable-thead th:nth-child(".ACTIONS.")').getAttribute('data-p-sortable-column')");

    expect($sortable)->toBe(6)
        ->and($actionsSortable)->not->toBe('true');
});

test('the columns are not sorted by default', function () {
    $page = visit(route('users.index', absolute: false));

    expectNames($page, 'Cara Regular,Bob Regular,Ada Admin'); // The default order: id descending

    $page->assertQueryStringMissing('sort');
});

test('clicking a column sorts, clicking again reverses and a third click removes the sort', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $page->click(sortHeader(AGE))
        ->assertQueryStringHas('sort', 'age|asc')
        ->assertAttribute(sortHeader(AGE), 'aria-sort', 'ascending');

    expectNames($page, 'Cara Regular,Ada Admin,Bob Regular'); // 20, 30 and 50

    $page->click(sortHeader(AGE))
        ->assertQueryStringHas('sort', 'age|desc')
        ->assertAttribute(sortHeader(AGE), 'aria-sort', 'descending');

    expectNames($page, 'Bob Regular,Ada Admin,Cara Regular');

    $page->click(sortHeader(AGE))
        ->assertQueryStringMissing('sort')
        ->assertAttribute(sortHeader(AGE), 'aria-sort', 'none')
        ->assertNoJavaScriptErrors();

    expectNames($page, 'Cara Regular,Bob Regular,Ada Admin'); // The default order again
});

test('sorting by another column replaces the sort', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $page->click(sortHeader(AGE))
        ->assertQueryStringHas('sort', 'age|asc')
        ->click(sortHeader(NAME))
        ->assertQueryStringHas('sort', 'name|asc')
        ->assertAttribute(sortHeader(NAME), 'aria-sort', 'ascending')
        ->assertAttribute(sortHeader(AGE), 'aria-sort', 'none');

    expectNames($page, 'Ada Admin,Bob Regular,Cara Regular');

    expect(urlSort($page))->toBe('sort=name|asc');
});

test('a column with a chip, as the role, is sorted by its value', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $page->click(sortHeader(ROLE))
        ->assertQueryStringHas('sort', 'role|asc')
        ->assertNoJavaScriptErrors();

    expectNames($page, 'Ada Admin,Cara Regular,Bob Regular'); // The admin first, then the newest of the users
});

test('the sort is read from the url query', function () {
    $page = visit(route('users.index', ['sort' => 'age|desc'], false));

    expectNames($page, 'Bob Regular,Ada Admin,Cara Regular');

    $page->assertAttribute(sortHeader(AGE), 'aria-sort', 'descending')
        ->assertNoJavaScriptErrors();
});

test('an invalid sort in the url is ignored', function (string $sort) {
    $page = visit(route('users.index', ['sort' => $sort], false));

    expectNames($page, 'Cara Regular,Bob Regular,Ada Admin');

    $page->assertAttribute(sortHeader(AGE), 'aria-sort', 'none')
        ->assertNoJavaScriptErrors();
})->with(['unknown column' => ['nope|asc'], 'unknown direction' => ['age|sideways'], 'without direction' => ['age']]);

test('sorting goes back to the first page and keeps the page size', function () {
    User::factory(30)->create(['age' => 60]);

    $page = visit(route('users.index', ['page' => 2, 'perPage' => 10], false));

    expectRows($page, 10);

    $page->click(sortHeader(AGE))
        ->assertQueryStringHas('sort', 'age|asc')
        ->assertQueryStringMissing('page')
        ->assertQueryStringHas('perPage', '10');

    expectRows($page, 10);
});

test('each click on a column makes just one request', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $requests = fn () => (int) $page->script("performance.getEntriesByType('resource').filter(entry => entry.name.includes('/admin/users') && entry.name.includes('sort=')).length");

    $page->click(sortHeader(NAME))
        ->assertQueryStringHas('sort', 'name|asc');

    expectNames($page, 'Ada Admin,Bob Regular,Cara Regular');

    expect($requests())->toBe(1);

    $page->click(sortHeader(NAME))
        ->assertQueryStringHas('sort', 'name|desc');

    expectNames($page, 'Cara Regular,Bob Regular,Ada Admin');

    expect($requests())->toBe(2);
});

test('the sort and the filters are kept when the other one changes', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $page->click(sortHeader(NAME))
        ->assertQueryStringHas('sort', 'name|asc')
        ->type('[role="search"] input[name="search"]', 'Regular')
        ->assertQueryStringHas('search', 'Regular')
        ->assertQueryStringHas('sort', 'name|asc'); // The filters keep the sort

    expectNames($page, 'Bob Regular,Cara Regular');

    $page->click(sortHeader(NAME))
        ->assertQueryStringHas('sort', 'name|desc')
        ->assertQueryStringHas('search', 'Regular') // And the sort keeps the filters
        ->assertNoJavaScriptErrors();

    expectNames($page, 'Cara Regular,Bob Regular');

    expect(urlSort($page))->toBe('search=Regular&sort=name|desc');
});

test('the sort is kept when changing the page', function () {
    User::factory(30)->create(['age' => 60]);

    $page = visit(route('users.index', ['sort' => 'age|desc'], false));

    expectRows($page, 25);

    $page->click('.p-datatable-paginator-bottom .p-paginator-next')
        ->assertQueryStringHas('page', '2')
        ->assertQueryStringHas('sort', 'age|desc')
        ->assertNoJavaScriptErrors();

    expectRows($page, 8);
});
