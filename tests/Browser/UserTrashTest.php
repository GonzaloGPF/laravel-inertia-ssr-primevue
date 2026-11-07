<?php

use App\Enums\Languages;
use App\Models\User;

const TRASH_DIALOG = '[data-pc-name="dialog"]';
const TRASH_FILTERS = '[role="search"]';

beforeEach(function () {
    $this->admin = User::factory()->admin()->create([
        'name' => 'Ada Admin',
        'language' => Languages::EN,
    ]);

    $this->bob = User::factory()->create(['name' => 'Bob Regular']);

    // In the trash, so it is the only one listed there
    $this->cara = User::factory()->create(['name' => 'Cara Regular']);
    $this->cara->delete();

    $this->actingAs($this->admin);
});

test('the trash lists just the deleted users, to restore them or to delete them for good', function () {
    $page = visit(route('users.index', ['deleted' => 1], false));

    $page->assertSee('Cara Regular')
        ->assertDontSee('Bob Regular');

    expectRows($page, 1); // And the admin, whose name is in the menu, is not in the table

    $page->assertPresent(rowAction(1, 'Restore'))
        ->assertPresent(rowAction(1, 'Delete Permanently'))
        ->assertMissing(rowAction(1, 'View')) // A deleted user cannot be opened, nor edited
        ->assertMissing(rowAction(1, 'Edit'))
        ->assertMissing(rowAction(1, 'Delete'))
        ->assertNoJavaScriptErrors();
});

test('the users that are not deleted keep their actions, and the trash is not there', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 2);

    $page->assertSee('Bob Regular')
        ->assertDontSee('Cara Regular')
        ->assertPresent(rowAction(1, 'View'))
        ->assertPresent(rowAction(1, 'Edit'))
        ->assertPresent(rowAction(1, 'Delete'))
        ->assertMissing(rowAction(1, 'Restore'))
        ->assertMissing(rowAction(1, 'Delete Permanently'))
        ->assertNoJavaScriptErrors();
});

test('the deleted filter shows the trash, and the other way round', function () {
    $page = visit(route('users.index', absolute: false));

    expectRows($page, 2);

    $page->click(TRASH_FILTERS.' button[aria-label="Advanced"]')
        ->click(TRASH_FILTERS.' [name="deleted"]')
        ->click('li[aria-label="Yes"]')
        ->assertQueryStringHas('deleted', '1')
        ->assertSee('Cara Regular')
        ->assertDontSee('Bob Regular');

    expectRows($page, 1);

    $page->click(TRASH_FILTERS.' [name="deleted"]')
        ->click('li[aria-label="No"]')
        ->assertQueryStringHas('deleted', '0')
        ->assertSee('Bob Regular')
        ->assertDontSee('Cara Regular')
        ->assertNoJavaScriptErrors();
});

test('restoring a user takes it out of the trash', function () {
    $page = visit(route('users.index', ['deleted' => 1], false));

    expectRows($page, 1);

    $page->click(rowAction(1, 'Restore'))
        ->assertSee('User Restored')
        ->assertDontSee('Cara Regular')
        ->assertNoJavaScriptErrors();

    expect($this->cara->fresh()->trashed())->toBeFalse();

    $list = visit(route('users.index', absolute: false));

    expectRows($list, 3);

    $list->assertSee('Cara Regular');
});

test('deleting for good asks for confirmation first, and can be cancelled', function () {
    $page = visit(route('users.index', ['deleted' => 1], false));

    expectRows($page, 1);

    $page->click(rowAction(1, 'Delete Permanently'))
        ->assertSee('This deletes it for good, with its files. Are you sure?')
        ->assertPresent(TRASH_DIALOG.' button[aria-label="Delete Permanently"]')
        ->click(TRASH_DIALOG.' button[aria-label="Cancel"]')
        ->assertDontSee('This deletes it for good')
        ->assertSee('Cara Regular')
        ->assertNoJavaScriptErrors();

    expect(User::withTrashed()->find($this->cara->id))->not->toBeNull();
});

test('confirming deletes the user for good', function () {
    $page = visit(route('users.index', ['deleted' => 1], false));

    expectRows($page, 1);

    $page->click(rowAction(1, 'Delete Permanently'))
        ->click(TRASH_DIALOG.' button[aria-label="Delete Permanently"]')
        ->assertSee('User Deleted Permanently')
        ->assertDontSee('Cara Regular')
        ->assertNoJavaScriptErrors();

    expect(User::withTrashed()->find($this->cara->id))->toBeNull()
        ->and($this->bob->fresh())->not->toBeNull();
});

test('the trash is translated', function () {
    $this->admin->update(['language' => Languages::ES]);

    $page = visit(route('users.index', ['deleted' => 1], false));

    expectRows($page, 1);

    $page->assertPresent(rowAction(1, 'Restaurar'))
        ->assertPresent(rowAction(1, 'Borrar definitivamente'))
        ->click(rowAction(1, 'Borrar definitivamente'))
        ->assertSee('Esto lo borra para siempre, con sus ficheros. ¿Está seguro?')
        ->assertNoJavaScriptErrors();
});
