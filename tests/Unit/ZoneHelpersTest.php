<?php

namespace Tests\Unit;

use App\Services\Simulation\ZoneHelpers;
use Tests\TestCase;

class ZoneHelpersTest extends TestCase
{
    public function test_attack_distance_mirrors_teams(): void
    {
        $this->assertSame(0, ZoneHelpers::attackDistance(10, 1));
        $this->assertSame(0, ZoneHelpers::attackDistance(0, 2));
        $this->assertSame(1, ZoneHelpers::attackDistance(9, 1));
        $this->assertSame(1, ZoneHelpers::attackDistance(1, 2));
        $this->assertSame(5, ZoneHelpers::attackDistance(5, 1));
        $this->assertSame(5, ZoneHelpers::attackDistance(5, 2));
    }

    public function test_zone_difficulty_is_symmetric(): void
    {
        $this->assertSame(
            ZoneHelpers::zoneDifficulty(8, 1),
            ZoneHelpers::zoneDifficulty(2, 2)
        );
        $this->assertSame(
            ZoneHelpers::zoneDifficulty(9, 1),
            ZoneHelpers::zoneDifficulty(1, 2)
        );
        $this->assertSame(
            ZoneHelpers::zoneDifficulty(5, 1),
            ZoneHelpers::zoneDifficulty(5, 2)
        );
    }

    public function test_shot_bonus_is_symmetric(): void
    {
        $this->assertSame(
            ZoneHelpers::shotBonus(9, 1),
            ZoneHelpers::shotBonus(1, 2)
        );
        $this->assertSame(
            ZoneHelpers::shotBonus(8, 1),
            ZoneHelpers::shotBonus(2, 2)
        );
    }

    public function test_attacking_third(): void
    {
        $this->assertTrue(ZoneHelpers::isAttackingThird(9, 1));
        $this->assertTrue(ZoneHelpers::isAttackingThird(1, 2));
        $this->assertFalse(ZoneHelpers::isAttackingThird(5, 1));
        $this->assertFalse(ZoneHelpers::isAttackingThird(5, 2));
    }

    public function test_midfield_weight_higher_in_center(): void
    {
        $this->assertGreaterThan(
            ZoneHelpers::midfieldWeight(1),
            ZoneHelpers::midfieldWeight(5)
        );
    }
}
