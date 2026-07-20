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
        $attackingStats = $currentTeam == 1 ? $team1Stats : $team2Stats;

        $foulThreshold = $this->foulHandler->calculateFoulThreshold(
            $defendingStats,
            $modifiers
        );

        if (rand(1, 100) <= $foulThreshold) {
            $result = $this->foulHandler->handleFoul(
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

            return $this->tag($result, 'foul', [
                'foul_threshold' => round($foulThreshold, 2),
                'zone_from' => $fieldPosition,
            ]);
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

        $buildEvent = $moveResult['event'] ?? 'move';
        $buildDetail = $moveResult['detail'] ?? [];

        if ($moveResult['stolen']) {
            // Turnover events: pressing, miscontrol, contest, offside, move_lost, aerial_lost
            // Counter only eligible on: pressing, contest (not miscontrol/offside)
            $counterEligible = in_array($buildEvent, ['pressing', 'contest', 'move_lost']);
            
            if ($counterEligible) {
                $counterResult = $this->counterHandler->attemptCounterAttack(
                    $moveResult['newPosition'],
                    $currentTeam,
                    $attackingStats,
                    $defendingStats,
                    $modifiers
                );

                if ($counterResult !== null) {
                    return $this->tag($counterResult, 'counter_attack', array_merge($buildDetail, [
                        'after' => $buildEvent,
                        'zone_from' => $fieldPosition,
                        'zone_after_steal' => $moveResult['newPosition'],
                    ]));
                }
            }

            return $this->tag([
                'fieldPosition' => $moveResult['newPosition'],
                'currentTeam' => $currentTeam == 1 ? 2 : 1,
                'goal' => false,
            ], $buildEvent ?: 'possession_lost', array_merge($buildDetail, [
                'zone_from' => $fieldPosition,
                'zone_to' => $moveResult['newPosition'],
                'stolen' => true,
            ]));
        }

        if (in_array($moveResult['newPosition'], FieldPositions::SHOOTING_POSITIONS)) {
            $shotDecisionChance = $this->shotDecisionChance(
                $moveResult['newPosition'],
                $attackingStats['attack'],
                $attackingStats['mental'],
                $modifiers,
                $currentTeam
            );

            if (rand(1, 100) <= $shotDecisionChance) {
                $shotResult = $this->shotHandler->handleShot(
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

                $shotEvent = ($shotResult['goal'] ?? false) ? 'goal' : 'shot';

                return $this->tag($shotResult, $shotEvent, array_merge($buildDetail, [
                    'via' => $buildEvent ?: 'move',
                    'zone_from' => $fieldPosition,
                    'zone_shot' => $moveResult['newPosition'],
                    'shot_decision_chance' => round($shotDecisionChance, 2),
                    'shot_detail' => $shotResult['detail'] ?? [],
                ]));
            }

            return $this->tag([
                'fieldPosition' => $moveResult['newPosition'],
                'currentTeam' => $currentTeam,
                'goal' => false,
            ], $buildEvent === 'contest_held' ? 'contest_held' : 'move_no_shot', array_merge($buildDetail, [
                'zone_from' => $fieldPosition,
                'zone_to' => $moveResult['newPosition'],
                'shot_decision_chance' => round($shotDecisionChance, 2),
                'shot_attempted' => false,
            ]));
        }

        return $this->tag([
            'fieldPosition' => $moveResult['newPosition'],
            'currentTeam' => $currentTeam,
            'goal' => false,
        ], $buildEvent ?: 'move', array_merge($buildDetail, [
            'zone_from' => $fieldPosition,
            'zone_to' => $moveResult['newPosition'],
            'stolen' => false,
        ]));
    }

    protected function tag(array $result, string $event, array $detail = []): array
    {
        $result['event'] = $event;
        $result['detail'] = $detail;

        return $result;
    }
}
