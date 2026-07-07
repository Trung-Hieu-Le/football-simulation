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
                        <a href="{{ route('cup.seasons.show', $season->id) }}" class="btn btn-info">Vòng bảng</a>
                        @if ($season->match_completion_rate == 100)
                            <a href="{{ route('cup.eliminate.create', $season->id) }}" class="btn btn-primary">Vòng Loại</a>

<<<<<<< Updated upstream
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        
    </table>
=======
<div class="row">
    @forelse($seasons as $season)
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Season {{ $season->season }}</h5>
            </div>
            <div class="card-body">
                <p><strong>Teams:</strong> {{ $season->teams_count }}</p>
                <p><strong>Groups:</strong> {{ $season->teams_count === 32 ? 8 : 16 }}</p>
                <p><strong>Meta:</strong> <span class="badge bg-secondary">{{ $season->meta }}</span></p>
                <p><strong>Created:</strong> {{ $season->created_at?->format('Y-m-d') ?? 'N/A' }}</p>
                
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('cup.seasons.show', $season->id) }}" class="btn btn-success btn-sm">Groups</a>
                    <a href="{{ route('cup.matches.index', $season->id) }}" class="btn btn-info btn-sm">Matches</a>
                    <a href="{{ route('cup.eliminate.index', $season->id) }}" class="btn btn-warning btn-sm">Knockout</a>
                    <form action="{{ route('cup.seasons.destroy', $season->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete season?')">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info">
            No cup seasons yet. <a href="{{ route('cup.seasons.create') }}">Create one</a>
        </div>
    </div>
    @endforelse
</div>
>>>>>>> Stashed changes
@endsection
