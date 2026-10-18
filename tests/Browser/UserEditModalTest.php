<?php

use App\Enums\Currencies;
use App\Enums\Languages;
use App\Enums\Roles;
use App\Models\User;

const EDIT_DIALOG = '[data-pc-name="dialog"]';

beforeEach(function () {
    $this->admin = User::factory()->admin()->create([
        'name' => 'Ada Admin',
        'email' => 'ada@example.com',
        'language' => Languages::EN,
    ]);

    // The newest user is listed first
    $this->grace = User::factory()->create([
        'name' => 'Grace Hopper',
        'email' => 'grace@example.com',
        'phone' => '666666666',
        'age' => 35,
        'role' => Roles::USER,
        'language' => Languages::ES,
        'currency' => Currencies::DOLLAR,
    ]);

    $this->actingAs($this->admin);
});

test('the edit action opens the modal with the data of its row', function () {
    $page = visit(route('users.index', absolute: false));

    $page->assertSee('Grace Hopper')
        ->click(rowAction(1, 'Edit'))
        ->assertSee('Edit User')
        ->assertValue(EDIT_DIALOG.' input[name="name"]', 'Grace Hopper')
        ->assertValue(EDIT_DIALOG.' input[name="email"]', 'grace@example.com')
        ->assertValue(EDIT_DIALOG.' input[name="phone"]', '666666666')
        ->assertValue(EDIT_DIALOG.' input[name="age"]', '35')
        ->assertSeeIn(EDIT_DIALOG.' [name="role"]', 'User')
        ->assertSeeIn(EDIT_DIALOG.' [name="language"]', 'Spanish')
        ->assertSeeIn(EDIT_DIALOG.' [name="currency"]', 'Dollar')
        ->assertNoJavaScriptErrors();
});

test('the edit modal submits with an Edit label instead of Create', function () {
    $page = visit(route('users.index', absolute: false));

    $page->click(rowAction(1, 'Edit'))
        ->assertPresent(EDIT_DIALOG.' button[type="submit"][aria-label="Edit"]')
        ->assertMissing(EDIT_DIALOG.' button[type="submit"][aria-label="Create"]');
});

test('the edit action loads the data of the row where it was clicked', function () {
    $page = visit(route('users.index', absolute: false));

    $page->assertSee('Ada Admin')
        ->click(rowAction(2, 'Edit'))
        ->assertValue(EDIT_DIALOG.' input[name="name"]', 'Ada Admin')
        ->assertValue(EDIT_DIALOG.' input[name="email"]', 'ada@example.com');
});

test('editing a user closes the modal and refreshes the list', function () {
    $page = visit(route('users.index', absolute: false));

    $page->assertSee('Grace Hopper')
        ->click(rowAction(1, 'Edit'))
        ->fill(EDIT_DIALOG.' input[name="name"]', 'Grace Murray Hopper')
        ->click(EDIT_DIALOG.' [name="role"]')
        ->click('li[aria-label="Admin"]')
        ->click(EDIT_DIALOG.' button[type="submit"]')
        ->assertDontSee('Edit User')
        ->assertSee('Grace Murray Hopper')
        ->assertNoJavaScriptErrors();

    $this->grace->refresh();

    expect($this->grace->name)->toBe('Grace Murray Hopper')
        ->and($this->grace->role)->toBe(Roles::ADMIN)
        ->and($this->grace->email)->toBe('grace@example.com')
        ->and(User::count())->toBe(2);
});

test('server validation errors keep the edit modal open', function () {
    $page = visit(route('users.index', absolute: false));

    $page->click(rowAction(1, 'Edit'))
        ->fill(EDIT_DIALOG.' input[name="email"]', 'ada@example.com')
        ->click(EDIT_DIALOG.' button[type="submit"]')
        ->assertSee('Edit User')
        ->assertSee('The email has already been taken.')
        ->assertValue(EDIT_DIALOG.' input[name="email"]', 'ada@example.com')
        ->assertNoJavaScriptErrors();

    expect($this->grace->fresh()->email)->toBe('grace@example.com');
});

test('a user without language or currency can be edited', function () {
    $this->grace->forceFill(['language' => null, 'currency' => null])->saveQuietly();

    $page = visit(route('users.index', absolute: false));

    $page->click(rowAction(1, 'Edit'))
        ->fill(EDIT_DIALOG.' input[name="name"]', 'Grace Edited')
        ->click(EDIT_DIALOG.' button[type="submit"]')
        ->assertDontSee('Edit User')
        ->assertSee('Grace Edited')
        ->assertNoJavaScriptErrors();

    $this->grace->refresh();

    expect($this->grace->name)->toBe('Grace Edited')
        ->and($this->grace->language)->toBeNull()
        ->and($this->grace->currency)->toBeNull();
});

test('the create modal opens empty after editing a user', function () {
    $page = visit(route('users.index', absolute: false));

    $page->click(rowAction(1, 'Edit'))
        ->assertValue(EDIT_DIALOG.' input[name="name"]', 'Grace Hopper')
        ->click(EDIT_DIALOG.' button[aria-label="Cancel"]')
        ->assertDontSee('Edit User')
        ->click('.p-datatable-header button')
        ->assertSee('Create User')
        ->assertValue(EDIT_DIALOG.' input[name="name"]', '')
        ->assertPresent(EDIT_DIALOG.' button[type="submit"][aria-label="Create"]')
        ->assertNoJavaScriptErrors();
});

test('the age of a user can be changed and removed', function () {
    $page = visit(route('users.index', absolute: false));

    $page->assertSee('Grace Hopper')
        ->click(rowAction(1, 'Edit'))
        ->fill(EDIT_DIALOG.' input[name="age"]', '36')
        ->click(EDIT_DIALOG.' button[type="submit"]')
        ->assertDontSee('Edit User')
        ->assertNoJavaScriptErrors();

    expect($this->grace->fresh()->age)->toBe(36);

    $page->click(rowAction(1, 'Edit'))
        ->fill(EDIT_DIALOG.' input[name="age"]', '')
        ->click(EDIT_DIALOG.' button[type="submit"]')
        ->assertDontSee('Edit User')
        ->assertNoJavaScriptErrors();

    expect($this->grace->fresh()->age)->toBeNull();
});

test('an optional text field of a user can be cleared', function () {
    $page = visit(route('users.index', absolute: false));

    $page->assertSee('Grace Hopper')
        ->click(rowAction(1, 'Edit'))
        ->fill(EDIT_DIALOG.' input[name="phone"]', '')
        ->click(EDIT_DIALOG.' button[type="submit"]')
        ->assertDontSee('Edit User')
        ->assertNoJavaScriptErrors();

    expect($this->grace->fresh()->phone)->toBeNull();
});

test('an age of zero is a value, not an empty field', function () {
    $this->grace->forceFill(['age' => 0])->saveQuietly();

    $page = visit(route('users.index', absolute: false));

    $page->assertSee('Grace Hopper')
        ->click(rowAction(1, 'Edit'))
        ->assertValue(EDIT_DIALOG.' input[name="age"]', '0')
        ->click(EDIT_DIALOG.' button[type="submit"]')
        ->assertDontSee('Edit User')
        ->assertNoJavaScriptErrors();

    expect($this->grace->fresh()->age)->toBe(0);
});
