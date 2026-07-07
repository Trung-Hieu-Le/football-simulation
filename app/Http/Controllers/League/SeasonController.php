<?php

namespace App\Http\Controllers\League;

use App\Http\Controllers\Controller;
use App\Models\League\Season;
use App\Models\League\GroupTeam;
use App\Models\League\LeagueMatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\League\Standing;
use App\Models\League\Position;
use App\Models\Team;
use App\Enums\SeasonMeta;
use App\Enums\DivisionLevel;
use App\Enums\LeagueSeasonResult;
use Illuminate\Http\Request;

class SeasonController extends Controller
{
    public function index()
    {
        $seasons = Season::orderBy('season', 'desc')->get();
        return view('league.seasons.index', compact('seasons'));
    }

    public function create()
    {
        $teams = Team::orderByDesc('elo')->get();
        $metas = SeasonMeta::options();
        $listTeamsCount = [12, 24, 36, 48, 60];
        $nextSeason = (Season::max('season') ?? 0) + 1;

        return view('league.seasons.create', compact('teams', 'metas', 'listTeamsCount', 'nextSeason'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'teams_count' => 'required|integer|min:12',
                'meta' => 'nullable|string',
                'selected_teams' => 'required|array|min:1',
                'selected_teams.*' => 'integer|exists:teams,id',
            ]);

            if ($validated['teams_count'] % 12 !== 0) {
                return back()->withInput()->withErrors(['teams_count' => 'Số đội phải chia hết cho 12']);
            }

            if (count($validated['selected_teams']) !== (int) $validated['teams_count']) {
                return back()->withInput()->withErrors(['selected_teams' => 'Phải chọn đúng ' . $validated['teams_count'] . ' đội']);
            }

            $meta = $validated['meta'] ?: SeasonMeta::random();

            $season = DB::transaction(function () use ($validated, $meta) {
                $lastSeason = Season::orderBy('season', 'desc')->first();
                $seasonNumber = $lastSeason ? $lastSeason->season + 1 : 1;

                $season = Season::create([
                    'season' => $seasonNumber,
                    'teams_count' => $validated['teams_count'],
                    'meta' => $meta,
                ]);

                $this->distributeToDivisions($season, $validated['selected_teams']);
                $this->generateMatches($season);
                $this->createStandings($season);

                return $season;
            });

            return redirect()->route('league.seasons.show', $season->id)
                ->with('success', 'League season created successfully!');
        } catch (\Throwable $e) {
            Log::error('League season create failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', 'Tạo mùa giải thất bại: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                Position::where('season_id', $id)->delete();
                Standing::where('season_id', $id)->delete();
                LeagueMatch::where('season_id', $id)->delete();
                GroupTeam::where('season_id', $id)->delete();
                Season::where('id', $id)->delete();
            });

