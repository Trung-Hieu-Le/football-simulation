<?php

namespace App\Constants;

class SimulationConstants
{
    // Time & situations
    public const SITUATIONS_PER_MINUTE = 3;
    public const HALFTIME_SITUATION = self::TOTAL_SITUATIONS_FULLTIME / 2; // 135
    public const TOTAL_SITUATIONS_FULLTIME = self::SITUATIONS_PER_MINUTE * 90; // 270
    public const TOTAL_SITUATIONS_EXTRATIME = self::SITUATIONS_PER_MINUTE * 30; // 90
    public const EXTRATIME_HALFTIME_SITUATION = self::TOTAL_SITUATIONS_FULLTIME + self::TOTAL_SITUATIONS_EXTRATIME / 2; // 315
    public const EXTRATIME_END_SITUATION = self::TOTAL_SITUATIONS_FULLTIME + self::TOTAL_SITUATIONS_EXTRATIME; // 360
    public const START_SITUATION = 1;
    public const HALFTIME_START_SITUATION = self::HALFTIME_SITUATION + 1; // 136
    public const EXTRATIME_START_SITUATION = self::TOTAL_SITUATIONS_FULLTIME + 1; // 271
    public const EXTRATIME_HALFTIME_START_SITUATION = self::EXTRATIME_HALFTIME_SITUATION + 1; // 316

    // Meta factors
    public const META_BONUS = 1.08; // +8%
    public const META_PENALTY = 0.92; // -8%

    // Stamina & fatigue
    public const STAMINA_FACTOR_BASE = 0.5;
    public const STAMINA_DIVISOR = 200;
    public const HALF_1_DECAY = 0.05;
    public const HALF_2_DECAY = 0.12;
    public const EXTRA_1_DECAY = 0.20;
    public const EXTRA_2_DECAY = 0.30;

    // Base chances
    public const BASE_SHOT_CHANCE = 8;
    public const BASE_FOUL_CHANCE = 7;
    public const BASE_PENALTY_GOAL = 78;
    public const BASE_SPECIAL_EVENT = 6;

    // Foul
    public const FOUL_DISCIPLINE_DIVISOR = 50;
    public const FOUL_CHANCE_MIN = 3;
    public const FOUL_CHANCE_MAX = 12;
    public const PENALTY_CHANCE = 40; // % penalty when foul at position 1 or 9

    // Shot decision
    public const SHOOT_DECISION_BASE_CHANCE = 8;
    public const SHOOT_DECISION_ATTACK_MULTIPLIER = 0.18;
    public const SHOOT_DECISION_MENTAL_MULTIPLIER = 0.10;
    public const SHOOT_DECISION_MAX = 55;

    // Shot & scoring
    public const NORMAL_SHOT_GOAL_CHANCE = 30;
    public const NORMAL_SHOT_ON_TARGET_CHANCE = 40;
    public const SHOT_ATTACK_BONUS_MULTIPLIER = 0.3;
    public const SHOT_ON_TARGET_MIN = 15;
    public const SHOT_ON_TARGET_MAX = 90;

    // Penalty
    public const PENALTY_GOAL_CHANCE = 70;
    public const PENALTY_ON_TARGET_CHANCE = 80;
    public const PENALTY_ON_TARGET_MIN = 70;
    public const PENALTY_ON_TARGET_MAX = 95;
    public const PENALTY_ATTACK_BONUS_MULTIPLIER = 0.1;
    public const PENALTY_MENTAL_BONUS_MULTIPLIER = 0.15;

    // Free kick
    public const FREE_KICK_GOAL_CHANCE = 5;
    public const FREE_KICK_SHOT_CHANCE = 25;
    public const FREE_KICK_ON_TARGET_CHANCE = 25;
    public const FREEKICK_ON_TARGET_MIN = 5;
    public const FREEKICK_ON_TARGET_MAX = 45;
    public const FREEKICK_PASS_DISTANCE_MIN = 1;
    public const FREEKICK_PASS_DISTANCE_MAX = 3;

    // Movement
    public const MOVE_DISTANCE_NORMAL = 1;
    public const MOVE_DISTANCE_FAST = 2;
    public const SPEED_THRESHOLD = 70;
    public const CREATIVE_BONUS_CHANCE = 15; // % chance move +2
    public const DIFFICULTY_FACTOR_MULTIPLIER = 0.3;

    // Counter attack
    public const COUNTER_ATTACK_CHANCE = 15;
    public const COUNTER_MOVE_DISTANCE = 2;
    public const COUNTER_AFTER_SHOT_CHANCE = 30;
    public const COUNTER_STEAL_CHANCE = 50;
    public const COUNTER_DISTANCE_MIN = 1;
    public const COUNTER_DISTANCE_MAX = 5;
    public const COUNTER_DISTANCE_DIVISOR = 20;

    // Special events
    public const SPECIAL_EVENT_LUCK_MULTIPLIER = 0.12;

    // Pressing & pace
    public const PACE_PRESSING_MULTIPLIER = 0.5;
}
