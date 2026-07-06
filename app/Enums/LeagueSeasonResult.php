<?php

namespace App\Enums;

enum LeagueSeasonResult: string
{
    case CHAMPION = 'champion';
    case PROMOTED = 'promoted';
    case RELEGATED = 'relegated';
    case STAY = 'stay';

    public function displayName(): string
    {
        return match($this) {
            self::CHAMPION => 'Vô Địch',
            self::PROMOTED => 'Thăng Hạng',
            self::RELEGATED => 'Xuống Hạng',
            self::STAY => 'Giữ Hạng',
        };
    }

    public function displayNameEn(): string
    {
        return match($this) {
            self::CHAMPION => 'Champion',
            self::PROMOTED => 'Promoted',
            self::RELEGATED => 'Relegated',
            self::STAY => 'Stay',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::CHAMPION => 'badge-warning',
            self::PROMOTED => 'badge-success',
            self::RELEGATED => 'badge-danger',
            self::STAY => 'badge-secondary',
        };
    }

    public function tableClass(): string
    {
        return match($this) {
            self::CHAMPION => 'table-warning',
            self::PROMOTED => 'table-success',
            self::RELEGATED => 'table-danger',
            self::STAY => '',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
