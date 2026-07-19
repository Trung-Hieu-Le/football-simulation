@extends('layouts.app')

@section('title', 'Cup Seasons')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Cup Seasons</h1>
    <a href="{{ route('cup.seasons.create') }}" class="btn btn-success">Create New Season</a>
</div>

<div class="row">
    @forelse($seasons as $season)
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Season {{ $season->season }}</h5>
            </div>
            <div class="card-body">
                <p><strong>Teams:</strong> {{ $season->teams_count }}</p>
                <p><strong>Groups:</strong> 8 (A–H) · {{ $season->teams_count / 8 }} teams/group</p>
                <p><strong>Meta:</strong> <span class="badge bg-secondary">{{ $season->meta }}</span></p>
                <p><strong>Created:</strong> {{ $season->created_at?->format('Y-m-d') ?? 'N/A' }}</p>
                <x-champion-line :champion="$champions->get($season->id)" />
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('cup.seasons.show', $season->id) }}" class="btn btn-success btn-sm">Groups</a>
                    <a href="{{ route('cup.matches.index', $season->id) }}" class="btn btn-info btn-sm">Matches</a>
                    <a href="{{ route('cup.eliminate.index', $season->id) }}" class="btn btn-warning btn-sm">Knockout</a>
                    <form action="{{ route('cup.seasons.destroy', $season->id) }}" method="POST" class="d-inline">
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
            No cup seasons yet. <a href="{{ route('cup.seasons.create') }}">Create one</a>
        </div>
    </div>
    @endforelse
</div>
@endsection
