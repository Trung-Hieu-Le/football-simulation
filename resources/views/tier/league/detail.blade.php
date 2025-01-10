@extends('tier.layouts.app')

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
                <h3>Group {{ $groupName }}</h3>
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
                            if ($index < 4) {
                                $bgColor = 'table-success'; // Light green
                            } elseif ($index >= count($standings) - 4) {
                                $bgColor = 'table-danger'; // Light red
                            }
                        @endphp
                            <tr class="{{ $bgColor }}">
                                <td>{{ $team->position }}</td>
                                <td>
                                    <div style="background: linear-gradient(to right, {{ $team->color_1 }} 60%, {{ $team->color_2 }} 40%);
                                        color: {{ $team->color_3 }};
                                        text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;
                                        padding: 5px; border-radius: 5px;">
                                        {{ $team->team_name }}
                                    </div>
                                </td>
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
            <form action="{{ route('simulate.next.match') }}" method="POST">
                @csrf
                <input type="hidden" name="season_id" value="{{ $season_id }}">
                <button type="submit" class="btn btn-primary">Next Match</button>
            </form>
            
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Round</th>
                        <th>Team 1</th>
                        <th>Team 2</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($nextMatch as $match)
                        <tr>
                            <td>{{ $match->round }}</td>
                            <td>
                                <div style="background: linear-gradient(to right, {{ $match->team1_color_1 }} 60%, {{ $match->team1_color_2 }} 40%);
                                    color: {{ $match->team1_color_2 }};
                                    text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;
                                    padding: 5px; border-radius: 5px;">
                                    {{ $match->team1_name }}
                                </div>
                            </td>
                            <td>
                                <div style="background: linear-gradient(to right, {{ $match->team2_color_1 }} 60%, {{ $match->team2_color_2 }} 40%);
                                    color: {{ $match->team2_color_2 }};
                                    text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;
                                    padding: 5px; border-radius: 5px;">
                                    {{ $match->team2_name }}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
