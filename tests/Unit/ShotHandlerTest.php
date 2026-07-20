<?php

namespace Tests\Unit;

use App\Constants\StatsWeights;
use App\Services\Simulation\EventHandlers\ShotHandler;
use Tests\TestCase;

class ShotHandlerTest extends TestCase
{
    protected ShotHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new ShotHandler();
    }

    public function test_clutch_shot_only_triggers_in_last_minutes(): void
    {
        $stats = ['mental' => 80, 'luck' => 50];

        $this->assertFalse($this->handler->rollClutchShot(45, $stats['mental'], $stats['luck'], []));
        $this->assertFalse($this->handler->rollClutchShot(70, $stats['mental'], $stats['luck'], []));
        $this->assertFalse($this->handler->rollClutchShot(100, $stats['mental'], $stats['luck'], []));

        $triggered = false;
        for ($i = 0; $i < 100; $i++) {
            if ($this->handler->rollClutchShot(87, 90, 50, [])) {
                $triggered = true;
                break;
            }
        }
        $this->assertTrue($triggered, 'Clutch shot should trigger at least once in 100 attempts with high mental');
    }

    public function test_high_mental_increases_clutch_shot_chance(): void
    {
        $highMentalCount = 0;
        $lowMentalCount = 0;

        for ($i = 0; $i < 2000; $i++) {
            if ($this->handler->rollClutchShot(87, 90, 50, [])) {
                $highMentalCount++;
            }
            if ($this->handler->rollClutchShot(87, 50, 50, [])) {
                $lowMentalCount++;
            }
        }

        $this->assertGreaterThan($lowMentalCount, $highMentalCount);
    }

    public function test_handle_shot_returns_expected_structure(): void
    {
        $team1Stats = [
            'attack' => 80,
            'mental' => 75,
            'goalkeeping' => 70,
            'defense' => 70,
            'physical' => 75,
            'pace' => 75,
            'creative' => 70,
            'luck' => 50,
        ];

        $team2Stats = [
            'attack' => 70,
            'mental' => 70,
            'goalkeeping' => 80,
            'defense' => 75,
            'physical' => 80,
            'pace' => 70,
            'creative' => 65,
            'luck' => 50,
        ];

        $matchData = [
            'team1_score' => 0,
            'team2_score' => 0,
            'team1_shots' => 0,
            'team2_shots' => 0,
            'team1_shots_on_target' => 0,
            'team2_shots_on_target' => 0,
            'goals' => [],
            'specialEvents' => [],
            'team1_id' => 1,
            'team2_id' => 2,
            'team1_name' => 'Team1',
            'team2_name' => 'Team2',
        ];

        $result = $this->handler->handleShot(9, 1, $team1Stats, $team2Stats, null, null, 45, $matchData, false, []);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('fieldPosition', $result);
        $this->assertArrayHasKey('currentTeam', $result);
        $this->assertArrayHasKey('goal', $result);
        $this->assertIsBool($result['goal']);
    }

    public function test_own_goal_method_removed(): void
    {
        $this->assertFalse(method_exists($this->handler, 'tryOwnGoal'));
    }

    public function test_higher_defense_reduces_on_target_chance(): void
    {
        $handler = new class extends ShotHandler {
            public function expose(int $distance, float $attack, float $defense): float
            {
                return $this->calculateOnTargetChance($distance, $attack, $defense);
            }
        };

        $lowDef = $handler->expose(1, 80, 50);
        $highDef = $handler->expose(1, 80, 85);

        $this->assertGreaterThan($highDef, $lowDef);
    }

    public function test_goalkeeping_weights_exceed_defense_for_saves(): void
    {
        $this->assertGreaterThan(
            StatsWeights::SAVE_POWER_DEFENSE_WEIGHT,
            StatsWeights::SAVE_POWER_GOALKEEPING_WEIGHT
        );
    }
}
