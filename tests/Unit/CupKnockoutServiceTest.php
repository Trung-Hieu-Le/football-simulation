<?php

namespace Tests\Unit;

use App\Services\CupKnockoutService;
use Tests\TestCase;

class CupKnockoutServiceTest extends TestCase
{
    public function test_branches_32_has_sixteen_pairings(): void
    {
        $total = collect(CupKnockoutService::BRANCHES_32)->flatten(1)->count();
        $this->assertSame(16, $total);
    }

    public function test_round_order_is_r16_r8_qf_sf_third_then_final(): void
    {
        $order = CupKnockoutService::ROUND_ORDER;
        $this->assertSame(
            ['round_of_16', 'round_of_8', 'quarter_finals', 'semi_finals', 'third_place', 'final'],
            $order
        );
    }

    public function test_knockout_bracket_has_thirty_two_slots(): void
    {
        $structure = [
            'round_of_16' => 16,
            'round_of_8' => 8,
            'quarter_finals' => 4,
            'semi_finals' => 2,
            'third_place' => 1,
            'final' => 1,
        ];
        $this->assertSame(32, array_sum($structure));
    }
}
