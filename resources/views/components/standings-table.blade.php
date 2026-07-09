@props([
    'title',
    'standings',
    'showResult' => false,
    'highlightTop' => 0,
    'promotionCount' => 0,
    'relegationCount' => 0,
    'division' => null,
])

<div class="card mb-4">
    <div class="card-header"><strong>{{ $title }}</strong></div>
    <div class="card-body table-responsive">
        <table class="table table-sm table-bordered mb-0">
            <thead>
                <tr>
                    <th>#</th><th>Team</th><th>MP</th><th>W</th><th>D</th><th>L</th>
                    <th>GF</th><th>GA</th><th>GD</th><th>Pts</th>
                    @if($showResult)<th>Result</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach($standings as $i => $s)
                @php
                    $pos = $i + 1;
                    $rowClass = '';
                    if ($showResult && $s->position?->result) {
                        $rowClass = match($s->position->result->value ?? $s->position->result) {
                            'champion' => 'table-warning',
                            'promoted' => 'table-success',
                            'relegated' => 'table-danger',
                            default => '',
                        };
                    } elseif ($highlightTop && $pos <= $highlightTop) {
                        $rowClass = 'table-success';
                    } elseif (!$division && $highlightTop && $relegationCount && $pos > $highlightTop) {
                        $rowClass = 'table-danger';
                    } elseif ($promotionCount && $division && $division !== 'division1' && $pos <= $promotionCount) {
                        $rowClass = 'table-success';
                    } elseif ($relegationCount && $division && $division !== 'division3' && $pos > count($standings) - $relegationCount) {
                        $rowClass = 'table-danger';
                    }
                @endphp
                <tr class="{{ $rowClass }}">
                    <td>{{ $pos }}</td>
                    <td>@include('partials.team-badge', ['team' => $s->team])</td>
                    <td>{{ $s->match_played }}</td>
                    <td>{{ $s->win }}</td><td>{{ $s->draw }}</td><td>{{ $s->lose }}</td>
                    <td>{{ $s->goal_scored }}</td><td>{{ $s->goal_conceded }}</td><td>{{ $s->goal_difference }}</td>
                    <td><strong>{{ $s->points }}</strong></td>
                    @if($showResult)
                        <td>{{ $s->position?->result?->displayName() ?? '' }}</td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
