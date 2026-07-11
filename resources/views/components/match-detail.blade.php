@props(['match', 'backUrl' => null])

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Match Detail</span>
        @if($backUrl)
            <a href="{{ $backUrl }}" class="btn btn-sm btn-outline-secondary">← Back</a>
        @endif
    </div>
    <div class="card-body text-center">
        <div class="row align-items-center mb-4">
            <div class="col-4 text-end">
                @include('partials.team-badge', ['team' => $match->team1])
            </div>
            <div class="col-4 text-center">
                @if($match->team1_score !== null && $match->team2_score !== null)
                    <h2 class="mb-0">{{ $match->displayScore() }}</h2>
                @else
                    <span class="text-muted">Not played</span>
                @endif
            </div>
            <div class="col-4 text-start">
                @include('partials.team-badge', ['team' => $match->team2])
            </div>
        </div>

        @if(isset($match->winner) && $match->winner_id)
            <p class="mb-3">Winner: @include('partials.team-badge', ['team' => $match->winner])</p>
        @elseif($match->isPlayed() && method_exists($match, 'getWinnerId') && $match->getWinnerId())
            <p class="mb-3">Winner: @include('partials.team-badge', ['team' => $match->team1_id === $match->getWinnerId() ? $match->team1 : $match->team2])</p>
        @endif

        @if($match->team1_score !== null)
            <div class="row text-start small mb-3">
                <div class="col-6 text-end">
                    <ul class="list-unstyled mb-0">
                        <li><strong>{{ $match->team1->name ?? 'Team 1' }}</strong></li>
                        <li>Possession: {{ $match->team1_possession }}%</li>
                        <li>Fouls: {{ $match->team1_foul }}</li>
                    </ul>
                </div>
                <div class="col-6 text-start">
                    <ul class="list-unstyled mb-0">
                        <li><strong>{{ $match->team2->name ?? 'Team 2' }}</strong></li>
                        <li>Possession: {{ $match->team2_possession }}%</li>
                        <li>Fouls: {{ $match->team2_foul }}</li>
                    </ul>
                </div>
            </div>

            @if(count($match->matchGoals()) > 0)
                <div class="mb-3">
                    <h6>Goals</h6>
                    <div class="row g-3 text-center small">
                        <div class="col-6 text-end">
                            <ul class="list-unstyled mb-0 mt-2">
                                @foreach(collect($match->matchGoals())->filter(fn ($goal) => ($goal['team_id'] ?? null) == $match->team1_id) as $goal)
                                    <li>{{ $goal['minute'] ?? '' }}'{{ isset($goal['type']) && $goal['type'] === 'penalty' ? ' (P)' : (isset($goal['type']) && $goal['type'] === 'freekick' ? ' (F)' : '') }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="col-6 text-start">
                            <ul class="list-unstyled mb-0 mt-2">
                                @foreach(collect($match->matchGoals())->filter(fn ($goal) => ($goal['team_id'] ?? null) == $match->team2_id) as $goal)
                                    <li>{{ $goal['minute'] ?? '' }}'{{ isset($goal['type']) && $goal['type'] === 'penalty' ? ' (P)' : (isset($goal['type']) && $goal['type'] === 'freekick' ? ' (F)' : '') }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            @if($match->decidedByPenalties())
                <div class="text-center">
                    <h6>Penalty shootout ({{ $match->penaltyScoreLabel() }})</h6>
                    <div class="row g-3 small">
                        <div class="col-6 text-end">
                            <span class="d-inline-flex align-items-center gap-1 flex-nowrap">
                                @foreach(collect($match->penaltyKicks())->filter(fn ($kick) => ($kick['team_id'] ?? null) == $match->team1_id) as $kick)
                                    <i class="text-{{ $kick['scored'] ? 'success' : 'danger' }} fa {{ $kick['scored'] ? 'fa-check-square' : 'fa-times-circle' }}"></i>
                                @endforeach
                            </span>
                        </div>
                        <div class="col-6 text-start">
                            <span class="d-inline-flex align-items-center gap-1 flex-nowrap">
                                @foreach(collect($match->penaltyKicks())->filter(fn ($kick) => ($kick['team_id'] ?? null) == $match->team2_id) as $kick)
                                    <i class="text-{{ $kick['scored'] ? 'success' : 'danger' }} fa {{ $kick['scored'] ? 'fa-check-square' : 'fa-times-circle' }}"></i>
                                @endforeach
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
