@props(['match'])

@php
    $goalLines = [];
    $team1Goals = collect($match->matchGoals())->filter(fn ($goal) => ($goal['team_id'] ?? null) == $match->team1_id)->values();
    $team2Goals = collect($match->matchGoals())->filter(fn ($goal) => ($goal['team_id'] ?? null) == $match->team2_id)->values();

    $lines = [];
    if ($match->team1_score !== null) {
        $lines[] = '<div class="row g-2 mt-1">'
            . '<div class="col-6 text-end">'
            . '<div><strong>' . e($match->team1->name ?? 'Team 1') . '</strong></div>'
            . '<div>Possession: ' . $match->team1_possession . '%</div>'
            . '<div>Fouls: ' . $match->team1_foul . '</div>'
            . '</div>'
            . '<div class="col-6 text-start">'
            . '<div><strong>' . e($match->team2->name ?? 'Team 2') . '</strong></div>'
            . '<div>Possession: ' . $match->team2_possession . '%</div>'
            . '<div>Fouls: ' . $match->team2_foul . '</div>'
            . '</div>'
            . '</div>';
    }

    if ($team1Goals->isNotEmpty() || $team2Goals->isNotEmpty()) {
        $lines[] = '<div class="row g-2 mt-1">'
            . '<div class="col-6 text-end">'
            . '<strong>Goals</strong><br>'
            . $team1Goals->map(fn ($goal) => e(($goal['minute'] ?? '') . "'" . (isset($goal['type']) && $goal['type'] === 'penalty' ? ' (P)' : (isset($goal['type']) && $goal['type'] === 'freekick' ? ' (F)' : ''))))->implode('<br>')
            . '</div>'
            . '<div class="col-6 text-start">'
            . '<strong>Goals</strong><br>'
            . $team2Goals->map(fn ($goal) => e(($goal['minute'] ?? '') . "'" . (isset($goal['type']) && $goal['type'] === 'penalty' ? ' (P)' : (isset($goal['type']) && $goal['type'] === 'freekick' ? ' (F)' : ''))))->implode('<br>')
            . '</div>'
            . '</div>';
    }

    if ($match->decidedByPenalties()) {
        $lines[] = '<div class="row g-2 mt-1">'
            . '<div class="col-6 text-end">'
            . '<strong>Penalties</strong><br>'
            . '<span class="d-inline-flex align-items-center gap-1 flex-nowrap">'
            . collect($match->penaltyKicks())->filter(fn ($kick) => ($kick['team_id'] ?? null) == $match->team1_id)
                ->map(fn ($kick) => '<i class="text-' . ($kick['scored'] ? 'success' : 'danger') . ' fa ' . ($kick['scored'] ? 'fa-check-square' : 'fa-times-circle') . '"></i>')
                ->implode(' ')
            . '</span>'
            . '</div>'
            . '<div class="col-6 text-start">'
            . '<strong>Penalties</strong><br>'
            . '<span class="d-inline-flex align-items-center gap-1 flex-nowrap">'
            . collect($match->penaltyKicks())->filter(fn ($kick) => ($kick['team_id'] ?? null) == $match->team2_id)
                ->map(fn ($kick) => '<i class="text-' . ($kick['scored'] ? 'success' : 'danger') . ' fa ' . ($kick['scored'] ? 'fa-check-square' : 'fa-times-circle') . '"></i>')
                ->implode(' ')
            . '</span>'
            . '</div>'
            . '</div>';
    }
    $content = $lines ? implode('<br>', $lines) : 'No details yet';
@endphp

{!! $content !!}
