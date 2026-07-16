<?php

namespace App\Enums;

enum SeasonMeta: string
{
    case POSSESSION = 'possession';
    case DIRECT = 'direct';
    case COUNTER_ATTACK = 'counter_attack';
    case HIGH_PRESS = 'high_press';
    case LOW_BLOCK = 'low_block';
    case LONG_BALL = 'long_ball';
    case WING_PLAY = 'wing_play';
    case BALANCED = 'balanced';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function random(): string
    {
        $values = self::values();

        return $values[array_rand($values)];
    }

    public function displayName(): string
    {
        return match ($this) {
            self::POSSESSION => 'Possession',
            self::DIRECT => 'Direct',
            self::COUNTER_ATTACK => 'Counter Attack',
            self::HIGH_PRESS => 'High Press',
            self::LOW_BLOCK => 'Low Block',
            self::LONG_BALL => 'Long Ball',
            self::WING_PLAY => 'Wing Play',
            self::BALANCED => 'Balanced',
        };
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->displayName();
        }

        return $options;
    }
}
