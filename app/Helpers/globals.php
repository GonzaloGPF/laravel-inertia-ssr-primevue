<?php

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

function morphedModel(?string $type, ?int $id): ?Model
{
    if (! $type || ! $id) {
        return null;
    }

    return resolve(modelByString($type))::findOrFail($id);
}

function getMorphIdAttribute(?string $modelType = null): string
{
    return Str::of($modelType)
        ->classBasename()
        ->snake()
        ->singular()
        ->append('_id')
        ->value();
}

function getMorphTable(?string $modelType = null): string
{
    return Str::of($modelType)
        ->classBasename()
        ->snake()
        ->plural()
        ->value();
}

/**
 * @return string|null string
 */
function modelByString(?string $type): ?string
{
    if (! $type) {
        return null;
    }

    if (Str::startsWith($type, 'App\\Models\\')) {
        return $type;
    }

    return Str::of($type)
        ->singular()
        ->studly()
        ->prepend('App\\Models\\')
        ->value();
}

function toSnake(string $className, ?bool $plural = false): string
{
    return Str::of($className)
        ->classBasename()
        ->snake()
        ->when($plural, fn (\Illuminate\Support\Stringable $value) => $value->plural())
        ->value();
}

/**
 * Helper function to know if User has a specific Role.
 * if no user is specified, it will use logged user
 */
function hasRole(string|BackedEnum $role, ?User $user = null, bool $allowAdmin = false): bool
{
    /** @var ?User $user */
    if (! $user && auth()->hasUser()) {
        $user = auth()->user();
    }

    if (! $user) {
        return false;
    }

    if ($role instanceof BackedEnum) { // same of $role instanceof UnitEnum
        $role = $role->value;
    }

    if ($allowAdmin && $user->role === Roles::ADMIN) {
        return true;
    }

    return $user->role->value === $role;
}

function loadModels(string $interface): Collection
{
    return collect(glob(app_path('Models').'/*.php'))
        ->each(fn (string $file) => require_once $file)
        ->map(fn (string $filePath) => 'App\\Models\\'.basename($filePath, '.php'))
        ->filter(fn (string $class) => implementsInterface($class, $interface))
        ->map(fn (string $modelString) => resolve($modelString));
}

function loadConstants(): Collection
{
    return collect(glob(app_path('Enums').'/*.php'))
        ->each(fn (string $file) => require_once $file)
        ->map(fn (string $filePath) => 'App\\Enums\\'.basename($filePath, '.php'))
        ->filter(fn (string $class) => implementsInterface($class, UnitEnum::class));
}

function implementsInterface(string $class, string $interface): bool
{
    try {
        return (new ReflectionClass($class))->implementsInterface($interface);
    } catch (ReflectionException $e) {
        return false;
    }
}

/**
 * Formats a currency value
 */
function money($value): string
{
    /** @var User $user */
    $user = auth()->user();
    $currency = $user->currency?->getCurrency() ?? config('app.default_currency');
    $locale = $user?->language ?? config('app.locale');

    return Number::currency($value, $currency, $locale);
}

function phone($value): string
{
    return preg_replace('~\D~', '', $value);
}

/**
 * Debugging purposes
 */
function debugBackTrace(): void
{
    print_r(debug_backtrace(2), true);
}
