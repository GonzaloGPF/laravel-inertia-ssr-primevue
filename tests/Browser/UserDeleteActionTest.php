<?php

use App\Enums\Languages;
use App\Models\User;

const CONFIRM_DIALOG = '[data-pc-name="dialog"]';

beforeEach(function () {
    $this->admin = User::factory()->admin()->create([
        'name' => 'Ada Admin',
        'language' => Languages::EN,
    ]);

    // Users are listed from the newest to the oldest: Cara, Bob and Ada
    $this->bob = User::factory()->create(['name' => 'Bob Regular']);
    $this->cara = User::factory()->create(['name' => 'Cara Regular']);

    $this->actingAs($this->admin);
});

test('the delete action asks for confirmation before deleting', function () {
    $page = visit(route('users.index', absolute: false));

    $page->assertSee('Cara Regular')
        ->click(rowAction(1, 'Delete'))
        ->assertSee('Are you sure you want to delete?')
        ->assertPresent(CONFIRM_DIALOG.' button[aria-label="Delete"]')
        ->assertPresent(CONFIRM_DIALOG.' button[aria-label="Cancel"]')
        ->assertNoJavaScriptErrors();

    expect($this->cara->fresh()->deleted_at)->toBeNull();
});

test('cancelling the confirmation does not delete the user', function () {
    $page = visit(route('users.index', absolute: false));

    $page->assertSee('Cara Regular')
        ->click(rowAction(1, 'Delete'))
        ->click(CONFIRM_DIALOG.' button[aria-label="Cancel"]')
        ->assertDontSee('Are you sure you want to delete?')
        ->assertSee('Cara Regular')
        ->assertNoJavaScriptErrors();

    expect($this->cara->fresh()->deleted_at)->toBeNull();
});

test('confirming deletes the user of the row and refreshes the list', function () {
    $page = visit(route('users.index', absolute: false));

    $page->assertSee('Cara Regular')
        ->click(rowAction(1, 'Delete'))
        ->click(CONFIRM_DIALOG.' button[aria-label="Delete"]')
        ->assertDontSee('Are you sure you want to delete?')
        ->assertSee('User Deleted')
        ->assertDontSee('Cara Regular')
        ->assertSee('Bob Regular')
        ->assertSee('Ada Admin')
        ->assertNoJavaScriptErrors();

    expectRows($page, 2);

    expect($this->cara->fresh()->deleted_at)->not->toBeNull()
        ->and($this->bob->fresh()->deleted_at)->toBeNull();
});

test('the delete action deletes the user of the clicked row', function () {
    $page = visit(route('users.index', absolute: false));

    $page->assertSee('Bob Regular')
        ->click(rowAction(2, 'Delete'))
        ->click(CONFIRM_DIALOG.' button[aria-label="Delete"]')
        ->assertDontSee('Bob Regular')
        ->assertSee('Cara Regular');

    expect($this->bob->fresh()->deleted_at)->not->toBeNull()
        ->and($this->cara->fresh()->deleted_at)->toBeNull();
});

test('several users can be deleted one after another', function () {
    $page = visit(route('users.index', absolute: false));

    $page->assertSee('Cara Regular')
        ->click(rowAction(1, 'Delete'))
        ->click(CONFIRM_DIALOG.' button[aria-label="Delete"]')
        ->assertDontSee('Cara Regular')
        ->click(rowAction(1, 'Delete'))
        ->click(CONFIRM_DIALOG.' button[aria-label="Delete"]')
        ->assertDontSee('Bob Regular')
        ->assertNoJavaScriptErrors();

    expectRows($page, 1);

    expect(User::count())->toBe(1);
});

test('deleting a user keeps the current page of the list', function () {
    User::factory(20)->create(); // 23 users in total

    $page = visit(route('users.index', ['page' => 2, 'perPage' => 10], false));

    expectRows($page, 10);

    $page->click(rowAction(1, 'Delete'))
        ->click(CONFIRM_DIALOG.' button[aria-label="Delete"]')
        ->assertQueryStringHas('page', '2')
        ->assertQueryStringHas('perPage', '10');

    expect(User::count())->toBe(22);
});

test('deleting the only user of the last page goes back to the previous page', function () {
    // The signed in admin is the newest user. With 3 users per page, Ada is alone on page 2
    $newest = User::factory()->admin()->create(['language' => Languages::EN]);
    $this->actingAs($newest);

    $page = visit(route('users.index', ['page' => 2, 'perPage' => 3], false));

    expectRows($page, 1);

    $page->assertSee('Ada Admin')
        ->click(rowAction(1, 'Delete'))
        ->click(CONFIRM_DIALOG.' button[aria-label="Delete"]');

    expectRows($page, 3);

    $page->assertQueryStringHas('page', '1')
        ->assertQueryStringHas('perPage', '3')
        ->assertNoJavaScriptErrors();
});

test('an admin cannot delete themself from the list', function () {
    $page = visit(route('users.index', absolute: false));

    $page->assertSee('Ada Admin')
        ->click(rowAction(3, 'Delete'))
        ->assertSee('You cannot delete yourself')
        ->assertDontSee('Are you sure you want to delete?')
        ->assertNoJavaScriptErrors();

    expectRows($page, 3);

    expect($this->admin->fresh()->deleted_at)->toBeNull();
});

