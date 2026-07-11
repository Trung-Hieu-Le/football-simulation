@php
    $stats = ['attack','defense','control','creative','pace','mental','discipline','luck','stamina','goalkeeping'];
@endphp
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $team->name ?? '') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Region</label>
        <select name="region_id" class="form-select" required>
            @foreach($regions as $region)
                <option value="{{ $region->id }}" @selected(old('region_id', $team->region_id ?? '') == $region->id)>{{ $region->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 col-lg-3"><label class="form-label">Color 1</label><input type="color" name="color_1" class="form-control" value="{{ old('color_1', $team->color_1 ?? '#000000') }}"></div>
    <div class="col-md-4 col-lg-3"><label class="form-label">Color 2</label><input type="color" name="color_2" class="form-control" value="{{ old('color_2', $team->color_2 ?? '#000000') }}"></div>
    <div class="col-md-4 col-lg-3"><label class="form-label">Color 3</label><input type="color" name="color_3" class="form-control" value="{{ old('color_3', $team->color_3 ?? '') }}"></div>
    <div class="col-md-4 col-lg-3"><label class="form-label">Shirt Type</label><input type="text" name="shirt_type" class="form-control" value="{{ old('shirt_type', $team->shirt_type ?? '') }}"></div>
    <div class="col-12 team-stats-wrapper">
        <div class="row g-3">
            @foreach($stats as $stat)
            <div class="col-md-4 col-lg-3">
                <label class="form-label text-capitalize">{{ $stat }}</label>
                <input type="number" name="{{ $stat }}" class="form-control team-stat-input" min="1" max="100" value="{{ old($stat, $team->$stat ?? 50) }}">
            </div>
            @endforeach
            <div class="col-md-4 col-lg-3">
                <label class="form-label">ELO</label>
                <input type="number" name="elo" class="form-control" value="{{ old('elo', $team->elo ?? 1000) }}" disabled>
            </div>
            <div class="col-md-4 col-lg-3">
                <label class="form-label">Total Stats</label>
                @php
                    $totalStats = 0;
                    foreach ($stats as $stat) {
                        $totalStats += (int) old($stat, $team->{$stat} ?? 50);
                    }
                @endphp
                <input type="number" class="form-control team-total-stats" value="{{ $totalStats }}" disabled>
            </div>
        </div>
    </div>
</div>
