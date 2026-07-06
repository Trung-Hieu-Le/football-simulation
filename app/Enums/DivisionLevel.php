<?php

namespace App\Enums;

enum DivisionLevel: string
{
    case DIVISION1 = 'division1';
    case DIVISION2 = 'division2';
    case DIVISION3 = 'division3';

    public function displayName(): string
    {
        return match($this) {
            self::DIVISION1 => 'Hạng 1',
            self::DIVISION2 => 'Hạng 2',
            self::DIVISION3 => 'Hạng 3',
        };
    }

    public function displayNameEn(): string
    {
        return match($this) {
            self::DIVISION1 => 'Division 1',
            self::DIVISION2 => 'Division 2',
            self::DIVISION3 => 'Division 3',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function all(): array
    {
        return self::cases();
    }
}
