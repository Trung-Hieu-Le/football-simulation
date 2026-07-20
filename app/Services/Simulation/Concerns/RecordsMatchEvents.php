<?php

namespace App\Services\Simulation\Concerns;

trait RecordsMatchEvents
{
    /**
     * Record a goal event in matchData
     *
     * @param int $team Team number (1 or 2)
     * @param int $time Current match time
     * @param array $matchData Match data array (passed by reference)
     * @param string $type Goal type: 'goal', 'penalty', 'freekick'
     * @return void
     */
    protected function recordGoal(int $team, int $time, array &$matchData, string $type = 'goal'): void
    {
        $scoreKey = $team == 1 ? 'team1_score' : 'team2_score';
        $matchData[$scoreKey]++;

        $teamId = $team == 1 ? $matchData['team1_id'] : $matchData['team2_id'];
        $teamName = $team == 1 ? $matchData['team1_name'] : $matchData['team2_name'];
        
        $suffix = match ($type) {
            'penalty' => ' (P)',
            'freekick' => ' (F)',
            default => '',
        };

        $matchData['goals'][] = [
            'minute' => $time,
            'team_id' => $teamId,
            'type' => $type,
            'label' => "{$time}' {$teamName}{$suffix}",
        ];

        $eventLabel = match ($type) {
            'penalty' => 'Penalty GOAL',
            'freekick' => 'Free Kick GOAL',
            default => 'GOAL',
        };
        
        $teamLabel = $team == 1 ? 'Team1' : 'Team2';
        $this->recordTimelineEvent($time, "{$eventLabel} by {$teamLabel}!", $matchData);
    }

    /**
     * Record a timeline event in matchData
     *
     * @param int $time Current match time
     * @param string $event Event description
     * @param array $matchData Match data array (passed by reference)
     * @return void
     */
    protected function recordTimelineEvent(int $time, string $event, array &$matchData): void
    {
        $matchData['specialEvents'][] = "{$time}': {$event}";
    }
}
