@extends('layouts.app')

@section('title', 'Teams Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Teams Management</h1>
    <div>
        <a href="{{ route('teams.create') }}" class="btn btn-primary">Add New Team</a>
        <form action="{{ route('teams.reset-elo') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-warning" onclick="return confirm('Reset all team ELO to 1000?')">Reset All ELO</button>
        </form>
        <form action="{{ route('teams.reset-form') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-info" onclick="return confirm('Reset all team forms to 50?')">Reset All Form</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Region</th>
                    <th>ELO</th>
                    <th>Form</th>
                    <th>Stats Summary</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($teams as $team)
                <tr>
                    <td>{{ $team->id }}</td>
                    <td>
                        <span style="background: linear-gradient(135deg, {{ $team->color_1 }}, {{ $team->color_2 }}); padding: 2px 8px; border-radius: 4px; color: white;">
                            {{ $team->name }}
                        </span>
                    </td>
                    <td>{{ $team->region->name ?? 'N/A' }}</td>
                    <td><strong>{{ $team->elo }}</strong></td>
                    <td>{{ $team->form }}</td>
                    <td>
                        <small>
                            ATK: {{ $team->attack }} | 
                            DEF: {{ $team->defense }} | 
                            CTL: {{ $team->control }}
                        </small>
                    </td>
                    <td>
                        <a href="{{ route('teams.edit', $team->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('teams.destroy', $team->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this team?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
