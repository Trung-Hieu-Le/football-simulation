@extends('layouts.app')

@section('title', 'Knockout Match')

@section('content')
@include('partials.season-nav', ['mode' => 'cup', 'season' => $match->season])

<h2 class="mb-3">{{ str_replace('_', ' ', ucfirst($match->round)) }}</h2>

<x-match-detail :match="$match" :back-url="route('cup.eliminate.index', $match->season_id)" />
@endsection
