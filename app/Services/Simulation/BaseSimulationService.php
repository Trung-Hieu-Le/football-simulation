<?php

namespace App\Services\Simulation;

use App\Constants\SimulationConstants;
use App\Constants\FieldPositions;
use App\Constants\StatsWeights;

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
            'discipline' => $team->discipline ?? 50,
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

    public function possessionPower(array $stats): float
    {
        return ($stats['control'] * StatsWeights::POSSESSION_CONTROL_WEIGHT)
             + ($stats['stamina'] * StatsWeights::POSSESSION_STAMINA_WEIGHT)
             + ($stats['mental'] * StatsWeights::POSSESSION_MENTAL_WEIGHT);
    }

    public function stealPower(array $stats): float
    {
        return ($stats['defense'] * StatsWeights::STEAL_DEFENSE_WEIGHT)
             + ($stats['pace'] * StatsWeights::STEAL_PACE_WEIGHT)
             + ($stats['discipline'] * StatsWeights::STEAL_DISCIPLINE_WEIGHT);
    }

    public function shotDecisionChance(int $zone, float $attack, float $mental, array $modifiers = []): float
    {
        $zoneBonus = StatsWeights::ZONE_SHOT_BONUS[$zone] ??
                     (($zone === FieldPositions::GOAL_TEAM1 || $zone === FieldPositions::GOAL_TEAM2) ? 30 : 0);

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
