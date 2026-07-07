<?php

namespace App\Enums;

enum MatchResult: string
{
    case WIN = 'win';
    case DRAW = 'draw';
    case LOSE = 'lose';

    public function displayName(): string
    {
        return match($this) {
            self::WIN => 'Win',
            self::DRAW => 'Draw',
            self::LOSE => 'Lose',
        };
    }

    /** ELO actual score: 1.0 win, 0.5 draw, 0.0 loss */
    public function eloPoints(): float
    {
        return match($this) {
            self::WIN => 1.0,
            self::DRAW => 0.5,
            self::LOSE => 0.0,
        };
    }

    public static function fromScores(int $team1Score, int $team2Score): self
    {
        if ($team1Score > $team2Score) {
            return self::WIN;
        } elseif ($team1Score < $team2Score) {
            return self::LOSE;
        }
        return self::DRAW;
    }
}
