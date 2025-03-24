<?php

namespace App\Enums;

enum Methods: int
{
    const BANK_DEPOSIT = 0;
    const MOBILE_MONEY = 1;
    const CASH_PICK_UP = 2;

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
