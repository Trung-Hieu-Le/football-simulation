<?php

namespace App\Services\Simulation\EventHandlers;

use App\Constants\SimulationConstants;
use App\Constants\FieldPositions;
use App\Constants\StatsWeights;
use App\Services\Simulation\BaseSimulationService;
use App\Services\Simulation\Concerns\RecordsMatchEvents;
use App\Services\Simulation\MetaModifiers;

class ShotHandler extends BaseSimulationService
{
    use RecordsMatchEvents;

    // Special shot event constants
    public const CLUTCH_SHOT_BASE_CHANCE = 3;  // Base chance for clutch shot in last minutes
    public const OWN_GOAL_BASE_CHANCE = 1;     // Base chance for own goal

    public function handleShot(
        int $fieldPosition,
        int $currentTeam,
        array $team1Stats,
        array $team2Stats,
        $team1,
        $team2,
        int $time,
        array &$matchData,
        bool $isPenalty = false,
        array $modifiers = []
    ): array {
        $modifiers = array_merge(MetaModifiers::defaults(), $modifiers);
        $attackingStats = $currentTeam == 1 ? $team1Stats : $team2Stats;
        $defendingStats = $currentTeam == 1 ? $team2Stats : $team1Stats;

        if ($isPenalty) {
            return $this->handlePenaltyShot($currentTeam, $attackingStats, $defendingStats, $time, $matchData);
        }

        $distance = $this->calculateDistanceToGoal($fieldPosition, $currentTeam);
        
        $onTargetChance = $this->calculateOnTargetChance($distance, $attackingStats['attack']);
        $onTarget = rand(1, 100) <= $onTargetChance;

        $this->recordShot($currentTeam, $matchData, $onTarget);

        // Check for own goal (rare defensive error in penalty area)
        if ($this->tryOwnGoal($fieldPosition, $defendingStats['discipline'], $defendingStats['luck'])) {
            $this->recordGoal($currentTeam, $time, $matchData, 'own_goal');
            return [
                'fieldPosition' => FieldPositions::MIDFIELD,
                'currentTeam' => $currentTeam == 1 ? 2 : 1,
                'goal' => true,
                'goalScoredBy' => $currentTeam,
            ];
        }

        if ($onTarget) {
            $goalChance = $this->shotGoalChance(
                $attackingStats['attack'],
                $attackingStats['mental'],
                $defendingStats['goalkeeping'],
                $defendingStats['defense'],
                $defendingStats['mental']
            ) * 100;

            // Clutch shot bonus in last minutes
            if ($this->rollClutchShot($time, $attackingStats['mental'], $attackingStats['luck'], $modifiers)) {
                $goalBoost = 1.0 + (0.3 * $modifiers['clutch_goal']);
                $goalChance = min(95, $goalChance * $goalBoost);
                $this->recordTimelineEvent($time, "Clutch shot by Team{$currentTeam}!", $matchData);
            }

            $isGoal = rand(1, 100) <= $goalChance;

            if ($isGoal) {
                $this->recordGoal($currentTeam, $time, $matchData, 'goal');
                return [
                    'fieldPosition' => FieldPositions::MIDFIELD,
                    'currentTeam' => $currentTeam == 1 ? 2 : 1,
                    'goal' => true,
                    'goalScoredBy' => $currentTeam,
                ];
            }

            // Shot on target but saved - 30% counter chance
            if (rand(1, 100) <= SimulationConstants::COUNTER_AFTER_SHOT_CHANCE) {
                return $this->counterAttack($fieldPosition, $currentTeam, $defendingStats);
            }
        } else {
            // Shot off target - 50% steal chance
            if (rand(1, 100) <= SimulationConstants::COUNTER_STEAL_CHANCE) {
                $newTeam = $currentTeam == 1 ? 2 : 1;
                return [
                    'fieldPosition' => $fieldPosition,
                    'currentTeam' => $newTeam,
                    'goal' => false,
                ];
            }
        }

        return [
            'fieldPosition' => $fieldPosition,
            'currentTeam' => $currentTeam,
            'goal' => false,
        ];
    }

