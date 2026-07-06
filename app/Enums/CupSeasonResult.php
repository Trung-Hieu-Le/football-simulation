<?php

namespace App\Enums;

enum CupSeasonResult: string
{
    // Group stage / default
    case GROUP_STAGE = 'group_stage';

    // Knockout rounds (eliminated at)
    case ROUND_OF_32 = 'round_of_32';
    case ROUND_OF_16 = 'round_of_16';
    case QUARTER_FINAL = 'quarter_finals';
    case SEMI_FINAL = 'semi_finals';

    // Top 4
    case CHAMPION = 'champion';
    case RUNNER_UP = 'runner_up';
    case THIRD_PLACE = '3rd_place';
    case FOURTH_PLACE = '4th_place';

    public function displayName(): string
    {
        return match($this) {
            self::GROUP_STAGE => 'Vòng Bảng',
            self::ROUND_OF_32 => 'Vòng 1/16',
            self::ROUND_OF_16 => 'Vòng 1/8',
            self::QUARTER_FINAL => 'Tứ Kết',
            self::SEMI_FINAL => 'Bán Kết',
            self::CHAMPION => 'Vô Địch',
            self::RUNNER_UP => 'Á Quân',
            self::THIRD_PLACE => 'Hạng 3',
            self::FOURTH_PLACE => 'Hạng 4',
        };
    }

    public function displayNameEn(): string
    {
        return match($this) {
            self::GROUP_STAGE => 'Group Stage',
            self::ROUND_OF_32 => 'Round of 32',
            self::ROUND_OF_16 => 'Round of 16',
            self::QUARTER_FINAL => 'Quarter Final',
            self::SEMI_FINAL => 'Semi Final',
            self::CHAMPION => 'Champion',
            self::RUNNER_UP => 'Runner-up',
            self::THIRD_PLACE => '3rd Place',
            self::FOURTH_PLACE => '4th Place',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::CHAMPION => 'badge-warning',
            self::RUNNER_UP => 'badge-secondary',
            self::THIRD_PLACE, self::FOURTH_PLACE => 'badge-info',
            self::SEMI_FINAL => 'badge-primary',
            self::QUARTER_FINAL, self::ROUND_OF_16, self::ROUND_OF_32 => 'badge-secondary',
            self::GROUP_STAGE => 'badge-light',
        };
    }

    public function isTopFour(): bool
    {
        return in_array($this, [
            self::CHAMPION,
            self::RUNNER_UP,
            self::THIRD_PLACE,
            self::FOURTH_PLACE,
        ]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fromRound(string $round, bool $isWinner): self
    {
        return match($round) {
            'final' => $isWinner ? self::CHAMPION : self::RUNNER_UP,
            'third_place' => $isWinner ? self::THIRD_PLACE : self::FOURTH_PLACE,
            'semi_finals' => self::SEMI_FINAL,
            'quarter_finals' => self::QUARTER_FINAL,
            'round_of_16' => self::ROUND_OF_16,
            'round_of_32' => self::ROUND_OF_32,
            default => self::GROUP_STAGE,
        };
    }
}
