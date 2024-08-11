<?php

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;


/**
 * Translate a Model
 */
function modelTitle(string $model, ?bool $plural = false): string
{
    $modelName = Str::snake(class_basename($model));

    return trans_choice("models.$modelName", $plural ? 2 : 1);
}

/**
 * Translate a Model
 */
function constantTitle(string $model, string $value, ?bool $plural = false): string
{
    $modelName = Str::singular(Str::snake(class_basename($model)));

    return trans_choice("constants.$modelName.$modelName", $plural ? 2 : 1);
}

/**
 * Translate a Model name, useful for Constants Models.
 */
function tConstValue(string $constant, string|BackedEnum $value): ?string
{
    if ($value instanceof BackedEnum) {
        $value = $value->value;
    }

    $constant = Str::of(class_basename($constant))
        ->snake()
        ->plural()
        ->value();

    return trans("constants.$constant.$value");
}

/**
 * Translate an action for a Model
 *
 * @param  bool|null  $female
 */
function tAction(string $action, ?string $model = null, bool $female = false, ?bool $plural = false): string
{
    $translation = trans("actions.$action");

    if ($model || str_contains($translation, '|')) {
        $translation = trans_choice("actions.$action", $female ? 2 : 1);
    }

    if ($model) {
        $modelName = Str::snake(class_basename($model));
        $translation = modelTitle($modelName, $plural).' '.$translation;
    }

    return $translation;
}

/**
 * Translate given attribute name
 */
function ta($attribute): string
{
    if (! Lang::has("validation.attributes.$attribute")) {
        return Str::title($attribute);
    }

    return trans("validation.attributes.$attribute");
}

/**
 * Translate given label
 */
function tl($label): string
{
    return trans("labels.$label");
}
