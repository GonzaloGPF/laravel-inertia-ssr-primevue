<?php

use App\Enums\Roles;
use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->admin = $this->signIn();
    $this->active = User::factory()->create(['name' => 'Active User']);
    $this->trashed = User::factory()->create(['name' => 'Trashed User']);
    $this->trashed->delete();
});

/**
 * The users the table asks for, as it does: only the data prop of the index, with the filters of the url.
 *
 * @return list<string>
 */
function listedUsers(array $filters = []): array
{
    return test()->get(route('users.index', $filters), [
        'X-Inertia' => 'true',
        'X-Inertia-Version' => app(HandleInertiaRequests::class)->version(request()),
        'X-Inertia-Partial-Component' => 'Users/UserIndex',
        'X-Inertia-Partial-Data' => 'data',
    ])->assertSuccessful()->json('props.data.data.*.name');
}

it('does not list the deleted users by default', function (array $filters) {
    expect(listedUsers($filters))->toContain('Active User')
        ->not->toContain('Trashed User');
})->with([
    'without filters' => [[]],
    'asking for the ones that are not deleted' => [['deleted' => 0]],
]);

it('lists just the deleted users when asked', function () {
    expect(listedUsers(['deleted' => 1]))->toBe(['Trashed User']);
});

it('tells which users are deleted in the list', function () {
    $this->get(route('users.index', ['deleted' => 1]), [
        'X-Inertia' => 'true',
        'X-Inertia-Version' => app(HandleInertiaRequests::class)->version(request()),
        'X-Inertia-Partial-Component' => 'Users/UserIndex',
        'X-Inertia-Partial-Data' => 'data',
    ])->assertJsonPath('props.data.data.0.deleted_at', $this->trashed->fresh()->deleted_at->toJSON());
});

it('does not open or edit a deleted user, as the others', function (string $route) {
    $this->get(route($route, $this->trashed))->assertNotFound();
})->with(['show' => 'users.show', 'edit' => 'users.edit']);

it('restores a deleted user', function () {
    $this->put(route('users.restore', $this->trashed))
        ->assertRedirect()
        ->assertSessionHas('flash_message_data.type', 'success');

    expect($this->trashed->fresh()->trashed())->toBeFalse()
        ->and(listedUsers())->toContain('Trashed User');
});

it('does not restore a user that is not deleted', function () {
    $this->put(route('users.restore', $this->active))->assertNotFound();
});

it('deletes a user for good, with its files', function () {
    Storage::fake('testing');
    $file = $this->trashed->saveFile(UploadedFile::fake()->image('me.png'), 'avatar');

    $this->delete(route('users.force-delete', $this->trashed))
        ->assertRedirect()
        ->assertSessionHas('flash_message_data.type', 'success');

    $this->assertDatabaseMissing('users', ['id' => $this->trashed->id]);
    $this->assertDatabaseMissing('files', ['id' => $file->id]);
    Storage::disk('testing')->assertMissing($file->path);
});

it('does not delete for good a user that is not in the trash', function () {
    $this->delete(route('users.force-delete', $this->active))->assertNotFound();

    expect($this->active->fresh())->not->toBeNull();
});

it('does not let a regular user restore or delete for good', function (string $method, string $route) {
    $this->signIn(User::factory()->create(['role' => Roles::USER]));

    $this->{$method}(route($route, $this->trashed))->assertForbidden();

    expect($this->trashed->fresh()->trashed())->toBeTrue();
})->with([
    'restore' => ['put', 'users.restore'],
    'force delete' => ['delete', 'users.force-delete'],
]);
