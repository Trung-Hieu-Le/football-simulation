@extends('layouts.app')

@section('title', 'Cup Knockout')

@section('content')
@include('partials.season-nav', ['mode' => 'cup', 'season' => $season])

<h2 class="mb-3">Knockout Stage</h2>

@forelse($matches as $round => $roundMatches)
    <x-match-round-card
        :title="str_replace('_', ' ', ucfirst($round))"
        :matches="$roundMatches"
        :simulate-url="route('cup.eliminate.simulate-round', [$season->id, $round])"
        match-show-route="cup.eliminate.show"
        :knockout="true"
        simulate-label="Simulate Round"
    />
@empty
<div class="alert alert-info">Knockout bracket not initialized yet.</div>
@endforelse
@endsection
