<?php

namespace App\Services\Simulation\EventHandlers;

use App\Constants\SimulationConstants;
use App\Constants\StatsWeights;
use App\Services\Simulation\BaseSimulationService;
use App\Services\Simulation\Concerns\RecordsMatchEvents;
use App\Services\Simulation\MetaModifiers;
use App\Services\Simulation\ZoneHelpers;

/**
 * Build-up handler: pressing → miscontrol → progress → long_ball / pace_boost / +1 → retain
 * Clear, stat-driven formulas with zone symmetry
 */
class BuildUpHandler extends BaseSimulationService
{
    use RecordsMatchEvents;

    // Base chances (before stat modifiers)
    public const PRESSING_BASE_CHANCE = 2.0;
    public const MISCONTROL_BASE_CHANCE = 1.2;
    public const OFFSIDE_BASE_CHANCE = 3.0;
    public const RETAIN_BASE_CHANCE = 8.0;

    public function moveBall(
        int $fieldPosition,
        int $currentTeam,
        array $team1Stats,
        array $team2Stats,
        int $time,
        array &$matchData,
        array $modifiers = []
    ): array {
        $modifiers = $this->resolveModifiers($modifiers);
        $attackingStats = $currentTeam == 1 ? $team1Stats : $team2Stats;
        $defendingStats = $currentTeam == 1 ? $team2Stats : $team1Stats;
        $defendingTeam = $currentTeam == 1 ? 2 : 1;

        // 1. PRESSING (defending team tries to steal early)
        $pressingChance = $this->pressingChance(
            $fieldPosition,
            $defendingStats,
            $attackingStats,
            $modifiers
        );
        
        if (rand(1, 1000) <= ($pressingChance * 10)) {
            $this->recordTimelineEvent($time, "Pressing steal by Team{$defendingTeam}", $matchData);
            
            return $this->outcome($fieldPosition, true, 'pressing', [
                'pressing_chance' => round($pressingChance, 2),
                'zone' => $fieldPosition,
            ]);
        }

        // 2. MISCONTROL (attacking team loses ball on own)
        $miscontrolChance = $this->miscontrolChance(
            $fieldPosition,
            $attackingStats,
            $modifiers
        );
        
        if (rand(1, 1000) <= ($miscontrolChance * 10)) {
            $this->recordTimelineEvent($time, "Miscontrol by Team{$currentTeam}", $matchData);
            
            return $this->outcome($fieldPosition, true, 'miscontrol', [
                'miscontrol_chance' => round($miscontrolChance, 2),
                'zone' => $fieldPosition,
            ]);
        }

        // 3. PROGRESS (can we move forward?)
        $buildUpPower = ($attackingStats['control'] * StatsWeights::BUILD_UP_CONTROL_WEIGHT)
                      + ($attackingStats['creative'] * StatsWeights::BUILD_UP_CREATIVE_WEIGHT)
                      + ($attackingStats['stamina'] * StatsWeights::BUILD_UP_STAMINA_WEIGHT);

        $zoneDifficulty = ZoneHelpers::zoneDifficulty($fieldPosition, $currentTeam);
        $stopProgress = ($defendingStats['defense'] * StatsWeights::STOP_DEFENSE_WEIGHT)
                      + ($defendingStats['physical'] * StatsWeights::STOP_PHYSICAL_WEIGHT);

        $progressChance = $buildUpPower / ($buildUpPower + ($stopProgress * $zoneDifficulty));
        $progressChance = $this->clamp($progressChance * 100 * $modifiers['move_chance'], 8, 92);

        if (rand(1, 100) > $progressChance) {
            // Failed progress → contest
            $contestDefWinChance = $this->contestDefenderWinChance(
                $attackingStats,
                $defendingStats,
                $modifiers
            );
            
            if (rand(1, 100) <= $contestDefWinChance) {
                $this->recordTimelineEvent($time, "Contest won by Team{$defendingTeam}", $matchData);
                
                return $this->outcome($fieldPosition, true, 'contest', [
                    'progress_chance' => round($progressChance, 2),
                    'contest_def_win_chance' => round($contestDefWinChance, 2),
                    'zone_difficulty' => $zoneDifficulty,
                    'control_ratio' => $this->controlRatio($attackingStats, $defendingStats),
                ]);
            }

            // Contest held (no progress, no turnover)
            $this->recordTimelineEvent($time, "Contest held by Team{$currentTeam}", $matchData);
            
            return $this->outcome($fieldPosition, false, 'contest_held', [
                'progress_chance' => round($progressChance, 2),
                'contest_def_win_chance' => round($contestDefWinChance, 2),
                'zone_difficulty' => $zoneDifficulty,
            ]);
        }

        // Progress succeeded — long_ball, pace_boost, or normal +1
        [$moveDistance, $distanceEvent] = $this->resolveMoveDistance($attackingStats, $modifiers, $currentTeam, $time, $matchData);

        // Calculate new position
        $newPosition = $currentTeam == 1
                     ? min(10, $fieldPosition + $moveDistance)
                     : max(0, $fieldPosition - $moveDistance);

        // 6. OFFSIDE CHECK (long ball, pace boost, or entering attacking third)
        $afterFastMove = in_array($distanceEvent, ['long_ball', 'pace_boost'], true);
        if ($afterFastMove || ZoneHelpers::isAttackingThird($newPosition, $currentTeam)) {
            $offsideChance = $this->offsideChance(
                $attackingStats,
                $defendingStats,
                $afterFastMove,
                $modifiers
            );
            
            if (rand(1, 1000) <= ($offsideChance * 10)) {
                $this->recordTimelineEvent($time, "Offside by Team{$currentTeam}", $matchData);
                
                return $this->outcome(5, true, 'offside', [
                    'progress_chance' => round($progressChance, 2),
                    'offside_chance' => round($offsideChance, 2),
                    'attempted_zone' => $newPosition,
                    'distance_event' => $distanceEvent,
                ]);
            }
        }

        // 7. AERIAL (in attacking third, before retain)
        if (ZoneHelpers::isAttackingThird($newPosition, $currentTeam)) {
            $aerialChance = $this->aerialChance($modifiers);
            
            if (rand(1, 100) <= $aerialChance) {
                $aerialWin = $this->aerialContest($attackingStats, $defendingStats);
                
                if (!$aerialWin) {
                    $this->recordTimelineEvent($time, "Aerial lost by Team{$currentTeam}", $matchData);
                    
                    return $this->outcome($newPosition, true, 'aerial_lost', [
                        'progress_chance' => round($progressChance, 2),
                        'aerial_chance' => round($aerialChance, 2),
                        'distance_event' => $distanceEvent,
                    ]);
                }
                
                $distanceEvent = $distanceEvent ? "{$distanceEvent}_aerial" : 'aerial';
            }
        }

        // 8. RETAIN (thin roll after successful move — control-heavy)
        $retainChance = $this->retainChance(
            $attackingStats,
            $defendingStats,
            $zoneDifficulty,
            in_array($distanceEvent, ['long_ball', 'pace_boost'], true),
            $modifiers
        );
        
        $retained = rand(1, 100) <= $retainChance;

        if (!$retained) {
            $this->recordTimelineEvent($time, "Lost possession after move — Team{$currentTeam}", $matchData);
            
            return $this->outcome($newPosition, true, 'move_lost', [
                'progress_chance' => round($progressChance, 2),
                'retain_chance' => round($retainChance, 2),
                'control_ratio' => $this->controlRatio($attackingStats, $defendingStats),
                'distance' => $moveDistance,
                'distance_event' => $distanceEvent,
            ]);
        }

        // SUCCESS — moved and retained
        $finalEvent = $distanceEvent ?: 'move';
        
        return $this->outcome($newPosition, false, $finalEvent, [
            'progress_chance' => round($progressChance, 2),
            'retain_chance' => round($retainChance, 2),
            'control_ratio' => $this->controlRatio($attackingStats, $defendingStats),
            'distance' => $moveDistance,
            'distance_event' => $distanceEvent,
            'zone_difficulty' => $zoneDifficulty,
        ]);
    }

