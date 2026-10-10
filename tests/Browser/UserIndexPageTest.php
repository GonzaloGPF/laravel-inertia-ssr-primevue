<?php

use App\Enums\Languages;
use App\Enums\Roles;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create([
        'name' => 'Ada Admin',
        'email' => 'ada@example.com',
        'language' => Languages::EN,
    ]);

    $this->actingAs($this->admin);
});

test('the users table lists the users with translated headers and values', function () {
    User::factory()->create([
        'name' => 'Bob Regular',
        'role' => Roles::USER,
        'language' => Languages::ES,
    ]);

    $page = visit(route('users.index', absolute: false));

    $page->assertSee('Ada Admin')
        ->assertSee('ada@example.com')
        ->assertSee('Bob Regular')
        ->assertSee('ID')
        ->assertSee('Name')
        ->assertSee('Email')
        ->assertSee('Role')
        ->assertSee('Language')
        ->assertSee('Actions')
        ->assertSee('Spanish')
        ->assertSee('English')
        ->assertNoJavaScriptErrors();
});

test('the users table headers are translated to the language of the user', function () {
    $this->admin->update(['language' => Languages::ES]);
    User::factory()->create(['language' => Languages::EN]);

    $page = visit(route('users.index', absolute: false));

    $page->assertSee('Nombre')
        ->assertSee('Correo Electrónico')
        ->assertSee('Acciones')
        ->assertSee('Español')
        ->assertSee('Inglés')
        ->assertNoJavaScriptErrors();
});

test('the id is the first column and actions are rendered as icon only buttons', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 1);

    $page->assertSee('Actions'); // The headers are translated once the language is loaded

    $headers = $page->script("[...document.querySelectorAll('.p-datatable-column-title')].map(e => e.textContent.trim()).join(',')");
    $buttons = $page->script("[...document.querySelectorAll('.p-datatable-tbody > tr:first-child button')].map(e => e.getAttribute('aria-label') + '|' + e.textContent.trim()).join(',')");

    expect($headers)->toBe('ID,Name,Email,Role,Language,Actions')
        ->and($buttons)->toBe('View|visibility,Edit|edit,Delete|delete');
});

test('the users table shows the first page with 25 users by default without touching the url', function () {
    User::factory(30)->create();

    $page = visit(route('users.index', absolute: false));

    $page->assertSee('Ada Admin')
        ->assertQueryStringMissing('page')
        ->assertQueryStringMissing('perPage');

    expectRows($page, 25);
});

test('changing the page requests the new page and reflects it in the url', function () {
    User::factory(30)->create(); // 31 users in total

    $page = visit(route('users.index', absolute: false));

    expectRows($page, 25);

    $page->click('.p-paginator-next')
        ->assertQueryStringHas('page', '2')
        ->assertQueryStringHas('perPage', '25')
        ->assertNoJavaScriptErrors();

    expectRows($page, 6);
});

test('the users table reads the page and page size from the url', function () {
    User::factory(30)->create();

    $page = visit(route('users.index', ['page' => 2, 'perPage' => 10], false));

    expectRows($page, 10);

    $page->assertQueryStringHas('page', '2')
        ->assertNoJavaScriptErrors();
});

test('the browser back button goes back to the previous table page', function () {
    User::factory(30)->create();

    $page = visit(route('users.index', absolute: false));

    expectRows($page, 25);

    $page->click('.p-paginator-next')
        ->assertQueryStringHas('page', '2');

    expectRows($page, 6);

    $page->script('window.history.back()');

    expectRows($page, 25);

    $page->assertQueryStringMissing('page');
});

test('the header actions and title are rendered above the table', function () {
    $page = visit(route('users.index', absolute: false));

    $page->assertSee('Users')
        ->assertSee('Create')
        ->assertNoJavaScriptErrors();
});

test('the row actions are squared, separated and neutral icon only buttons', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 1);

    $metrics = $page->script(<<<'JS'
        (() => {
            const buttons = [...document.querySelectorAll('.p-datatable-tbody > tr:first-child td:last-child button')]
            const rects = buttons.map((button) => button.getBoundingClientRect())

            return JSON.stringify({
                squared: rects.every((rect) => Math.abs(rect.width - rect.height) < 0.5),
                gaps: rects.slice(1).map((rect, i) => rect.left - rects[i].right),
                backgrounds: buttons.map((button) => getComputedStyle(button).backgroundColor),
                createRatio: (() => {
                    const rect = document.querySelector('.p-datatable-header button').getBoundingClientRect()
                    return rect.width / rect.height
                })(),
            })
        })()
    JS);

    $metrics = json_decode($metrics, true);

    expect($metrics['squared'])->toBeTrue()
        ->and($metrics['gaps'])->each->toBeGreaterThan(0)
        ->and($metrics['backgrounds'])->each->toBe('rgba(0, 0, 0, 0)')
        ->and($metrics['createRatio'])->toBeGreaterThan(1.5); // The labelled button keeps its natural width
});

test('a user without language does not render an empty language chip', function () {
    User::factory()->create(['language' => null]);

    $page = visit(route('users.index', absolute: false));

    expectRows($page, 2);

    $chips = $page->script("[...document.querySelectorAll('.p-datatable-tbody > tr')].map(row => row.querySelectorAll('.p-chip').length).sort().join(',')");

    expect($chips)->toBe('1,2'); // Role chip only, and role and language chips
});
