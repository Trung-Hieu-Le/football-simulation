@extends('cup.layouts.app')
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
<div class="container-fluid">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/cup/seasons/{{$season->id}}">Bảng Xếp Hạng</a></li>
          <li class="breadcrumb-item"><a href="/cup/matches/{{$season->id}}">Lịch Thi Đấu</a></li>
          <li class="breadcrumb-item"><a href="/cup/histories/{{$season->id}}">Thống Kê Mùa Giải</a></li>
        </ol>
    </nav>
      

    <div class="row">
        <!-- Col-8: Bảng xếp hạng -->
        <div class="col-md-8">
            <h1>Mùa giải: {{ $season->season }} (meta {{ $season->meta }}) </h1>
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
                        <div class="stats row mt-4">
                            <?php 
                            $team1_possession = $matchResult['team1_possession'];
                            $team2_possession = $matchResult['team2_possession'];
                            $team1_shots = $matchResult['team1_shots'];
                            $team2_shots = $matchResult['team2_shots'];
                            $team1_shots_on_target = $matchResult['team1_shots_on_target'];
                            $team2_shots_on_target = $matchResult['team2_shots_on_target'];
                            ?>
                            <div class="col-6 text-end">
                                <p>Possession: {{ $team1_possession }}%</p>
                                <p>Shots: {{ $team1_shots }}</p>
                                <p>Shots on Target: {{ $team1_shots_on_target }}</p>
                            </div>
                            <div class="col-6 text-start">
                                <p>Possession: {{ $team2_possession }}%</p>
                                <p>Shots: {{ $team2_shots }}</p>
                                <p>Shots on Target: {{ $team2_shots_on_target }}</p>
                            </div>
                        </div>
                        <div class="situations fst-italic fs-6 lh-1 mb-2">
                            <div class="situation-left">
                                @foreach ($matchResult['specialEvents'] as $situation)
                                    @if (str_contains($situation, $matchResult['team1_name']))
                                        <p class="mb-0">{{ $situation }}</p>
                                    @endif
                                @endforeach
                            </div>
                            <div class="situation-right">
                                @foreach ($matchResult['specialEvents'] as $situation)
                                    @if (str_contains($situation, $matchResult['team2_name']))
                                        <p class="mb-0">{{ $situation }}</p>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                @endif
            @endif
            <h2>Bảng xếp hạng</h2>

<!-- Tabs Navigation -->
<ul class="nav nav-tabs" id="groupTabs" role="tablist">
    @foreach ($groupStandings as $groupName => $standings)
        <li class="nav-item" role="presentation">
            <button 
                class="nav-link @if ($loop->first) active @endif" 
                id="tab-{{ $groupName }}" 
                data-bs-toggle="tab" 
                data-bs-target="#group-{{ $groupName }}" 
                type="button" 
                role="tab" 
                aria-controls="group-{{ $groupName }}" 
                aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                Group {{ ucfirst($groupName) }}
            </button>
        </li>
    @endforeach
</ul>

<!-- Tabs Content -->
<div class="tab-content" id="groupTabsContent">
    @foreach ($groupStandings as $groupName => $standings)
        <div 
            class="tab-pane fade @if ($loop->first) show active @endif" 
            id="group-{{ $groupName }}" 
            role="tabpanel" 
            aria-labelledby="tab-{{ $groupName }}">
            
            <h3>Group {{ ucfirst($groupName) }}</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Team</th>
                        <th>Số trận</th>
                        <th>Win</th>
                        <th>Draw</th>
                        <th>Lose</th>
                        <th>GF</th>
                        <th>GC</th>
                        <th>GD</th>
                        <th>Pts</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($standings as $index => $team)
                        @php
                            $bgColor = '';
                            $continueCount = 4;
                            if ($index < $continueCount) {
                                $bgColor = 'table-success'; // Light green
                            } elseif ($index >= (count($standings) - $continueCount)) {
                                $bgColor = 'table-danger'; // Light red
                            }
                        @endphp
                        <tr class="{{ $bgColor }}">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div style="background: linear-gradient(to right, {{ $team->color_1 }} 60%, {{ $team->color_2 }} 40%);
                                            color: {{ $team->color_3 }};
                                            text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;
                                            padding: 5px; border-radius: 5px;">
                                    {{ $team->team_name }}
                                </div>
                            </td>
                            <td>{{ $team->match_played }}</td>
                            <td>{{ $team->win }}</td>
                            <td>{{ $team->draw }}</td>
                            <td>{{ $team->lose }}</td>
                            <td>{{ $team->goal_scored }}</td>
                            <td>{{ $team->goal_conceded }}</td>
                            <td>{{ $team->goal_difference }}</td>
                            <td>{{ $team->points }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</div>

        </div>

        <!-- Col-4: Vòng đấu -->
        <div class="col-md-4">
            <h2>Vòng đấu</h2>
            <form action="{{ route('cup.seasons.simulate') }}" method="POST">
                @csrf
                <input type="hidden" name="season_id" value="{{ $season->id }}">
                <div class="row">
                    <label for="match_count" class="form-label">Số trận đấu muốn giả lập:</label>
                    <div class="col-6">
                        <input type="number" id="match_count" name="match_count" class="form-control" value="1" min="1" max="96" required>
                    </div>
                    <button type="submit" class="btn btn-primary col-6">Next Match</button>
                </div>
            </form>

            <h3>Next Matches</h3>
            <table class="table table-centered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Rnd</th>
                        <th>Team 1</th>
                        <th>Score</th>
                        <th>team 2</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($nextMatches as $index => $match)
                        <tr>
                            <td>{{ $index+1 }}</td>
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
                        <th>#</th>
                        <th>Rnd</th>
                        <th>Team 1</th>
                        <th>Score</th>
                        <th>team 2</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($completedMatches as $index => $match)
                        <tr>
                            <td>{{ $index+1 }}</td>
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
