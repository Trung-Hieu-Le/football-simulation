<?php

namespace Tests\Unit;

use App\Enums\SeasonMeta;
use App\Services\Simulation\EventHandlers\BuildUpHandler;
use App\Services\Simulation\MetaModifiers;
use Tests\TestCase;

class BuildUpHandlerTest extends TestCase
{
    protected BuildUpHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new BuildUpHandler();
    }

    protected function sampleStats(): array
    {
        return [
            'control' => 80,
            'creative' => 75,
            'stamina' => 70,
            'pace' => 75,
            'discipline' => 80,
            'defense' => 70,
            'luck' => 50,
        ];
    }

    public function test_moveball_returns_expected_structure(): void
    {
        $matchData = [
            'specialEvents' => [],
            'team1_id' => 1,
            'team2_id' => 2,
            'team1_name' => 'Team1',
            'team2_name' => 'Team2',
        ];

        $result = $this->handler->moveBall(
            5,
            1,
            $this->sampleStats(),
            $this->sampleStats(),
            45,
            $matchData,
            MetaModifiers::for(SeasonMeta::BALANCED->value)
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('newPosition', $result);
        $this->assertArrayHasKey('stolen', $result);
        $this->assertArrayHasKey('event', $result);
        $this->assertIsBool($result['stolen']);
    }

    public function test_moveball_position_stays_within_bounds(): void
    {
        $strong = $this->sampleStats();
        $weak = [
            'control' => 60,
            'creative' => 55,
            'stamina' => 55,
            'pace' => 60,
            'discipline' => 65,
            'defense' => 70,
            'luck' => 50,
        ];
        $matchData = ['specialEvents' => []];
        $modifiers = MetaModifiers::for(SeasonMeta::BALANCED->value);

        for ($i = 0; $i < 10; $i++) {
            $result = $this->handler->moveBall(9, 1, $strong, $weak, 45, $matchData, $modifiers);
            $this->assertGreaterThanOrEqual(0, $result['newPosition']);
            $this->assertLessThanOrEqual(10, $result['newPosition']);
        }
    }

    public function test_high_press_modifier_increases_pressing_base(): void
    {
        $highPress = MetaModifiers::for(SeasonMeta::HIGH_PRESS->value);
        $lowBlock = MetaModifiers::for(SeasonMeta::LOW_BLOCK->value);

        $this->assertGreaterThan($lowBlock['pressing'], $highPress['pressing']);
    }
}
