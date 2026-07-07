@extends('layouts.app')

@section('title', 'League Matches')

@section('content')
@include('partials.season-nav', ['mode' => 'league', 'season' => $season])

<h2 class="mb-3">Matches</h2>

@foreach($matches as $division => $rounds)
<h4 class="mt-4 text-capitalize">{{ str_replace('division', 'Division ', $division) }}</h4>
@foreach($rounds as $round => $roundMatches)
    <x-match-round-card
        :title="'Round ' . $round"
        :matches="$roundMatches"
        :simulate-url="route('league.matches.simulate-round', [$season->id, $round])"
        match-show-route="league.matches.show"
    />
@endforeach
@endforeach
@endsection
