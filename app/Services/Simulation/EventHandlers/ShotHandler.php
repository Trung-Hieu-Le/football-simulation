<?php

namespace App\Services\Simulation\EventHandlers;

use App\Constants\SimulationConstants;
use App\Constants\FieldPositions;
use App\Constants\StatsWeights;
use App\Services\Simulation\BaseSimulationService;
use App\Services\Simulation\Concerns\RecordsMatchEvents;
use App\Services\Simulation\MetaModifiers;
use App\Services\Simulation\PenaltyCalculator;
use App\Services\Simulation\ZoneHelpers;

class ShotHandler extends BaseSimulationService
{
    use RecordsMatchEvents;

    // Special shot event constants
    public const CLUTCH_SHOT_BASE_CHANCE = 3;  // Base chance for clutch shot in last minutes when trailing

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

        $distance = ZoneHelpers::attackDistance($fieldPosition, $currentTeam);
        
        $onTargetChance = $this->calculateOnTargetChance(
            $distance,
            $attackingStats['attack'],
            $defendingStats['defense']
        );
        $onTarget = rand(1, 100) <= $onTargetChance;

        $this->recordShot($currentTeam, $matchData, $onTarget);

        $detail = [
            'distance' => $distance,
            'on_target_chance' => round($onTargetChance, 2),
            'on_target' => $onTarget,
        ];

        if ($onTarget) {
            $goalChance = $this->shotGoalChance(
                $attackingStats['attack'],
                $attackingStats['mental'],
                $defendingStats['goalkeeping'],
                $defendingStats['defense'],
                $defendingStats['mental']
            ) * 100;

            // Clutch shot: only when team is trailing
            $currentScore = $currentTeam == 1 ? $matchData['team1_score'] : $matchData['team2_score'];
            $opponentScore = $currentTeam == 1 ? $matchData['team2_score'] : $matchData['team1_score'];
            $isTrailing = $currentScore < $opponentScore;
            
            $clutch = $isTrailing && $this->rollClutchShot($time, $attackingStats['mental'], $attackingStats['luck'], $modifiers);
            if ($clutch) {
                $goalBoost = 1.0 + (0.4 * $modifiers['clutch_goal']);
                $goalChance = min(88, $goalChance * $goalBoost);
                $this->recordTimelineEvent($time, "Clutch shot by Team{$currentTeam}!", $matchData);
            }

            $detail['goal_chance'] = round($goalChance, 2);
            $detail['clutch'] = $clutch;

            $isGoal = rand(1, 100) <= $goalChance;

            if ($isGoal) {
                $this->recordGoal($currentTeam, $time, $matchData, 'goal');
                return [
                    'fieldPosition' => FieldPositions::MIDFIELD,
                    'currentTeam' => $currentTeam == 1 ? 2 : 1,
                    'goal' => true,
                    'goalScoredBy' => $currentTeam,
                    'detail' => array_merge($detail, ['outcome' => 'goal']),
                ];
            }

            // Shot on target but saved - 30% counter chance
            if (rand(1, 100) <= SimulationConstants::COUNTER_AFTER_SHOT_CHANCE) {
                $counter = $this->counterAttack($fieldPosition, $currentTeam, $defendingStats, $modifiers);
                $counter['detail'] = array_merge($detail, ['outcome' => 'saved_then_counter']);
                return $counter;
            }

            return [
                'fieldPosition' => $fieldPosition,
                'currentTeam' => $currentTeam,
                'goal' => false,
                'detail' => array_merge($detail, ['outcome' => 'saved']),
            ];
        }

        // Shot off target - 50% steal chance
        if (rand(1, 100) <= SimulationConstants::COUNTER_STEAL_CHANCE) {
            $newTeam = $currentTeam == 1 ? 2 : 1;
            return [
                'fieldPosition' => $fieldPosition,
                'currentTeam' => $newTeam,
                'goal' => false,
                'detail' => array_merge($detail, ['outcome' => 'off_target_steal']),
            ];
        }

        return [
            'fieldPosition' => $fieldPosition,
            'currentTeam' => $currentTeam,
            'goal' => false,
            'detail' => array_merge($detail, ['outcome' => 'off_target']),
        ];
    }

    protected function handlePenaltyShot(int $currentTeam, array $attackingStats, array $defendingStats, int $time, array &$matchData): array
    {
        $result = PenaltyCalculator::attempt($attackingStats, $defendingStats, PenaltyCalculator::CONTEXT_OPEN_PLAY);
        
        $this->recordShot($currentTeam, $matchData, $result['on_target']);

        if ($result['success']) {
            $this->recordGoal($currentTeam, $time, $matchData, 'penalty');
            return [
                'fieldPosition' => FieldPositions::MIDFIELD,
                'currentTeam' => $currentTeam == 1 ? 2 : 1,
                'goal' => true,
                'goalScoredBy' => $currentTeam,
            ];
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

    protected function calculateOnTargetChance(int $distance, float $attack, float $defense): float
    {
        $distanceBonus = max(0, (3 - $distance)) * 5;
        $onTargetChance = SimulationConstants::NORMAL_SHOT_ON_TARGET_CHANCE
                        + $distanceBonus
                        + ($attack * StatsWeights::ON_TARGET_ATTACK_WEIGHT)
                        - ($defense * StatsWeights::ON_TARGET_DEFENSE_WEIGHT);

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
     * Check for clutch shot in last minutes (85-90, 115-120) when team is trailing
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
        $mentalBonus = ($mental - 70) * 0.10;
        $luckModifier = $this->specialEventChance($luck) * 0.40;

        $chance = $baseChance + $mentalBonus + $luckModifier;
        $chance = $this->clamp($chance, 0.5, 18);

        return rand(1, 1000) <= ($chance * 10);
    }

    protected function counterAttack(int $fieldPosition, int $currentTeam, array $defendingStats, array $modifiers = []): array
    {
        $counterDistance = $this->calculateCounterDistance($defendingStats, $modifiers);

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
