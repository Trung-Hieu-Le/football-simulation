<?php

namespace App\Services\Simulation\EventHandlers;

use App\Constants\SimulationConstants;
use App\Constants\StatsWeights;
use App\Services\Simulation\BaseSimulationService;
use App\Services\Simulation\Concerns\RecordsMatchEvents;
use App\Services\Simulation\MetaModifiers;

class BuildUpHandler extends BaseSimulationService
{
    use RecordsMatchEvents;

    public const MISCONTROL_BASE_CHANCE = 3;
    public const DRIBBLE_BASE_CHANCE = 5;
    public const OFFSIDE_BASE_CHANCE = 4;
    public const TACKLE_BASE_CHANCE = 8;
    public const PRESSING_BASE_CHANCE = 6;
    public const SOFT_STEAL_MARGIN = 15;

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

        if ($this->rollMiscontrol($attackingStats['control'], $attackingStats['luck'], $modifiers)) {
            $this->recordTimelineEvent($time, "Miscontrol by Team{$currentTeam}", $matchData);

            return ['newPosition' => $fieldPosition, 'stolen' => true, 'event' => 'miscontrol'];
        }

        if ($this->rollPressingSteal(
            $defendingStats['pace'],
            $defendingStats['discipline'],
            $defendingStats['luck'],
            $modifiers
        )) {
            $this->recordTimelineEvent($time, "Pressing steal by Team{$defendingTeam}", $matchData);

            return ['newPosition' => $fieldPosition, 'stolen' => true, 'event' => 'pressing'];
        }

        $buildUpPower = ($attackingStats['control'] * StatsWeights::BUILD_UP_CONTROL_WEIGHT)
                      + ($attackingStats['creative'] * StatsWeights::BUILD_UP_CREATIVE_WEIGHT)
                      + ($attackingStats['stamina'] * StatsWeights::BUILD_UP_STAMINA_WEIGHT);

        $zoneDifficulty = $this->getZoneDifficulty($fieldPosition);
        $stopProgress = ($defendingStats['defense'] * StatsWeights::STOP_DEFENSE_WEIGHT)
                      + ($defendingStats['discipline'] * StatsWeights::STOP_DISCIPLINE_WEIGHT);

        $moveChance = $buildUpPower / ($buildUpPower + ($stopProgress * $zoneDifficulty));
        $moveChance = $this->clamp($moveChance * 100 * $modifiers['move_chance'], 5, 95);

        if (rand(1, 100) > $moveChance) {
            if ($this->resolveContest($attackingStats, $defendingStats, $modifiers)) {
                $this->recordTimelineEvent($time, "Contest won by Team{$defendingTeam}", $matchData);

                return ['newPosition' => $fieldPosition, 'stolen' => true, 'event' => 'contest'];
            }

            $this->recordTimelineEvent($time, "Contest held by Team{$currentTeam}", $matchData);

            return ['newPosition' => $fieldPosition, 'stolen' => false, 'event' => 'contest_held'];
        }

        if ($this->rollTackle($defendingStats['defense'], $defendingStats['luck'], $modifiers)) {
            $this->recordTimelineEvent($time, "Tackle by Team{$defendingTeam}", $matchData);

            return ['newPosition' => $fieldPosition, 'stolen' => true, 'event' => 'tackle'];
        }

        $moveDistance = SimulationConstants::MOVE_DISTANCE_NORMAL;

        if ($this->rollLongBallSkip($modifiers)) {
            $moveDistance = SimulationConstants::MOVE_DISTANCE_FAST;
            $this->recordTimelineEvent($time, "Long ball by Team{$currentTeam}", $matchData);
        } elseif ($this->rollDribble(
            $attackingStats['pace'],
            $attackingStats['creative'],
            $attackingStats['luck'],
            $modifiers
        )) {
            $moveDistance = SimulationConstants::MOVE_DISTANCE_FAST;
            $this->recordTimelineEvent($time, "Successful dribble by Team{$currentTeam}", $matchData);
        }

        $newPosition = $currentTeam == 1
                     ? min(10, $fieldPosition + $moveDistance)
                     : max(0, $fieldPosition - $moveDistance);

        if ($this->isAttackingThird($newPosition, $currentTeam)
            && $this->rollOffside($attackingStats['discipline'], $attackingStats['luck'], $modifiers)) {
            $this->recordTimelineEvent($time, "Offside by Team{$currentTeam}", $matchData);

            return ['newPosition' => 5, 'stolen' => true, 'event' => 'offside'];
        }

        $controlRatio = ($attackingStats['control'] / ($attackingStats['control'] + $defendingStats['control'])) * 100;
        $stealMargin = self::SOFT_STEAL_MARGIN / max(0.1, $modifiers['steal_threshold'] ?? 1.0);
        $stealThreshold = $controlRatio - $stealMargin;
        $stolen = rand(1, 100) > $stealThreshold;

