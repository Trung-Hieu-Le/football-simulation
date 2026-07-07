@extends('layouts.app')

@section('title', 'Create League Season')

@section('content')
<h1 class="mb-4">Create League Season</h1>

<form action="{{ route('league.seasons.store') }}" method="POST">
    @csrf
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label">Season #</label>
            <input type="number" class="form-control" value="{{ $nextSeason }}" disabled>
            <small class="text-muted">Auto-assigned on create</small>
        </div>
        <div class="col-md-4">
            <label class="form-label">Teams Count</label>
            <select name="teams_count" id="teams_count" class="form-select" required>
                @foreach($listTeamsCount as $count)
                    <option value="{{ $count }}" @selected(old('teams_count') == $count)>{{ $count }} teams (3 divisions)</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Meta</label>
            <select name="meta" class="form-select">
                <option value="">Random</option>
                @foreach($metas as $value => $label)
                    <option value="{{ $value }}" @selected(old('meta') == $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Select Teams <span id="selected-count" class="badge bg-primary">0</span></span>
            <div>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="select-top-elo">Top by ELO</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-selection">Clear</button>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-2">
                @foreach($teams as $team)
                <div class="col-md-3 col-sm-6">
                    <label class="d-flex align-items-center gap-2 border rounded p-2 team-checkbox-label">
                        <input type="checkbox" name="selected_teams[]" value="{{ $team->id }}" class="team-checkbox" @checked(is_array(old('selected_teams')) && in_array($team->id, old('selected_teams')))>
                        @include('partials.team-badge', ['team' => $team])
                        <small class="text-muted">ELO {{ $team->elo }}</small>
                    </label>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Create Season</button>
    <a href="{{ route('league.seasons.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const countEl = document.getElementById('teams_count');
    const selectedCount = document.getElementById('selected-count');
    const boxes = () => [...document.querySelectorAll('.team-checkbox')];

    function updateCount() {
        selectedCount.textContent = boxes().filter(b => b.checked).length;
    }
    boxes().forEach(b => b.addEventListener('change', updateCount));
    updateCount();

    document.getElementById('clear-selection').addEventListener('click', () => {
        boxes().forEach(b => b.checked = false);
        updateCount();
    });

    document.getElementById('select-top-elo').addEventListener('click', () => {
        const n = parseInt(countEl.value, 10);
        boxes().forEach(b => b.checked = false);
        boxes().slice(0, n).forEach(b => b.checked = true);
        updateCount();
    });
});
</script>
@endpush
