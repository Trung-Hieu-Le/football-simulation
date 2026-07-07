@extends('layouts.app')

@section('title', 'League Season ' . $season->season)

@section('content')
@include('partials.season-nav', ['mode' => 'league', 'season' => $season])

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Season {{ $season->season }} <span class="badge bg-secondary">{{ $season->meta }}</span></h1>
    <div class="d-flex gap-2">
        <form action="{{ route('league.matches.simulate-all', $season->id) }}" method="POST">@csrf
            <button class="btn btn-success btn-sm">Simulate All</button>
        </form>
        <form action="{{ route('league.seasons.calculate-results', $season->id) }}" method="POST">@csrf
            <button class="btn btn-warning btn-sm">Calculate Results</button>
        </form>
    </div>
</div>

@php
    $promotionCount = $season->teams_count ? (int) ceil(($season->teams_count / 3) * 0.25) : 0;
    $relegationCount = $promotionCount;
@endphp

@foreach($divisions as $division => $standings)
    <x-standings-table
        :title="ucfirst(str_replace('division', 'Division ', $division))"
        :standings="$standings"
        :show-result="true"
        :promotion-count="$promotionCount"
        :relegation-count="$relegationCount"
        :division="$division"
    />
@endforeach
@endsection
