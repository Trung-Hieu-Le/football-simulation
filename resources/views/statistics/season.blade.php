@extends('layouts.app')

@section('title', ucfirst($mode) . ' Statistics')

@section('content')
@include('partials.season-nav', ['mode' => $mode, 'season' => $season])

<h2 class="mb-4">Season {{ $season->season }} Statistics</h2>

<div class="row">
    <div class="col-md-6 col-lg-3"><x-stats-card title="Top Scorers" :items="$topScorers" value-key="goal_scored" /></div>
    <div class="col-md-6 col-lg-3"><x-stats-card title="Top Possession %" :items="$topPossession" value-key="average_possession" suffix="%" /></div>
    <div class="col-md-6 col-lg-3"><x-stats-card title="Most Fouls" :items="$mostFouls" value-key="foul" /></div>
    <div class="col-md-6 col-lg-3"><x-stats-card title="Best Defense (least conceded)" :items="$bestDefense" value-key="goal_conceded" /></div>
</div>
@endsection
