@extends('layouts.app')

@section('content')
    <h1>Create New Season</h1>
    <form action="{{ route('seasons.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="season">Season Year</label>
            <input type="number" class="form-control" id="season" name="season" value="{{ $nextSeason }}" required>
        </div>
        <div class="form-group">
            <label for="teams_count">Number of Teams (must be divisible by 12)</label>
            <input type="number" class="form-control" id="teams_count" name="teams_count" value="{{ $nextTeamsCount }}" required min="12" max="60" step="12">
        </div>
        <div class="form-group">
            <label for="meta">Meta</label>
            <select class="form-control" id="meta" name="meta">
                <option value="" selected>Random</option>
                <option value="attack">Attack</option>
                <option value="defense">Defense</option>
                <option value="control">Control</option>
                <option value="aggressive">Aggressive</option>
                <option value="stamina">Stamina</option>
                <option value="penalty">Penalty</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Create</button>
    </form>
@endsection
