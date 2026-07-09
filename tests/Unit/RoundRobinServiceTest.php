<?php

namespace Tests\Unit;

use App\Services\RoundRobinService;
use Tests\TestCase;

class RoundRobinServiceTest extends TestCase
{
    private RoundRobinService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RoundRobinService();
    }

    public function test_four_teams_single_round_robin(): void
    {
        $schedule = $this->service->generateSingleRoundRobin([1, 2, 3, 4]);

        $this->assertCount(3, $schedule);
        $this->assertSame(6, $this->countMatches($schedule));

        $counts = $this->countAppearances($schedule);
        foreach ([1, 2, 3, 4] as $teamId) {
            $this->assertSame(3, $counts[$teamId] ?? 0, "Team {$teamId} should play 3 matches");
        }
    }

    public function test_eight_teams_single_round_robin(): void
    {
        $teams = range(1, 8);
        $schedule = $this->service->generateSingleRoundRobin($teams);

        $this->assertCount(7, $schedule);
        $this->assertSame(28, $this->countMatches($schedule));

        $counts = $this->countAppearances($schedule);
        foreach ($teams as $teamId) {
            $this->assertSame(7, $counts[$teamId] ?? 0);
        }
    }

    private function countMatches(array $schedule): int
    {
        return collect($schedule)->sum(fn ($round) => count($round));
    }

    private function countAppearances(array $schedule): array
    {
        $counts = [];
        foreach ($schedule as $roundMatches) {
            foreach ($roundMatches as [$a, $b]) {
                $counts[$a] = ($counts[$a] ?? 0) + 1;
                $counts[$b] = ($counts[$b] ?? 0) + 1;
            }
        }

        return $counts;
    }
}
