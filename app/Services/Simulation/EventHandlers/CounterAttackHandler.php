<?php

namespace App\Services\Simulation\EventHandlers;

use App\Constants\SimulationConstants;
use App\Constants\StatsWeights;
use App\Services\Simulation\BaseSimulationService;
use App\Services\Simulation\MetaModifiers;

class CounterAttackHandler extends BaseSimulationService
{
    public function attemptCounterAttack(
        int $fieldPosition,
        int $currentTeam,
        array $attackingStats,
        array $defendingStats,
        array $modifiers = []
    ): ?array {
        $modifiers = array_merge(MetaModifiers::defaults(), $modifiers);

        $counterChance = (
            ($defendingStats['control']
            + $defendingStats['pace'] * 0.5
            + $defendingStats['discipline'] * 0.2)
            / 300
        ) * 100 * $modifiers['counter_chance'];

        if (rand(1, 100) > $counterChance) {
            return null;
        }

        $counterPower = ($defendingStats['pace'] * StatsWeights::COUNTER_PACE_WEIGHT)
                      + ($defendingStats['attack'] * StatsWeights::COUNTER_ATTACK_WEIGHT)
                      + ($defendingStats['creative'] * StatsWeights::COUNTER_CREATIVE_WEIGHT);

        $counterDistance = (int) (($counterPower / SimulationConstants::COUNTER_DISTANCE_DIVISOR) * $modifiers['counter_distance']);
        $counterDistance = $this->clamp(
            $counterDistance,
            SimulationConstants::COUNTER_DISTANCE_MIN,
            SimulationConstants::COUNTER_DISTANCE_MAX
        );

        $newTeam = $currentTeam == 1 ? 2 : 1;
        $newPosition = $newTeam == 1
                     ? max(0, $fieldPosition - $counterDistance)
                     : min(10, $fieldPosition + $counterDistance);

        return [
            'fieldPosition' => $newPosition,
            'currentTeam' => $newTeam,
            'goal' => false,
        ];
    }
}
