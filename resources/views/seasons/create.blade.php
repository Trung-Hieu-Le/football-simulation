@extends('layouts.app')

@section('content')
    <h1>Create New Season</h1>
    <form action="{{ route('seasons.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="season">Season Year</label>
            <input type="number" class="form-control" id="season" name="season" required>
        </div>
        <div class="form-group">
            <label for="teams_count">Number of Teams (must be divisible by 6)</label>
            <input type="number" class="form-control" id="teams_count" name="teams_count" required min="12" max="60" step="12">
        </div>
        <button type="submit" class="btn btn-success">Create</button>
    </form>
@endsection
