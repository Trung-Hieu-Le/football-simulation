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
      <h1>Matches</h1>
      <div class="row">
          @foreach ($matchesByRound as $round => $matches)
          <div class="col-md-6">
            <h3>Vòng {{ $round }}</h3>
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Time</th>
                  <th>Team 1</th>
                  <th>Score</th>
                  <th>Team 2</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($matches as $match)
                  <tr>
                    <td>{{ $match->round }}</td>
                    <td>{{ $match->team1_name }}</td>
                    <td>{{ $match->team1_score }} - {{ $match->team2_score }}</td>
                    <td>{{ $match->team2_name }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @endforeach
        </div>
</div>

@endsection
