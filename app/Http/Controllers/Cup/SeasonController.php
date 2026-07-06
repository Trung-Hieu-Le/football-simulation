<?php

namespace App\Http\Controllers\Cup;

use App\Http\Controllers\Controller;
use App\Models\Cup\Season;
use App\Models\Cup\GroupTeam;
use App\Models\Cup\GroupStageMatch;
use App\Models\Cup\Standing;
use App\Models\Team;
use App\Enums\SeasonMeta;
use App\Services\CupPotSeedingService;
use App\Services\CupKnockoutService;
use Illuminate\Http\Request;

class SeasonController extends Controller
{
    protected CupPotSeedingService $potSeedingService;
    protected CupKnockoutService $knockoutService;

    public function __construct(CupPotSeedingService $potSeedingService, CupKnockoutService $knockoutService)
    {
        $this->potSeedingService = $potSeedingService;
        $this->knockoutService = $knockoutService;
    }

    public function index()
    {
        $seasons = Season::orderBy('season', 'desc')->get();
        return view('cup.seasons.index', compact('seasons'));
    }

    public function create()
    {
        $teams = Team::orderByDesc('elo')->get();
        $metas = SeasonMeta::values();
        
        return view('cup.seasons.create', compact('teams', 'metas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'teams_count' => 'required|integer|in:32,64',
            'meta' => 'required|string',
            'selected_teams' => 'required|array',
        ]);

        $lastSeason = Season::orderBy('season', 'desc')->first();
        $seasonNumber = $lastSeason ? $lastSeason->season + 1 : 1;

        $season = Season::create([
            'season' => $seasonNumber,
            'teams_count' => $validated['teams_count'],
            'meta' => $validated['meta'],
        ]);

        $selectedTeams = Team::whereIn('id', $validated['selected_teams'])->get();
        $this->distributeToGroups($season, $selectedTeams);
        $this->generateGroupStageMatches($season);
        $this->createStandings($season);
        $this->knockoutService->createKnockoutBracket($season, $validated['teams_count']);

        return redirect()->route('cup.seasons.show', $season->id)
                        ->with('success', 'Cup season created successfully!');
    }

    public function show($id)
    {
        $season = Season::with(['groupTeams', 'standings.team'])->findOrFail($id);
        $groups = $this->getGroupStandings($season);
        
        return view('cup.seasons.show', compact('season', 'groups'));
    }

    protected function distributeToGroups(Season $season, $teams)
    {
        $groupCount = $season->teams_count === 32 ? 8 : 16;
        
        $pots = $this->potSeedingService->distributeToPots($teams);
        $groups = $this->potSeedingService->drawGroups($pots, $groupCount);

        foreach ($groups as $groupLetter => $groupTeams) {
            $groupTeam = new GroupTeam([
                'season_id' => $season->id,
                'group' => $groupLetter,
            ]);
            $teamIds = collect($groupTeams)->pluck('id')->toArray();
            $groupTeam->setTeamIdsArray($teamIds);
            $groupTeam->save();
        }
    }

    protected function generateGroupStageMatches(Season $season)
    {
        $groupTeams = $season->groupTeams;

        foreach ($groupTeams as $groupTeam) {
            $teamIds = $groupTeam->getTeamIdsArray();
            $teamsCount = count($teamIds);
            $rounds = ($teamsCount - 1) * 2;

            for ($round = 1; $round <= $rounds; $round++) {
                $matches = $this->generateRoundMatches($teamIds, $round);
                
                foreach ($matches as $match) {
                    GroupStageMatch::create([
                        'season_id' => $season->id,
                        'group' => $groupTeam->group,
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
                    'group' => $groupTeam->group,
                ]);
            }
        }
    }

    protected function getGroupStandings(Season $season): array
    {
        $standings = $season->standings()
                           ->with(['team', 'position'])
                           ->get()
                           ->groupBy('group');

        $groups = [];
        foreach ($standings as $group => $groupStandings) {
            $sorted = $groupStandings->sortByDesc(function ($standing) {
                return [
                    $standing->points,
                    $standing->goal_difference,
                    $standing->goal_scored,
                ];
            })->values();

            $groups[$group] = $sorted;
        }

        return $groups;
    }

    public function advanceToKnockout($id)
    {
        $season = Season::findOrFail($id);
        $groups = $this->getGroupStandings($season);
        
        $groupStandingsArray = [];
        foreach ($groups as $groupName => $standings) {
            $groupStandingsArray[$groupName] = $standings->map(function ($standing) {
                return [
                    'team_id' => $standing->team_id,
                    'points' => $standing->points,
                ];
            })->toArray();
        }

        $matches = $this->knockoutService->generateRoundOf16($season, $groupStandingsArray);
        
        foreach ($matches as $match) {
            $match->save();
        }

        return redirect()->route('cup.eliminate.index', $id)
                        ->with('success', 'Knockout stage initialized!');
    }
}
