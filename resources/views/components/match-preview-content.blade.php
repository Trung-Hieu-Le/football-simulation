@props(['match'])

@php
    $lines = [];
    if ($match->team1_score !== null) {
        $lines[] = 'Possession: ' . ($match->team1->name ?? 'T1') . ' ' . $match->team1_possession . '% · '
            . ($match->team2->name ?? 'T2') . ' ' . $match->team2_possession . '%';
        $lines[] = 'Fouls: ' . $match->team1_foul . ' – ' . $match->team2_foul;
    }
    foreach ($match->matchGoals() as $goal) {
        $lines[] = e($goal['label'] ?? '');
    }
    if ($match->decidedByPenalties()) {
        $lines[] = 'Penalties: ' . e($match->penaltyScoreLabel() ?? '');
        foreach ($match->penaltyKicks() as $kick) {
            $teamName = $kick['team_id'] == $match->team1_id
                ? ($match->team1->name ?? 'T1')
                : ($match->team2->name ?? 'T2');
            $lines[] = 'R' . $kick['round'] . ' ' . e($teamName) . ': ' . ($kick['scored'] ? '✓' : '✗');
        }
    }
    $content = $lines ? implode('<br>', $lines) : 'No details yet';
@endphp

{!! $content !!}
