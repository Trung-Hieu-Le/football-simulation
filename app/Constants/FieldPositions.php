<?php

namespace App\Constants;

class FieldPositions
{
    // Field positions (0-10)
    public const GOAL_TEAM1 = 0;
    public const PENALTY_AREA_TEAM1 = 1;
    public const FINAL_THIRD_TEAM1 = 2;
    public const MIDFIELD_LOW = 3;
    public const MIDFIELD_HIGH = 4;
    public const MIDFIELD = 5;
    public const MIDFIELD_LOW_TEAM2 = 6;
    public const MIDFIELD_HIGH_TEAM2 = 7;
    public const FINAL_THIRD_TEAM2 = 8;
    public const PENALTY_AREA_TEAM2 = 9;
    public const GOAL_TEAM2 = 10;

    // Shooting positions
    public const SHOOTING_POSITIONS = [
        self::GOAL_TEAM1,
        self::PENALTY_AREA_TEAM1,
        self::FINAL_THIRD_TEAM1,
        self::FINAL_THIRD_TEAM2,
        self::PENALTY_AREA_TEAM2,
        self::GOAL_TEAM2,
    ];

    // Position names for display
    public const POSITION_NAMES = [
        self::GOAL_TEAM1 => 'Goal Team 1',
        self::PENALTY_AREA_TEAM1 => 'Penalty Area Team 1',
        self::FINAL_THIRD_TEAM1 => 'Final Third Team 1',
        self::MIDFIELD_LOW => 'Midfield Low',
        self::MIDFIELD_HIGH => 'Midfield High',
        self::MIDFIELD => 'Midfield Center',
        self::MIDFIELD_LOW_TEAM2 => 'Midfield Low Team 2',
        self::MIDFIELD_HIGH_TEAM2 => 'Midfield High Team 2',
        self::FINAL_THIRD_TEAM2 => 'Final Third Team 2',
        self::PENALTY_AREA_TEAM2 => 'Penalty Area Team 2',
        self::GOAL_TEAM2 => 'Goal Team 2',
    ];
}
