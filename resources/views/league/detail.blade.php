@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Mùa giải: {{ $season->season }}</h1>

    <div class="row">
        <!-- Col-8: Bảng xếp hạng -->
        <div class="col-md-8">
            <h2>Bảng xếp hạng</h2>
            @foreach ($groupStandings as $groupName => $standings)
                <h3>{{ $groupName }}</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Hạng</th>
                            <th>Đội</th>
                            <th>Điểm</th>
                            <th>Hiệu số</th>
                            <th>Bàn thắng</th>
                            <th>Phong độ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($standings as $index => $team)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $team['name'] }}</td>
                                <td>{{ $team['points'] }}</td>
                                <td>{{ $team['goal_difference'] }}</td>
                                <td>{{ $team['goals_scored'] }}</td>
                                <td>
                                    @foreach ($team['form'] as $result)
                                        <span class="badge {{ $result == 'W' ? 'bg-success' : ($result == 'L' ? 'bg-danger' : 'bg-secondary') }}">
                                            {{ $result }}
                                        </span>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        </div>

        <!-- Col-4: Vòng đấu -->
        <div class="col-md-4">
            <h2>Vòng đấu</h2>
            @foreach ($matches as $round => $roundMatches)
                <h3>{{ $round }}</h3>
                @foreach ($roundMatches as $match)
                    <p>{{ $match->team1_name ?? 'TBA' }} {{ $match->team1_score ?? '?' }} - {{ $match->team2_score ?? '?' }} {{ $match->team2_name ?? 'TBA' }}</p>
                @endforeach
            @endforeach
        </div>
    </div>
</div>
@endsection
