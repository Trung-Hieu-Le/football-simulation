<?php

namespace App\Services\Simulation;

use App\Constants\SimulationConstants;
use App\Constants\FieldPositions;
use App\Constants\StatsWeights;
use App\Services\Simulation\EventHandlers\ShotHandler;
use App\Services\Simulation\EventHandlers\FoulHandler;
use App\Services\Simulation\EventHandlers\CounterAttackHandler;

class SituationProcessor extends BaseSimulationService
{
    protected ShotHandler $shotHandler;
    protected FoulHandler $foulHandler;
    protected CounterAttackHandler $counterHandler;

    public function __construct()
    {
        $this->shotHandler = new ShotHandler();
        $this->foulHandler = new FoulHandler();
        $this->counterHandler = new CounterAttackHandler();
    }

    public function processSituation(
        int $fieldPosition,
        int $currentTeam,
        array $team1Stats,
        array $team2Stats,
        $team1,
        $team2,
        int $time,
        array &$matchData
    ): array {
        $defendingStats = $currentTeam == 1 ? $team2Stats : $team1Stats;
        $foulThreshold = $this->calculateFoulThreshold($defendingStats['discipline']);

        if (rand(1, 100) <= $foulThreshold) {
            return $this->foulHandler->handleFoul(
                $fieldPosition,
                $currentTeam,
                $team1Stats,
                $team2Stats,
                $team1,
                $team2,
                $time,
                $matchData
            );
        }

        $moveResult = $this->moveBall($fieldPosition, $currentTeam, $team1Stats, $team2Stats);
        
        if ($moveResult['stolen']) {
            $counterResult = $this->counterHandler->attemptCounterAttack(
                $moveResult['newPosition'],
                $currentTeam,
                $currentTeam == 1 ? $team1Stats : $team2Stats,
                $currentTeam == 1 ? $team2Stats : $team1Stats
            );

            if ($counterResult !== null) {
                return $counterResult;
            }

            return [
                'fieldPosition' => $moveResult['newPosition'],
                'currentTeam' => $currentTeam == 1 ? 2 : 1,
                'goal' => false,
            ];
        }

        if (in_array($moveResult['newPosition'], FieldPositions::SHOOTING_POSITIONS)) {
            $attackingStats = $currentTeam == 1 ? $team1Stats : $team2Stats;
            $shotDecisionChance = $this->shotDecisionChance(
                $moveResult['newPosition'],
                $attackingStats['attack'],
                $attackingStats['mental']
            );

            if (rand(1, 100) <= $shotDecisionChance) {
                return $this->shotHandler->handleShot(
                    $moveResult['newPosition'],
                    $currentTeam,
                    $team1Stats,
                    $team2Stats,
                    $team1,
                    $team2,
                    $time,
                    $matchData
                );
            }
        }

        return [
            'fieldPosition' => $moveResult['newPosition'],
            'currentTeam' => $currentTeam,
            'goal' => false,
        ];
    }

    protected function moveBall(int $fieldPosition, int $currentTeam, array $team1Stats, array $team2Stats): array
    {
        $attackingStats = $currentTeam == 1 ? $team1Stats : $team2Stats;
        $defendingStats = $currentTeam == 1 ? $team2Stats : $team1Stats;

        $buildUpPower = ($attackingStats['control'] * StatsWeights::BUILD_UP_CONTROL_WEIGHT)
                      + ($attackingStats['creative'] * StatsWeights::BUILD_UP_CREATIVE_WEIGHT)
                      + ($attackingStats['stamina'] * StatsWeights::BUILD_UP_STAMINA_WEIGHT);

        $zoneDifficulty = $this->getZoneDifficulty($fieldPosition);
        $stopProgress = ($defendingStats['defense'] * StatsWeights::STOP_DEFENSE_WEIGHT)
                      + ($defendingStats['discipline'] * StatsWeights::STOP_DISCIPLINE_WEIGHT);

        $moveChance = $buildUpPower / ($buildUpPower + ($stopProgress * $zoneDifficulty));
        $moveChance = $this->clamp($moveChance * 100, 5, 95);

        if (rand(1, 100) > $moveChance) {
            return ['newPosition' => $fieldPosition, 'stolen' => true];
        }

        $moveDistance = SimulationConstants::MOVE_DISTANCE_NORMAL;
        if ($attackingStats['pace'] > SimulationConstants::SPEED_THRESHOLD 
            && rand(1, 100) <= SimulationConstants::CREATIVE_BONUS_CHANCE) {
            $moveDistance = SimulationConstants::MOVE_DISTANCE_FAST;
        }

        $newPosition = $currentTeam == 1
                     ? min(10, $fieldPosition + $moveDistance)
                     : max(0, $fieldPosition - $moveDistance);

        $controlRatio = ($attackingStats['control'] / ($attackingStats['control'] + $defendingStats['control'])) * 100;
        $stealThreshold = $controlRatio - 15;

        $stolen = rand(1, 100) > $stealThreshold;

        return [
            'newPosition' => $newPosition,
            'stolen' => $stolen,
        ];
    }

    protected function calculateFoulThreshold(float $discipline): float
    {
        $foulThreshold = SimulationConstants::BASE_FOUL_CHANCE - ($discipline * 0.08);
        return $this->clamp($foulThreshold, SimulationConstants::FOUL_CHANCE_MIN, SimulationConstants::FOUL_CHANCE_MAX);
    }

    protected function getZoneDifficulty(int $position): float
    {
        return StatsWeights::ZONE_PROGRESS_DIFFICULTY[$position] ?? 1.0;
    }
}
