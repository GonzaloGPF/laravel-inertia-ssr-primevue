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
