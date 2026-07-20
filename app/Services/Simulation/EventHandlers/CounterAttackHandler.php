<?php

namespace App\Services\Simulation\EventHandlers;

use App\Constants\SimulationConstants;
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

        // Counter eligibility: steal must be in opponent's half
        $inOpponentHalf = $currentTeam == 1 ? $fieldPosition >= 5 : $fieldPosition <= 5;

        if (!$inOpponentHalf) {
            return null;
        }

        $counterChance = (
            ($defendingStats['pace'] * 1.0
            + $defendingStats['control'] * 0.3
            + $defendingStats['stamina'] * 0.2)
            / 400
        ) * 100 * $modifiers['counter_chance'];

        $counterChance += $this->specialEventChance($defendingStats['luck'] ?? 50) * 0.03;

        if (rand(1, 100) > $counterChance) {
            return null;
        }

        $counterDistance = $this->calculateCounterDistance($defendingStats, $modifiers);

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
