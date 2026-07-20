<?php

namespace App\Services\Simulation;

use App\Enums\SeasonMeta;

class MetaModifiers
{
    public const KEYS = [
        'move_chance',
        'retain_chance',
        'pressing',
        'miscontrol',
        'contest',
        'pace_boost',
        'offside',
        'aerial',
        'foul_chance',
        'shot_decision',
        'clutch_goal',
        'counter_chance',
        'counter_distance',
        'stamina_decay',
        'long_ball_skip',
    ];

    /** @var array<string, string> Legacy meta mapping */
    protected const LEGACY_MAP = [
        'tiki-taka' => 'possession',
        'build_up' => 'possession',
        'pressing' => 'high_press',
        'counter' => 'counter_attack',
        'high_risk' => 'direct',
        'high_line' => 'high_press',
        'attack' => 'balanced',
    ];

    public static function defaults(): array
    {
        $modifiers = [];
        foreach (self::KEYS as $key) {
            $modifiers[$key] = ($key === 'long_ball_skip') ? 0.0 : 1.0;
        }

        return $modifiers;
    }

    public static function for(string $meta): array
    {
        $normalized = self::normalize($meta);
        $modifiers = self::defaults();

        $overrides = match ($normalized) {
            SeasonMeta::POSSESSION->value => [
                'move_chance' => 1.15,
                'retain_chance' => 1.25,
                'pressing' => 0.75,      // Less vulnerable to press
                'miscontrol' => 0.85,
                'contest' => 0.88,
                'shot_decision' => 0.70,
                'counter_chance' => 0.80,
                'stamina_decay' => 1.05,
            ],
            SeasonMeta::DIRECT->value => [
                'move_chance' => 1.05,
                'retain_chance' => 0.80,
                'pace_boost' => 1.40,
                'shot_decision' => 1.30,
                'miscontrol' => 1.15,
                'contest' => 0.95,
                'clutch_goal' => 1.15,
            ],
            SeasonMeta::COUNTER_ATTACK->value => [
                'counter_chance' => 1.50,
                'counter_distance' => 1.35,
                'move_chance' => 0.85,
                'retain_chance' => 0.90,
                'shot_decision' => 1.15,
                'pressing' => 1.15,      // Press after losing ball high
                'contest' => 1.05,
            ],
            SeasonMeta::HIGH_PRESS->value => [
                'pressing' => 1.50,      // Press much more
                'contest' => 1.15,
                'foul_chance' => 1.25,
                'move_chance' => 0.95,
                'stamina_decay' => 1.30, // High fatigue
            ],
            SeasonMeta::LOW_BLOCK->value => [
                'pressing' => 0.60,      // Midfield press low
                'move_chance' => 0.80,
                'contest' => 1.25,       // Deep contests
                'counter_chance' => 1.20,
                'shot_decision' => 0.85,
            ],
            SeasonMeta::LONG_BALL->value => [
                'long_ball_skip' => 0.20,  // +20% long ball chance; +3 when creative high
                'move_chance' => 0.90,
                'retain_chance' => 0.80,   // Harder to keep after long ball
                'miscontrol' => 1.20,
                'offside' => 1.25,
                'aerial' => 1.10,          // Slight aerial (not main like wing)
            ],
            SeasonMeta::WING_PLAY->value => [
                'aerial' => 1.35,
                'pressing' => 0.70,
                'pace_boost' => 1.15,
                'offside' => 0.85,
                'retain_chance' => 1.05,
            ],
            SeasonMeta::BALANCED->value => [
                // All 1.0 (defaults)
            ],
            default => []
        };

        foreach ($overrides as $key => $value) {
            $modifiers[$key] = $value;
        }

        return $modifiers;
    }

    protected static function normalize(string $meta): string
    {
        $lower = strtolower(trim($meta));
        return self::LEGACY_MAP[$lower] ?? $lower;
    }
}
