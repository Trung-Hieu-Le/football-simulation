@extends('cup.layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <!-- Left Column -->
        <!-- Top Teams Table -->
        <div class="col-7 col-lg-8 col-md-12 col-sm-12">
            <h3>Top Teams</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Season</th>
                        <th>Team 1</th>
                        <th>Team 2</th>
                        <th>Team 3</th>
                        <th>Team 4</th>
                        <th>Points 1</th>
                        <th>Points 2</th>
                        <th>Points 3</th>
                        <th>Points 4</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($topTeams as $season => $teams)
                        <tr>
                            <td>{{ $season }}</td>
                            @foreach ($teams as $team)
                                <td>{{ $team->team_name }}</td>
                            @endforeach
                            @foreach ($teams as $team)
                                <td>{{ $team->points }}</td>
                            @endforeach
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>


        <!-- Right Column -->
        <div class="col-5 col-lg-4 col-md-12 col-sm-12">
            <h3>Champions (All Seasons)</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Region</th>
                        <th>Titles</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($champions as $team)
                    <tr>
                        <td>{{ $team->name }}</td>
                        <td>{{ $team->region_name }}</td>
                        <td>{{ $team->championships }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Additional Stats -->
    <h3>Additional Statistics</h3>
    <div class="row">
        <div class="col-3">
            <h4>Top 5 Teams with Highest Points</h4>
            <ol>
                @foreach ($statistics['highest_points'] as $team)
                    <li>{{ $team->team_name }} - {{ $team->points }} points</li>
                @endforeach
            </ol>
        </div>
        <div class="col-3">
            <h4>Top 5 Teams with Lowest Points</h4>
            <ol>
                @foreach ($statistics['lowest_points'] as $team)
                    <li>{{ $team->team_name }} - {{ $team->points }} points</li>
                @endforeach
            </ol>
        </div>
        <div class="col-3">
            <h4>Top 5 Largest Gaps between 1st and 2nd place</h4>
            <ol>
                @foreach ($statistics['largest_gap'] as $gap)
                    <li>{{ $gap->team1_name }} vs {{ $gap->team2_name }} - Gap: {{ $gap->gap }} points</li>
                @endforeach
            </ol>
        </div>
        <div class="col-3">
            <h4>Top 5 Smallest Gaps between 1st and 2nd place</h4>
            <ol>
                @foreach ($statistics['smallest_gap'] as $gap)
                    <li>{{ $gap->team1_name }} vs {{ $gap->team2_name }} - Gap: {{ $gap->gap }} points</li>
                @endforeach
            </ol>
        </div>
        <div class="col-3">
            <h4>Teams with Most Top 4 Appearances</h4>
            <ol>
                @foreach ($statistics['most_top4_appearances'] as $team)
                    <li>{{ $team->team_name }} - {{ $team->top4_count }} appearances</li>
                @endforeach
            </ol>
        </div>
        <div class="col-3">
            <h4>Top 5 Teams with Most Wins</h4>
            <ul>
                @foreach ($topWinTeams as $team)
                    <li>{{ $team->name }}: {{ $team->total_win }} wins</li>
                @endforeach
            </ul>
        </div>
        <div class="col-3">
            <h4>Top 5 Teams with Most Draws</h4>
            <ul>
                @foreach ($topDrawTeams as $team)
                    <li>{{ $team->name }}: {{ $team->total_draw }} draws</li>
                @endforeach
            </ul>
        </div>
        <div class="col-3">
            <h4>Top 5 Teams with Most Losses</h4>
            <ul>
                @foreach ($topLoseTeams as $team)
                    <li>{{ $team->name }}: {{ $team->total_lose }} losses</li>
                @endforeach
            </ul>
        </div>
        <div class="col-3">
            <h4>Top 5 Teams (High Stats, Never Won)</h4>
            <ul>
                @foreach ($highestStatsNoChampion as $team)
                    <li>{{ $team->name }}: {{ $team->total_stats }}</li>
                @endforeach
            </ul>
        </div>
        <div class="col-3">
            <h4>Top 5 Teams (Low Stats, Won)</h4>
            <ul>
                @foreach ($lowestStatsChampion as $team)
                    <li>{{ $team->name }}: {{ $team->total_stats }}</li>
                @endforeach
            </ul>
        </div>
    </div>

</div>
@endsection
