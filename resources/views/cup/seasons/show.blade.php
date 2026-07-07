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

@foreach($groups as $group => $standings)
    <x-standings-table
        :title="'Group ' . $group"
        :standings="$standings"
        :highlight-top="2"
    />
@endforeach
@endsection
