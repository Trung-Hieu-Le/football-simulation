<?php

namespace App\Constants;

/**
 * Stat formula weights only — zone tables live in ZoneHelpers.
 */
class StatsWeights
{
    // Build-up power — control/stamina dominate; creative noticeable
    public const BUILD_UP_CONTROL_WEIGHT = 2.0;
    public const BUILD_UP_CREATIVE_WEIGHT = 1.35;
    public const BUILD_UP_STAMINA_WEIGHT = 0.6;

    // Stop progress — defense blocks advance (× zone difficulty)
    public const STOP_DEFENSE_WEIGHT = 2.5;
    public const STOP_PHYSICAL_WEIGHT = 0.65;

    // Shot on-target — attack vs defense (GK not involved)
    public const ON_TARGET_ATTACK_WEIGHT = 0.42;
    public const ON_TARGET_DEFENSE_WEIGHT = 0.28;

    // Shot power — attack leads; conversion tempered by saves
    public const SHOT_POWER_ATTACK_WEIGHT = 2.0;
    public const SHOT_POWER_MENTAL_WEIGHT = 0.55;

    // Save power — GK leads; defense supports blocks in the box
    public const SAVE_POWER_GOALKEEPING_WEIGHT = 1.75;
    public const SAVE_POWER_DEFENSE_WEIGHT = 1.20;
    public const SAVE_POWER_MENTAL_WEIGHT = 0.40;

    // Penalty shot
    public const PENALTY_SHOT_ATTACK_WEIGHT = 1.6;
    public const PENALTY_SHOT_MENTAL_WEIGHT = 0.8;

    // Penalty save
    public const PENALTY_SAVE_GOALKEEPING_WEIGHT = 1.6;
    public const PENALTY_SAVE_MENTAL_WEIGHT = 0.6;

    // Free kick
    public const FREEKICK_POWER_CREATIVE_WEIGHT = 1.4;
    public const FREEKICK_POWER_ATTACK_WEIGHT = 0.9;
    public const FREEKICK_POWER_MENTAL_WEIGHT = 0.6;

    // Counter
    public const COUNTER_PACE_WEIGHT = 1.6;
    public const COUNTER_ATTACK_WEIGHT = 1.2;
    public const COUNTER_CREATIVE_WEIGHT = 0.4;

    // Contest (after failed progress)
    public const CONTEST_STAMINA_WEIGHT = 1.0;
    public const CONTEST_CONTROL_WEIGHT = 0.8;
    public const CONTEST_DEFENSE_WEIGHT = 0.8;
    public const CONTEST_PHYSICAL_WEIGHT = 0.5;
}
