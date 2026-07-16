<?php

namespace App\Constants;

class StatsWeights
{
    // Zone shot bonus (index 1-9)
    public const ZONE_SHOT_BONUS = [
        1 => 30,
        2 => 18,
        3 => 8,
        4 => -5,
        5 => -20,
        6 => -5,
        7 => 8,
        8 => 18,
        9 => 30,
    ];

    // Zone progress difficulty (index 1-9)
    public const ZONE_PROGRESS_DIFFICULTY = [
        1 => 2.5,
        2 => 2.0,
        3 => 1.5,
        4 => 1.2,
        5 => 1.0,
        6 => 1.2,
        7 => 1.5,
        8 => 2.0,
        9 => 2.5,
    ];

    // Build-up power — primary control/stamina dominate; creative has noticeable effect
    public const BUILD_UP_CONTROL_WEIGHT = 2.0;
    public const BUILD_UP_CREATIVE_WEIGHT = 1.0;  // Increased from 0.8 for more noticeable creative impact
    public const BUILD_UP_STAMINA_WEIGHT = 0.6;

    // Stop progress — primary defense dominates; discipline has noticeable effect
    public const STOP_DEFENSE_WEIGHT = 2.2;
    public const STOP_DISCIPLINE_WEIGHT = 0.5;  // Increased from 0.4 for more noticeable discipline impact

    // Shot power — primary attack dominates; mental has noticeable effect
    public const SHOT_POWER_ATTACK_WEIGHT = 2.4;
    public const SHOT_POWER_MENTAL_WEIGHT = 0.5;  // Increased from 0.4 for more noticeable mental impact

    // Save power — GK secondary in open play, defense primary; mental has noticeable effect
    public const SAVE_POWER_GOALKEEPING_WEIGHT = 1.2;
    public const SAVE_POWER_DEFENSE_WEIGHT = 0.7;
    public const SAVE_POWER_MENTAL_WEIGHT = 0.4;  // Increased from 0.3 for more noticeable mental impact

    // Penalty shot — attack primary, mental secondary
    public const PENALTY_SHOT_ATTACK_WEIGHT = 1.6;
    public const PENALTY_SHOT_MENTAL_WEIGHT = 0.8;

    // Penalty save — goalkeeping special-case
    public const PENALTY_SAVE_GOALKEEPING_WEIGHT = 1.6;
    public const PENALTY_SAVE_MENTAL_WEIGHT = 0.6;

    // Free kick — creative secondary special-case
    public const FREEKICK_POWER_CREATIVE_WEIGHT = 1.4;
    public const FREEKICK_POWER_ATTACK_WEIGHT = 0.9;
    public const FREEKICK_POWER_MENTAL_WEIGHT = 0.6;

    // Counter — pace secondary special-case, attack still leads
    public const COUNTER_PACE_WEIGHT = 1.6;
    public const COUNTER_ATTACK_WEIGHT = 1.2;
    public const COUNTER_CREATIVE_WEIGHT = 0.4;

    // Possession power formula weights (unused currently)
    public const POSSESSION_CONTROL_WEIGHT = 2.0;
    public const POSSESSION_STAMINA_WEIGHT = 0.8;
    public const POSSESSION_MENTAL_WEIGHT = 0.3;

    // Steal power formula weights (unused currently)
    public const STEAL_DEFENSE_WEIGHT = 1.2;
    public const STEAL_PACE_WEIGHT = 0.8;
    public const STEAL_DISCIPLINE_WEIGHT = 0.7;

    // Contest (stamina duel after failed progress)
    public const CONTEST_STAMINA_WEIGHT = 1.0;
    public const CONTEST_CONTROL_WEIGHT = 0.8;
    public const CONTEST_DEFENSE_WEIGHT = 0.8;
}
