<?php

namespace App\Enums;

enum Channel: int
{
    const TRANSFER = 0;
    const E_TRANSFER = 1;
    const BANK_TRANSFER = 2;

    public static function getAll(): array
    {
        return self::toArray();
    }

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
