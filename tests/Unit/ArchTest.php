<?php

use App\Interfaces\HasCreatedByInterface;
use App\Interfaces\HasFilesInterface;
use App\Traits\HasCreatedBy;
use App\Traits\HasFiles;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

arch()->preset()->php();
arch()->preset()->security();
arch()->preset()->laravel();

arch('the models have a ulid as their id')
    ->expect('App\Models')
    ->toUseTrait(HasUlids::class);

arch('the enums are backed by strings, as they are what is saved in the database')
    ->expect('App\Enums')
    ->toBeStringBackedEnums();

arch('the interfaces are interfaces')
    ->expect('App\Interfaces')
    ->toBeInterfaces();

arch('the services are named as such')
    ->expect('App\Services')
    ->toHaveSuffix('Service');

arch('the filters are named as such, and are model filters')
    ->expect('App\Filters')
    ->toHaveSuffix('Filter')
    ->toExtend(ModelFilter::class);

arch('the controllers are all the children of the controller of the application')
    ->expect('App\Http\Controllers')
    ->toExtend('App\Http\Controllers\Controller')
    ->ignoring('App\Http\Controllers\Controller');

it('implements the interface of a trait when it uses it, and only then', function (string $model, string $trait, string $interface) {
    expect(in_array($interface, class_implements($model), true))
        ->toBe(in_array($trait, class_uses_recursive($model), true));
})->with('models')->with([
    'files' => [HasFiles::class, HasFilesInterface::class],
    'created by' => [HasCreatedBy::class, HasCreatedByInterface::class],
]);

it('has a filter named after it', function (string $model) {
    expect(class_exists('App\\Filters\\'.class_basename($model).'Filter'))->toBeTrue();
})->with('filterable models');
