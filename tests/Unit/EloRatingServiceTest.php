<?php

namespace Tests\Unit;

use App\Enums\DivisionLevel;
use App\Services\EloRatingService;
use Tests\TestCase;

class EloRatingServiceTest extends TestCase
{
    public function test_weaker_team_gains_on_draw_against_stronger(): void
    {
        $service = new EloRatingService();

        $change = $service->calculateEloChange(1200, 1400, 0.5);

        $this->assertGreaterThan(0, $change);
    }

    public function test_equal_elo_draw_is_neutral(): void
    {
        $service = new EloRatingService();

        $this->assertSame(0, $service->calculateEloChange(1000, 1000, 0.5));
    }

    public function test_division1_win_scaled_up_loss_scaled_down(): void
    {
        $service = new EloRatingService();

        $baseWin = $service->calculateEloChange(1000, 1000, 1.0);
        $div1Win = $service->scaleByDivision($baseWin, DivisionLevel::DIVISION1->value);
        $div1Loss = $service->scaleByDivision(-$baseWin, DivisionLevel::DIVISION1->value);

        $this->assertGreaterThan($baseWin, $div1Win);
        $this->assertGreaterThan(-$baseWin, $div1Loss);
    }

    public function test_division3_win_scaled_down_loss_scaled_up(): void
    {
        $service = new EloRatingService();

        $baseWin = $service->calculateEloChange(1000, 1000, 1.0);
        $div3Win = $service->scaleByDivision($baseWin, DivisionLevel::DIVISION3->value);
        $div3Loss = $service->scaleByDivision(-$baseWin, DivisionLevel::DIVISION3->value);

        $this->assertLessThan($baseWin, $div3Win);
        $this->assertLessThan(-$baseWin, $div3Loss);
    }
}
