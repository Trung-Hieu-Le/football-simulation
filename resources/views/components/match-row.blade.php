@props([
    'match',
    'showUrl' => null,
    'knockout' => false,
])

<div class="d-flex justify-content-between align-items-center border-bottom py-2">
    <span>@include('partials.team-badge', ['team' => $match->team1])</span>
    <span class="text-center">
        @if($knockout)
            @if($match->isPlayed())
                @if($showUrl)
                    <a href="{{ $showUrl }}" class="text-decoration-none">
                        <strong>{{ $match->team1_score }} : {{ $match->team2_score }}</strong>
                    </a>
                @else
                    <strong>{{ $match->team1_score }} : {{ $match->team2_score }}</strong>
                @endif
                <span class="mx-1">→</span>
                @include('partials.team-badge', ['team' => $match->winner])
            @else
                <span class="text-muted">vs</span>
            @endif
        @elseif($showUrl)
            <a href="{{ $showUrl }}" class="text-decoration-none">
                <strong>{{ $match->team1_score ?? '-' }} : {{ $match->team2_score ?? '-' }}</strong>
            </a>
        @else
            <strong>{{ $match->team1_score ?? '-' }} : {{ $match->team2_score ?? '-' }}</strong>
        @endif
    </span>
    <span>@include('partials.team-badge', ['team' => $match->team2])</span>
</div>
