@extends('layouts.app')

@section('title', 'League Match')

@section('content')
@include('partials.season-nav', ['mode' => 'league', 'season' => $match->season])

<h2 class="mb-3">
    {{ ucfirst(str_replace('division', 'Division ', $match->division)) }} — Round {{ $match->round }}
</h2>

<x-match-detail :match="$match" :back-url="route('league.matches.index', $match->season_id)" />
@endsection
