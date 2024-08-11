<?php

namespace App\Interfaces;

interface FilterableMorph
{
    public static function getMorphColumn(): string;

    public function modelFilter(): string;
}
