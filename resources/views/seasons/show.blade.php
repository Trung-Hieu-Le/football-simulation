@extends('layouts.app')
<style>
    .scoreboard {
    text-align: center;
    margin: 20px 0;
    font-size: 24px;
    font-weight: bold;
}

.teams {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.team {
    width: 45%;
    text-align: center;
}

.situations {
    display: flex;
    justify-content: space-between;
}

.situation-left {
    text-align: left;
    width: 45%;
}

.situation-right {
    text-align: right;
    width: 45%;
}

</style>
@section('content')
<div class="container">
    <h1>Mùa giải: {{ $season->season }}</h1>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @if (session('matchResult'))
            @php $matchResult = session('matchResult'); @endphp
            <div class="scoreboard">
                {{ $matchResult['team1_name'] }} {{ $matchResult['team1_score'] }} - {{ $matchResult['team2_score'] }} {{ $matchResult['team2_name'] }}
            </div>

            <div class="situations">
                <div class="situation-left">
                    @foreach ($matchResult['dangerousSituations'] as $situation)
                        @if (str_contains($situation, $matchResult['team1_name']))
                            <p>{{ $situation }}</p>
                        @endif
                    @endforeach
                </div>
                <div class="situation-right">
                    @foreach ($matchResult['dangerousSituations'] as $situation)
                        @if (str_contains($situation, $matchResult['team2_name']))
                            <p>{{ $situation }}</p>
                        @endif
                    @endforeach
                </div>
            </div>

        @endif
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
                            $promotionRelegationCount = floor(count($standings) / 4);

                            // Nhà vô địch
                            if ($groupName == 'tier1' && $index == 0) {
                                $bgColor = 'table-warning'; // Light yellow
                            }
                            // Các đội lên hạng
                            elseif ($groupName != 'tier1' && $index < $promotionRelegationCount) {
                                $bgColor = 'table-success'; // Light green
                            }
                            // Các đội xuống hạng (trừ tier cuối)
                            elseif ($groupName != 'tier3' && $index >= (count($standings) - $promotionRelegationCount)) {
                                $bgColor = 'table-danger'; // Light red
                            }
                        @endphp
                            <tr class="{{ $bgColor }}">
                                <td>
                                    @if ($groupName == 'tier1' && $index == 0)
                                        <span style="color: gold; font-size: 1em;">&#x1F451;</span>
                                    @else
                                        {{ $index+1 }}
                                    @endif
                                </td>
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
            @if($currentRound === $maxRound+1)
                <h2>Vô địch Tier 1</h2>
                @if($champion)
                    <p class="bg-warning text-white p-2">{{ $champion->team_name }} - {{ $champion->points }}pts</p>
                @else
                    <p>Chưa xác định</p>
                @endif

                <h2>Các đội lên hạng</h2>
                <ul>
                    @foreach($promotedTeams as $team)
                        <li class="bg-success text-white p-2">{{ $team->team_name }} - {{ $team->tier }}</li>
                    @endforeach
                </ul>

                <h2>Các đội xuống hạng</h2>
                <ul>
                    @foreach($relegatedTeams as $team)
                        <li class="bg-danger text-white p-2">{{ $team->team_name }} - {{ $team->tier }}</li>
                    @endforeach
                </ul>
            @endif
            <h2>Vòng đấu</h2>
            <form action="{{ route('seasons.simulate') }}" method="POST">
                @csrf
                <input type="hidden" name="season_id" value="{{ $season->id }}">
                <button type="submit" class="btn btn-primary">Next Match</button>
            </form>

            <h3>Next Matches</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Round</th>
                        <th>Match</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($nextMatches as $match)
                        <tr>
                            <td>{{ $match->round }}</td>
                            <td>{{ $match->team1_name }} 
                                {{ $match->team1_score }} - {{ $match->team2_score }} 
                                {{ $match->team2_name }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <h3>Matches Played</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Round</th>
                        <th>Match</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($completedMatches as $match)
                        <tr>
                            <td>{{ $match->round }}</td>
                            <td>{{ $match->team1_name }} 
                                {{ $match->team1_score }} - {{ $match->team2_score }} 
                                {{ $match->team2_name }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
