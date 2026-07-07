<?php

namespace App\Services\Simulation;

use App\Constants\SimulationConstants;
use App\Constants\FieldPositions;
use App\Constants\StatsWeights;

class BaseSimulationService
{
    /**
     * Calculate team stats with stamina and meta factors
     */
    public function calculateTeamStats($team, string $seasonMeta, bool $isSecondHalf = false, bool $isExtraTime = false): array
    {
        $phaseDecay = $this->getPhaseDecay($isSecondHalf, $isExtraTime);
        
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

        return $this->applyMetaFactors($stats, $seasonMeta, $isSecondHalf);
    }

    protected function getPhaseDecay(bool $isSecondHalf, bool $isExtraTime): float
    {
        if ($isExtraTime) {
            return $isSecondHalf ? SimulationConstants::EXTRA_2_DECAY : SimulationConstants::EXTRA_1_DECAY;
        }
        return $isSecondHalf ? SimulationConstants::HALF_2_DECAY : SimulationConstants::HALF_1_DECAY;
    }

    protected function applyMetaFactors(array $stats, string $seasonMeta, bool $isSecondHalf): array
    {
        $metaBonus = SimulationConstants::META_BONUS;
        $metaPenalty = SimulationConstants::META_PENALTY;

        switch ($seasonMeta) {
            case 'possession':
                $stats['control'] *= $metaBonus;
                $stats['attack'] *= $metaPenalty;
                $stats['defense'] *= $metaPenalty;
                break;
            case 'counter':
                $stats['pace'] *= $metaBonus;
                $stats['control'] *= $metaPenalty;
                break;
            case 'pressing':
                $stats['defense'] *= $metaBonus;
                $stats['stamina_factor'] *= $metaPenalty;
                break;
            case 'tiki-taka':
                $stats['control'] *= $metaBonus;
                $stats['pace'] *= $metaPenalty;
                break;
            case 'long_ball':
                $stats['attack'] *= $metaBonus;
                $stats['control'] *= $metaPenalty;
                break;
            case 'build_up':
                $stats['control'] *= $metaBonus;
                $stats['pace'] *= $metaPenalty;
                break;
            case 'low_block':
                $stats['defense'] *= $metaBonus;
                $stats['attack'] *= $metaPenalty;
                break;
            case 'high_risk':
                $stats['attack'] *= $metaBonus;
                $stats['defense'] *= $metaPenalty;
                break;
            case 'high_line':
                $stats['defense'] *= $metaBonus;
                $stats['stamina_factor'] *= $metaPenalty;
                break;
        }

        return $stats;
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

    public function shotDecisionChance(int $zone, float $attack, float $mental): float
    {
        $zoneBonus = StatsWeights::ZONE_SHOT_BONUS[$zone] ?? 
                     (($zone === FieldPositions::GOAL_TEAM1 || $zone === FieldPositions::GOAL_TEAM2) ? 30 : 0);
        
        return min(
            SimulationConstants::SHOOT_DECISION_MAX,
            SimulationConstants::SHOOT_DECISION_BASE_CHANCE 
            + $zoneBonus 
            + ($attack * SimulationConstants::SHOOT_DECISION_ATTACK_MULTIPLIER) 
            + ($mental * SimulationConstants::SHOOT_DECISION_MENTAL_MULTIPLIER)
        );
    }

    public function shotGoalChance(float $attack, float $mental, float $goalkeeping, float $keeperMental): float
    {
        $shotPower = ($attack * StatsWeights::SHOT_POWER_ATTACK_WEIGHT) 
                   + ($mental * StatsWeights::SHOT_POWER_MENTAL_WEIGHT);
        $savePower = ($goalkeeping * StatsWeights::SAVE_POWER_GOALKEEPING_WEIGHT) 
                   + ($keeperMental * StatsWeights::SAVE_POWER_MENTAL_WEIGHT);
        
        if (($shotPower + $savePower) == 0) {
            return 0.0;
        }
        
        return $shotPower / ($shotPower + $savePower);
    }

    public function specialEventChance(float $luck): float
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
