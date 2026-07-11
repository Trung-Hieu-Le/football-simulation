@props(['match', 'showUrl' => null, 'compact' => false])

<div class="bracket-match {{ $compact ? 'bracket-match--compact' : '' }}">
    <div class="bracket-match__team {{ $match->winner_id === $match->team1_id ? 'bracket-match__team--winner' : '' }}">
        {{ $match->team1->name ?? 'TBD' }}
    </div>
    <div class="bracket-match__score text-center">
        @if($match->isPlayed())
            <x-match-score-trigger :match="$match" :show-url="$showUrl" :knockout="true" />
        @else
            <span class="text-muted">vs</span>
        @endif
    </div>
    <div class="bracket-match__team {{ $match->winner_id === $match->team2_id ? 'bracket-match__team--winner' : '' }}">
        {{ $match->team2->name ?? 'TBD' }}
    </div>
</div>
