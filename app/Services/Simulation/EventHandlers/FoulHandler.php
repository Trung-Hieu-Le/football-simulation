<?php

namespace App\Services\Simulation\EventHandlers;

use App\Constants\SimulationConstants;
use App\Constants\FieldPositions;
use App\Services\Simulation\BaseSimulationService;

class FoulHandler extends BaseSimulationService
{
    protected ShotHandler $shotHandler;

    public function __construct()
    {
        $this->shotHandler = new ShotHandler();
    }

    public function handleFoul(
        int $fieldPosition,
        int $currentTeam,
        array $team1Stats,
        array $team2Stats,
        $team1,
        $team2,
        int $time,
        array &$matchData
    ): array {
        $defendingTeam = $currentTeam == 1 ? 2 : 1;
        $this->recordFoul($defendingTeam, $time, $matchData);

        if (in_array($fieldPosition, [FieldPositions::PENALTY_AREA_TEAM1, FieldPositions::PENALTY_AREA_TEAM2])) {
            if (rand(1, 100) <= SimulationConstants::PENALTY_CHANCE) {
                return $this->handlePenalty($currentTeam, $team1Stats, $team2Stats, $time, $matchData);
            }
        }

        return $this->handleFreeKick($fieldPosition, $currentTeam, $team1Stats, $team2Stats, $time, $matchData);
    }

    protected function handlePenalty(int $currentTeam, array $team1Stats, array $team2Stats, int $time, array &$matchData): array
    {
        $attackingStats = $currentTeam == 1 ? $team1Stats : $team2Stats;
        $defendingStats = $currentTeam == 1 ? $team2Stats : $team1Stats;

        $matchData['specialEvents'][] = "{$time}': Penalty for Team{$currentTeam}!";

        return $this->shotHandler->handleShot(
            $currentTeam == 1 ? FieldPositions::PENALTY_AREA_TEAM2 : FieldPositions::PENALTY_AREA_TEAM1,
            $currentTeam,
            $team1Stats,
            $team2Stats,
            null,
            null,
            $time,
            $matchData,
            true
        );
    }

    protected function handleFreeKick(int $fieldPosition, int $currentTeam, array $team1Stats, array $team2Stats, int $time, array &$matchData): array
    {
        $attackingStats = $currentTeam == 1 ? $team1Stats : $team2Stats;
        $defendingStats = $currentTeam == 1 ? $team2Stats : $team1Stats;

        $shotResult = $this->shotHandler->handleFreeKickShot($currentTeam, $attackingStats, $defendingStats, $time, $matchData);

        if ($shotResult !== null) {
            return $shotResult;
        }

        $passDistance = rand(SimulationConstants::FREEKICK_PASS_DISTANCE_MIN, SimulationConstants::FREEKICK_PASS_DISTANCE_MAX);
        $newPosition = $currentTeam == 1 
                     ? min(10, $fieldPosition + $passDistance)
                     : max(0, $fieldPosition - $passDistance);

        return [
            'fieldPosition' => $newPosition,
            'currentTeam' => $currentTeam,
            'goal' => false,
        ];
    }

    protected function recordFoul(int $team, int $time, array &$matchData): void
    {
        $foulKey = $team == 1 ? 'team1_fouls' : 'team2_fouls';
        $matchData[$foulKey] = ($matchData[$foulKey] ?? 0) + 1;

        $teamName = $team == 1 ? 'Team1' : 'Team2';
        $matchData['specialEvents'][] = "{$time}': Foul by {$teamName}";
    }
}
