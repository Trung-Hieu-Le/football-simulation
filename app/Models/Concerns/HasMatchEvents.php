<?php

namespace App\Models\Concerns;

trait HasMatchEvents
{
    public function matchGoals(): array
    {
        return data_get($this->match_events, 'goals', []);
    }

    public function penaltyShootout(): ?array
    {
        return data_get($this->match_events, 'penalty_shootout');
    }

    public function decidedByPenalties(): bool
    {
        return (bool) data_get($this->match_events, 'penalty_shootout.decided_by_penalties');
    }

    public function penaltyKicks(): array
    {
        return data_get($this->match_events, 'penalty_shootout.kicks', []);
    }

    public function penaltyScoreLabel(): ?string
    {
        if (!$this->decidedByPenalties()) {
            return null;
        }

        $t1 = data_get($this->match_events, 'penalty_shootout.team1_penalty_score');
        $t2 = data_get($this->match_events, 'penalty_shootout.team2_penalty_score');

        if ($t1 === null || $t2 === null) {
            return null;
        }

        return "{$t1}–{$t2} pens";
    }

    public function displayScore(): string
    {
        if ($this->team1_score === null || $this->team2_score === null) {
            return '- : -';
        }

        $score = "{$this->team1_score} : {$this->team2_score}";

        if ($label = $this->penaltyScoreLabel()) {
            return "{$score} ({$label})";
        }

        return $score;
    }
}
