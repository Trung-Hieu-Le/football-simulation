@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Mùa giải: {{ $season->season }}</h1>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <div class="row">
        <!-- Col-8: Bảng xếp hạng -->
        <div class="col-md-8">
            <h2>Bảng xếp hạng</h2>
            @foreach ($groupStandings as $groupName => $standings)
                <h3>Group {{ ucfirst($groupName) }}</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Team</th>
                            <th>Matches Played</th>
                            <th>Goals Scored</th>
                            <th>Goals Conceded</th>
                            <th>Goal Difference</th>
                            <th>Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($standings as $index => $team)
                        @php
                            $bgColor = '';
                            if ($index < 1) {
                                $bgColor = 'table-success'; // Light green
                            } elseif ($index >= (count($standings)*3/4)) {
                                $bgColor = 'table-danger'; // Light red
                            }
                        @endphp
                            <tr class="{{ $bgColor }}">
                                <td>{{ $index+1 }}</td>
                                <td>{{ $team->team_name }}</td>
                                <td>{{ $team->match_played }}</td>
                                <td>{{ $team->goal_scored }}</td>
                                <td>{{ $team->goal_conceded }}</td>
                                <td>{{ $team->goal_difference }}</td>
                                <td>{{ $team->points }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        </div>

        <!-- Col-4: Vòng đấu -->
        <div class="col-md-4">
            <h2>Vòng đấu</h2>
            <form action="{{ route('seasons.simulate') }}" method="POST">
                @csrf
                <input type="hidden" name="season_id" value="{{ $season->id }}">
                <button type="submit" class="btn btn-primary">Next Match</button>
            </form>

            <h3>Matches Played</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Round</th>
                        <th>Team 1</th>
                        <th>Team 2</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($completedMatches as $match)
                        <tr>
                            <td>{{ $match->tier }}</td>
                            <td>{{ $match->team1_name }}</td>
                            <td>{{ $match->team2_name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <h3>Next Matches</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Round</th>
                        <th>Team 1</th>
                        <th>Team 2</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($nextMatches as $match)
                        <tr>
                            <td>{{ $match->round }}</td>
                            <td>{{ $match->team1_name }}</td>
                            <td>{{ $match->team2_name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
