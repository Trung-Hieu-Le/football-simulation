<?php

namespace App\Services\Simulation\EventHandlers;

use App\Constants\SimulationConstants;
use App\Constants\FieldPositions;
use App\Constants\StatsWeights;
use App\Services\Simulation\BaseSimulationService;

class ShotHandler extends BaseSimulationService
{
    public function handleShot(
        int $fieldPosition,
        int $currentTeam,
        array $team1Stats,
        array $team2Stats,
        $team1,
        $team2,
        int $time,
        array &$matchData,
        bool $isPenalty = false
    ): array {
        $attackingStats = $currentTeam == 1 ? $team1Stats : $team2Stats;
        $defendingStats = $currentTeam == 1 ? $team2Stats : $team1Stats;

        if ($isPenalty) {
            return $this->handlePenaltyShot($currentTeam, $attackingStats, $defendingStats, $time, $matchData);
        }

        $distance = $this->calculateDistanceToGoal($fieldPosition, $currentTeam);
        
        $onTargetChance = $this->calculateOnTargetChance($distance, $attackingStats['attack']);
        $onTarget = rand(1, 100) <= $onTargetChance;

        $this->recordShot($currentTeam, $matchData, $onTarget);

        if ($onTarget) {
            $goalChance = $this->shotGoalChance(
                $attackingStats['attack'],
                $attackingStats['mental'],
                $defendingStats['goalkeeping'],
                $defendingStats['mental']
            ) * 100;

            $isGoal = rand(1, 100) <= $goalChance;

            if ($isGoal) {
                $this->recordGoal($currentTeam, $time, $matchData);
                return [
                    'fieldPosition' => FieldPositions::MIDFIELD,
                    'currentTeam' => $currentTeam == 1 ? 2 : 1,
                    'goal' => true,
                    'goalScoredBy' => $currentTeam,
                ];
            } else {
                // Shot on target but saved - 30% counter chance
                if (rand(1, 100) <= SimulationConstants::COUNTER_AFTER_SHOT_CHANCE) {
                    return $this->counterAttack($fieldPosition, $currentTeam, $defendingStats);
                }
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
                $this->recordGoal($currentTeam, $time, $matchData, true);
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
                $this->recordGoal($currentTeam, $time, $matchData, false, true);
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

    protected function recordGoal(int $team, int $time, array &$matchData, bool $isPenalty = false, bool $isFreeKick = false): void
    {
        $scoreKey = $team == 1 ? 'team1_score' : 'team2_score';
        $matchData[$scoreKey]++;

        $teamId = $team == 1 ? $matchData['team1_id'] : $matchData['team2_id'];
        $teamName = $team == 1 ? $matchData['team1_name'] : $matchData['team2_name'];
        $type = $isPenalty ? 'penalty' : ($isFreeKick ? 'freekick' : 'goal');
        $suffix = $isPenalty ? ' (P)' : ($isFreeKick ? ' (F)' : '');

        $matchData['goals'][] = [
            'minute' => $time,
            'team_id' => $teamId,
            'type' => $type,
            'label' => "{$time}' {$teamName}{$suffix}",
        ];

        $eventType = $isPenalty ? 'Penalty GOAL' : ($isFreeKick ? 'Free Kick GOAL' : 'GOAL');
        $teamLabel = $team == 1 ? 'Team1' : 'Team2';
        $matchData['specialEvents'][] = "{$time}': {$eventType} by {$teamLabel}!";
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