            return redirect()->route('league.seasons.index')
                ->with('success', 'Season deleted successfully!');
        } catch (\Throwable $e) {
            Log::error('League season delete failed', ['id' => $id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Xóa mùa giải thất bại: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $season = Season::with(['groupTeams', 'standings.team', 'standings.position'])
                        ->findOrFail($id);
        
        $divisions = $this->getDivisionStandings($season);
        
        return view('league.seasons.show', compact('season', 'divisions'));
    }

    protected function distributeToDivisions(Season $season, array $teamIds)
    {
        $teamsPerDivision = $season->teams_count / 3;
        $divisions = [
            DivisionLevel::DIVISION1->value,
            DivisionLevel::DIVISION2->value,
            DivisionLevel::DIVISION3->value,
        ];

        $teamChunks = array_chunk($teamIds, $teamsPerDivision);

        foreach ($divisions as $index => $division) {
            if (isset($teamChunks[$index])) {
                $groupTeam = new GroupTeam([
                    'season_id' => $season->id,
                    'group' => $division,
                ]);
                $groupTeam->setTeamIdsArray($teamChunks[$index]);
                $groupTeam->save();
            }
        }
    }

    protected function generateMatches(Season $season)
    {
        $groupTeams = $season->groupTeams;

        foreach ($groupTeams as $groupTeam) {
            $teamIds = $groupTeam->getTeamIdsArray();
            $teamsCount = count($teamIds);
            $rounds = ($teamsCount - 1) * 2;

            for ($round = 1; $round <= $rounds; $round++) {
                $matches = $this->generateRoundMatches($teamIds, $round);
                
                foreach ($matches as $match) {
                    LeagueMatch::create([
                        'season_id' => $season->id,
                        'division' => $groupTeam->group,
                        'round' => $round,
                        'team1_id' => $match[0],
                        'team2_id' => $match[1],
                    ]);
                }
            }
        }
    }

    protected function generateRoundMatches(array $teams, int $round): array
    {
        $count = count($teams);
        $half = $count / 2;
        $matches = [];

        if ($count % 2 !== 0) {
            $teams[] = null;
            $count++;
        }

        $roundIndex = ($round - 1) % ($count - 1);

        for ($i = 0; $i < $half; $i++) {
            $home = ($roundIndex + $i) % ($count - 1);
            $away = ($count - 1 - $i + $roundIndex) % ($count - 1);
            
            if ($i == 0) {
                $away = $count - 1;
            }

            $homeTeam = $teams[$home];
            $awayTeam = $teams[$away];

            if ($homeTeam !== null && $awayTeam !== null) {
                if ($round > ($count - 1)) {
                    $matches[] = [$awayTeam, $homeTeam];
                } else {
                    $matches[] = [$homeTeam, $awayTeam];
                }
            }
        }

        return $matches;
    }

    protected function createStandings(Season $season)
    {
        $groupTeams = $season->groupTeams;

        foreach ($groupTeams as $groupTeam) {
            $teamIds = $groupTeam->getTeamIdsArray();
            
            foreach ($teamIds as $teamId) {
                Standing::create([
                    'team_id' => $teamId,
                    'season_id' => $season->id,
                    'division' => $groupTeam->group,
                ]);
            }
        }
    }

    protected function getDivisionStandings(Season $season): array
    {
        $standings = $season->standings()
                           ->with(['team', 'position'])
                           ->get()
                           ->groupBy('division');

        $divisions = [];
        foreach ($standings as $division => $divisionStandings) {
            $sorted = $divisionStandings->sortByDesc(function ($standing) {
                return [
                    $standing->points,
                    $standing->goal_difference,
                    $standing->goal_scored,
                ];
            })->values();

            $divisions[$division] = $sorted;
        }

        return $divisions;
    }

    public function calculateResults($id)
    {
        $season = Season::findOrFail($id);
        
        $groupTeams = $season->groupTeams;
        foreach ($groupTeams as $groupTeam) {
            $this->calculateDivisionResults($season, $groupTeam->group);
        }

        return redirect()->route('league.seasons.show', $id)
                        ->with('success', 'Season results calculated!');
    }

    protected function calculateDivisionResults(Season $season, string $division)
    {
        $standings = Standing::where('season_id', $season->id)
                            ->where('division', $division)
                            ->orderByDesc('points')
                            ->orderByDesc('goal_difference')
                            ->orderByDesc('goal_scored')
                            ->get();

        $teamsCount = $standings->count();
        $promotionCount = (int)ceil($teamsCount * 0.25);
        $relegationCount = (int)ceil($teamsCount * 0.25);

        foreach ($standings as $index => $standing) {
            $position = $index + 1;
            $result = $this->determineResult($position, $teamsCount, $promotionCount, $relegationCount, $division);

            Position::updateOrCreate(
                ['league_standing_id' => $standing->id],
                [
                    'season_id' => $season->id,
                    'position' => $position,
                    'result' => $result,
                ]
            );
        }
    }

    protected function determineResult(int $position, int $teamsCount, int $promotionCount, int $relegationCount, string $division): string
    {
        if ($position === 1) {
            return LeagueSeasonResult::CHAMPION->value;
        }

        if ($division !== DivisionLevel::DIVISION1->value && $position <= $promotionCount) {
            return LeagueSeasonResult::PROMOTED->value;
        }

        if ($division !== DivisionLevel::DIVISION3->value && $position > ($teamsCount - $relegationCount)) {
            return LeagueSeasonResult::RELEGATED->value;
        }

        return LeagueSeasonResult::STAY->value;
    }
}
