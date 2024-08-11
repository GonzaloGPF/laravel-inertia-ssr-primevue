<?php

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

function randomConstant(BackedEnum|string $enum, bool $multiple = false): string|array
{
    $constants = collect($enum::cases());

    $itemsCount = $multiple
        ? rand(1, $constants->count())
        : 1;

    $result = $constants->random($itemsCount)
        ->map(fn (BackedEnum $backedEnum) => $backedEnum->value);

    if (! $multiple) {
        return $result->first();
    }

    return $result->toArray();
}

function make($class, array $parameters = [], $count = null): Collection|Model
{
    /** @var HasFactory $model */
    $model = resolve($class);

    return $model::factory()
        ->count($count)
        ->make($parameters);
}

function create($class, array $parameters = [], $count = null): Collection|Model
{
    /** @var HasFactory $model */
    $model = resolve($class);

    return $model::factory()
        ->count($count)
        ->create($parameters);
}

/**
 * Helper that returns an ID of a specified Model (by class name).
 * If there are any Model, it will grab one randomly, if not, it will create a new one using its Factory
 * When using make() it won't write in Database, instead, it will return a 1
 */
function associateTo(string $modelClass, array $attributes = [], bool $create = false): int|string
{
    $isMaking = collect(debug_backtrace())
        ->filter(fn ($item) => isset($item['function']) && $item['function'] === 'make')
        ->filter(fn ($item) => isset($item['file']) && Str::endsWith($item['file'], 'Pest.php'))
        ->count();

    if ($isMaking) {
        return 1;
    }

    if ($create) {
        return create($modelClass, $attributes)->id;
    }

    /** @var Model $model */
    $model = resolve($modelClass);

    return $model::where($attributes)->inRandomOrder()->first()?->id
        ?? create($modelClass, $attributes)->id; // call to Factory
}


/**
 * Creates a file and returns the path where it has been saved.
 */
function generateTestFile(?string $name = 'Test', ?string $extension = null, string $fileContent = 'This is a test content'): string
{
    $extension = $extension ?? 'txt';

    $path = "$name.$extension";

    Storage::put($path, $fileContent);

    return $path;
}

function makeMock(string $className, array $attributes = [], int $times = 0): mixed
{
    if (! $times) {
        return Mockery::mock(make($className, $attributes));
    }

    return collect(range(0, $times - 1))
        ->map(fn () => Mockery::mock(make($className, $attributes)));
}

function mockFileContent(string $path): ?string
{
    try {
        return Storage::disk('mocks')->get($path);
    } catch (FileNotFoundException $e) {
        return null;
    }
}