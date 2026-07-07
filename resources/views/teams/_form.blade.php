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
        <select name="region" class="form-select" required>
            @foreach($regions as $region)
                <option value="{{ $region->id }}" @selected(old('region', $team->region ?? '') == $region->id)>{{ $region->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4"><label class="form-label">Color 1</label><input type="text" name="color_1" class="form-control" value="{{ old('color_1', $team->color_1 ?? '#000000') }}"></div>
    <div class="col-md-4"><label class="form-label">Color 2</label><input type="text" name="color_2" class="form-control" value="{{ old('color_2', $team->color_2 ?? '#000000') }}"></div>
    <div class="col-md-4"><label class="form-label">Color 3</label><input type="text" name="color_3" class="form-control" value="{{ old('color_3', $team->color_3 ?? '') }}"></div>
    <div class="col-md-6"><label class="form-label">Shirt Type</label><input type="text" name="shirt_type" class="form-control" value="{{ old('shirt_type', $team->shirt_type ?? '') }}"></div>
    @foreach($stats as $stat)
    <div class="col-md-4 col-lg-3">
        <label class="form-label text-capitalize">{{ $stat }}</label>
        <input type="number" name="{{ $stat }}" class="form-control" min="1" max="100" value="{{ old($stat, $team->$stat ?? 50) }}">
    </div>
    @endforeach
</div>
