<?php

namespace App\Services;

use App\Models\Team;
use App\Models\League\Standing as LeagueStanding;
use App\Models\League\Position as LeaguePosition;
use App\Models\Cup\Standing as CupStanding;
use App\Models\Cup\Position as CupPosition;
use App\Enums\CupSeasonResult;

class MatchHistoryService
{
    protected EloRatingService $eloService;

    public function __construct(EloRatingService $eloService)
    {
        $this->eloService = $eloService;
    }

    public function updateLeagueMatchHistory(
        int $team1Id,
        int $team2Id,
        int $seasonId,
        string $division,
        int $team1Score,
        int $team2Score,
        int $team1Fouls,
        int $team2Fouls,
        int $team1Possession,
        int $team2Possession
    ): void {
        $team1 = Team::find($team1Id);
        $team2 = Team::find($team2Id);

        if (!$team1 || !$team2) {
            return;
        }

        $this->updateLeagueStanding($team1Id, $seasonId, $division, $team1Score, $team2Score, $team1Fouls, $team1Possession);
        $this->updateLeagueStanding($team2Id, $seasonId, $division, $team2Score, $team1Score, $team2Fouls, $team2Possession);

        $this->eloService->updateEloAfterMatch($team1, $team2, $team1Score, $team2Score, $division);
    }

    public function updateCupGroupStageHistory(
        int $team1Id,
        int $team2Id,
        int $seasonId,
        int $team1Score,
        int $team2Score,
        int $team1Fouls,
        int $team2Fouls,
        int $team1Possession,
        int $team2Possession
    ): void {
        $team1 = Team::find($team1Id);
        $team2 = Team::find($team2Id);

        if (!$team1 || !$team2) {
            return;
        }

        $this->updateCupStanding($team1Id, $seasonId, $team1Score, $team2Score, $team1Fouls, $team1Possession);
        $this->updateCupStanding($team2Id, $seasonId, $team2Score, $team1Score, $team2Fouls, $team2Possession);

        $this->eloService->updateEloAfterMatch($team1, $team2, $team1Score, $team2Score);
    }

    public function updateCupEliminateHistory(
        int $team1Id,
        int $team2Id,
        int $seasonId,
        int $team1Score,
        int $team2Score,
        int $team1Fouls,
        int $team2Fouls,
        int $team1Possession,
        int $team2Possession,
        string $round,
        int $winnerId
    ): void {
        $team1 = Team::find($team1Id);
        $team2 = Team::find($team2Id);

        if (!$team1 || !$team2) {
            return;
        }

        $isTeam1Winner = $winnerId === $team1Id;

        $team1Result = CupSeasonResult::fromRound($round, $isTeam1Winner);
        $team2Result = CupSeasonResult::fromRound($round, !$isTeam1Winner);

        $this->updateCupStanding($team1Id, $seasonId, $team1Score, $team2Score, $team1Fouls, $team1Possession, $team1Result?->value);
        $this->updateCupStanding($team2Id, $seasonId, $team2Score, $team1Score, $team2Fouls, $team2Possession, $team2Result?->value);

        $this->eloService->updateEloAfterMatch($team1, $team2, $team1Score, $team2Score);
    }

    protected function updateLeagueStanding(
        int $teamId,
        int $seasonId,
        string $division,
        int $goalsScored,
        int $goalsConceded,
        int $fouls,
        int $possession
    ): void {
        $standing = LeagueStanding::firstOrCreate(
            ['team_id' => $teamId, 'season_id' => $seasonId],
            ['division' => $division]
        );

        $result = $this->getMatchResult($goalsScored, $goalsConceded);
        $standing->updateFromMatch($goalsScored, $goalsConceded, $possession, $fouls, $result);
    }

    protected function updateCupStanding(
        int $teamId,
        int $seasonId,
        int $goalsScored,
        int $goalsConceded,
        int $fouls,
        int $possession,
        ?string $result = null
    ): void {
        $standing = CupStanding::firstOrCreate(
            ['team_id' => $teamId, 'season_id' => $seasonId]
        );

        $matchResult = $this->getMatchResult($goalsScored, $goalsConceded);
        $standing->updateFromMatch($goalsScored, $goalsConceded, $possession, $fouls, $matchResult);

        if ($result) {
            CupPosition::updateOrCreate(
                ['cup_standing_id' => $standing->id],
                [
                    'season_id' => $seasonId,
                    'result' => $result,
                ]
            );
        }
    }

    protected function getMatchResult(int $goalsScored, int $goalsConceded): string
    {
        if ($goalsScored > $goalsConceded) {
            return 'win';
        }
        if ($goalsScored < $goalsConceded) {
            return 'lose';
        }
        return 'draw';
    }
}