        if ($stolen) {
            $this->recordTimelineEvent($time, "Loose ball — possession lost by Team{$currentTeam}", $matchData);
        }

        return [
            'newPosition' => $newPosition,
            'stolen' => $stolen,
            'event' => $stolen ? 'steal' : null,
        ];
    }

    protected function resolveModifiers(array $modifiers): array
    {
        return array_merge(MetaModifiers::defaults(), $modifiers);
    }

    protected function resolveContest(array $attackingStats, array $defendingStats, array $modifiers): bool
    {
        $attackerPower = ($attackingStats['stamina'] * StatsWeights::CONTEST_STAMINA_WEIGHT)
                       + ($attackingStats['control'] * StatsWeights::CONTEST_CONTROL_WEIGHT);
        $defenderPower = ($defendingStats['stamina'] * StatsWeights::CONTEST_STAMINA_WEIGHT)
                       + ($defendingStats['defense'] * StatsWeights::CONTEST_DEFENSE_WEIGHT);

        if ($attackerPower + $defenderPower <= 0) {
            return rand(0, 1) === 1;
        }

        $defenderWinChance = ($defenderPower / ($attackerPower + $defenderPower)) * 100 * $modifiers['contest'];
        $defenderWinChance = $this->clamp($defenderWinChance, 5, 95);

        return rand(1, 100) <= $defenderWinChance;
    }

    protected function isAttackingThird(int $position, int $team): bool
    {
        return $team == 1 ? $position >= 8 : $position <= 2;
    }

    protected function rollLongBallSkip(array $modifiers): bool
    {
        $chance = ($modifiers['long_ball_skip'] ?? 0.0) * 100;

        return $chance > 0 && rand(1, 100) <= $chance;
    }

    protected function rollMiscontrol(float $control, float $luck, array $modifiers): bool
    {
        $baseChance = self::MISCONTROL_BASE_CHANCE * $modifiers['miscontrol'];
        $controlBonus = ($control - 70) * 0.08;
        $luckModifier = $this->specialEventChance($luck) * 0.3;

        $chance = $baseChance - $controlBonus + $luckModifier;
        $chance = $this->clamp($chance, 0.5, 10);

        return rand(1, 1000) <= ($chance * 10);
    }

    protected function rollDribble(float $pace, float $creative, float $luck, array $modifiers): bool
    {
        if ($pace < SimulationConstants::SPEED_THRESHOLD) {
            return false;
        }

        $baseChance = self::DRIBBLE_BASE_CHANCE * $modifiers['dribble'];
        $paceBonus = ($pace - 70) * 0.1;
        $creativeBonus = ($creative - 70) * 0.05;
        $luckModifier = $this->specialEventChance($luck) * 0.2;

        $chance = $baseChance + $paceBonus + $creativeBonus + $luckModifier;
        $chance = $this->clamp($chance, 1, 25);

        return rand(1, 100) <= $chance;
    }

    protected function rollOffside(float $discipline, float $luck, array $modifiers): bool
    {
        $baseChance = self::OFFSIDE_BASE_CHANCE * $modifiers['offside'];
        $disciplineBonus = ($discipline - 70) * 0.06;
        $luckModifier = $this->specialEventChance($luck) * 0.25;

        $chance = $baseChance - $disciplineBonus + $luckModifier;
        $chance = $this->clamp($chance, 0.5, 12);

        return rand(1, 1000) <= ($chance * 10);
    }

    protected function rollTackle(float $defense, float $luck, array $modifiers): bool
    {
        $baseChance = self::TACKLE_BASE_CHANCE * $modifiers['tackle'];
        $defenseBonus = ($defense - 70) * 0.08;
        $luckModifier = $this->specialEventChance($luck) * 0.15;

        $chance = $baseChance + $defenseBonus + $luckModifier;
        $chance = $this->clamp($chance, 2, 20);

        return rand(1, 100) <= $chance;
    }

    protected function rollPressingSteal(float $pace, float $discipline, float $luck, array $modifiers): bool
    {
        $baseChance = self::PRESSING_BASE_CHANCE * $modifiers['pressing'];
        $paceBonus = ($pace - 70) * 0.07;
        $disciplineBonus = ($discipline - 70) * 0.05;
        $luckModifier = $this->specialEventChance($luck) * 0.2;

        $chance = $baseChance + $paceBonus + $disciplineBonus + $luckModifier;
        $chance = $this->clamp($chance, 1, 18);

        return rand(1, 100) <= $chance;
    }

    protected function getZoneDifficulty(int $position): float
    {
        return StatsWeights::ZONE_PROGRESS_DIFFICULTY[$position] ?? 1.0;
    }
}
