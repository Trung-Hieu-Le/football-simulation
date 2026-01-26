<?php

namespace App\Enums;

enum SeasonMeta: string
{
    case POSSESSION = 'possession';
    case COUNTER = 'counter';
    case PRESSING = 'pressing';
    case TIKI_TAKA = 'tiki-taka';
    case LONG_BALL = 'long_ball';
    case BUILD_UP = 'build_up';
    case LOW_BLOCK = 'low_block';
    case HIGH_RISK = 'high_risk';
    case HIGH_LINE = 'high_line';

    /**
     * Get all meta values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get random meta value
     */
    public static function random(): string
    {
        $values = self::values();
        return $values[array_rand($values)];
    }

    /**
     * Get display name for meta
     */
    public function displayName(): string
    {
        return match($this) {
            self::POSSESSION => 'Possession',
            self::COUNTER => 'Counter',
            self::PRESSING => 'Pressing',
            self::TIKI_TAKA => 'Tiki-taka',
            self::LONG_BALL => 'Long Ball',
            self::BUILD_UP => 'Build Up',
            self::LOW_BLOCK => 'Low Block',
            self::HIGH_RISK => 'High Risk',
            self::HIGH_LINE => 'High Line',
        };
    }

    /**
     * Get all metas with display names for select options
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->displayName();
        }
        return $options;
    }
}