    // ==================== FORMULAS ====================

    protected function pressingChance(
        int $zone,
        array $defendingStats,
        array $attackingStats,
        array $modifiers
    ): float {
        $midfieldWeight = ZoneHelpers::midfieldWeight($zone);
        
        // Pressing power: pace + physical + control (coordination)
        $pressPower = $defendingStats['pace'] * 0.25
                    + $defendingStats['physical'] * 0.30
                    + $defendingStats['control'] * 0.25;
        
        // Resist: attacking control + luck
        $resistPower = $attackingStats['control'] * 0.30
                     + $this->specialEventChance($attackingStats['luck']) * 20;
        
        $baseChance = self::PRESSING_BASE_CHANCE * $modifiers['pressing'] * $midfieldWeight;
        $statBonus = ($pressPower - $resistPower) * 0.04;
        
        return $this->clamp($baseChance + $statBonus, 0.5, 8);
    }

    protected function miscontrolChance(
        int $zone,
        array $attackingStats,
        array $modifiers
    ): float {
        $midfieldWeight = ZoneHelpers::midfieldWeight($zone);
        
        $baseChance = self::MISCONTROL_BASE_CHANCE * $modifiers['miscontrol'] * $midfieldWeight;
        $controlBonus = ($attackingStats['control'] - 70) * 0.08;
        $luckResist = $this->specialEventChance($attackingStats['luck']) * 0.30;

        return $this->clamp($baseChance - $controlBonus - $luckResist, 0.2, 5);
    }

