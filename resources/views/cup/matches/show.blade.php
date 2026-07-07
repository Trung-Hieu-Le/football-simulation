@extends('layouts.app')

@section('title', 'Cup Match')

@section('content')
@include('partials.season-nav', ['mode' => 'cup', 'season' => $match->season])

<h2 class="mb-3">Group {{ strtoupper($match->group) }} — Round {{ $match->round }}</h2>

<x-match-detail :match="$match" :back-url="route('cup.matches.index', $match->season_id)" />
@endsection
