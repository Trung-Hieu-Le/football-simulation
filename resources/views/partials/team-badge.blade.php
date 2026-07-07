@props(['team'])
@if($team)
<span class="team-badge" style="background: linear-gradient(135deg, {{ $team->color_1 ?? '#333' }}, {{ $team->color_2 ?? '#666' }}); padding: 2px 8px; border-radius: 4px; color: #fff; white-space: nowrap;">
    {{ $team->name }}
</span>
@else
<span class="text-muted">TBD</span>
@endif
