<?php

namespace App\Services\Simulation;

use App\Constants\SimulationConstants;
use App\Constants\StatsWeights;
use App\Services\Simulation\MetaModifiers;

class BaseSimulationService
{
    /**
     * Calculate team stats with stamina phase decay only (meta affects events, not raw stats).
     */
    public function calculateTeamStats($team, string $seasonMeta, bool $isSecondHalf = false, bool $isExtraTime = false): array
    {
        $modifiers = MetaModifiers::for($seasonMeta);
        $phaseDecay = $this->getPhaseDecay($isSecondHalf, $isExtraTime) * $modifiers['stamina_decay'];

        $stamina = $team->stamina ?? 50;
        $fatigueResist = pow($stamina / 100, 0.65);
        $staminaFactor = 1 - ($phaseDecay * (1 - $fatigueResist));

        $stats = [
            'attack' => $team->attack ?? 50,
            'defense' => $team->defense ?? 50,
            'control' => $team->control ?? 50,
            'stamina' => $stamina,
            'goalkeeping' => $team->goalkeeping ?? $team->goalkeeper ?? 50,
            'creative' => $team->creative ?? 50,
            'pace' => $team->pace ?? 50,
            'mental' => $team->mental ?? 50,
            'physical' => $team->physical ?? 50,
            'luck' => $team->luck ?? 50,
            'stamina_factor' => $staminaFactor,
        ];

        foreach (['attack', 'defense', 'control', 'pace', 'creative', 'goalkeeping'] as $key) {
            $stats[$key] *= $staminaFactor;
        }

        return $stats;
    }

    protected function getPhaseDecay(bool $isSecondHalf, bool $isExtraTime): float
    {
        if ($isExtraTime) {
            return $isSecondHalf ? SimulationConstants::EXTRA_2_DECAY : SimulationConstants::EXTRA_1_DECAY;
        }

        return $isSecondHalf ? SimulationConstants::HALF_2_DECAY : SimulationConstants::HALF_1_DECAY;
    }

    /**
     * @param int $team Attacking team (1 or 2) — required for zone symmetry
     */
    public function shotDecisionChance(int $zone, float $attack, float $mental, array $modifiers = [], int $team = 1): float
    {
        $zoneBonus = ZoneHelpers::shotBonus($zone, $team);

        $chance = SimulationConstants::SHOOT_DECISION_BASE_CHANCE
            + $zoneBonus
            + ($attack * SimulationConstants::SHOOT_DECISION_ATTACK_MULTIPLIER)
            + ($mental * SimulationConstants::SHOOT_DECISION_MENTAL_MULTIPLIER);

        $chance *= $modifiers['shot_decision'] ?? 1.0;

        return min(SimulationConstants::SHOOT_DECISION_MAX, $chance);
    }

    public function shotGoalChance(
        float $attack,
        float $mental,
        float $goalkeeping,
        float $defense,
        float $keeperMental
    ): float {
        $shotPower = ($attack * StatsWeights::SHOT_POWER_ATTACK_WEIGHT)
                   + ($mental * StatsWeights::SHOT_POWER_MENTAL_WEIGHT);
        $savePower = ($goalkeeping * StatsWeights::SAVE_POWER_GOALKEEPING_WEIGHT)
                   + ($defense * StatsWeights::SAVE_POWER_DEFENSE_WEIGHT)
                   + ($keeperMental * StatsWeights::SAVE_POWER_MENTAL_WEIGHT);

        if (($shotPower + $savePower) == 0) {
            return 0.0;
        }

        return $shotPower / ($shotPower + $savePower);
    }

    public function specialEventChance(float $luck, array $modifiers = []): float
    {
        return SimulationConstants::BASE_SPECIAL_EVENT
             + ($luck * SimulationConstants::SPECIAL_EVENT_LUCK_MULTIPLIER);
    }

    /**
     * Random counter distance 1–4; higher pace shifts weight toward longer runs.
     */
    public function calculateCounterDistance(array $stats, array $modifiers = []): int
    {
        $modifiers = array_merge(MetaModifiers::defaults(), $modifiers);
        $pace = $this->clamp($stats['pace'] ?? 50, 40, 100);
        $bias = ($pace - 40) / 60;

        $weights = [
            1 => max(1, 45 - ($bias * 35)),
            2 => max(1, 30 - ($bias * 10)),
            3 => max(1, 18 + ($bias * 22)),
            4 => max(1, 7 + ($bias * 28)),
        ];

        if (($modifiers['counter_distance'] ?? 1.0) > 1.0) {
            $metaBoost = $modifiers['counter_distance'];
            $weights[3] *= $metaBoost;
            $weights[4] *= $metaBoost;
        }

        $scaled = [];
        foreach ($weights as $distance => $weight) {
            $scaled[$distance] = max(1, (int) round($weight * 10));
        }

        $total = array_sum($scaled);
        $pick = mt_rand(1, $total);
        $cursor = 0;

        foreach ($scaled as $distance => $weight) {
            $cursor += $weight;
            if ($pick <= $cursor) {
                return (int) $distance;
            }
        }

        return SimulationConstants::COUNTER_DISTANCE_MAX;
    }

    public function getKickoffTeam(int $situation, bool $isExtraTime = false): int
    {
        if ($isExtraTime) {
            if ($situation == SimulationConstants::EXTRATIME_START_SITUATION) {
                return 1;
            } elseif ($situation == SimulationConstants::EXTRATIME_HALFTIME_START_SITUATION) {
                return 2;
            }
        } else {
            if ($situation == SimulationConstants::START_SITUATION) {
                return 1;
            } elseif ($situation == SimulationConstants::HALFTIME_START_SITUATION) {
                return 2;
            }
        }

        return 1;
    }

    protected function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }
}
