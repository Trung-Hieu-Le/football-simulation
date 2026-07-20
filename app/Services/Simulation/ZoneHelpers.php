<?php

namespace App\Services\Simulation;

/**
 * Single source of truth for zone geometry and difficulty.
 * Team1 attacks toward zone 10; Team2 attacks toward zone 0.
 */
class ZoneHelpers
{
    /**
     * Progress difficulty by attack distance (1 = near opponent goal … 5 = midfield … 9 = own half).
     */
    public const PROGRESS_DIFFICULTY_BY_DISTANCE = [
        1 => 3.4,
        2 => 2.7,
        3 => 1.8,
        4 => 1.3,
        5 => 1.0,
        6 => 1.3,
        7 => 1.8,
        8 => 2.7,
        9 => 3.4,
    ];

    /**
     * Shot-decision bonus by attack distance (closer = higher).
     */
    public const SHOT_BONUS_BY_DISTANCE = [
        1 => 16,
        2 => 9,
        3 => 3,
        4 => -10,
        5 => -28,
        6 => -10,
        7 => 3,
        8 => 9,
        9 => 16,
    ];

    /**
     * Distance from opponent's goal (0 = at goal line, 10 = own goal line).
     */
    public static function attackDistance(int $zone, int $team): int
    {
        return $team == 1 ? (10 - $zone) : $zone;
    }

    /**
     * Attacking third: team1 zones 8–10, team2 zones 0–2.
     */
    public static function isAttackingThird(int $zone, int $team): bool
    {
        return self::attackDistance($zone, $team) <= 2;
    }

    /**
     * Progress difficulty for the team currently in possession.
     */
    public static function zoneDifficulty(int $zone, int $team): float
    {
        $distance = self::attackDistance($zone, $team);

        return self::PROGRESS_DIFFICULTY_BY_DISTANCE[$distance] ?? 1.0;
    }

    /**
     * Shot-decision zone bonus for the attacking team.
     */
    public static function shotBonus(int $zone, int $team): int
    {
        $distance = self::attackDistance($zone, $team);

        return self::SHOT_BONUS_BY_DISTANCE[$distance] ?? -28;
    }

    /**
     * Midfield (3–7) presses/miscontrols more than final thirds.
     */
    public static function midfieldWeight(int $zone): float
    {
        return ($zone >= 3 && $zone <= 7) ? 1.5 : 0.8;
    }
}
