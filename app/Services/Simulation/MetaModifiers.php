<?php

namespace App\Services\Simulation;

use App\Enums\SeasonMeta;

class MetaModifiers
{
    public const KEYS = [
        'move_chance',
        'contest',
        'dribble',
        'pressing',
        'tackle',
        'offside',
        'miscontrol',
        'steal_threshold',
        'shot_decision',
        'clutch_goal',
        'counter_chance',
        'counter_distance',
        'foul_chance',
        'stamina_decay',
        'long_ball_skip',
    ];

    /** @var array<string, string> */
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
            $modifiers[$key] = $key === 'long_ball_skip' ? 0.0 : 1.0;
        }

        return $modifiers;
    }

    public static function for(string $meta): array
    {
        $normalized = self::normalize($meta);
        $modifiers = self::defaults();

        $overrides = match ($normalized) {
            SeasonMeta::POSSESSION->value => [
                'move_chance' => 1.20,
                'contest' => 0.88,
                'shot_decision' => 0.75,
                'miscontrol' => 0.80,
                'pressing' => 0.90,
                'steal_threshold' => 1.15,
                'counter_chance' => 0.85,
                'stamina_decay' => 1.05,
            ],
            SeasonMeta::DIRECT->value => [
                'move_chance' => 1.10,
                'shot_decision' => 1.30,
                'dribble' => 0.85,
                'miscontrol' => 1.15,
                'contest' => 0.95,
                'clutch_goal' => 1.15,
            ],
            SeasonMeta::COUNTER_ATTACK->value => [
                'counter_chance' => 1.40,
                'counter_distance' => 1.30,
                'move_chance' => 0.90,
                'shot_decision' => 1.15,
                'pressing' => 1.10,
                'contest' => 1.05,
            ],
            SeasonMeta::HIGH_PRESS->value => [
                'pressing' => 1.40,
                'tackle' => 1.20,
                'foul_chance' => 1.20,
                'contest' => 1.15,
                'stamina_decay' => 1.35,
                'offside' => 1.10,
                'clutch_goal' => 1.10,
            ],
            SeasonMeta::LOW_BLOCK->value => [
                'pressing' => 0.70,
                'tackle' => 1.15,
                'move_chance' => 0.85,
                'contest' => 1.20,
                'shot_decision' => 0.90,
                'counter_chance' => 1.20,
                'foul_chance' => 0.85,
                'stamina_decay' => 0.90,
            ],
            SeasonMeta::LONG_BALL->value => [
                'long_ball_skip' => 0.20,
                'move_chance' => 0.95,
                'miscontrol' => 1.25,
                'contest' => 1.15,
                'shot_decision' => 1.20,
                'offside' => 1.20,
            ],
            SeasonMeta::WING_PLAY->value => [
                'dribble' => 1.35,
                'move_chance' => 1.10,
                'offside' => 0.80,
                'shot_decision' => 1.05,
                'tackle' => 0.95,
            ],
            default => [],
        };

        return array_merge($modifiers, $overrides);
    }

    public static function normalize(string $meta): string
    {
        $meta = strtolower(trim($meta));

        if (isset(self::LEGACY_MAP[$meta])) {
            return self::LEGACY_MAP[$meta];
        }

        foreach (SeasonMeta::values() as $value) {
            if ($meta === $value) {
                return $value;
            }
        }

        return SeasonMeta::BALANCED->value;
    }
}
