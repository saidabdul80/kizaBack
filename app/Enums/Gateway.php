<?php

namespace App\Enums;

enum Gateway: int
{
    const WALLET = 0;
    const COWRISPAY = 1;
    const FIAT_MATCH = 2;

    public static function getKey($value)
    {
        $constants = self::toArray();
        return array_search($value, $constants, true);
    }

    public static function getValue(string $key): ?int
    {
        $constants = self::toArray();
        return $constants[$key] ?? null;
    }


    private static function toArray(): array
    {
        $reflection = new \ReflectionClass(self::class);
        return $reflection->getConstants();
    }
}
