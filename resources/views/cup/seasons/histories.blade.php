@extends('cup.layouts.app')

@section('content')
<div class="container">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="/cup/seasons/{{$seasonId}}">Bảng Xếp Hạng</a></li>
      <li class="breadcrumb-item"><a href="/cup/matches/{{$seasonId}}">Lịch Thi Đấu</a></li>
      <li class="breadcrumb-item"><a href="/cup/histories/{{$seasonId}}">Thống Kê Mùa Giải</a></li>
    </ol>
  </nav>
      <h1>Histories</h1>
      <div>
          <div>
              <form method="GET" action="/cup/histories/{{$seasonId}}">
                <select name="sort_by" onchange="this.form.submit()">
                  <option value="points" {{ $sortBy == 'points' ? 'selected' : '' }}>Points</option>
                  <option value="goals_scored" {{ $sortBy == 'goals_scored' ? 'selected' : '' }}>Goals Scored</option>
                  <option value="goals_conceded" {{ $sortBy == 'goals_conceded' ? 'selected' : '' }}>Goals Conceded</option>
                  <option value="goal_difference" {{ $sortBy == 'goal_difference' ? 'selected' : '' }}>Goal Difference</option>
                  <option value="possession" {{ $sortBy == 'possession' ? 'selected' : '' }}>Possession</option>
                  <option value="fouls" {{ $sortBy == 'fouls' ? 'selected' : '' }}>Fouls</option>
                </select>
              </form>
            </div>
            
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Team</th>
                  <th>Số trận</th>
                  <th>Win</th>
                  <th>Draw</th>
                  <th>Lose</th>
                  <th>GF</th>
                  <th>GC</th>
                  <th>GD</th>
                  <th>Kiểm soát</th>
                  <th>Lỗi</th>
                  <th>Pts</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($histories as $history)
                  <tr>
                    <td>{{ $history->team_name }}</td>
                    <td>{{ $history->matches_played }}</td>
                    <td>{{ $history->wins }}</td>
                    <td>{{ $history->draws }}</td>
                    <td>{{ $history->loses }}</td>
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
