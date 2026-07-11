@extends('layouts.app')

@section('title', 'Cup Matches')

@section('content')
@include('partials.season-nav', ['mode' => 'cup', 'season' => $season])

<h2 class="mb-3">Group Stage Matches</h2>

<div class="row">
    @foreach($matches as $group => $rounds)
        <div class="col-lg-3">
            <h4 class="mt-4">Group {{ $group }}</h4>
            @foreach($rounds as $round => $roundMatches)
                <x-match-round-card
                    :title="'Round ' . $round"
                    :matches="$roundMatches"
                    :simulate-url="route('cup.matches.simulate-round', [$season->id, $round])"
                    match-show-route="cup.matches.show"
                />
            @endforeach
        </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/match-popover.js') }}"></script>
@endpush
