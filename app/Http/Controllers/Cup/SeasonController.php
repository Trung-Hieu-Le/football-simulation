<?php

namespace App\Http\Controllers\Cup;

use App\Http\Controllers\Controller;
use App\Models\Cup\Season;
use App\Models\Cup\Standing;
use App\Models\Cup\GroupStageMatch;
use App\Models\Cup\EliminateMatch;
use App\Models\Cup\GroupTeam;
use App\Models\Cup\Position;
use App\Models\Team;
use App\Enums\SeasonMeta;
use App\Services\CupPotSeedingService;
use App\Services\CupKnockoutService;
use App\Services\CupGroupStageService;
use App\Services\RoundRobinService;
use App\Services\StatisticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SeasonController extends Controller
{
    public function __construct(
        protected CupPotSeedingService $potSeedingService,
        protected CupKnockoutService $knockoutService,
        protected CupGroupStageService $groupStageService,
        protected RoundRobinService $roundRobinService,
        protected StatisticsService $statisticsService,
    ) {
    }

    public function index()
    {
        $seasons = Season::orderBy('season', 'desc')->get();
        $champions = $this->statisticsService->getCupChampionsForSeasonIds(
            $seasons->pluck('id')->all()
        );

        return view('cup.seasons.index', compact('seasons', 'champions'));
    }

    public function create()
    {
        $teams = Team::orderByDesc('elo')->get();
        $metas = SeasonMeta::options();
        $listTeamsCount = [32, 64];
        $nextSeason = (Season::max('season') ?? 0) + 1;

        return view('cup.seasons.create', compact('teams', 'metas', 'listTeamsCount', 'nextSeason'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'teams_count' => 'required|integer|in:32,64',
                'meta' => 'nullable|string',
                'selected_teams' => 'required|array|min:1',
                'selected_teams.*' => 'integer|exists:teams,id',
            ]);

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

                $selectedTeams = Team::whereIn('id', $validated['selected_teams'])
                    ->orderByDesc('elo')
                    ->get();

                $this->distributeToGroups($season, $selectedTeams);
                $season->load('groupTeams');
                $this->generateGroupStageMatches($season);
                $this->createStandings($season);
                $this->knockoutService->createKnockoutBracket($season);

                return $season;
            });

            return redirect()->route('cup.seasons.show', $season->id)
                ->with('success', 'Cup season created successfully!');
        } catch (\Throwable $e) {
            Log::error('Cup season create failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', 'Tạo mùa giải thất bại: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $season = Season::with(['groupTeams', 'standings.team'])->findOrFail($id);
        $groups = $this->groupStageService->getGroupStandings($season);

        return view('cup.seasons.show', compact('season', 'groups'));
    }

    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                Position::where('season_id', $id)->delete();
                Standing::where('season_id', $id)->delete();
                GroupStageMatch::where('season_id', $id)->delete();
                EliminateMatch::where('season_id', $id)->delete();
                GroupTeam::where('season_id', $id)->delete();
                Season::where('id', $id)->delete();
            });

            return redirect()->route('cup.seasons.index')
                ->with('success', 'Season deleted successfully!');
        } catch (\Throwable $e) {
            Log::error('Cup season delete failed', ['id' => $id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Xóa mùa giải thất bại: ' . $e->getMessage());
        }
    }

    protected function distributeToGroups(Season $season, $teams): void
    {
        $pots = $this->potSeedingService->distributeToPots($teams);
        $groups = $this->potSeedingService->drawGroups($pots);

        foreach ($groups as $groupLetter => $groupTeams) {
            $groupTeam = new GroupTeam([
                'season_id' => $season->id,
                'group' => $groupLetter,
            ]);
            $teamIds = $groupTeams->pluck('id')->toArray();
            $groupTeam->setTeamIdsArray($teamIds);
            $groupTeam->save();
        }
    }

    protected function generateGroupStageMatches(Season $season): void
    {
        foreach ($season->groupTeams as $groupTeam) {
            $teamIds = $groupTeam->getTeamIdsArray();
            $rounds = $this->roundRobinService->generateSingleRoundRobin($teamIds);

            foreach ($rounds as $roundNumber => $matches) {
                foreach ($matches as $match) {
                    GroupStageMatch::create([
                        'season_id' => $season->id,
                        'group' => $groupTeam->group,
                        'round' => $roundNumber,
                        'team1_id' => $match[0],
                        'team2_id' => $match[1],
                    ]);
                }
            }
        }
    }

    protected function createStandings(Season $season): void
    {
        foreach ($season->groupTeams as $groupTeam) {
            foreach ($groupTeam->getTeamIdsArray() as $teamId) {
                Standing::create([
                    'team_id' => $teamId,
                    'season_id' => $season->id,
                    'group' => $groupTeam->group,
                ]);
            }
        }
    }

    public function advanceToKnockout($id)
    {
        try {
            $season = Season::findOrFail($id);
            $this->groupStageService->syncGroupPositions($season);
            $groupStandingsArray = $this->groupStageService->getGroupStandingsForKnockout($season);

            $this->knockoutService->populateRoundOf16($season, $groupStandingsArray);

            return redirect()->route('cup.eliminate.index', $id)
                ->with('success', 'Knockout stage initialized!');
        } catch (\Throwable $e) {
            Log::error('Cup advance knockout failed', ['id' => $id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Khởi tạo knockout thất bại: ' . $e->getMessage());
        }
    }
}
