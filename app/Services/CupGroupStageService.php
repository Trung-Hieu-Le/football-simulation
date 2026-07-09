<?php

namespace App\Services;

use App\Models\Cup\Position;
use App\Models\Cup\Season;
use App\Models\Cup\Standing;

class CupGroupStageService
{
    public function getGroupStandings(Season $season): array
    {
        $standings = $season->standings()
            ->with(['team', 'position'])
            ->get()
            ->groupBy('group');

        $groups = [];
        foreach ($standings as $group => $groupStandings) {
            $groups[$group] = $groupStandings->sortByDesc(function ($standing) {
                return [
                    $standing->points,
                    $standing->goal_difference,
                    $standing->goal_scored,
                ];
            })->values();
        }

        return $groups;
    }

    public function syncGroupPositions(Season $season): void
    {
        $groups = $this->getGroupStandings($season);

        foreach ($groups as $groupStandings) {
            foreach ($groupStandings as $index => $standing) {
                Position::updateOrCreate(
                    ['cup_standing_id' => $standing->id],
                    [
                        'season_id' => $season->id,
                        'position' => $index + 1,
                        'result' => 'group_stage',
                    ]
                );
            }
        }
    }

    /**
     * @return array<string, array<int, array{team_id: int, points: int, goal_difference: int, goal_scored: int}>>
     */
    public function getGroupStandingsForKnockout(Season $season): array
    {
        $groups = $this->getGroupStandings($season);
        $result = [];

        foreach ($groups as $groupName => $standings) {
            $result[$groupName] = $standings->map(fn ($s) => [
                'team_id' => $s->team_id,
                'points' => $s->points,
                'goal_difference' => $s->goal_difference,
                'goal_scored' => $s->goal_scored,
            ])->toArray();
        }

        return $result;
    }
}
