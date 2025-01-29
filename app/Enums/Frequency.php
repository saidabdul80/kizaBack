<?php

namespace App\Enums;

enum Frequency
{
    const DEFAULT = 0;
    const DAILY = 1;
    const WEEKLY = 2;
    const MONTHLY = 3;
    const YEARLY = 4;

    private const RELATED = [];
    public static function getKey($value)
    {
        $constants = self::toArray();
        return array_search($value, $constants);
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

    public static function toArrayWithRelationship()
    {
        $constants = self::toArray();
        asort($constants);
        $result = [];

        foreach ($constants as $key => $value) {
            if(!is_array($value)){
                $result[$key] = [
                    'name' => $key,
                    'value' => $value,
                    'label' => ucwords(strtolower(str_replace('_',' ',$key))),
                ];
            }
        }

        return $result;
    }

}
