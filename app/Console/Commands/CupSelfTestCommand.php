<?php

namespace App\Console\Commands;

use App\Models\Cup\EliminateMatch;
use App\Models\Cup\GroupStageMatch;
use App\Models\Cup\GroupTeam;
use App\Models\Cup\Season;
use App\Models\Cup\Standing;
use App\Models\Team;
use App\Services\CupGroupStageService;
use App\Services\CupKnockoutService;
use App\Services\CupPotSeedingService;
use App\Services\MatchHistoryService;
use App\Services\RoundRobinService;
use App\Services\Simulation\MatchSimulator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CupSelfTestCommand extends Command
{
    protected $signature = 'cup:self-test {--teams=32 : 32 or 64}';

    protected $description = 'Run cup mode integration self-test (group stage + knockout bracket)';

    public function handle(
        CupPotSeedingService $potService,
        RoundRobinService $roundRobin,
        CupKnockoutService $knockout,
        CupGroupStageService $groupStage,
        MatchSimulator $simulator,
        MatchHistoryService $history,
    ): int {
        $teamsCount = (int) $this->option('teams');
        if (!in_array($teamsCount, [32, 64], true)) {
            $this->error('teams must be 32 or 64');
            return self::FAILURE;
        }

        try {
            DB::beginTransaction();

            $teams = Team::orderByDesc('elo')->limit($teamsCount)->get();
            if ($teams->count() < $teamsCount) {
                $this->error("Need at least {$teamsCount} teams in DB, found {$teams->count()}");
                return self::FAILURE;
            }

            $season = Season::create([
                'season' => (Season::max('season') ?? 0) + 9000,
                'teams_count' => $teamsCount,
                'meta' => 'possession',
            ]);

            $pots = $potService->distributeToPots($teams);
            $groups = $potService->drawGroups($pots);

            $this->info("Groups: " . count($groups));
            foreach ($groups as $letter => $groupTeams) {
                $gt = new GroupTeam(['season_id' => $season->id, 'group' => $letter]);
                $gt->setTeamIdsArray($groupTeams->pluck('id')->toArray());
                $gt->save();

                foreach ($groupTeams as $team) {
                    Standing::create(['team_id' => $team->id, 'season_id' => $season->id, 'group' => $letter]);
                }

                $rounds = $roundRobin->generateSingleRoundRobin($groupTeams->pluck('id')->toArray());
                foreach ($rounds as $roundNum => $matches) {
                    foreach ($matches as [$t1, $t2]) {
                        GroupStageMatch::create([
                            'season_id' => $season->id,
                            'group' => $letter,
                            'round' => $roundNum,
                            'team1_id' => $t1,
                            'team2_id' => $t2,
                        ]);
                    }
                }
            }

            $expectedGroupMatches = ($teamsCount / 8) * (($teamsCount / 8) - 1) * 4;
            $actualGroupMatches = GroupStageMatch::where('season_id', $season->id)->count();
            $this->assertStep($expectedGroupMatches === $actualGroupMatches, "Group matches: expected {$expectedGroupMatches}, got {$actualGroupMatches}");

            $knockout->createKnockoutBracket($season);

            GroupStageMatch::where('season_id', $season->id)->with(['team1', 'team2'])->each(function ($match) use ($simulator, $history, $season) {
                $result = $simulator->simulateMatch($match->team1, $match->team2, $season->meta, false);
                $match->update(['team1_score' => $result['team1_score'], 'team2_score' => $result['team2_score']]);
                $history->updateCupGroupStageHistory(
                    $match->team1_id, $match->team2_id, $season->id,
                    $result['team1_score'], $result['team2_score'],
                    $result['team1_fouls'], $result['team2_fouls'],
                    $result['team1_possession'], $result['team2_possession']
                );
            });

            $groupStage->syncGroupPositions($season);
            $knockout->populateRoundOf16($season, $groupStage->getGroupStandingsForKnockout($season));

            $r16 = EliminateMatch::where('season_id', $season->id)->where('round', 'round_of_16')->get();
            $this->assertStep($r16->count() === 16, 'R16 has 16 matches');
            $this->assertStep($r16->whereNotNull('team1_id')->whereNotNull('team2_id')->count() === 16, 'R16 all slots filled');

            DB::rollBack();
            $this->info('Cup self-test PASSED (rolled back test data).');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Cup self-test FAILED: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function assertStep(bool $ok, string $message): void
    {
        if (!$ok) {
            throw new \RuntimeException($message);
        }
        $this->line('  ✓ ' . $message);
    }
}
