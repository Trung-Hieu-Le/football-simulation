@props(['champion'])

@if($champion)
    <p class="mb-2">
        <strong>Champion:</strong>
        @include('partials.team-badge', ['team' => (object) [
            'name' => $champion->name,
            'color_1' => $champion->color_1 ?? '#000',
            'color_2' => $champion->color_2 ?? '#fff',
            'color_3' => $champion->color_3 ?? null,
            'shirt_type' => $champion->shirt_type ?? null,
        ]])
        @if(isset($champion->points))
            <span class="text-muted small">({{ $champion->points }} pts)</span>
        @endif
    </p>
@else
    <p class="text-muted mb-2"><strong>Champion:</strong> not decided yet</p>
@endif
