@props([
    'match',
    'showUrl' => null,
    'knockout' => false,
])

@php
    $played = $knockout ? $match->isPlayed() : $match->isPlayed();
    $scoreText = $played ? $match->displayScore() : ($knockout ? 'vs' : '- : -');
    $preview = view('components.match-preview-content', ['match' => $match])->render();
@endphp

<span class="match-score-trigger d-inline-block"
      tabindex="0"
      role="button"
      data-bs-title="Match detail"
      @if($showUrl) data-detail-url="{{ $showUrl }}" @endif>
    <template class="match-preview-source">{!! $preview !!}</template>
    @if($showUrl && $played)
        <a href="{{ $showUrl }}" class="text-decoration-none match-score-link">
            <strong>{{ $scoreText }}</strong>
        </a>
    @else
        <strong>{{ $scoreText }}</strong>
    @endif
</span>
