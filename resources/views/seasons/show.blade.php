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
.table-centered th,
.table-centered td {
    text-align: center; /* Căn giữa nội dung trong th và td */
    vertical-align: middle; /* Căn giữa nội dung theo chiều dọc */
}

</style>
@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/seasons/{{$season->id}}">Bảng Xếp Hạng</a></li>
          <li class="breadcrumb-item"><a href="/matches/{{$season->id}}">Lịch Thi Đấu</a></li>
          <li class="breadcrumb-item"><a href="/histories/{{$season->id}}">Thống Kê Mùa Giải</a></li>
        </ol>
    </nav>
      
    <h1>Mùa giải: {{ $season->season }}</h1>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @if (session('matchResult'))
            @php $matchResult = session('matchResult'); @endphp
            <div class="border px-5 pt-2 mb-2">
                <div class="scoreboard h2">
                    {{ $matchResult['team1_name'] }} &nbsp; {{ $matchResult['team1_score'] }} - {{ $matchResult['team2_score'] }} &nbsp; {{ $matchResult['team2_name'] }}
                </div>
    
                <div class="situations fst-italic lh-1">
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
            <table class="table table-centered">
                <thead>
                    <tr>
                        <th>Round</th>
                        <th>Team 1</th>
                        <th>Score</th>
                        <th>team 2</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($nextMatches as $match)
                        <tr>
                            <td>{{ $match->round }}</td>
                            <td>
                                <div style="background: linear-gradient(to right, {{ $match->team1_c1 }} 60%, {{ $match->team1_c2 }} 40%);
                                    color: {{ $match->team1_c3 }};
                                    text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;
                                    padding: 5px; border-radius: 5px;">
                                    {{ $match->team1_name }}
                                </div>
                            </td>
                            <td>
                                {{ $match->team1_score }} - {{ $match->team2_score }} 
                            </td>
                            <td>
                                <div style="background: linear-gradient(to right, {{ $match->team2_c1 }} 60%, {{ $match->team2_c2 }} 40%);
                                    color: {{ $match->team2_c3 }};
                                    text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;
                                    padding: 5px; border-radius: 5px;">
                                    {{ $match->team2_name }}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <h3>Matches Played</h3>
            <center>
                
            </center>
            <table class="table table-centered">
                <thead>
                    <tr>
                        <th>Round</th>
                        <th>Team 1</th>
                        <th>Score</th>
                        <th>team 2</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($completedMatches as $match)
                        <tr>
                            <td>{{ $match->round }}</td>
                            <td>
                                <div style="background: linear-gradient(to right, {{ $match->team1_c1 }} 60%, {{ $match->team1_c2 }} 40%);
                                    color: {{ $match->team1_c3 }};
                                    text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;
                                    padding: 5px; border-radius: 5px;">
                                    {{ $match->team1_name }}
                                </div>
                            </td>
                            <td>
                                {{ $match->team1_score }} - {{ $match->team2_score }} 
                            </td>
                            <td>
                                <div style="background: linear-gradient(to right, {{ $match->team2_c1 }} 60%, {{ $match->team2_c2 }} 40%);
                                    color: {{ $match->team2_c3 }};
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
