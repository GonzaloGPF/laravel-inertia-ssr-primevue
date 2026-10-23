<?php

use App\Models\File;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('builds the url from the disk where the file was saved', function () {
    $user = User::factory()->create();

    $inTesting = File::factory()->for($user, 'fileable')->create(['disk' => 'testing', 'path' => 'users/a.png']);
    $inPublic = File::factory()->for($user, 'fileable')->create(['disk' => 'public', 'path' => 'users/b.png']);

    expect($inTesting->url)->toBe(Storage::disk('testing')->url('users/a.png'))
        ->and($inPublic->url)->toBe(Storage::disk('public')->url('users/b.png'))
        ->and($inTesting->url)->not->toBe($inPublic->url);
});

it('does not expose where the file is stored', function () {
    $file = File::factory()->for(User::factory()->create(), 'fileable')->create();

    expect($file->toArray())->toHaveKeys(['id', 'collection', 'name', 'mime', 'size', 'url'])
        ->not->toHaveKeys(['disk', 'path']);
});

it('deletes the physical file, from its own disk, when it is deleted', function () {
    Storage::fake('public');
    Storage::fake('testing');
    $file = File::factory()->for(User::factory()->create(), 'fileable')->create(['disk' => 'public', 'path' => 'users/a.png']);
    Storage::disk('public')->put('users/a.png', 'content');
    Storage::disk('testing')->put('users/a.png', 'other content');

    $file->delete();

    Storage::disk('public')->assertMissing('users/a.png');
    Storage::disk('testing')->assertExists('users/a.png');
});

it('belongs to its model by an alias, not by its class name', function () {
    $user = User::factory()->create();

    $file = File::factory()->for($user, 'fileable')->create();

    expect($file->fileable_type)->toBe('user')
        ->and($file->fileable->is($user))->toBeTrue();
});
