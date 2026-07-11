<?php

namespace Tests\Unit;

use App\Enums\DivisionLevel;
use App\Services\LeagueSeasonResultService;
use Tests\TestCase;

class LeagueSeasonResultServiceTest extends TestCase
{
    public function test_division1_first_place_is_champion(): void
    {
        $service = new LeagueSeasonResultService();

        $this->assertSame(
            'champion',
            $service->determineResult(1, 12, 3, 3, DivisionLevel::DIVISION1->value)
        );
    }

    public function test_division2_first_place_is_promoted(): void
    {
        $service = new LeagueSeasonResultService();

        $this->assertSame(
            'promoted',
            $service->determineResult(1, 12, 3, 3, DivisionLevel::DIVISION2->value)
        );
    }
}
