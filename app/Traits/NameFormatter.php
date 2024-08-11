<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

trait NameFormatter
{
    public function name(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => Str::title($value),
            set: fn (?string $value) => Str::of($value)->title()->trim()->value(),
        );
    }
}
