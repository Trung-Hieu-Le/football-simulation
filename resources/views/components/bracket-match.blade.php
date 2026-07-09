@props(['match', 'showUrl' => null, 'compact' => false])

<div class="bracket-match {{ $compact ? 'bracket-match--compact' : '' }}">
    <div class="bracket-match__team {{ $match->winner_id === $match->team1_id ? 'bracket-match__team--winner' : '' }}">
        @include('partials.team-badge', ['team' => $match->team1])
    </div>
    <div class="bracket-match__score">
        @if($showUrl && $match->isPlayed())
            <a href="{{ $showUrl }}" class="text-decoration-none text-dark">
                <strong>{{ $match->team1_score ?? '-' }} : {{ $match->team2_score ?? '-' }}</strong>
            </a>
        @elseif($match->team1_id && $match->team2_id)
            <strong>{{ $match->team1_score ?? '-' }} : {{ $match->team2_score ?? '-' }}</strong>
        @else
            <span class="text-muted">vs</span>
        @endif
    </div>
    <div class="bracket-match__team {{ $match->winner_id === $match->team2_id ? 'bracket-match__team--winner' : '' }}">
        @include('partials.team-badge', ['team' => $match->team2])
    </div>
</div>
