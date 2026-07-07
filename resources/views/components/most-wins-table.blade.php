@props(['items', 'title' => 'Most Wins (all competitions)'])

<div class="card">
    <div class="card-header">{{ $title }}</div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Team</th><th>Wins</th><th>Matches</th><th>Goals</th></tr></thead>
            <tbody>
                @forelse($items as $row)
                <tr>
                    <td>@include('partials.team-badge', ['team' => $row->team])</td>
                    <td><strong>{{ $row->total_wins }}</strong></td>
                    <td>{{ $row->total_matches }}</td>
                    <td>{{ $row->total_goals }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-muted text-center">No data yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
