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
        ->assertSee('Name')
        ->assertSee('Email')
        ->assertSee('Role')
        ->assertSee('Age')
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

test('the table has the columns of the page, with no id, and the actions are rendered as icon only buttons', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 1);

    $page->assertSee('Actions'); // The headers are translated once the language is loaded

    $headers = $page->script("[...document.querySelectorAll('.p-datatable-column-title')].map(e => e.textContent.trim()).join(',')");
    $buttons = $page->script("[...document.querySelectorAll('.p-datatable-tbody > tr:first-child td:last-child button')].map(e => e.getAttribute('aria-label') + '|' + e.textContent.trim()).join(',')");

    expect($headers)->toBe('Name,Email,Age,Role,Language,Actions')
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

    $page->click('.p-datatable-paginator-bottom .p-paginator-next')
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

    $page->click('.p-datatable-paginator-bottom .p-paginator-next')
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
                    const rect = document.querySelector('.p-datatable-header button[aria-label="Create"]').getBoundingClientRect()
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

test('the table shows which items of the total are in the page', function () {
    User::factory(54)->create(); // 55 users in total, with the admin

    $page = visit(route('users.index', absolute: false));

    expectRows($page, 25);

    $page->assertSee('Showing [1-25] of 55 items')
        ->click('.p-datatable-paginator-bottom .p-paginator-next');

    expectRows($page, 25);

    $page->assertSee('Showing [26-50] of 55 items')
        ->click('.p-datatable-paginator-bottom .p-paginator-next');

    expectRows($page, 5);

    $page->assertSee('Showing [51-55] of 55 items')
        ->assertNoJavaScriptErrors();
});

test('the shown items follow the page size and the filters', function () {
    User::factory(30)->create(['name' => 'Extra Person']);

    $page = visit(route('users.index', ['perPage' => 10, 'name' => 'Extra'], false));

    expectRows($page, 10);

    $page->assertSee('Showing [1-10] of 30 items')
        ->assertNoJavaScriptErrors();
});

test('the shown items are translated', function () {
    $this->admin->update(['language' => Languages::ES]);

    $page = visit(route('users.index', absolute: false));

    expectRows($page, 1);

    $page->assertSee('Mostrando [1-1] de 1 elementos')
        ->assertNoJavaScriptErrors();
});

test('the new rows of the table fade in', function () {
    User::factory(30)->create(); // 31 users in total, so there is a second page

    $page = visit(route('users.index', absolute: false));

    expectRows($page, 25);

    // Records the rows that are added to the table, with their classes
    $page->script("window.__rows = []; new MutationObserver(mutations => mutations.forEach(mutation => mutation.addedNodes.forEach(node => { if (node.tagName === 'TR' && node.className.includes('p-row-')) { window.__rows.push(node.className) } }))).observe(document.querySelector('.p-datatable-tbody'), { childList: true })");

    $page->click('.p-datatable-paginator-bottom .p-paginator-next')
        ->assertQueryStringHas('page', '2');

    expectRows($page, 6);

    $rows = json_decode($page->script('JSON.stringify(window.__rows)'), true);

    expect($rows)->not->toBeEmpty()
        ->and(collect($rows)->every(fn (string $classes) => str_contains($classes, 'animate-fadein')))->toBeTrue();

    $animation = $page->script("(() => { const style = getComputedStyle(document.querySelector('.p-datatable-tbody > tr')); return style.animationName + ' ' + style.animationDuration })()");

    expect($animation)->toBe('fadein 0.3s');
});

test('refreshing the table does not replay the fade of the rows that are still there', function () {
    $page = visit(route('users.index', absolute: false));

    // Waits for the user to be listed: the row of a table without data is a row too
    for ($attempt = 0; $attempt < 50 && $page->script("document.querySelectorAll('.p-datatable-tbody > tr[class*=\"p-row-\"]').length") !== 1; $attempt++) {
        usleep(100_000);
    }

    $page->script("window.__rows = 0; new MutationObserver(mutations => mutations.forEach(mutation => mutation.addedNodes.forEach(node => { if (node.tagName === 'TR' && node.className.includes('p-row-')) { window.__rows++ } }))).observe(document.querySelector('.p-datatable-tbody'), { childList: true })");

    $page->click('[role="search"] button[aria-label="Refresh"]');

    usleep(1_000_000); // The same users are loaded again

    expectRows($page, 1);

    expect($page->script('window.__rows'))->toBe(0);
});

test('the rows fade in one after the other, up to a limit', function () {
    User::factory(30)->create(); // 31 users in total

    $page = visit(route('users.index', absolute: false));

    expectRows($page, 25);

    $delays = $page->script("[...document.querySelectorAll('.p-datatable-tbody > tr[class*=p-row-]')].map(row => getComputedStyle(row).animationDelay).join(' ')");
    $fill = $page->script("getComputedStyle(document.querySelector('.p-datatable-tbody > tr[class*=p-row-]')).animationFillMode");

    expect(explode(' ', $delays))->toHaveCount(25)
        ->and(array_slice(explode(' ', $delays), 0, 4))->toBe(['0s', '0.035s', '0.07s', '0.105s'])
        ->and(array_slice(explode(' ', $delays), 14))->each->toBe('0.49s') // The rows from the 15th fade in together
        ->and($fill)->toBe('backwards'); // So they are hidden until their turn
});
