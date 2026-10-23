<?php

use App\Models\File;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('reads each file relation from its own collection', function () {
    $user = User::factory()->create();
    $photo = File::factory()->for($user, 'fileable')->inCollection('photos')->create();
    $avatar = File::factory()->for($user, 'fileable')->inCollection('avatar')->create();

    expect($user->avatar->is($avatar))->toBeTrue()
        ->and($user->files)->toHaveCount(2)
        ->and($user->files->contains($photo))->toBeTrue();
});

it('gives the latest file when a collection has several', function () {
    $user = User::factory()->create();
    File::factory()->for($user, 'fileable')->inCollection('avatar')->create();
    $latest = File::factory()->for($user, 'fileable')->inCollection('avatar')->create();

    expect($user->avatar->is($latest))->toBeTrue();
});

it('saves and deletes the files of a collection from the model', function () {
    Storage::fake('testing');
    $user = User::factory()->create();

    $file = $user->saveFile(UploadedFile::fake()->image('photo.png'), 'avatar');

    expect($user->avatar->is($file))->toBeTrue()
        ->and($user->avatar_url)->toBe($file->url);

    $user->deleteFile('avatar');

    expect($user->avatar)->toBeNull()
        ->and($user->avatar_url)->toBeNull();

    Storage::disk('testing')->assertMissing($file->path);
});

it('keeps the files of a soft deleted model, as it can be restored', function () {
    Storage::fake('testing');
    $user = User::factory()->create();
    $file = $user->saveFile(UploadedFile::fake()->image('photo.png'), 'avatar');

    $user->delete();

    expect(File::find($file->id))->not->toBeNull();

    Storage::disk('testing')->assertExists($file->path);

    $user->restore();

    expect($user->fresh()->avatar_url)->toBe($file->url);
});

it('deletes the files of a model that is permanently deleted', function () {
    Storage::fake('testing');
    $user = User::factory()->create();
    $file = $user->saveFile(UploadedFile::fake()->image('photo.png'), 'avatar');

    $user->forceDelete();

    expect(File::find($file->id))->toBeNull();

    Storage::disk('testing')->assertMissing($file->path);
});
