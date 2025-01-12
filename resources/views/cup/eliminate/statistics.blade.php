@extends('cup.layouts.app')

@section('content')
<div class="container">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/cup/eliminate/view/{{ $seasonId }}">Bảng Xếp Hạng</a></li>
        <li class="breadcrumb-item"><a href="/cup/eliminate/statistics/{{ $seasonId }}">Thống Kê Mùa Giải</a></li>
    </ol>
  </nav>
      <h1>Histories</h1>
      <div>
            <div>
                <form method="GET" action="/cup/eliminate/statistics/{{ $seasonId }}">
                    <select name="sort_by" onchange="this.form.submit()">
                        <option value="points" {{ $sortBy == 'points' ? 'selected' : '' }}>Points</option>
                        <option value="goal_scored" {{ $sortBy == 'goal_scored' ? 'selected' : '' }}>Goals Scored</option>
                        <option value="goal_conceded" {{ $sortBy == 'goal_conceded' ? 'selected' : '' }}>Goals Conceded</option>
                        <option value="goal_difference" {{ $sortBy == 'goal_difference' ? 'selected' : '' }}>Goal Difference</option>
                        <option value="average_possession" {{ $sortBy == 'average_possession' ? 'selected' : '' }}>Possession</option>
                        <option value="foul" {{ $sortBy == 'foul' ? 'selected' : '' }}>Fouls</option>
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
                    <td>{{ $history->match_played }}</td>
                    <td>{{ $history->win }}</td>
                    <td>{{ $history->draw }}</td>
                    <td>{{ $history->lose }}</td>
                    <td>{{ $history->goal_scored }}</td>
                    <td>{{ $history->goal_conceded }}</td>
                    <td>{{ $history->goal_difference }}</td>
                    <td>{{ $history->average_possession }}%</td>
                    <td>{{ $history->foul }}</td>
                    <td>{{ $history->points }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
            
      </div>
</div>
@endsection
