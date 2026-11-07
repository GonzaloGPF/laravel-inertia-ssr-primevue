<?php

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @param  class-string<BackedEnum>  $enum
 * @return ($multiple is true ? array<int, int|string> : int|string)
 */
function randomConstant(string $enum, bool $multiple = false): int|string|array
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

/**
 * @param  class-string<Model>  $class
 * @param  array<string, mixed>  $parameters
 * @param  int|null  $count
 * @return ($count is null ? Model : Collection<int, Model>)
 */
function make($class, array $parameters = [], $count = null): Collection|Model
{
    return Factory::factoryForModel($class)
        ->count($count)
        ->make($parameters);
}

/**
 * @param  class-string<Model>  $class
 * @param  array<string, mixed>  $parameters
 * @param  int|null  $count
 * @return ($count is null ? Model : Collection<int, Model>)
 */
function create($class, array $parameters = [], $count = null): Collection|Model
{
    return Factory::factoryForModel($class)
        ->count($count)
        ->create($parameters);
}

/**
 * Helper that returns an ID of a specified Model (by class name).
 * If there are any Model, it will grab one randomly, if not, it will create a new one using its Factory
 * When using make() it won't write in Database, instead, it will return a 1
 *
 * @param  class-string<Model>  $modelClass
 * @param  array<string, mixed>  $attributes
 */
function associateTo(string $modelClass, array $attributes = [], bool $create = false): int|string
{
    $isMaking = collect(debug_backtrace())
        ->filter(fn ($item) => $item['function'] === 'make')
        ->filter(fn ($item) => isset($item['file']) && Str::endsWith($item['file'], 'Pest.php'))
        ->count();

    if ($isMaking) {
        return 1;
    }

    if ($create) {
        return create($modelClass, $attributes)->getKey();
    }

    /** @var Model $model */
    $model = resolve($modelClass);

    return $model::where($attributes)->inRandomOrder()->first()?->getKey()
        ?? create($modelClass, $attributes)->getKey(); // call to Factory
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

/**
 * @param  class-string<Model>  $className
 * @param  array<string, mixed>  $attributes
 */
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
