@props(['team'])
@if($team)
<!-- color is color_3 -->
<span class="team-badge" style="background: linear-gradient(135deg, {{ $team->color_1 ?? '#333' }}, {{ $team->color_2 ?? '#000' }});
    color: {{ $team->color_3 ?? '#fff' }}; white-space: nowrap; border: 1px solid #ccc;
    padding: 2px 6px; border-radius: 4px;
    width: 90px; display: inline-block;
    text-shadow: 1px 1px 2px rgba(0,0,0);"
>
    {{ $team->name }}
</span>
@else
<span class="text-muted">TBD</span>
@endif
