<?php

namespace App\Http\Controllers\Cup;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class TeamCupController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');
        
        $teams = DB::table('teams')
            ->selectRaw('teams.*, (attack + defense + control + stamina + pass + speed + mental + discipline) as total, regions.shortname as region_name')
            ->join('regions', 'regions.id', '=', 'teams.region')
            ->orderBy($sort, $direction)
            ->get();
        
        $teamHistories = DB::table('group_stage_standings')
            ->join('teams', 'teams.id', '=', 'group_stage_standings.team_id')
            ->join('seasons', 'seasons.id', '=', 'group_stage_standings.season_id')
            ->select('group_stage_standings.*', 'teams.name as team_name', 'seasons.season')
            ->get();
        $regions = DB::table('regions')->get();

        return view('cup.teams.index', compact('teams', 'teamHistories', 'regions'));
    }

    public function edit($id)
    {
        $team = DB::table('teams')->where('id', $id)->first();
        
        return view('teams.edit', compact('team'));
    }

    public function update(Request $request, $id)
    {
        $team = DB::table('teams')->where('id', $id)->first();
        DB::table('teams')->where('id', $id)->update([
            'name' => $request->name,
            'attack' => $request->attack,
            'defense' => $request->defense,
            'control' => $request->control,
            'stamina' => $request->stamina,
            'pass' => $request->pass,
            'speed' => $request->speed,
            'mental' => $request->mental,
            'discipline' => $request->discipline,
            'form' => $request->form,
            'region' => $request->region,
            'color_1' => $request->color_1,
            'color_2' => $request->color_2,
            'color_3' => $request->color_3,
        ]);

        return redirect()->back()->with('success', 'Thông tin đội bóng đã được cập nhật!');
    }

    public function resetForm()
    {
        DB::table('teams')->update([
            'form' => 50
        ]);

        return redirect()->back()->with('success', 'All teams updated successfully!');
    }
}