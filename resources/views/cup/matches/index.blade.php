@extends('layouts.app')

@section('title', 'Cup Matches')

@section('content')
@include('partials.season-nav', ['mode' => 'cup', 'season' => $season])

<h2 class="mb-3">Group Stage Matches</h2>

@foreach($matches as $group => $rounds)
<h4 class="mt-4">Group {{ $group }}</h4>
@foreach($rounds as $round => $roundMatches)
    <x-match-round-card
        :title="'Round ' . $round"
        :matches="$roundMatches"
        :simulate-url="route('cup.matches.simulate-round', [$season->id, $round])"
        match-show-route="cup.matches.show"
    />
@endforeach
@endforeach
@endsection
