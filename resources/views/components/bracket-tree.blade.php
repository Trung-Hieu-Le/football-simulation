@props(['matches', 'season', 'roundOrder'])

@php
    $roundLabels = [
        'round_of_16' => 'Round of 16',
        'round_of_8' => 'Round of 8',
        'quarter_finals' => 'Quarter-finals',
        'semi_finals' => 'Semi-finals',
        'third_place' => 'Third place',
        'final' => 'Final',
    ];
    $roundHeights = [
        'round_of_16' => 16,
        'round_of_8' => 8,
        'quarter_finals' => 4,
        'semi_finals' => 2,
        'third_place' => 1,
        'final' => 1,
    ];
@endphp

<div class="bracket-tree mb-4">
    <div class="bracket-tree__grid">
        @foreach(['round_of_16', 'round_of_8', 'quarter_finals', 'semi_finals'] as $round)
            @php $roundMatches = $matches->get($round, collect()); @endphp
            <div class="bracket-tree__column" style="--rows: {{ $roundHeights[$round] }}">
                <div class="bracket-tree__column-title">{{ $roundLabels[$round] }}</div>
                <div class="bracket-tree__slots">
                    @for($slot = 0; $slot < $roundHeights[$round]; $slot++)
                        @php $match = $roundMatches->firstWhere('slot_index', $slot); @endphp
                        <div class="bracket-tree__slot">
                            @if($match)
                                <x-bracket-match
                                    :match="$match"
                                    :show-url="route('cup.eliminate.show', $match->id)"
                                    compact
                                />
                            @else
                                <div class="bracket-match bracket-match--empty text-muted small">TBD</div>
                            @endif
                        </div>
                    @endfor
                </div>
                @if($roundMatches->isNotEmpty())
                    <form action="{{ route('cup.eliminate.simulate-round', [$season->id, $round]) }}" method="POST" class="text-center mt-2">
                        @csrf
                        <button class="btn btn-sm btn-warning">Simulate {{ $roundLabels[$round] }}</button>
                    </form>
                @endif
            </div>
        @endforeach

        <div class="bracket-tree__column bracket-tree__column--center" style="--rows: 4">
            <div class="bracket-tree__column-title">Podium</div>
            <div class="bracket-tree__slots bracket-tree__slots--center">
                @php
                    $third = $matches->get('third_place', collect())->firstWhere('slot_index', 0);
                    $final = $matches->get('final', collect())->firstWhere('slot_index', 0);
                @endphp
                <div class="bracket-tree__slot bracket-tree__slot--third">
                    <div class="small fw-bold text-muted mb-1">🥉 Third place</div>
                    @if($third)
                        <x-bracket-match :match="$third" :show-url="route('cup.eliminate.show', $third->id)" compact />
                        <form action="{{ route('cup.eliminate.simulate-round', [$season->id, 'third_place']) }}" method="POST" class="mt-1 text-center">
                            @csrf
                            <button class="btn btn-sm btn-outline-secondary" @disabled($third->isPlayed() || !$third->team1_id)>Simulate</button>
                        </form>
                    @else
                        <div class="bracket-match bracket-match--empty text-muted small">After semi-finals</div>
                    @endif
                </div>
                <div class="bracket-tree__slot bracket-tree__slot--final">
                    <div class="small fw-bold text-muted mb-1">🏆 Final</div>
                    @if($final)
                        <x-bracket-match :match="$final" :show-url="route('cup.eliminate.show', $final->id)" compact />
                        <form action="{{ route('cup.eliminate.simulate-round', [$season->id, 'final']) }}" method="POST" class="mt-1 text-center">
                            @csrf
                            <button class="btn btn-sm btn-success" @disabled($final->isPlayed() || !$final->team1_id)>Simulate</button>
                        </form>
                    @else
                        <div class="bracket-match bracket-match--empty text-muted small">After semi-finals</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@once
@push('styles')
<style>
.bracket-tree { overflow-x: auto; }
.bracket-tree__grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(180px, 1fr));
    gap: 1rem;
    min-width: 1100px;
}
.bracket-tree__column-title {
    font-weight: 600;
    text-align: center;
    margin-bottom: .75rem;
    padding-bottom: .25rem;
    border-bottom: 2px solid #dee2e6;
}
.bracket-tree__slots {
    display: grid;
    grid-template-rows: repeat(var(--rows), 1fr);
    gap: .35rem;
    min-height: calc(var(--rows) * 52px);
}
.bracket-tree__slots--center {
    grid-template-rows: 1fr 1fr;
    align-content: center;
    min-height: 280px;
}
.bracket-tree__slot {
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.bracket-tree__slot--third { align-self: end; }
.bracket-tree__slot--final { align-self: start; }
.bracket-match {
    border: 1px solid #dee2e6;
    border-radius: .375rem;
    padding: .35rem .5rem;
    background: #fff;
    font-size: .85rem;
}
.bracket-match--empty {
    min-height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
}
.bracket-match__team { margin: .1rem 0; }
.bracket-match__team--winner { font-weight: 700; }
.bracket-match__score { text-align: center; font-size: .8rem; }
</style>
@endpush
@endonce
