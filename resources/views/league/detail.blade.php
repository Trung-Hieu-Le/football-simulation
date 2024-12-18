@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <!-- Left Section: Danh sách team theo bảng -->
        <div class="col-8">
            <h4>Danh Sách Các Bảng</h4>
            @foreach ($groups as $group)
                <div class="mb-3">
                    <h5>Bảng {{ $group->group_name }}</h5>
                    <ul class="list-group">
                        @foreach ($group->teams as $team)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $team->name }} 
                                <span>Form: {{ $team->form }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <!-- Right Section: Trận đấu gần nhất -->
        <div class="col-4">
            <div class="sticky-top">
                <h4>8 Trận Gần Nhất</h4>
                <ul class="list-group mb-3">
                    @foreach ($current_matches as $match)
                        <li class="list-group-item">
                            <strong>Team {{ $match->team1_id }}</strong> vs <strong>Team {{ $match->team2_id }}</strong>
                            <span class="text-muted"> (Chưa có kết quả)</span>
                        </li>
                    @endforeach
                </ul>
                <form action="{{ route('league.simulate', $season_id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100">Giả lập kết quả 8 trận tiếp theo</button>
                </form>
            </div>
        </div>
    </div>

    <!-- History Section -->
    <div class="row mt-5">
        <div class="col-12">
            <h4>Lịch Sử Trận Đấu</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Team 1</th>
                        <th>Team 2</th>
                        <th>Tỉ số</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($match_history as $index => $match)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>Team {{ $match->team1_id }}</td>
                            <td>Team {{ $match->team2_id }}</td>
                            <td>{{ $match->team1_score }} - {{ $match->team2_score }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
