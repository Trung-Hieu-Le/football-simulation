<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Region;
use App\Services\EloRatingService;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    protected EloRatingService $eloService;

    public function __construct(EloRatingService $eloService)
    {
        $this->eloService = $eloService;
    }

    public function index()
    {
        $teams = Team::with('region')->orderBy('name')->get();
        $regions = Region::all();
        
        return view('teams.index', compact('teams', 'regions'));
    }

    public function create()
    {
        $regions = Region::all();
        return view('teams.create', compact('regions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'region' => 'required|integer|exists:regions,id',
            'color_1' => 'nullable|string|max:10',
            'color_2' => 'nullable|string|max:10',
            'color_3' => 'nullable|string|max:10',
            'attack' => 'integer|min:1|max:100',
            'defense' => 'integer|min:1|max:100',
            'control' => 'integer|min:1|max:100',
            'creative' => 'integer|min:1|max:100',
            'pace' => 'integer|min:1|max:100',
            'mental' => 'integer|min:1|max:100',
            'discipline' => 'integer|min:1|max:100',
            'luck' => 'integer|min:1|max:100',
            'stamina' => 'integer|min:1|max:100',
            'goalkeeping' => 'integer|min:1|max:100',
        ]);

        Team::create($validated);

        return redirect()->route('teams.index')
                        ->with('success', 'Team created successfully!');
    }

    public function edit($id)
    {
        $team = Team::findOrFail($id);
        $regions = Region::all();
        
        return view('teams.edit', compact('team', 'regions'));
    }

    public function update(Request $request, $id)
    {
        $team = Team::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'region' => 'required|integer|exists:regions,id',
            'color_1' => 'nullable|string|max:10',
            'color_2' => 'nullable|string|max:10',
            'color_3' => 'nullable|string|max:10',
            'attack' => 'integer|min:1|max:100',
            'defense' => 'integer|min:1|max:100',
            'control' => 'integer|min:1|max:100',
            'creative' => 'integer|min:1|max:100',
            'pace' => 'integer|min:1|max:100',
            'mental' => 'integer|min:1|max:100',
            'discipline' => 'integer|min:1|max:100',
            'luck' => 'integer|min:1|max:100',
            'stamina' => 'integer|min:1|max:100',
            'goalkeeping' => 'integer|min:1|max:100',
        ]);

        $team->update($validated);

        return redirect()->route('teams.index')
                        ->with('success', 'Team updated successfully!');
    }

    public function destroy($id)
    {
        $team = Team::findOrFail($id);
        $team->delete();

        return redirect()->route('teams.index')
                        ->with('success', 'Team deleted successfully!');
    }

    public function resetAllElo()
    {
        $this->eloService->resetAllTeamsElo();

        return redirect()->route('teams.index')
                        ->with('success', 'All team ELO ratings reset to 1000!');
    }

    public function resetAllForm()
    {
        Team::query()->update(['form' => 50]);

        return redirect()->route('teams.index')
                        ->with('success', 'All team forms reset to 50!');
    }
}
