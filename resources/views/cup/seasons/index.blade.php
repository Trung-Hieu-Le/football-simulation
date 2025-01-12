@extends('cup.layouts.app')

@section('content')
    <h1>Seasons</h1>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @elseif (session('fail'))
        <div class="alert alert-danger">
            {{ session('fail') }}
        </div>
    @endif
    <div class="d-flex justify-content-start mb-3">
        <a href="{{ route('cup.seasons.create') }}" class="btn btn-primary mr-2">Create New Season</a>
        <form action="{{ route('cup.seasons.destroy_all') }}" method="GET" onsubmit="return confirm('Are you sure you want to delete this season?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Delete All</button>
        </form>
    </div>
    <table class="table">
        <thead>
            <tr>
                {{-- <th>ID</th> --}}
                <th>Season</th>
                <th>Meta</th>
                <th>Số đội</th>
                <th>Tỷ lệ trận đã hoàn thành (%)</th>
                <th>Vòng hiện tại / Tổng vòng</th>
                <th>Champion</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($seasons as $season)
                <tr>
                    {{-- <td>{{ $season->id }}</td> --}}
                    <td>{{ $season->season }}</td>
                    <th>{{ $season->meta }}</th>
                    <td>{{ $season->teams_count }}</td>
                    <td>{{ $season->match_completion_rate }}%</td>
                    <td>{{ $season->current_round }} / {{ $season->max_round }}</td>
                    <td>
                        @if ($season->champion)
                            <div style="background: linear-gradient(to right, {{ $season->champion['color_1'] }} 60%, {{ $season->champion['color_2']}} 40%);
                                color: {{ $season->champion['color_3'] }};
                                text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;
                                padding: 5px; border-radius: 5px;">
                            {{ $season->champion['name'] }}
                        @else
                            <span>No Champion</span>
                        @endif
                        </div>
                    </td>
                    <td>
                        <form action="{{ route('cup.seasons.destroy', $season->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this season?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                        <a href="{{ route('cup.seasons.show', $season->id) }}" class="btn btn-info">Xem vòng bảng</a>
                        @if ($season->match_completion_rate == 100)
                            <a href="{{ route('cup.eliminate.create', $season->id) }}" class="btn btn-success">Tạo V.Loại</a>
                            <a href="{{ route('cup.eliminate.view', $season->id) }}" class="btn btn-primary">Xem V.Loại</a> <!-- Nút mới -->

                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        
    </table>
@endsection