test('the admin can still delete other users after trying to delete themself', function () {
    $page = visit(route('users.index', absolute: false));

    $page->assertSee('Ada Admin')
        ->click(rowAction(3, 'Delete'))
        ->assertSee('You cannot delete yourself')
        ->click(rowAction(1, 'Delete'))
        ->assertSee('Are you sure you want to delete?')
        ->click(CONFIRM_DIALOG.' button[aria-label="Delete"]')
        ->assertDontSee('Cara Regular')
        ->assertSee('Ada Admin');

    expect($this->cara->fresh()->deleted_at)->not->toBeNull()
        ->and($this->admin->fresh()->deleted_at)->toBeNull();
});

/**
 * Records what happens to the rows of the table, from now on: the ones that leave, and those added or removed.
 */
function recordRows($page): void
{
    $page->script("window.__events = []; new MutationObserver(mutations => mutations.forEach(mutation => { if (mutation.type === 'attributes' && String(mutation.target.className).includes('animate-fadeoutright')) { window.__events.push('leaving') } mutation.removedNodes.forEach(node => { if (node.tagName === 'TR' && String(node.className).includes('p-row-')) { window.__events.push('removed') } }); mutation.addedNodes.forEach(node => { if (node.tagName === 'TR' && String(node.className).includes('p-row-')) { window.__events.push('added:' + (String(node.className).includes('animate-fadein') ? 'fadein' : 'plain')) } }) })).observe(document.body, { attributes: true, subtree: true, childList: true, attributeFilter: ['class'] })");
}

function recordedRows($page): string
{
    return (string) $page->script("window.__events.join(',')");
}

test('the deleted row leaves with an animation and is then removed', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $page->click(rowAction(1, 'Delete'))
        ->assertSee('Are you sure you want to delete?');

    recordRows($page);

    $page->click(CONFIRM_DIALOG.' button[aria-label="Delete"]')
        ->assertDontSee('Cara Regular');

    expectRows($page, 2);

    // The others stay where they are: no row is added, and they don't play their fade again
    expect(recordedRows($page))->toBe('leaving,removed');
});

test('the row that leaves moves aside while it fades out', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $page->click(rowAction(1, 'Delete'))
        ->assertSee('Are you sure you want to delete?');

    // Samples, while it leaves, how far the row has moved and how visible it is
    $page->script("window.__samples = []; setInterval(() => { const row = [...document.querySelectorAll('.p-datatable-tbody > tr')].find(row => String(row.className).includes('animate-fadeoutright')); if (row) { const style = getComputedStyle(row); window.__samples.push({ x: new DOMMatrix(style.transform).m41, opacity: Number(style.opacity) }) } }, 30)");

    $page->click(CONFIRM_DIALOG.' button[aria-label="Delete"]')
        ->assertDontSee('Cara Regular');

    $samples = $page->script('JSON.stringify(window.__samples)');

    $samples = json_decode($samples, true);
    $positions = array_column($samples, 'x');
    $opacities = array_column($samples, 'opacity');

    expect(count($samples))->toBeGreaterThan(3)
        ->and($positions)->toBe(collect($positions)->sort()->values()->all()) // It only moves one way
        ->and(end($positions))->toBeGreaterThan(100)
        ->and($opacities)->toBe(collect($opacities)->sortDesc()->values()->all()) // And only fades
        ->and(end($opacities))->toBeLessThan(0.3); // Almost gone, as it is removed when it ends
});

test('the row that takes the place of the deleted one fades in, once the backend is asked again', function () {
    User::factory(28)->create(); // 31 users in total, so the 26th takes the place of the deleted one

    $page = visit(route('users.index', absolute: false));

    expectRows($page, 25);

    $page->assertSee('Showing [1-25] of 31 items')
        ->click(rowAction(1, 'Delete'))
        ->assertSee('Are you sure you want to delete?');

    recordRows($page);

    $page->click(CONFIRM_DIALOG.' button[aria-label="Delete"]')
        ->assertSee('Showing [1-25] of 30 items');

    expectRows($page, 25);

    expect(recordedRows($page))->toBe('leaving,removed,added:fadein');
});

test('the total of the table goes down as soon as the row is removed', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $page->assertSee('Showing [1-3] of 3 items')
        ->click(rowAction(1, 'Delete'))
        ->click(CONFIRM_DIALOG.' button[aria-label="Delete"]')
        ->assertSee('Showing [1-2] of 2 items')
        ->assertNoJavaScriptErrors();
});

test('an expanded row closes when its user is deleted', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 3);

    $page->click('.p-datatable-row-toggle-button >> nth=0')
        ->assertPresent('.p-datatable-row-expansion')
        ->click(rowAction(1, 'Delete'))
        ->click(CONFIRM_DIALOG.' button[aria-label="Delete"]')
        ->assertDontSee('Cara Regular')
        ->assertNoJavaScriptErrors();

    expectRows($page, 2);

    expect($page->script("document.querySelectorAll('.p-datatable-row-expansion').length"))->toBe(0);
});
