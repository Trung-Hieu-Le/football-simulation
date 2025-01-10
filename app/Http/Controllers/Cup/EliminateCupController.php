<?php

namespace App\Http\Controllers\Cup;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class EliminateCupController extends Controller
{
    public function view($seasonId)
    {
        $season = DB::table('seasons')->where('id', $seasonId)->first();

        $matches = DB::table('eliminate_stage_matches')
            ->leftJoin('teams as team1', 'eliminate_stage_matches.team1_id', '=', 'team1.id')
            ->leftJoin('teams as team2', 'eliminate_stage_matches.team2_id', '=', 'team2.id')
            ->select(
                'eliminate_stage_matches.*',
                'team1.name as team1_name',
                'team1.color_1 as team1_c1',
                'team1.color_2 as team1_c2',
                'team1.color_3 as team1_c3',
                'team2.name as team2_name',
                'team2.color_1 as team2_c1',
                'team2.color_2 as team2_c2',
                'team2.color_3 as team2_c3'
            )
            ->where('eliminate_stage_matches.season_id', $seasonId)
            ->orderBy('id', 'asc')
            ->get();
        $currentRound = DB::table('eliminate_stage_matches')
            ->where('season_id', $seasonId)
            ->where(function ($query) {
                $query->whereNull('team1_score')
                    ->orWhereNull('team2_score');
            })
            ->orderBy('id', 'asc')
            ->value('round');

        $lastRound = DB::table('eliminate_stage_matches')
            ->where('season_id', $seasonId)
            ->where(function ($query) {
                $query->whereNotNull('team1_score')
                    ->whereNotNull('team2_score');
            })
            ->whereNot('round', $currentRound)
            ->orderBy('id', 'desc')
            ->value('round');

        // dd($currentRound, $lastRound);
        $nextMatches = $matches->filter(fn($match) => $match->round === $currentRound);
        $completedMatches = $matches->filter(fn($match) => $match->round === $lastRound && !is_null($match->team1_score) && !is_null($match->team2_score));

        $champion = DB::table('teams')
            ->join('eliminate_stage_matches', 'teams.id', '=', 'eliminate_stage_matches.winner_id')
            ->select('teams.name as team_name', 'eliminate_stage_matches.id')
            ->where('eliminate_stage_matches.season_id', $seasonId)
            ->where('eliminate_stage_matches.round', 'final')
            ->where(function ($query) {
                $query->whereNotNull('team1_score')
                    ->whereNotNull('team2_score');
            })
            ->first();
        // dd($currentRound, $lastRound, $champion, $filteredMatches);
        // Split matches into rounds
        $roundOf32MatchesLeft = $matches->slice(0, 8);
        $roundOf32MatchesRight = $matches->slice(8, 8);

        $roundOf16MatchesLeft = $matches->slice(16, 4);
        $roundOf16MatchesRight = $matches->slice(20, 4);

        $quarterFinalMatchesLeft = $matches->slice(24, 2);
        $quarterFinalMatchesRight = $matches->slice(26, 2);

        $semiFinalMatchesLeft = $matches->slice(28, 1);
        $semiFinalMatchesRight = $matches->slice(29, 1);

        $thirdPlaceMatch = $matches->get(30); // Match at index 30
        $finalMatch = $matches->get(31);      // Match at index 31

        return view('cup.eliminate.view', compact(
            'roundOf32MatchesLeft',
            'roundOf32MatchesRight',
            'roundOf16MatchesLeft',
            'roundOf16MatchesRight',
            'quarterFinalMatchesLeft',
            'quarterFinalMatchesRight',
            'semiFinalMatchesLeft',
            'semiFinalMatchesRight',
            'finalMatch',
            'thirdPlaceMatch',
            'currentRound',
            'lastRound',
            'champion',
            'nextMatches',
            'completedMatches',
            'season'
        ));
    }
}