    protected function handlePenaltyShot(int $currentTeam, array $attackingStats, array $defendingStats, int $time, array &$matchData): array
    {
        $onTargetChance = SimulationConstants::BASE_PENALTY_GOAL 
                        + ($attackingStats['attack'] * SimulationConstants::PENALTY_ATTACK_BONUS_MULTIPLIER)
                        + ($attackingStats['mental'] * SimulationConstants::PENALTY_MENTAL_BONUS_MULTIPLIER);
        $onTargetChance = $this->clamp($onTargetChance, SimulationConstants::PENALTY_ON_TARGET_MIN, SimulationConstants::PENALTY_ON_TARGET_MAX);

        $onTarget = rand(1, 100) <= $onTargetChance;
        $this->recordShot($currentTeam, $matchData, $onTarget);

        if ($onTarget) {
            $penaltyShotPower = ($attackingStats['attack'] * StatsWeights::PENALTY_SHOT_ATTACK_WEIGHT)
                              + ($attackingStats['mental'] * StatsWeights::PENALTY_SHOT_MENTAL_WEIGHT);
            $penaltySavePower = ($defendingStats['goalkeeping'] * StatsWeights::PENALTY_SAVE_GOALKEEPING_WEIGHT)
                              + ($defendingStats['mental'] * StatsWeights::PENALTY_SAVE_MENTAL_WEIGHT);
            
            $goalChance = ($penaltyShotPower / ($penaltyShotPower + $penaltySavePower)) * 100;
            $isGoal = rand(1, 100) <= $goalChance;

            if ($isGoal) {
                $this->recordGoal($currentTeam, $time, $matchData, 'penalty');
                return [
                    'fieldPosition' => FieldPositions::MIDFIELD,
                    'currentTeam' => $currentTeam == 1 ? 2 : 1,
                    'goal' => true,
                    'goalScoredBy' => $currentTeam,
                ];
            }
        }

        return [
            'fieldPosition' => FieldPositions::MIDFIELD,
            'currentTeam' => $currentTeam == 1 ? 2 : 1,
            'goal' => false,
        ];
    }

    public function handleFreeKickShot(int $currentTeam, array $attackingStats, array $defendingStats, int $time, array &$matchData): ?array
    {
        if (rand(1, 100) > SimulationConstants::FREE_KICK_SHOT_CHANCE) {
            return null; // Pass instead
        }

        $onTargetChance = SimulationConstants::FREE_KICK_ON_TARGET_CHANCE 
                        + ($attackingStats['attack'] * 0.3) 
                        + ($attackingStats['mental'] * 0.10);
        $onTargetChance = $this->clamp($onTargetChance, SimulationConstants::FREEKICK_ON_TARGET_MIN, SimulationConstants::FREEKICK_ON_TARGET_MAX);

        $onTarget = rand(1, 100) <= $onTargetChance;
        $this->recordShot($currentTeam, $matchData, $onTarget);

        if ($onTarget) {
            $freeKickPower = ($attackingStats['creative'] * StatsWeights::FREEKICK_POWER_CREATIVE_WEIGHT)
                           + ($attackingStats['attack'] * StatsWeights::FREEKICK_POWER_ATTACK_WEIGHT)
                           + ($attackingStats['mental'] * StatsWeights::FREEKICK_POWER_MENTAL_WEIGHT);
            $savePower = ($defendingStats['goalkeeping'] * StatsWeights::PENALTY_SAVE_GOALKEEPING_WEIGHT)
                       + ($defendingStats['mental'] * StatsWeights::PENALTY_SAVE_MENTAL_WEIGHT);
            
            $goalChance = ($freeKickPower / ($freeKickPower + $savePower)) * 100;
            $isGoal = rand(1, 100) <= $goalChance;

            if ($isGoal) {
                $this->recordGoal($currentTeam, $time, $matchData, 'freekick');
                return [
                    'fieldPosition' => FieldPositions::MIDFIELD,
                    'currentTeam' => $currentTeam == 1 ? 2 : 1,
                    'goal' => true,
                    'goalScoredBy' => $currentTeam,
                ];
            }
        }

        return null; // Pass after free kick miss
    }

