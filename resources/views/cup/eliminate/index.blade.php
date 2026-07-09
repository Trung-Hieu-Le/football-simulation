@extends('layouts.app')

@section('title', 'Cup Knockout')

@section('content')
@include('partials.season-nav', ['mode' => 'cup', 'season' => $season])

<h2 class="mb-3">Knockout Bracket</h2>

@if($matches->isEmpty())
    <div class="alert alert-info">
        Knockout bracket not initialized yet.
        <a href="{{ route('cup.seasons.show', $season->id) }}">Complete group stage</a> then click <strong>Advance to Knockout</strong>.
    </div>
@else
    <x-bracket-tree :matches="$matches" :season="$season" :round-order="$roundOrder" />
@endif
@endsection
