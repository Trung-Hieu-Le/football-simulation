<?php

namespace Tests\Unit;

use App\Services\MatchEventNormalizer;
use Tests\TestCase;

class MatchEventNormalizerTest extends TestCase
{
    public function test_build_from_simulation_with_goals(): void
    {
        $normalizer = new MatchEventNormalizer();
        $result = $normalizer->buildFromSimulation([
            'goals' => [
                ['minute' => 23, 'team_id' => 1, 'type' => 'goal', 'label' => "23' Team A"],
            ],
        ]);

        $this->assertCount(1, $result['goals']);
        $this->assertNull($result['penalty_shootout']);
    }

    public function test_build_penalty_shootout_payload(): void
    {
        $normalizer = new MatchEventNormalizer();
        $payload = $normalizer->buildPenaltyShootout(1, 2, [
            'team1_penalty_score' => 4,
            'team2_penalty_score' => 3,
            'winner' => 1,
            'kicks' => [
                ['round' => 1, 'phase' => 'initial', 'team_id' => 2, 'scored' => true],
            ],
        ]);

        $this->assertTrue($payload['decided_by_penalties']);
        $this->assertSame(4, $payload['team1_penalty_score']);
        $this->assertSame(1, $payload['winner_team_id']);
    }
}