    protected function contestDefenderWinChance(
        array $attackingStats,
        array $defendingStats,
        array $modifiers
    ): float {
        // Physical contest after failed progress
        $attackerPower = ($attackingStats['stamina'] * StatsWeights::CONTEST_STAMINA_WEIGHT)
                       + ($attackingStats['physical'] * StatsWeights::CONTEST_PHYSICAL_WEIGHT);
        $defenderPower = ($defendingStats['stamina'] * StatsWeights::CONTEST_STAMINA_WEIGHT)
                       + ($defendingStats['defense'] * StatsWeights::CONTEST_DEFENSE_WEIGHT)
                       + ($defendingStats['physical'] * StatsWeights::CONTEST_PHYSICAL_WEIGHT);

        if ($attackerPower + $defenderPower <= 0) {
            return 50.0;
        }

        $luckEdge = ($defendingStats['luck'] - $attackingStats['luck']) * 0.04;
        $chance = (($defenderPower / ($attackerPower + $defenderPower)) * 100 * $modifiers['contest']) + $luckEdge;

        return $this->clamp($chance, 8, 92);
    }

    protected function resolveMoveDistance(
        array $attackingStats,
        array $modifiers,
        int $currentTeam,
        int $time,
        array &$matchData
    ): array {
        $longBallChance = $this->longBallChance($attackingStats, $modifiers);

        if (rand(1, 100) <= $longBallChance) {
            $hasLongBallMeta = ($modifiers['long_ball_skip'] ?? 0) > 0;
            $creativeHigh = ($attackingStats['creative'] ?? 50) >= 80;
            $distance = ($hasLongBallMeta && $creativeHigh)
                ? SimulationConstants::MOVE_DISTANCE_LONG_BALL
                : SimulationConstants::MOVE_DISTANCE_FAST;

            $this->recordTimelineEvent($time, "Long ball by Team{$currentTeam}", $matchData);

            return [$distance, 'long_ball'];
        }

        if ($this->shouldPaceBoost($attackingStats, $modifiers)) {
            $this->recordTimelineEvent($time, "Pace burst by Team{$currentTeam}", $matchData);

            return [SimulationConstants::MOVE_DISTANCE_FAST, 'pace_boost'];
        }

        return [SimulationConstants::MOVE_DISTANCE_NORMAL, 'move'];
    }

