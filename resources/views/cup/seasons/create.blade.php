@extends('cup.layouts.app')

@section('content')
    <h1>Create New Season</h1>
    <form action="{{ route('cup.seasons.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="season">Season Year</label>
            <input type="number" class="form-control" id="season" name="season" value="{{ $nextSeason }}" required>
        </div>
        <div class="form-group">
            <label for="teams_count">Number of Teams</label>
            <select class="form-control" id="teams_count" name="teams_count">
                @foreach($listTeamsCount as $teamsCount)
                    <option value="{{ $teamsCount }}">{{ $teamsCount }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="meta">Meta</label>
            <select class="form-control" id="meta" name="meta">
                <option value="" selected>Random</option>
                @foreach(\App\Enums\SeasonMeta::options() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-success">Create</button>
    </form>
@endsection