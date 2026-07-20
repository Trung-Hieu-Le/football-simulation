<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Region;
use App\Enums\ShirtType;
use App\Services\EloRatingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    protected EloRatingService $eloService;

    public function __construct(EloRatingService $eloService)
    {
        $this->eloService = $eloService;
    }

    public function index()
    {
        $teams = Team::with('region')->orderBy('id')->get();
        $regions = Region::all();

        // Order must match public/js/radar-chart.js statFields
        $statFields = [
            'attack', 'creative', 'pace', 'control', 'luck',
            'defense', 'goalkeeping', 'physical', 'stamina', 'mental',
        ];
        $avgStats = array_map(
            fn (string $field) => round((float) $teams->avg($field), 1),
            $statFields
        );

        return view('teams.index', compact('teams', 'regions', 'avgStats'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateTeam($request);
        Team::create($validated);

        return redirect()->route('teams.index')
            ->with('success', 'Team created successfully!');
    }

    public function update(Request $request, $id)
    {
        $team = Team::findOrFail($id);
        $team->update($this->validateTeam($request));

        return redirect()->route('teams.index')
            ->with('success', 'Team updated successfully!');
    }

    public function destroy($id)
    {
        Team::findOrFail($id)->delete();

        return redirect()->route('teams.index')
            ->with('success', 'Team deleted successfully!');
    }

    public function resetAllElo()
    {
        $this->eloService->resetAllTeamsElo();

        return redirect()->route('teams.index')
            ->with('success', 'All team ELO ratings reset to 1000!');
    }

    protected function validateTeam(Request $request): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'region_id' => 'required|integer|exists:regions,id',
            'color_1' => 'nullable|string|max:10',
            'color_2' => 'nullable|string|max:10',
            'color_3' => 'nullable|string|max:10',
            'shirt_type' => ['nullable', Rule::enum(ShirtType::class)],
            'attack' => 'integer|min:1|max:100',
            'defense' => 'integer|min:1|max:100',
            'control' => 'integer|min:1|max:100',
            'creative' => 'integer|min:1|max:100',
            'pace' => 'integer|min:1|max:100',
            'mental' => 'integer|min:1|max:100',
            'physical' => 'integer|min:1|max:100',
            'luck' => 'integer|min:1|max:100',
            'stamina' => 'integer|min:1|max:100',
            'goalkeeping' => 'integer|min:1|max:100',
        ]);

        return $validated;
    }
}
