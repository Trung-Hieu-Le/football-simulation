@props(['match', 'showUrl' => null, 'compact' => false])

<div class="bracket-match {{ $compact ? 'bracket-match--compact' : '' }}">
    <div class="bracket-match__teams d-flex align-items-center justify-content-center gap-2 flex-wrap">
        <span class="bracket-match__team text-end {{ $match->winner_id === $match->team1_id ? 'bracket-match__team--winner' : '' }}">
            @include('partials.team-badge', ['team' => $match->team1])
        </span>
        <span class="text-muted small">VS</span>
        <span class="bracket-match__team text-start {{ $match->winner_id === $match->team2_id ? 'bracket-match__team--winner' : '' }}">
            @include('partials.team-badge', ['team' => $match->team2])
        </span>
    </div>
    <div class="bracket-match__score text-center mt-1">
        @if($match->isPlayed())
            <x-match-score-trigger :match="$match" :show-url="$showUrl" :knockout="true" />
        @else
            <span class="text-muted">---</span>
        @endif
    </div>
</div>
