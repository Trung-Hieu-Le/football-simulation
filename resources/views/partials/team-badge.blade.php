@props(['team'])
@if($team)
<span class="team-badge" style="background: linear-gradient(135deg, {{ $team->color_1 ?? '#333' }}, {{ $team->color_2 ?? '#666' }}); padding: 2px 8px; border-radius: 4px; color: #fff; white-space: nowrap; border: 1px solid #ccc; text-shadow: 1px 1px 2px rgba(0,0,0);">
    {{ $team->name }}
</span>
@else
<span class="text-muted">TBD</span>
@endif
