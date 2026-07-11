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
            <div class="col-5 text-end">
                @include('partials.team-badge', ['team' => $match->team1])
            </div>
            <div class="col-2">
                @if($match->team1_score !== null && $match->team2_score !== null)
                    <h2 class="mb-0">{{ $match->displayScore() }}</h2>
                @else
                    <span class="text-muted">Not played</span>
                @endif
            </div>
            <div class="col-5 text-start">
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
                <div class="col-md-6">
                    <strong>{{ $match->team1->name ?? 'Team 1' }}</strong>
                    <ul class="list-unstyled mb-0">
                        <li>Possession: {{ $match->team1_possession }}%</li>
                        <li>Fouls: {{ $match->team1_foul }}</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <strong>{{ $match->team2->name ?? 'Team 2' }}</strong>
                    <ul class="list-unstyled mb-0">
                        <li>Possession: {{ $match->team2_possession }}%</li>
                        <li>Fouls: {{ $match->team2_foul }}</li>
                    </ul>
                </div>
            </div>

            @if(count($match->matchGoals()) > 0)
                <div class="text-start mb-3">
                    <h6>Goals</h6>
                    <ul class="list-unstyled small mb-0">
                        @foreach($match->matchGoals() as $goal)
                            <li>{{ $goal['label'] }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($match->decidedByPenalties())
                <div class="text-start">
                    <h6>Penalty shootout ({{ $match->penaltyScoreLabel() }})</h6>
                    <table class="table table-sm table-bordered small">
                        <thead>
                            <tr>
                                <th>Round</th>
                                <th>Phase</th>
                                <th>Team</th>
                                <th>Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($match->penaltyKicks() as $kick)
                                <tr>
                                    <td>{{ $kick['round'] }}</td>
                                    <td>{{ str_replace('_', ' ', $kick['phase']) }}</td>
                                    <td>
                                        @if($kick['team_id'] == $match->team1_id)
                                            {{ $match->team1->name }}
                                        @else
                                            {{ $match->team2->name }}
                                        @endif
                                    </td>
                                    <td>{{ $kick['scored'] ? 'Scored' : 'Missed' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endif
    </div>
</div>
