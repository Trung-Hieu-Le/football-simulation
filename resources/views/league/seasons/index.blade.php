@extends('layouts.app')

@section('title', 'League Seasons')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>League Seasons</h1>
    <a href="{{ route('league.seasons.create') }}" class="btn btn-primary">Create New Season</a>
</div>

<div class="row">
    @forelse($seasons as $season)
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Season {{ $season->season }}</h5>
            </div>
            <div class="card-body">
                <p><strong>Teams:</strong> {{ $season->teams_count }}</p>
                <p><strong>Divisions:</strong> 3 ({{ $season->teams_count / 3 }} teams each)</p>
                <p><strong>Meta:</strong> <span class="badge bg-secondary">{{ $season->meta }}</span></p>
                <p><strong>Created:</strong> {{ $season->created_at?->format('Y-m-d') ?? 'N/A' }}</p>
                
                <div class="d-flex gap-2">
                    <a href="{{ route('league.seasons.show', $season->id) }}" class="btn btn-success btn-sm">View</a>
                    <a href="{{ route('league.matches.index', $season->id) }}" class="btn btn-info btn-sm">Matches</a>
                    <form action="{{ route('league.seasons.destroy', $season->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete season?')">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info">
            No league seasons yet. <a href="{{ route('league.seasons.create') }}">Create one</a>
        </div>
    </div>
    @endforelse
</div>
@endsection
