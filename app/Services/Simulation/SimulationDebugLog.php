<?php

namespace App\Services\Simulation;

/**
 * Collects a per-situation timeline for match balance debugging.
 * Enabled when matchData['debug'] === true.
 */
class SimulationDebugLog
{
    public static function enabled(array $matchData): bool
    {
        return (bool) ($matchData['debug'] ?? false);
    }

    public static function push(array &$matchData, array $entry): void
    {
        if (!self::enabled($matchData)) {
            return;
        }

        $matchData['debug_log'][] = $entry;
    }

    /**
     * Aggregate event counts + zone occupancy from timeline.
     */
    public static function summarize(array $matchData): array
    {
        $log = $matchData['debug_log'] ?? [];
        $eventCounts = [];
        $zoneTicks = array_fill(0, 11, 0);
        $possessionTicks = [1 => 0, 2 => 0];

        foreach ($log as $row) {
            $event = $row['event'] ?? 'unknown';
            $eventCounts[$event] = ($eventCounts[$event] ?? 0) + 1;

            $zone = $row['zone'] ?? null;
            if ($zone !== null && $zone >= 0 && $zone <= 10) {
                $zoneTicks[$zone]++;
            }

            $team = $row['possession'] ?? null;
            if ($team === 1 || $team === 2) {
                $possessionTicks[$team]++;
            }
        }

        arsort($eventCounts);

        return [
            'situations' => count($log),
            'event_counts' => $eventCounts,
            'zone_ticks' => $zoneTicks,
            'possession_ticks' => $possessionTicks,
            'score' => [
                'team1' => $matchData['team1_score'] ?? 0,
                'team2' => $matchData['team2_score'] ?? 0,
            ],
            'shots' => [
                'team1' => $matchData['team1_shots'] ?? 0,
                'team1_on_target' => $matchData['team1_shots_on_target'] ?? 0,
                'team2' => $matchData['team2_shots'] ?? 0,
                'team2_on_target' => $matchData['team2_shots_on_target'] ?? 0,
            ],
            'fouls' => [
                'team1' => $matchData['team1_fouls'] ?? 0,
                'team2' => $matchData['team2_fouls'] ?? 0,
            ],
            'possession_pct' => [
                'team1' => $matchData['team1_possession'] ?? 0,
                'team2' => $matchData['team2_possession'] ?? 0,
            ],
        ];
    }
}
