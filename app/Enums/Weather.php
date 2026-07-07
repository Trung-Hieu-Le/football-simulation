<?php

namespace App\Enums;

enum Weather: string
{
    case CLEAR = 'clear';
    case RAINY = 'rainy';
    case SNOWY = 'snowy';
    case WINDY = 'windy';

    public function displayName(): string
    {
        return match($this) {
            self::CLEAR => 'Clear',
            self::RAINY => 'Rainy',
            self::SNOWY => 'Snowy',
            self::WINDY => 'Windy',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function random(): string
    {
        $values = self::values();
        return $values[array_rand($values)];
    }
}
