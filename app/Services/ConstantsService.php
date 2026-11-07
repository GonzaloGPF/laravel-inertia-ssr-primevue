<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Str;

class ConstantsService
{
    public static int $DAY = 86400; // 86400 seconds = 1 day

    /**
     * @return array<string, array<int, int|string>>
     *
     * @throws Exception
     */
    public function getConstants(): array
    {
        $locale = app()->getLocale();

        return cache()->remember("constants-$locale", self::$DAY, function () {
            return loadConstants()
                ->mapWithKeys(function (string $enum) {
                    $key = $this->getConstantKey($enum);

                    return [$key => array_column($enum::cases(), 'value')];
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
