<?php

namespace App\Services;

use BackedEnum;
use Exception;
use Illuminate\Support\Str;

class ConstantsService
{
    public static int $DAY = 86400; // 86400 seconds = 1 day

    /**
     * @throws Exception
     */
    public function getConstants(): array
    {
        $locale = app()->getLocale();

        return cache()->remember("constants-$locale", self::$DAY, function () {
            return loadConstants()
                ->mapWithKeys(function (string $enum) {
                    $key = $this->getConstantKey($enum);
                    $values = collect($enum::cases())
                        ->map(fn (BackedEnum $enum) => $enum->value)
                        ->values()
                        ->toArray();

                    return [$key => $values];
                })
                ->toArray();
        });
    }

    private function getConstantKey(string $className): string
    {
        return Str::of(class_basename($className))
            ->plural()
            ->snake()
            ->value();
    }
}
