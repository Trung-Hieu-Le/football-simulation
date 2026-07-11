@props([
    'match',
    'showUrl' => null,
    'knockout' => false,
])

<div class="match-row-grid py-2 border-bottom">
    <div class="match-row-grid__team match-row-grid__team--left text-end">
        @include('partials.team-badge', ['team' => $match->team1])
    </div>
    <div class="match-row-grid__score text-center">
        <x-match-score-trigger :match="$match" :show-url="$showUrl" :knockout="$knockout" />
        @if($knockout && $match->isPlayed())
            <div class="small text-muted mt-1">
                → @include('partials.team-badge', ['team' => $match->winner])
            </div>
        @endif
    </div>
    <div class="match-row-grid__team match-row-grid__team--right text-start">
        @include('partials.team-badge', ['team' => $match->team2])
    </div>
</div>

@once
@push('styles')
<style>
.match-row-grid {
    display: grid;
    grid-template-columns: 1fr minmax(100px, auto) 1fr;
    align-items: center;
    gap: .5rem;
}
.match-row-grid__score { min-width: 100px; }
.match-score-trigger { cursor: pointer; }
</style>
@endpush
@endonce
