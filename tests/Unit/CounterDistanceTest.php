<?php

namespace Tests\Unit;

use App\Constants\SimulationConstants;
use App\Enums\SeasonMeta;
use App\Services\Simulation\BaseSimulationService;
use App\Services\Simulation\MetaModifiers;
use Tests\TestCase;

class CounterDistanceTest extends TestCase
{
    protected object $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new class extends BaseSimulationService {
            public function expose(array $stats, array $modifiers = []): int
            {
                return $this->calculateCounterDistance($stats, $modifiers);
            }
        };
    }

    public function test_counter_distance_is_between_one_and_four(): void
    {
        for ($i = 0; $i < 200; $i++) {
            $distance = $this->service->expose(['pace' => 75]);
            $this->assertGreaterThanOrEqual(SimulationConstants::COUNTER_DISTANCE_MIN, $distance);
            $this->assertLessThanOrEqual(SimulationConstants::COUNTER_DISTANCE_MAX, $distance);
        }
    }

    public function test_higher_pace_tends_to_longer_counter_distance(): void
    {
        $lowPaceTotal = 0;
        $highPaceTotal = 0;
        $iterations = 3000;

        for ($i = 0; $i < $iterations; $i++) {
            $lowPaceTotal += $this->service->expose(['pace' => 45]);
            $highPaceTotal += $this->service->expose(['pace' => 95]);
        }

        $this->assertGreaterThan($lowPaceTotal / $iterations, $highPaceTotal / $iterations);
    }

    public function test_counter_attack_meta_increases_average_distance(): void
    {
        $balancedTotal = 0;
        $counterTotal = 0;
        $iterations = 3000;
        $stats = ['pace' => 80];
        $counterModifiers = MetaModifiers::for(SeasonMeta::COUNTER_ATTACK->value);

        for ($i = 0; $i < $iterations; $i++) {
            $balancedTotal += $this->service->expose($stats);
            $counterTotal += $this->service->expose($stats, $counterModifiers);
        }

        $this->assertGreaterThan($balancedTotal / $iterations, $counterTotal / $iterations);
    }
}
