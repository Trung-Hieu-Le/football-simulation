<?php

namespace App\Enums;

enum MatchResult: float
{
    case WIN = 1.0;
    case DRAW = 0.5;
    case LOSE = 0.0;

    public function displayName(): string
    {
        return match($this) {
            self::WIN => 'Win',
            self::DRAW => 'Draw',
            self::LOSE => 'Lose',
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
