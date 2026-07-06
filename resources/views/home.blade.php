@extends('layouts.app')

@section('title', 'Home - Football Simulation')

@section('content')
<div class="row">
    <div class="col-12">
        <h1 class="mb-4">Football Simulation</h1>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">🏆 League Mode</h3>
            </div>
            <div class="card-body">
                @if($latestLeague)
                    <p><strong>Current Season:</strong> {{ $latestLeague->season }}</p>
                    <p><strong>Teams:</strong> {{ $latestLeague->teams_count }}</p>
                    <p><strong>Meta:</strong> {{ $latestLeague->meta }}</p>
                    <a href="{{ route('league.seasons.show', $latestLeague->id) }}" class="btn btn-primary">View Season</a>
                @else
                    <p class="text-muted">No league season yet</p>
                @endif
                <a href="{{ route('league.seasons.create') }}" class="btn btn-success mt-2">Create New Season</a>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h3 class="mb-0">🏅 Cup Mode</h3>
            </div>
            <div class="card-body">
                @if($latestCup)
                    <p><strong>Current Season:</strong> {{ $latestCup->season }}</p>
                    <p><strong>Teams:</strong> {{ $latestCup->teams_count }}</p>
                    <p><strong>Meta:</strong> {{ $latestCup->meta }}</p>
                    <a href="{{ route('cup.seasons.show', $latestCup->id) }}" class="btn btn-success">View Season</a>
                @else
                    <p class="text-muted">No cup season yet</p>
                @endif
                <a href="{{ route('cup.seasons.create') }}" class="btn btn-success mt-2">Create New Season</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="mb-0">⭐ Top ELO Rankings</h3>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Team</th>
                            <th>ELO</th>
                            <th>Form</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topEloTeams as $index => $team)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <span style="background: linear-gradient(135deg, {{ $team->color_1 }}, {{ $team->color_2 }}); padding: 2px 8px; border-radius: 4px; color: white;">
                                    {{ $team->name }}
                                </span>
                            </td>
                            <td><strong>{{ $team->elo }}</strong></td>
                            <td>{{ $team->form }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center">
                <h4>Quick Actions</h4>
                <a href="{{ route('teams.index') }}" class="btn btn-outline-primary m-1">Manage Teams</a>
                <a href="{{ route('league.seasons.index') }}" class="btn btn-outline-primary m-1">League Seasons</a>
                <a href="{{ route('cup.seasons.index') }}" class="btn btn-outline-success m-1">Cup Seasons</a>
            </div>
        </div>
    </div>
</div>
@endsection
