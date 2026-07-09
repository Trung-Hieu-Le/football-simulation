@extends('layouts.app')

@section('title', 'Cup Season ' . $season->season)

@section('content')
@include('partials.season-nav', ['mode' => 'cup', 'season' => $season])

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Season {{ $season->season }} <span class="badge bg-secondary">{{ $season->meta }}</span></h1>
    <div class="d-flex gap-2">
        <form action="{{ route('cup.matches.simulate-all', $season->id) }}" method="POST">@csrf
            <button class="btn btn-success btn-sm">Simulate Group Stage</button>
        </form>
        <form action="{{ route('cup.seasons.advance-knockout', $season->id) }}" method="POST">@csrf
            <button class="btn btn-warning btn-sm">Advance to Knockout</button>
        </form>
    </div>
</div>

@php
    $teamsPerGroup = $season->teams_count / 8;
    $qualifyCount = 4;
@endphp

<p class="text-muted small mb-3">
    8 groups (A–H) · {{ (int) $teamsPerGroup }} teams/group · Top {{ $qualifyCount }} advance to Round of 16
    @if($teamsPerGroup > 4)
        · Ranks 5–{{ (int) $teamsPerGroup }} eliminated
    @endif
</p>

@foreach($groups as $group => $standings)
    <x-standings-table
        :title="'Group ' . $group"
        :standings="$standings"
        :highlight-top="$teamsPerGroup <= 4 ? 4 : $qualifyCount"
        :promotion-count="0"
        :relegation-count="$teamsPerGroup > 4 ? $teamsPerGroup - $qualifyCount : 0"
        :division="null"
    />
@endforeach
@endsection
