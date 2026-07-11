@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="row mb-4">
    <div class="col-12"><h1>Football Simulation</h1></div>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header bg-primary text-white"><h3 class="mb-0">🏆 League</h3></div>
            <div class="card-body">
                @if($latestLeague)
                    <p><strong>Season:</strong> {{ $latestLeague->season }} | <strong>Teams:</strong> {{ $latestLeague->teams_count }} | <strong>Meta:</strong> {{ $latestLeague->meta }}</p>
                    <x-champion-line :champion="$leagueChampion" />
                    <a href="{{ route('league.seasons.show', $latestLeague->id) }}" class="btn btn-primary mt-2">View Season</a>
                @else
                    <p class="text-muted">No league season yet.</p>
                @endif
                <a href="{{ route('league.seasons.create') }}" class="btn btn-outline-primary mt-2">Create Season</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header bg-success text-white"><h3 class="mb-0">🏅 Cup</h3></div>
            <div class="card-body">
                @if($latestCup)
                    <p><strong>Season:</strong> {{ $latestCup->season }} | <strong>Teams:</strong> {{ $latestCup->teams_count }} | <strong>Meta:</strong> {{ $latestCup->meta }}</p>
                    <x-champion-line :champion="$cupChampion" />
                    <a href="{{ route('cup.seasons.show', $latestCup->id) }}" class="btn btn-success mt-2">View Season</a>
                @else
                    <p class="text-muted">No cup season yet.</p>
                @endif
                <a href="{{ route('cup.seasons.create') }}" class="btn btn-outline-success mt-2">Create Season</a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="mb-0">⭐ Top ELO (global)</h3></div>
    <div class="card-body table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>#</th><th>Team</th><th>ELO</th></tr></thead>
            <tbody>
                @foreach($topEloTeams as $i => $team)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>@include('partials.team-badge', ['team' => $team])</td>
                    <td><strong>{{ $team->elo }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
