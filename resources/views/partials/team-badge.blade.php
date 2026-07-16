@props(['team'])
@if($team)
@php
    $shirtType = $team->shirt_type ?: 'gradient';
    $shirtClass = 'shirt-' . str_replace('_', '-', $shirtType);
@endphp
<span class="team-badge {{ $shirtClass }}"
      style="--c1:{{ $team->color_1 ?? '#333' }};--c2:{{ $team->color_2 ?? '#000' }};--c3:{{ $team->color_3 ?? '#fff' }};">
    {{ $team->name }}
</span>
@else
<span class="text-muted">TBD</span>
@endif