    protected function calculateDistanceToGoal(int $fieldPosition, int $currentTeam): int
    {
        if ($currentTeam == 1) {
            return 10 - $fieldPosition;
        } else {
            return $fieldPosition;
        }
    }

    protected function calculateOnTargetChance(int $distance, float $attack): float
    {
        $distanceBonus = max(0, (3 - $distance)) * 5;
        $onTargetChance = SimulationConstants::NORMAL_SHOT_ON_TARGET_CHANCE 
                        + $distanceBonus 
                        + ($attack * SimulationConstants::SHOT_ATTACK_BONUS_MULTIPLIER);
        
        return $this->clamp($onTargetChance, SimulationConstants::SHOT_ON_TARGET_MIN, SimulationConstants::SHOT_ON_TARGET_MAX);
    }

    protected function recordShot(int $team, array &$matchData, bool $onTarget): void
    {
        $shotsKey = $team == 1 ? 'team1_shots' : 'team2_shots';
        $matchData[$shotsKey] = ($matchData[$shotsKey] ?? 0) + 1;

        if ($onTarget) {
            $onTargetKey = $team == 1 ? 'team1_shots_on_target' : 'team2_shots_on_target';
            $matchData[$onTargetKey] = ($matchData[$onTargetKey] ?? 0) + 1;
        }
    }

    /**
     * Check for clutch shot in last minutes (85-90, 115-120)
     * High mental stat increases chance
     */
    public function rollClutchShot(int $time, float $mental, float $luck, array $modifiers = []): bool
    {
        $modifiers = array_merge(MetaModifiers::defaults(), $modifiers);
        $isLastMinutes = ($time >= 85 && $time <= 90) || ($time >= 115 && $time <= 120);

        if (!$isLastMinutes) {
            return false;
        }

        $baseChance = self::CLUTCH_SHOT_BASE_CHANCE * $modifiers['clutch_goal'];
        $mentalBonus = ($mental - 70) * 0.08;
        $luckModifier = $this->specialEventChance($luck) * 0.2;

        $chance = $baseChance + $mentalBonus + $luckModifier;
        $chance = $this->clamp($chance, 0.5, 15);

        return rand(1, 1000) <= ($chance * 10);
    }

    /**
     * Try for own goal event (rare, defender error in penalty area)
     * Low discipline or bad luck increases chance
     */
    public function tryOwnGoal(int $fieldPosition, float $discipline, float $luck): bool
    {
        // Only in penalty areas (positions 1 and 9)
        if (!in_array($fieldPosition, [1, 9])) {
            return false;
        }

        $baseChance = self::OWN_GOAL_BASE_CHANCE;
        $disciplineBonus = ($discipline - 70) * 0.03;
        $luckModifier = $this->specialEventChance($luck) * 0.3;
        
        $chance = $baseChance - $disciplineBonus + $luckModifier;
        $chance = $this->clamp($chance, 0.1, 5);
        
        return rand(1, 1000) <= ($chance * 10);
    }

    protected function counterAttack(int $fieldPosition, int $currentTeam, array $defendingStats): array
    {
        $counterPower = ($defendingStats['pace'] * StatsWeights::COUNTER_PACE_WEIGHT)
                      + ($defendingStats['attack'] * StatsWeights::COUNTER_ATTACK_WEIGHT)
                      + ($defendingStats['creative'] * StatsWeights::COUNTER_CREATIVE_WEIGHT);
        
        $counterDistance = (int)($counterPower / SimulationConstants::COUNTER_DISTANCE_DIVISOR);
        $counterDistance = $this->clamp($counterDistance, SimulationConstants::COUNTER_DISTANCE_MIN, SimulationConstants::COUNTER_DISTANCE_MAX);

        $newTeam = $currentTeam == 1 ? 2 : 1;
        $newPosition = $newTeam == 1 
                     ? max(0, $fieldPosition - $counterDistance)
                     : min(10, $fieldPosition + $counterDistance);

        return [
            'fieldPosition' => $newPosition,
            'currentTeam' => $newTeam,
            'goal' => false,
        ];
    }
}
