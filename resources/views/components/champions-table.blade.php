@props(['title', 'champions', 'showDivision' => false])

<div class="card mb-4">
    <div class="card-header">{{ $title }}</div>
    <div class="card-body table-responsive">
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th>Season</th>
                    <th>Team</th>
                    @if($showDivision)<th>Division</th>@endif
                    <th>Pts</th>
                    <th>GF</th>
                    <th>GA</th>
                </tr>
            </thead>
            <tbody>
                @forelse($champions as $c)
                <tr>
                    <td>{{ $c->season }}</td>
                    <td>{{ $c->name }}</td>
                    @if($showDivision)<td>{{ $c->division ?? '' }}</td>@endif
                    <td>{{ $c->points }}</td>
                    <td>{{ $c->goal_scored }}</td>
                    <td>{{ $c->goal_conceded }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $showDivision ? 6 : 5 }}" class="text-muted">No champions yet</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
