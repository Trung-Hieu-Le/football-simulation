@extends('layouts.app')

@section('content')
    <h1>Seasons</h1>
    <a href="{{ route('seasons.create') }}" class="btn btn-primary">Create New Season</a>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Season</th>
                <th>Số đội</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($seasons as $season)
                <tr>
                    <td>{{ $season->id }}</td>
                    <td>{{ $season->season }}</td>
                    <td>{{ $season->teams_count }}</td>
                    <td>
                        <form action="{{ route('seasons.destroy', $season->id) }}" method="POST">
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
