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
                    <th>GD</th>
                </tr>
            </thead>
            <tbody>
                @forelse($champions as $c)
                <tr>
                    <td>{{ $c->season }}</td>
                    <td>@include('partials.team-badge', ['team' => (object) [
                            'name' => $c->name,
                            'color_1' => $c->color_1 ?? '#333',
                            'color_2' => $c->color_2 ?? '#000',
                            'color_3' => $c->color_3 ?? '#fff',
                            'shirt_type' => $c->shirt_type ?? null,
                        ]])</td>
                    @if($showDivision)<td>{{ $c->division ?? '' }}</td>@endif
                    <td>{{ $c->points }}</td>
                    <td>{{ $c->goal_scored }}</td>
                    <td>{{ $c->goal_conceded }}</td>
                    <td>{{ $c->goal_scored - $c->goal_conceded }}</td>
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