    protected function shouldPaceBoost(array $attackingStats, array $modifiers): bool
    {
        $paceThreshold = 80;
        $boostChance = ($modifiers['pace_boost'] ?? 1.0) * 100;

        if ($attackingStats['pace'] < $paceThreshold || $boostChance <= 0) {
            return false;
        }

        $paceBonus = ($attackingStats['pace'] - $paceThreshold) * 0.3;
        $finalChance = min($boostChance + $paceBonus, 25);

        return rand(1, 100) <= $finalChance;
    }

    protected function longBallChance(array $attackingStats, array $modifiers): float
    {
        $creative = $attackingStats['creative'] ?? 50;
        $base = 5 + max(0, $creative - 50) * 0.12;
        $metaBoost = ($modifiers['long_ball_skip'] ?? 0) * 100;

        return $this->clamp($base + $metaBoost, 3, 35);
    }

    protected function offsideChance(
        array $attackingStats,
        array $defendingStats,
        bool $afterFastMove,
        array $modifiers
    ): float {
        $baseChance = self::OFFSIDE_BASE_CHANCE * $modifiers['offside'];
        
        // Creative reduces offside (timing/positioning)
        $creativeReduce = ($attackingStats['creative'] - 70) * 0.08;
        
        // Attack reduces slightly (movement/finishing runs)
        $attackReduce = ($attackingStats['attack'] - 70) * 0.03;
        
        // Defending team defense increases offside trap
        $defenseBonus = ($defendingStats['defense'] - 70) * 0.04;
        
        // Fast move penalty (long ball / pace boost)
        $fastPenalty = $afterFastMove ? 1.5 : 0;
        
        $luckModifier = $this->specialEventChance($attackingStats['luck']) * 0.15;
        
        $chance = $baseChance - $creativeReduce - $attackReduce + $defenseBonus + $fastPenalty + $luckModifier;
        
        return $this->clamp($chance, 0.3, 10);
    }

    protected function aerialChance(array $modifiers): float
    {
        // Meta-driven (wing_play high)
        return ($modifiers['aerial'] ?? 1.0) * 15;
    }

    protected function aerialContest(array $attackingStats, array $defendingStats): bool
    {
        $attPower = $attackingStats['physical'] + $attackingStats['stamina'] * 0.3;
        $defPower = $defendingStats['physical']
                  + $defendingStats['defense'] * 0.5
                  + ($defendingStats['goalkeeping'] ?? 50) * 0.4;

        $attWinChance = ($attPower / ($attPower + $defPower)) * 100;

        return rand(1, 100) <= $attWinChance;
    }

    protected function retainChance(
        array $attackingStats,
        array $defendingStats,
        float $zoneDifficulty,
        bool $afterFastMove,
        array $modifiers
    ): float {
        $baseChance = self::RETAIN_BASE_CHANCE * ($modifiers['retain_chance'] ?? 1.0);

        // Control is king for retention
        $controlBonus = ($attackingStats['control'] - 70) * 0.35;
        $defensePress = ($defendingStats['defense'] - 70) * 0.15 * $zoneDifficulty;

        // Long ball / pace boost penalty (harder to keep after vertical or sprint)
        $fastMovePenalty = $afterFastMove ? 8 : 0;
        
        $chance = $baseChance + $controlBonus - $defensePress - $fastMovePenalty;
        
        return $this->clamp($chance, 60, 95); // High floor — not many random turnovers
    }

    // ==================== HELPERS ====================

    protected function controlRatio(array $attackingStats, array $defendingStats): float
    {
        $total = $attackingStats['control'] + $defendingStats['control'];
        return $total > 0 ? round(($attackingStats['control'] / $total) * 100, 2) : 50.0;
    }

    protected function outcome(int $newPosition, bool $stolen, string $event, array $detail): array
    {
        return [
            'newPosition' => $newPosition,
            'stolen' => $stolen,
            'event' => $event,
            'detail' => $detail,
        ];
    }

    protected function resolveModifiers(array $modifiers): array
    {
        return array_merge(MetaModifiers::defaults(), $modifiers);
    }
}
