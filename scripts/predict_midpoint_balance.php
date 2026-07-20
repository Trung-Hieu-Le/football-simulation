<?php

/**
 * Midpoint balance prediction for current simulation formulas.
 *
 * Does NOT run a full match — prints formula chances (as if rand ≈ midpoint)
 * so you can see whether move/shot/goal rates look sane before playing.
 *
 * Run:
 *   php8.2 scripts/predict_midpoint_balance.php
 *   php8.2 scripts/predict_midpoint_balance.php | jq .
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Constants\SimulationConstants;
use App\Constants\StatsWeights;
use App\Services\Simulation\MetaModifiers;
use App\Services\Simulation\ZoneHelpers;

function clamp(float $v, float $min, float $max): float
{
    return max($min, min($max, $v));
}

function specialEventChance(float $luck): float
{
    return SimulationConstants::BASE_SPECIAL_EVENT
        + ($luck * SimulationConstants::SPECIAL_EVENT_LUCK_MULTIPLIER);
}

function analyzePair(string $label, float $a, float $b, string $meta = 'balanced', int $team = 1): array
{
    $modifiers = MetaModifiers::for($meta);
    $keys = [
        'attack', 'defense', 'control', 'creative', 'pace',
        'mental', 'physical', 'luck', 'stamina', 'goalkeeping',
    ];
    $atk = array_fill_keys($keys, $a);
    $def = array_fill_keys($keys, $b);

    $buildUpPower = ($atk['control'] * StatsWeights::BUILD_UP_CONTROL_WEIGHT)
                  + ($atk['creative'] * StatsWeights::BUILD_UP_CREATIVE_WEIGHT)
                  + ($atk['stamina'] * StatsWeights::BUILD_UP_STAMINA_WEIGHT);
    $stopProgress = ($def['defense'] * StatsWeights::STOP_DEFENSE_WEIGHT)
                  + ($def['physical'] * StatsWeights::STOP_PHYSICAL_WEIGHT);

    $zones = [];
    foreach ([3, 5, 7, 8, 9] as $zone) {
        $diff = ZoneHelpers::zoneDifficulty($zone, $team);
        $mc = $buildUpPower / ($buildUpPower + ($stopProgress * $diff));
        $zones[$zone] = round(clamp($mc * 100 * $modifiers['move_chance'], 8, 92), 2);
    }

    // Pressing / miscontrol at zone 5 (midfield weight 1.5)
    $midWeight = ZoneHelpers::midfieldWeight(5);
    $pressPower = $def['pace'] * 0.25 + $def['physical'] * 0.30 + $def['control'] * 0.25;
    $resistPower = $atk['control'] * 0.30 + specialEventChance($atk['luck']) * 20;
    $pressing = clamp(
        2.0 * $modifiers['pressing'] * $midWeight + ($pressPower - $resistPower) * 0.04,
        0.5,
        8
    );
    $miscontrol = clamp(
        1.2 * $modifiers['miscontrol'] * $midWeight
            - ($atk['control'] - 70) * 0.08
            + specialEventChance($atk['luck']) * 0.25,
        0.2,
        5
    );

    // Foul (new formula)
    $foul = SimulationConstants::BASE_FOUL_CHANCE
        + max(0, ($def['physical'] - $def['defense']) * 0.03)
        - ($def['defense'] - 70) * 0.08
        - specialEventChance($def['luck']) * 0.22;
    $foul = round(clamp($foul * $modifiers['foul_chance'], SimulationConstants::FOUL_CHANCE_MIN, SimulationConstants::FOUL_CHANCE_MAX), 2);

    $shotAt = function (int $zone) use ($atk, $modifiers, $team): float {
        $zoneBonus = ZoneHelpers::shotBonus($zone, $team);
        $chance = SimulationConstants::SHOOT_DECISION_BASE_CHANCE
            + $zoneBonus
            + ($atk['attack'] * SimulationConstants::SHOOT_DECISION_ATTACK_MULTIPLIER)
            + ($atk['mental'] * SimulationConstants::SHOOT_DECISION_MENTAL_MULTIPLIER);
        $chance *= $modifiers['shot_decision'];

        return round(min(SimulationConstants::SHOOT_DECISION_MAX, $chance), 2);
    };

    $onTargetDist1 = clamp(
        SimulationConstants::NORMAL_SHOT_ON_TARGET_CHANCE
            + max(0, (3 - 1)) * 5
            + ($atk['attack'] * StatsWeights::ON_TARGET_ATTACK_WEIGHT)
            - ($def['defense'] * StatsWeights::ON_TARGET_DEFENSE_WEIGHT),
        SimulationConstants::SHOT_ON_TARGET_MIN,
        SimulationConstants::SHOT_ON_TARGET_MAX
    );

    $shotPower = ($atk['attack'] * StatsWeights::SHOT_POWER_ATTACK_WEIGHT)
               + ($atk['mental'] * StatsWeights::SHOT_POWER_MENTAL_WEIGHT);
    $savePower = ($def['goalkeeping'] * StatsWeights::SAVE_POWER_GOALKEEPING_WEIGHT)
               + ($def['defense'] * StatsWeights::SAVE_POWER_DEFENSE_WEIGHT)
               + ($def['mental'] * StatsWeights::SAVE_POWER_MENTAL_WEIGHT);
    $goalChance = round(($shotPower / ($shotPower + $savePower)) * 100, 2);

    $controlRatio = ($atk['control'] / ($atk['control'] + $def['control'])) * 100;
    $retain = clamp(
        8.0 * ($modifiers['retain_chance'] ?? 1.0)
            + ($atk['control'] - 70) * 0.35
            - ($def['defense'] - 70) * 0.15 * ZoneHelpers::zoneDifficulty(5, $team),
        60,
        95
    );

    $midpoint = static fn (float $chance): string => $chance >= 50 ? 'YES (chance>=50)' : 'NO (chance<50)';

    $pFoul = $foul / 100;
    $pMis = (1 - $pFoul) * ($miscontrol / 100);
    $pPress = (1 - $pFoul) * (1 - $miscontrol / 100) * ($pressing / 100);
    $pReachProgress = (1 - $pFoul) * (1 - $miscontrol / 100) * (1 - $pressing / 100);
    $moveMid = $zones[5];
    $pFailProgress = $pReachProgress * (1 - $moveMid / 100);
    $pSuccessProgress = $pReachProgress * ($moveMid / 100);
    $pCleanMove = $pSuccessProgress * ($retain / 100);

    return [
        'label' => $label,
        'stats' => "attacker={$a} defender={$b} meta={$meta} team={$team}",
        'chances_pct' => [
            'foul' => $foul,
            'miscontrol' => round($miscontrol, 2),
            'pressing' => round($pressing, 2),
            'move_zone5' => $moveMid,
            'move_zone8' => $zones[8],
            'move_zone9' => $zones[9],
            'retain_after_move' => round($retain, 2),
            'control_ratio' => round($controlRatio, 2),
            'shot_decision_zone8' => $shotAt(8),
            'shot_decision_zone9' => $shotAt(9),
            'on_target_distance1' => round($onTargetDist1, 2),
            'goal_if_on_target' => $goalChance,
        ],
        'midpoint_triggers_if_rand_50' => [
            'foul' => $midpoint($foul),
            'miscontrol' => $midpoint($miscontrol),
            'pressing' => $midpoint($pressing),
            'progress_zone5' => $midpoint($moveMid),
            'retain' => $midpoint($retain),
            'shot_decision_zone9' => $midpoint($shotAt(9)),
            'on_target' => $midpoint($onTargetDist1),
            'goal' => $midpoint($goalChance),
        ],
        'expected_branch_share_at_zone5' => [
            'foul' => round($pFoul * 100, 2),
            'miscontrol' => round($pMis * 100, 2),
            'pressing' => round($pPress * 100, 2),
            'progress_fail_contest_path' => round($pFailProgress * 100, 2),
            'clean_move_keep_ball' => round($pCleanMove * 100, 2),
            'note' => 'Approx shares for situations starting at zone 5',
        ],
        'diagnosis' => [
            'clean_move_rare' => ($pCleanMove * 100) < 20,
            'shot_decision_always_at_box' => $shotAt(9) >= 50,
            'goal_conversion_high' => $goalChance >= 50,
            'zone_mirror_ok' => ZoneHelpers::zoneDifficulty(8, 1) === ZoneHelpers::zoneDifficulty(2, 2),
        ],
    ];
}

$cases = [
    analyzePair('near_equal_90_vs_88', 90, 88),
    analyzePair('gap_90_vs_80', 90, 80),
    analyzePair('near_equal_88_has_ball_vs_90', 88, 90),
    analyzePair('gap_80_has_ball_vs_90', 80, 90),
    analyzePair('team2_attack_zone_mirror', 90, 88, 'balanced', 2),
];

echo json_encode($cases, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
