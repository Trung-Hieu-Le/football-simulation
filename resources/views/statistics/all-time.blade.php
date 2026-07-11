@extends('layouts.app')

@section('title', 'All-time Statistics')

@section('content')
<h1 class="mb-4">All-time Statistics</h1>

<div class="row">
    <div class="col-lg-8">
        <x-champions-table title="🏆 League Champions (Division 1)" :champions="$leagueChampions" :show-division="false" />
        <x-champions-table title="🏅 Cup Champions" :champions="$cupChampions" />
    </div>
    <div class="col-lg-4">
        <x-most-wins-table :items="$mostWins" />
    </div>
</div>
@endsection
