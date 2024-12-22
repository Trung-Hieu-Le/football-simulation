@extends('layouts.app')

@section('content')
<div class="container">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="/seasons/{{$seasonId}}">Bảng Xếp Hạng</a></li>
      <li class="breadcrumb-item"><a href="/matches/{{$seasonId}}">Lịch Thi Đấu</a></li>
      <li class="breadcrumb-item"><a href="/histories/{{$seasonId}}">Thống Kê Mùa Giải</a></li>
    </ol>
  </nav>
      <h1>Histories</h1>
      <div>
          <div>
              <form method="GET" action="/histories/{{$seasonId}}">
                <select name="sort_by" onchange="this.form.submit()">
                  <option value="points" {{ $sortBy == 'points' ? 'selected' : '' }}>Points</option>
                  <option value="goals_scored" {{ $sortBy == 'goal_scored' ? 'selected' : '' }}>Goals Scored</option>
                  <option value="goals_conceded" {{ $sortBy == 'goal_conceded' ? 'selected' : '' }}>Goals Conceded</option>
                  <option value="goal_difference" {{ $sortBy == 'goal_difference' ? 'selected' : '' }}>Goal Difference</option>
                  <option value="possession" {{ $sortBy == 'average_possession' ? 'selected' : '' }}>Possession</option>
                  <option value="fouls" {{ $sortBy == 'foul' ? 'selected' : '' }}>Fouls</option>
                </select>
              </form>
            </div>
            
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Team</th>
                  <th>Goals Scored</th>
                  <th>Goals Conceded</th>
                  <th>Goal Difference</th>
                  <th>Possession</th>
                  <th>Fouls</th>
                  <th>Points</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($histories as $history)
                  <tr>
                    <td>{{ $history->team_name }}</td>
                    <td>{{ $history->goals_scored }}</td>
                    <td>{{ $history->goals_conceded }}</td>
                    <td>{{ $history->goal_difference }}</td>
                    <td>{{ $history->possession }}%</td>
                    <td>{{ $history->fouls }}</td>
                    <td>{{ $history->points }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
            
      </div>
</div>
@endsection
