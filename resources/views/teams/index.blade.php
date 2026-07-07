@extends('layouts.app')

@section('title', 'Teams')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Teams Management</h1>
    <div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#teamCreateModal">Add Team</button>
        <form action="{{ route('teams.reset-elo') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-warning" onclick="return confirm('Reset all ELO to 1000?')">Reset All ELO</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Region</th>
                    <th>ELO</th>
                    <th>Stats</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($teams as $team)
                <tr>
                    <td>{{ $team->id }}</td>
                    <td>@include('partials.team-badge', ['team' => $team])</td>
                    <td>{{ $team->region->name ?? 'N/A' }}</td>
                    <td><strong>{{ $team->elo }}</strong></td>
                    <td><small>ATK {{ $team->attack }} | DEF {{ $team->defense }} | CTL {{ $team->control }}</small></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#teamEditModal{{ $team->id }}">Edit</button>
                        <form action="{{ route('teams.destroy', $team->id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete {{ $team->name }}?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Create Modal --}}
<div class="modal fade" id="teamCreateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('teams.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Add Team</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">@include('teams._form', ['team' => null, 'regions' => $regions])</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modals --}}
@foreach($teams as $team)
<div class="modal fade" id="teamEditModal{{ $team->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('teams.update', $team->id) }}" method="POST" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header"><h5 class="modal-title">Edit {{ $team->name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">@include('teams._form', ['team' => $team, 'regions' => $regions])</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning">Save</button>
            </div>
        </form>
    </div>
</div>
@endforeach

@if($errors->any())
@push('scripts')
<script>document.addEventListener('DOMContentLoaded', () => new bootstrap.Modal(document.getElementById('teamCreateModal')).show());</script>
@endpush
@endif
@endsection
