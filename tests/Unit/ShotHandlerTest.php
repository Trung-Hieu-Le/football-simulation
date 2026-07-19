<?php

namespace Tests\Unit;

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

        // Not last minutes
        $this->assertFalse($this->handler->rollClutchShot(45, $stats['mental'], $stats['luck'], []));
        $this->assertFalse($this->handler->rollClutchShot(70, $stats['mental'], $stats['luck'], []));
        $this->assertFalse($this->handler->rollClutchShot(100, $stats['mental'], $stats['luck'], []));

        // Last minutes might trigger (probabilistic, so we test many times)
        $triggered = false;
        for ($i = 0; $i < 100; $i++) {
            if ($this->handler->rollClutchShot(87, 90, 50, [])) {
                $triggered = true;
                break;
            }
        }
        // With high mental and 100 attempts, should trigger at least once
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

    public function test_own_goal_only_in_penalty_areas(): void
    {
        $stats = ['discipline' => 70, 'luck' => 50];

        // Not in penalty areas
        $this->assertFalse($this->handler->tryOwnGoal(0, $stats['discipline'], $stats['luck']));
        $this->assertFalse($this->handler->tryOwnGoal(5, $stats['discipline'], $stats['luck']));
        $this->assertFalse($this->handler->tryOwnGoal(10, $stats['discipline'], $stats['luck']));

        // In penalty areas (positions 1 and 9) - might trigger but rare
        // We can't easily test the probabilistic nature here without many iterations
        // But we can verify it doesn't error
        for ($i = 0; $i < 10; $i++) {
            $result = $this->handler->tryOwnGoal(1, $stats['discipline'], $stats['luck']);
            $this->assertIsBool($result);

            $result = $this->handler->tryOwnGoal(9, $stats['discipline'], $stats['luck']);
            $this->assertIsBool($result);
        }
    }

    public function test_low_discipline_increases_own_goal_chance(): void
    {
        $lowDisciplineCount = 0;
        $highDisciplineCount = 0;

        for ($i = 0; $i < 500; $i++) {
            if ($this->handler->tryOwnGoal(1, 50, 50)) { // Low discipline
                $lowDisciplineCount++;
            }
            if ($this->handler->tryOwnGoal(1, 90, 50)) { // High discipline
                $highDisciplineCount++;
            }
        }

        // Low discipline should have more own goals
        $this->assertGreaterThanOrEqual($highDisciplineCount, $lowDisciplineCount);
    }

    public function test_handle_shot_returns_expected_structure(): void
    {
        $team1Stats = [
            'attack' => 80,
            'mental' => 75,
            'goalkeeping' => 70,
            'defense' => 70,
            'discipline' => 75,
            'pace' => 75,
            'creative' => 70,
            'luck' => 50,
        ];

        $team2Stats = [
            'attack' => 70,
            'mental' => 70,
            'goalkeeping' => 80,
            'defense' => 75,
            'discipline' => 80,
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
}
