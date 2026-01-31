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
            ->selectRaw('teams.*, (attack + creative + control + pace + defense + mental + discipline + stamina) as total, regions.shortname as region_name')
            ->join('regions', 'regions.id', '=', 'teams.region')
            ->orderBy($sort, $direction)
            ->get();
        
        $teamHistories = DB::table('cup_standings')
            ->join('teams', 'teams.id', '=', 'cup_standings.team_id')
            ->join('cup_seasons', 'cup_seasons.id', '=', 'cup_standings.season_id')
            ->leftJoin('cup_positions', 'cup_positions.cup_standing_id', '=', 'cup_standings.id')
            ->select('cup_standings.*', 'teams.name as team_name', 'cup_seasons.season',
                     'cup_positions.position', 'cup_positions.result as title')
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
        try {
            DB::beginTransaction();
            
            $team = DB::table('teams')->where('id', $id)->first();
            if (!$team) {
                DB::rollBack();
                return redirect()->back()->with('fail', 'Team not found!');
            }
            
            DB::table('teams')->where('id', $id)->update([
                'name' => $request->name,
                'attack' => $request->attack,
                'creative' => $request->creative,
                'control' => $request->control,
                'pace' => $request->pace,
                'defense' => $request->defense,
                'stamina' => $request->stamina,
                'mental' => $request->mental,
                'discipline' => $request->discipline,
                'form' => $request->form,
                'region' => $request->region,
                'color_1' => $request->color_1,
                'color_2' => $request->color_2,
                'color_3' => $request->color_3,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Thông tin đội bóng đã được cập nhật!');
        } catch (\Throwable $th) {
            DB::rollBack();
            \App\Services\ErrorLogService::logException($th);
            return redirect()->back()->with('fail', 'Failed to update team.');
        }
    }

    public function resetForm()
    {
        DB::table('teams')->update([
            'form' => 50
        ]);

        return redirect()->back()->with('success', 'All teams updated successfully!');
    }
}