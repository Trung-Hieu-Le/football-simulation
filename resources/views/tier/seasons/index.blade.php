@extends('tier.layouts.app')

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
        <a href="{{ route('seasons.create') }}" class="btn btn-primary mr-2">Create New Season</a>
        <form action="{{ route('tier.seasons.destroy_all') }}" method="GET" onsubmit="return confirm('Are you sure you want to delete all seasons?');">
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
                        <form action="{{ route('seasons.destroy', $season->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this season?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                        <a href="{{ route('seasons.show', $season->id) }}" class="btn btn-info">View Details</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
        
    </table>
@endsection
