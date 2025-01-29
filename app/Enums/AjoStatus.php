<?php

namespace App\Enums;

enum AjoStatus
{
    const ACTIVE = 0;
    const INACTIVE = 1;

    private const RELATED = [];
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

    public static function getValues(): array
    {
        $reflection = new \ReflectionClass(self::class);
        $constants =  $reflection->getConstants();
        return array_values($constants);
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
