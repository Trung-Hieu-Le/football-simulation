<?php

namespace App\Services;

use App\Enums\DivisionLevel;
use App\Enums\LeagueSeasonResult;
use App\Models\League\Position;
use App\Models\League\Season;
use App\Models\League\Standing;

class LeagueSeasonResultService
{
    public function calculateSeasonResults(Season $season): void
    {
        $season->loadMissing('groupTeams');

        foreach ($season->groupTeams as $groupTeam) {
            $this->calculateDivisionResults($season, $groupTeam->group);
        }
    }

    public function calculateDivisionResults(Season $season, string $division): void
    {
        $standings = Standing::where('season_id', $season->id)
            ->where('division', $division)
            ->orderByDesc('points')
            ->orderByDesc('goal_difference')
            ->orderByDesc('goal_scored')
            ->get();

        $teamsCount = $standings->count();
        $promotionCount = (int) ceil($teamsCount * 0.25);
        $relegationCount = (int) ceil($teamsCount * 0.25);

        foreach ($standings as $index => $standing) {
            $position = $index + 1;
            $result = $this->determineResult($position, $teamsCount, $promotionCount, $relegationCount, $division);

            Position::updateOrCreate(
                ['league_standing_id' => $standing->id],
                [
                    'season_id' => $season->id,
                    'position' => $position,
                    'result' => $result,
                ]
            );
        }
    }

    public function determineResult(
        int $position,
        int $teamsCount,
        int $promotionCount,
        int $relegationCount,
        string $division
    ): string {
        if ($position === 1 && $division === DivisionLevel::DIVISION1->value) {
            return LeagueSeasonResult::CHAMPION->value;
        }

        if ($position === 1 && $division !== DivisionLevel::DIVISION1->value) {
            return LeagueSeasonResult::PROMOTED->value;
        }

        if ($division !== DivisionLevel::DIVISION1->value && $position <= $promotionCount) {
            return LeagueSeasonResult::PROMOTED->value;
        }

        if ($division !== DivisionLevel::DIVISION3->value && $position > ($teamsCount - $relegationCount)) {
            return LeagueSeasonResult::RELEGATED->value;
        }

        return LeagueSeasonResult::STAY->value;
    }
}
