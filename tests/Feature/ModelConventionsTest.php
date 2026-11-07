<?php

use App\Traits\HasCreatedBy;
use Illuminate\Support\Facades\Schema;

it('has a created_by column when it uses HasCreatedBy, and only then', function (string $model) {
    expect(Schema::hasColumn((new $model)->getTable(), 'created_by'))
        ->toBe(in_array(HasCreatedBy::class, class_uses_recursive($model), true));
})->with('models');
