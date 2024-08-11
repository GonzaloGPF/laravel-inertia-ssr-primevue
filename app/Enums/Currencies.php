<?php

namespace App\Enums;

enum Currencies: string
{
    case EURO = 'euro';
    case DOLLAR = 'dollar';

    public function getCurrency(): string
    {
        return match ($this) {
            self::EURO => 'EUR',
            self::DOLLAR => 'USD',
        };
    }
}
