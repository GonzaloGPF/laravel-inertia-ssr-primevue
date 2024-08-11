<?php

use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

/**
 * Dates always have formats 'Y-m-d' or 'Y/m/d'
 */
function isDateString($value): bool
{
    if (empty($value)) {
        return false;
    }

    $fails = Validator::make([
        'date' => $value,
    ], [
        'date' => 'date',
    ])->fails();

    return ! $fails;
}

/**
 * Returns a Collection of Carbon dates
 */
function createPeriod(?string $minDate = null, ?string $maxDate = null, ?string $interval = '1 month'): Collection
{
    $minDate = Carbon::parse($minDate) ?? today();

    $maxDate = $maxDate ?? Carbon::parse($minDate)->addYear();

    return collect(CarbonPeriod::create($minDate, $interval, $maxDate));
}
