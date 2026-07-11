@props(['title', 'items', 'valueKey' => 'goal_scored', 'suffix' => ''])

<div class="card mb-3">
    <div class="card-header">{{ $title }}</div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>#</th><th>Team</th><th>Value</th></tr></thead>
            <tbody>
                @forelse($items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>@include('partials.team-badge', ['team' => $item->team])</td>
                    <td><strong>{{ is_float(data_get($item, $valueKey)) ? number_format(data_get($item, $valueKey), 2) : data_get($item, $valueKey) }}{{ $suffix }}</strong></td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-muted text-center">No data yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
