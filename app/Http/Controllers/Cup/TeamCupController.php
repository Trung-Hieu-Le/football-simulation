<?php

namespace App\Http\Controllers\Cup;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class TeamCupController extends Controller
{

    public function index(Request $request)
    {
        $sort = $request->input('sort', 'id'); // Cột sắp xếp, mặc định là 'name'
        $direction = $request->input('direction', 'asc');  // Hướng sắp xếp, mặc định là 'asc'
        // Lấy tất cả các đội bóng
        $teams = DB::table('teams')
        ->selectRaw('*, (attack + defense + control + stamina + aggressive + penalty) as total') // Tính tổng cộng
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
        // Lấy thông tin đội bóng cần chỉnh sửa
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
        'aggressive' => $request->aggressive,
        'penalty' => $request->penalty,
        'form' => $request->form,
        'region' => $request->region,
        'color_1' => $request->color_1,
        'color_2' => $request->color_2,
        'color_3' => $request->color_3,
        // 'shirt_type' => $request->shirt_type,
    ]);

    return redirect()->back()->with('success', 'Thông tin đội bóng đã được cập nhật!');
}

public function resetForm()
    {
        // Cập nhật tất cả các team, đặt các trường về 50
        DB::table('teams')->update([
            'form' => 50
        ]);

        // Thông báo thành công
        return redirect()->back()->with('success', 'All teams updated successfully!');
    }
    // public function history($id)
    // {
    //     // Lấy lịch sử của đội bóng
    //     $team = DB::table('teams')->where('id', $id)->first();
    //     $histories = DB::table('histories')
    //         ->where('team_id', $id)
    //         ->join('seasons', 'seasons.id', '=', 'histories.season_id')
    //         ->select('histories.*', 'seasons.season')
    //         ->get();

    //     return view('teams.history', compact('team', 'histories'));
    // }
}
