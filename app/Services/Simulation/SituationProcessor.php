<?php

namespace App\Services\Simulation;

use App\Constants\FieldPositions;
use App\Services\Simulation\EventHandlers\BuildUpHandler;
use App\Services\Simulation\EventHandlers\ShotHandler;
use App\Services\Simulation\EventHandlers\FoulHandler;
use App\Services\Simulation\EventHandlers\CounterAttackHandler;

/**
 * Thin orchestrator for match situations
 * Delegates to specialized handlers: Foul → BuildUp → Shot/Counter
 */
class SituationProcessor extends BaseSimulationService
{
    protected BuildUpHandler $buildUpHandler;
    protected ShotHandler $shotHandler;
    protected FoulHandler $foulHandler;
    protected CounterAttackHandler $counterHandler;

    public function __construct()
    {
        $this->buildUpHandler = new BuildUpHandler();
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
        array &$matchData,
        array $modifiers = []
    ): array {
        $modifiers = array_merge(MetaModifiers::defaults(), $modifiers);
        $defendingStats = $currentTeam == 1 ? $team2Stats : $team1Stats;

        $foulThreshold = $this->foulHandler->calculateFoulThreshold(
            $defendingStats['discipline'],
            $modifiers
        );

        if (rand(1, 100) <= $foulThreshold) {
            return $this->foulHandler->handleFoul(
                $fieldPosition,
                $currentTeam,
                $team1Stats,
                $team2Stats,
                $team1,
                $team2,
                $time,
                $matchData,
                $modifiers
            );
        }

        $moveResult = $this->buildUpHandler->moveBall(
            $fieldPosition,
            $currentTeam,
            $team1Stats,
            $team2Stats,
            $time,
            $matchData,
            $modifiers
        );

        if ($moveResult['stolen']) {
            $counterResult = $this->counterHandler->attemptCounterAttack(
                $moveResult['newPosition'],
                $currentTeam,
                $currentTeam == 1 ? $team1Stats : $team2Stats,
                $currentTeam == 1 ? $team2Stats : $team1Stats,
                $modifiers
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
                $attackingStats['mental'],
                $modifiers
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
                    $matchData,
                    false,
                    $modifiers
                );
            }
        }

        return [
            'fieldPosition' => $moveResult['newPosition'],
            'currentTeam' => $currentTeam,
            'goal' => false,
        ];
    }
}
