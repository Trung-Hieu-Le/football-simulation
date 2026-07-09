<?php

namespace Tests\Unit;

use App\Models\Team;
use App\Services\CupPotSeedingService;
use Tests\TestCase;

class CupPotSeedingServiceTest extends TestCase
{
    private CupPotSeedingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CupPotSeedingService();
    }

    public function test_thirty_two_teams_eight_groups_four_pots(): void
    {
        $teams = $this->makeTeams(32);
        $pots = $this->service->distributeToPots($teams);

        $this->assertCount(4, $pots);
        foreach ($pots as $pot) {
            $this->assertCount(8, $pot);
        }

        $groups = $this->service->drawGroups($pots);
        $this->assertSame(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'], array_keys($groups));

        foreach ($groups as $groupTeams) {
            $this->assertCount(4, $groupTeams);
        }

        $allIds = collect($groups)->flatMap(fn ($g) => $g)->pluck('id')->unique();
        $this->assertSame(32, $allIds->count());
    }

    public function test_sixty_four_teams_eight_groups_eight_pots(): void
    {
        $teams = $this->makeTeams(64);
        $pots = $this->service->distributeToPots($teams);

        $this->assertCount(8, $pots);
        foreach ($pots as $pot) {
            $this->assertCount(8, $pot);
        }

        $groups = $this->service->drawGroups($pots);
        $this->assertCount(8, $groups);

        foreach ($groups as $groupTeams) {
            $this->assertCount(8, $groupTeams);
        }

        $allIds = collect($groups)->flatMap(fn ($g) => $g)->pluck('id')->unique();
        $this->assertSame(64, $allIds->count());
    }

    private function makeTeams(int $count)
    {
        return collect(range(1, $count))->map(function ($i) use ($count) {
            $team = new Team([
                'name' => "Team {$i}",
                'elo' => 1000 + ($count - $i),
            ]);
            $team->id = $i;

            return $team;
        });
    }
}
