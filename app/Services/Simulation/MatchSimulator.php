<?php

namespace App\Services\Simulation;

use App\Constants\SimulationConstants;
use App\Constants\FieldPositions;

class MatchSimulator extends BaseSimulationService
{
    protected SituationProcessor $situationProcessor;

    public function __construct()
    {
        $this->situationProcessor = new SituationProcessor();
    }

    public function simulateMatch($team1, $team2, string $seasonMeta, bool $requireWinner = false): array
    {
        $matchData = [
            'team1_score' => 0,
            'team2_score' => 0,
            'team1_fouls' => 0,
            'team2_fouls' => 0,
            'team1_possession' => 0,
            'team2_possession' => 0,
            'team1_shots' => 0,
            'team2_shots' => 0,
            'team1_shots_on_target' => 0,
            'team2_shots_on_target' => 0,
            'specialEvents' => [],
        ];

        $this->simulateFullTime($team1, $team2, $seasonMeta, $matchData);

        if ($requireWinner && $matchData['team1_score'] === $matchData['team2_score']) {
            $this->simulateExtraTime($team1, $team2, $seasonMeta, $matchData);
        }

        $totalPossession = $matchData['team1_possession'] + $matchData['team2_possession'];
        if ($totalPossession > 0) {
            $matchData['team1_possession'] = (int)(($matchData['team1_possession'] / $totalPossession) * 100);
            $matchData['team2_possession'] = 100 - $matchData['team1_possession'];
        }

        return $matchData;
    }

    protected function simulateFullTime($team1, $team2, string $seasonMeta, array &$matchData): void
    {
        $this->simulatePeriod(
            SimulationConstants::START_SITUATION,
            SimulationConstants::TOTAL_SITUATIONS_FULLTIME,
            $team1,
            $team2,
            $seasonMeta,
            $matchData,
            false
        );
    }

    protected function simulateExtraTime($team1, $team2, string $seasonMeta, array &$matchData): void
    {
        $this->simulatePeriod(
            SimulationConstants::EXTRATIME_START_SITUATION,
            SimulationConstants::EXTRATIME_END_SITUATION,
            $team1,
            $team2,
            $seasonMeta,
            $matchData,
            true
        );
    }

    protected function simulatePeriod(
        int $startSituation,
        int $endSituation,
        $team1,
        $team2,
        string $seasonMeta,
        array &$matchData,
        bool $isExtraTime
    ): void {
        $fieldPosition = FieldPositions::MIDFIELD;
        $currentTeam = 1;

        $statsCache = [];

        for ($situation = $startSituation; $situation <= $endSituation; $situation++) {
            $time = $this->calculateTime($situation, $isExtraTime);

            if ($this->isKickoffSituation($situation, $isExtraTime)) {
                $currentTeam = $this->getKickoffTeam($situation, $isExtraTime);
                $fieldPosition = FieldPositions::MIDFIELD;
            }

            $isSecondHalf = $this->isSecondHalf($situation, $isExtraTime);

            $cacheKey = $this->getStatsCacheKey($isSecondHalf, $isExtraTime);
            if (!isset($statsCache[$cacheKey])) {
                $statsCache[$cacheKey] = [
                    'team1' => $this->calculateTeamStats($team1, $seasonMeta, $isSecondHalf, $isExtraTime),
                    'team2' => $this->calculateTeamStats($team2, $seasonMeta, $isSecondHalf, $isExtraTime),
                ];
            }
            
            $team1Stats = $statsCache[$cacheKey]['team1'];
            $team2Stats = $statsCache[$cacheKey]['team2'];

            if ($currentTeam == 1) {
                $matchData['team1_possession']++;
            } else {
                $matchData['team2_possession']++;
            }

            $result = $this->situationProcessor->processSituation(
                $fieldPosition,
                $currentTeam,
                $team1Stats,
                $team2Stats,
                $team1,
                $team2,
                $time,
                $matchData
            );

            $fieldPosition = $result['fieldPosition'];
            $currentTeam = $result['currentTeam'];

            if ($result['goal'] ?? false) {
                $fieldPosition = FieldPositions::MIDFIELD;
                $currentTeam = ($result['goalScoredBy'] == 1) ? 2 : 1;
            }
        }
    }

    protected function calculateTime(int $situation, bool $isExtraTime): int
    {
        if ($isExtraTime) {
            return 90 + (int)ceil(($situation - SimulationConstants::EXTRATIME_START_SITUATION + 1) / SimulationConstants::SITUATIONS_PER_MINUTE);
        }
        return (int)ceil($situation / SimulationConstants::SITUATIONS_PER_MINUTE);
    }

    protected function isKickoffSituation(int $situation, bool $isExtraTime): bool
    {
        if ($isExtraTime) {
            return $situation == SimulationConstants::EXTRATIME_START_SITUATION 
                || $situation == SimulationConstants::EXTRATIME_HALFTIME_START_SITUATION;
        }
        return $situation == SimulationConstants::START_SITUATION 
            || $situation == SimulationConstants::HALFTIME_START_SITUATION;
    }

    protected function isSecondHalf(int $situation, bool $isExtraTime): bool
    {
        if ($isExtraTime) {
            return $situation > SimulationConstants::EXTRATIME_HALFTIME_SITUATION;
        }
        return $situation > SimulationConstants::HALFTIME_SITUATION;
    }

    protected function getStatsCacheKey(bool $isSecondHalf, bool $isExtraTime): string
    {
        if ($isExtraTime) {
            return $isSecondHalf ? 'et_half2' : 'et_half1';
        }
        return $isSecondHalf ? 'half2' : 'half1';
    }
}
