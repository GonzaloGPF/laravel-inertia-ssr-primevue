<?php

use App\Models\File;
use App\Models\User;
use App\Services\FileService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * The same user, but with the collections given, as the User only has a single file one.
 */
function userWithCollections(User $user, array $collections): User
{
    $model = new class extends User
    {
        public array $collections = [];

        public function fileCollections(): array
        {
            return $this->collections;
        }

        public function getMorphClass(): string
        {
            return 'user';
        }
    };

    $model = $model->newFromBuilder($user->getAttributes());
    $model->collections = $collections;

    return $model;
}

it('saves the file in the files disk, in the folder of the model and its collection', function () {
    Storage::fake('testing');
    $user = User::factory()->create();

    $file = app(FileService::class)->save($user, UploadedFile::fake()->image('photo.png')->size(100), 'avatar');

    expect($file->exists)->toBeTrue()
        ->and($file->fileable->is($user))->toBeTrue()
        ->and($file->collection)->toBe('avatar')
        ->and($file->disk)->toBe('testing')
        ->and($file->name)->toBe('photo.png')
        ->and($file->mime)->toBe('image/png')
        ->and($file->size)->toBe(100 * 1024)
        ->and($file->path)->toStartWith("users/{$user->id}/avatar/")
        ->and($file->path)->toEndWith('.png');

    Storage::disk('testing')->assertExists($file->path);
});

it('never uses the name of the uploaded file as the path', function () {
    Storage::fake('testing');
    $user = User::factory()->create();

    $file = app(FileService::class)->save($user, UploadedFile::fake()->image('../../evil name.png'), 'avatar');

    expect($file->path)->not->toContain('evil')
        ->and($file->path)->toStartWith("users/{$user->id}/avatar/");
});

it('replaces the file of a single file collection', function () {
    Storage::fake('testing');
    $user = User::factory()->create();
    $service = app(FileService::class);

    $previous = $service->save($user, UploadedFile::fake()->image('first.png'), 'avatar');
    $current = $service->save($user, UploadedFile::fake()->image('second.png'), 'avatar');

    expect($user->filesOf('avatar')->get()->modelKeys())->toBe([$current->id])
        ->and($user->avatar->is($current))->toBeTrue();

    Storage::disk('testing')->assertMissing($previous->path);
    Storage::disk('testing')->assertExists($current->path);
});

it('keeps all the files of a collection that is not single', function () {
    Storage::fake('testing');
    $user = userWithCollections(User::factory()->create(), ['photos' => []]);
    $service = app(FileService::class);

    $first = $service->save($user, UploadedFile::fake()->image('first.png'), 'photos');
    $second = $service->save($user, UploadedFile::fake()->image('second.png'), 'photos');

    expect($user->filesOf('photos')->count())->toBe(2);

    Storage::disk('testing')->assertExists([$first->path, $second->path]);
});

it('deletes the files of the collection, and only those', function () {
    Storage::fake('testing');
    $user = userWithCollections(User::factory()->create(), ['avatar' => [], 'photos' => []]);
    $service = app(FileService::class);

    $avatar = $service->save($user, UploadedFile::fake()->image('avatar.png'), 'avatar');
    $photo = $service->save($user, UploadedFile::fake()->image('photo.png'), 'photos');

    $service->delete($user, 'avatar');

    expect(File::find($avatar->id))->toBeNull()
        ->and(File::find($photo->id))->not->toBeNull();

    Storage::disk('testing')->assertMissing($avatar->path);
    Storage::disk('testing')->assertExists($photo->path);
});

it('deletes every file of the model, and not the ones of other models', function () {
    Storage::fake('testing');
    $user = userWithCollections(User::factory()->create(), ['avatar' => [], 'photos' => []]);
    $other = User::factory()->create();
    $service = app(FileService::class);

    $avatar = $service->save($user, UploadedFile::fake()->image('avatar.png'), 'avatar');
    $photo = $service->save($user, UploadedFile::fake()->image('photo.png'), 'photos');
    $otherAvatar = $service->save($other, UploadedFile::fake()->image('other.png'), 'avatar');

    $service->deleteAll($user);

    expect($user->files()->count())->toBe(0)
        ->and($other->files()->count())->toBe(1);

    Storage::disk('testing')->assertMissing([$avatar->path, $photo->path]);
    Storage::disk('testing')->assertExists($otherAvatar->path);
});

it('refuses a collection that the model does not declare', function () {
    Storage::fake('testing');
    $user = User::factory()->create();

    expect(fn () => app(FileService::class)->save($user, UploadedFile::fake()->image('photo.png'), 'photos'))
        ->toThrow(InvalidArgumentException::class, 'no [photos] file collection');

    expect(Storage::disk('testing')->allFiles())->toBeEmpty()
        ->and(File::count())->toBe(0);
});

it('deletes the folders of the models from the files disk, even when the database has no rows of them', function () {
    Storage::fake('testing');
    $user = User::factory()->withAvatar()->create();
    Storage::disk('testing')->put('other/keep.txt', 'something else');

    File::query()->delete(); // As after dropping the database: the files are there, but nothing points at them

    app(FileService::class)->deleteAllFolders();

    expect(Storage::disk('testing')->exists("users/{$user->getKey()}"))->toBeFalse();
    Storage::disk('testing')->assertExists('other/keep.txt');
});
