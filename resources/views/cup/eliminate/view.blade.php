@extends('cup.layouts.app')

@section('content')
<div class="container-fluid row">
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
                    <div class="col-6 text-end">
                        <p>Possession: {{ $matchResult['team1_possession'] }}%</p>
                        <p>Shots: {{ $matchResult['team1_shots'] }}</p>
                        <p>Shots on Target: {{ $matchResult['team1_shots_on_target'] }}</p>
                    </div>
                    <div class="col-6 text-start">
                        <p>Possession: {{ $matchResult['team2_possession'] }}%</p>
                        <p>Shots: {{ $matchResult['team2_shots'] }}</p>
                        <p>Shots on Target: {{ $matchResult['team2_shots_on_target'] }}</p>
                    </div>
                </div>
                <div class="situations fst-italic fs-6 lh-1 mb-2">
                    <div class="situation-left">
                        @foreach ($matchResult['dangerousSituations'] as $situation)
                            @if (str_contains($situation, $matchResult['team1_name']))
                                <p class="mb-0">{{ $situation }}</p>
                            @endif
                        @endforeach
                    </div>
                    <div class="situation-right">
                        @foreach ($matchResult['dangerousSituations'] as $situation)
                            @if (str_contains($situation, $matchResult['team2_name']))
                                <p class="mb-0">{{ $situation }}</p>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Hiển thị kết quả sút penalty -->
                @if (!empty($matchResult['penaltyresult']))
                    <div class="penalty-results mt-4 text-center">
                        <h4>Penalty Shootout Results:</h4>
                        
                        <div class="d-flex justify-content-center">
                            <div class="team-result me-5">
                                <strong>{{ $matchResult['team1_name'] }}: </strong>
                                <span>
                                    @foreach (json_decode($matchResult['penaltyresult']['team1_results']) as $shot)
                                        {!! $shot ? '✅' : '❌' !!}
                                    @endforeach
                                </span>
                            </div>
                            <div class="team-result ms-5">
                                <strong>{{ $matchResult['team2_name'] }}: </strong>
                                <span>
                                    @foreach (json_decode($matchResult['penaltyresult']['team2_results']) as $shot)
                                        {!! $shot ? '✅' : '❌' !!}
                                    @endforeach
                                </span>
                            </div>
                        </div>

                        <div class="mt-3">
                            <strong>Winner: </strong>
                            <span>{{ $matchResult['penaltyresult']['winnerName'] }}</span> <!-- Hiển thị tên đội chiến thắng -->
                        </div>
                    </div>
                @endif


            </div>
        @endif
    @endif
    <div class="tournament-container col-md-10" style="height: fit-content;">
        <!-- Vòng 32 -->
        <div class="round round-32 left">
            @foreach ($roundOf32MatchesLeft as $match)
                @include('cup.partials.match', ['match' => $match])
            @endforeach
        </div>

        <div class="round round-32 right">
            @foreach ($roundOf32MatchesRight as $match)
                @include('cup.partials.match', ['match' => $match])
            @endforeach
        </div>

        <!-- Vòng 16 -->
        <div class="round round-16 left">
            @foreach ($roundOf16MatchesLeft as $match)
                @include('cup.partials.match', ['match' => $match])
            @endforeach
        </div>

        <div class="round round-16 right">
            @foreach ($roundOf16MatchesRight as $match)
                @include('cup.partials.match', ['match' => $match])
            @endforeach
        </div>

        <!-- Quarter Finals -->
        <div class="round quarter-finals left">
            @foreach ($quarterFinalMatchesLeft as $match)
                @include('cup.partials.match', ['match' => $match])
            @endforeach
        </div>

        <div class="round quarter-finals right">
            @foreach ($quarterFinalMatchesRight as $match)
                @include('cup.partials.match', ['match' => $match])
            @endforeach
        </div>

        <!-- Semi Finals -->
        <div class="round semi-finals left">
            @foreach ($semiFinalMatchesLeft as $match)
                @include('cup.partials.match', ['match' => $match])
            @endforeach
        </div>

        <div class="round semi-finals right">
            @foreach ($semiFinalMatchesRight as $match)
                @include('cup.partials.match', ['match' => $match])
            @endforeach
        </div>

        <!-- Finals -->
        <div class="finals">
            
            <div class="final">
                <center>

                    <h3>Finals</h3>
                    <span style="color: gold; font-size: 3em;">&#127942;</span>
                </center>
                @include('cup.partials.match', ['match' => $finalMatch])
            </div>
            <div class="third-place">
                <h3 class="center">3rd place</h3>

                @include('cup.partials.match', ['match' => $thirdPlaceMatch])
            </div>
        </div>
    </div>
    <div class="col-md-2">
        @if($champion)
            <h2>Champion</h2>
            <p class="bg-warning text-white p-2">{{ $champion->team_name }}</p>
        @else
            <h2>Champion</h2>
            <p>Not determined yet</p>
        @endif
    
        <h2>Matches</h2>
        <form action="{{ route('cup.seasons.eliminate.simulate') }}" method="POST">
            @csrf
            <input type="hidden" name="season_id" value="{{ $season->id }}">
            <div class="row">
                {{-- <label for="match_count" class="form-label">Number of Matches to Simulate:</label> --}}
                <div class="col-6">
                    <input type="number" id="match_count" name="match_count" class="form-control" value="1" min="1" max="32" required>
                </div>
                <button type="submit" class="btn btn-primary col-6">Next Match</button>
            </div>
        </form>
    
        <p class="bg-info text-white p-2 mt-2 mb-0">Round {{ $currentRound ?? 'Not started' }}</p>
        <table class="table table-centered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Team 1</th>
                    <th>Score</th>
                    <th>Team 2</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($nextMatches as $index => $match)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $match->team1_name }}</td>
                        <td>{{ $match->team1_score }} - {{ $match->team2_score }}</td>
                        <td>{{ $match->team2_name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p class="bg-secondary text-white p-2 mb-0">Round {{ $lastRound ?? 'Not available' }}</p>
        <table class="table table-centered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Team 1</th>
                    <th>Score</th>
                    <th>Team 2</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($completedMatches as $index => $match)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $match->team1_name }}</td>
                        <td>{{ $match->team1_score }} - {{ $match->team2_score }}</td>
                        <td>{{ $match->team2_name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
    
</div>

<style>
    .tournament-container {
    display: grid;
    grid-template-columns: repeat(9, 1fr);
    gap: 1px;
    padding: 20px;
    align-items: center;
}

.round {
    display: flex;
    flex-direction: column;
    gap: 1px;
    justify-content: center;
}

.round-32.left {
    grid-column: 1 / 2;
    grid-row: 1 / span 2;
}

.round-32.right {
    grid-column: 9 / 10;
    grid-row: 1 / span 2;
}

.round-16.left {
    grid-column: 2 / 3;
    grid-row: 1 / span 2;
}

.round-16.right {
    grid-column: 8 / 9;
    grid-row: 1 / span 2;
}

.quarter-finals.left {
    grid-column: 3 / 4;
    grid-row: 1 / span 2;
}

.quarter-finals.right {
    grid-column: 7 / 8;
    grid-row: 1 / span 2;
}

.semi-finals.left {
    grid-column: 4 / 5;
    grid-row: 1 / span 2;
}

.semi-finals.right {
    grid-column: 6 / 7;
    grid-row: 1 / span 2;
}

.finals {
    grid-column: 5 / 6;
    grid-row: 1 / span 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.finals .final {
    width: 100%;
}
.finals .third-place {
    width: 100%;
}

.match {
    text-align: center;
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 5px;
    background: #f9f9f9;
}

.match table {
    width: 100%;
    border-collapse: collapse;
}

.match td {
    padding: 1px;
    text-align: center;
}

.match td:first-child {
    text-align: left;
}

.team {
    margin-bottom: 5px;
}
.scoreboard {
    text-align: center;
    margin: 20px 0;
    font-size: 24px;
    font-weight: bold;
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
@endsection
