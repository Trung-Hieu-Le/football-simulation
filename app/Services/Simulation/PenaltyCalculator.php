<?php

namespace App\Services\Simulation;

use App\Constants\StatsWeights;
use App\Constants\SimulationConstants;

/**
 * Shared penalty success calculation
 * Used by: ShotHandler (in-play penalties) and PenaltyShootoutService (shootout)
 */
class PenaltyCalculator
{
    public const CONTEXT_OPEN_PLAY = 'open_play';
    public const CONTEXT_SHOOTOUT = 'shootout';

    /**
     * Calculate penalty on-target chance
     */
    public static function onTargetChance(array $shooterStats, string $context = self::CONTEXT_OPEN_PLAY): float
    {
        $baseChance = SimulationConstants::BASE_PENALTY_GOAL;
        $attackBonus = $shooterStats['attack'] * SimulationConstants::PENALTY_ATTACK_BONUS_MULTIPLIER;
        $mentalBonus = $shooterStats['mental'] * SimulationConstants::PENALTY_MENTAL_BONUS_MULTIPLIER;
        
        // Shootout: higher mental pressure
        if ($context === self::CONTEXT_SHOOTOUT) {
            $mentalBonus *= 1.3;
        }
        
        $chance = $baseChance + $attackBonus + $mentalBonus;
        
        return self::clamp($chance, SimulationConstants::PENALTY_ON_TARGET_MIN, SimulationConstants::PENALTY_ON_TARGET_MAX);
    }

    /**
     * Calculate penalty goal chance (from on-target shot)
     */
    public static function goalChance(array $shooterStats, array $keeperStats, string $context = self::CONTEXT_OPEN_PLAY): float
    {
        $shotPower = ($shooterStats['attack'] * StatsWeights::PENALTY_SHOT_ATTACK_WEIGHT)
                   + ($shooterStats['mental'] * StatsWeights::PENALTY_SHOT_MENTAL_WEIGHT);
        
        // Save power: shootout emphasizes goalkeeping + mental composure
        $saveWeight = $context === self::CONTEXT_SHOOTOUT ? 1.8 : 1.6;
        $savePower = ($keeperStats['goalkeeping'] * $saveWeight)
                   + ($keeperStats['mental'] * StatsWeights::PENALTY_SAVE_MENTAL_WEIGHT);
        
        if ($shotPower + $savePower <= 0) {
            return 70.0; // Default fallback
        }
        
        $chance = ($shotPower / ($shotPower + $savePower)) * 100;
        
        return self::clamp($chance, 30, 95);
    }

    /**
     * Full penalty success check (on-target AND goal)
     * Returns: ['success' => bool, 'on_target' => bool, 'goal_chance' => float]
     */
    public static function attempt(array $shooterStats, array $keeperStats, string $context = self::CONTEXT_OPEN_PLAY): array
    {
        $onTargetChance = self::onTargetChance($shooterStats, $context);
        $onTarget = rand(1, 100) <= $onTargetChance;
        
        if (!$onTarget) {
            return [
                'success' => false,
                'on_target' => false,
                'on_target_chance' => round($onTargetChance, 2),
                'goal_chance' => 0,
            ];
        }
        
        $goalChance = self::goalChance($shooterStats, $keeperStats, $context);
        $isGoal = rand(1, 100) <= $goalChance;
        
        return [
            'success' => $isGoal,
            'on_target' => true,
            'on_target_chance' => round($onTargetChance, 2),
            'goal_chance' => round($goalChance, 2),
        ];
    }

    protected static function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }
}
