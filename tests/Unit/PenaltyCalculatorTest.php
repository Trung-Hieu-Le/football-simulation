<?php

namespace Tests\Unit;

use App\Services\Simulation\PenaltyCalculator;
use Tests\TestCase;

class PenaltyCalculatorTest extends TestCase
{
    public function test_on_target_chance_clamped(): void
    {
        $chance = PenaltyCalculator::onTargetChance([
            'attack' => 99,
            'mental' => 99,
        ]);

        $this->assertGreaterThanOrEqual(70, $chance);
        $this->assertLessThanOrEqual(95, $chance);
    }

    public function test_goal_chance_higher_for_stronger_shooter(): void
    {
        $strong = PenaltyCalculator::goalChance(
            ['attack' => 95, 'mental' => 90],
            ['goalkeeping' => 50, 'mental' => 50]
        );
        $weak = PenaltyCalculator::goalChance(
            ['attack' => 50, 'mental' => 50],
            ['goalkeeping' => 95, 'mental' => 90]
        );

        $this->assertGreaterThan($weak, $strong);
    }

    public function test_attempt_returns_structure(): void
    {
        $result = PenaltyCalculator::attempt(
            ['attack' => 80, 'mental' => 75],
            ['goalkeeping' => 70, 'mental' => 70]
        );

        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('on_target', $result);
        $this->assertArrayHasKey('goal_chance', $result);
        $this->assertIsBool($result['success']);
    }
}
