<?php

namespace Tests\Unit;

use App\Enums\SeasonMeta;
use App\Services\Simulation\MetaModifiers;
use Tests\TestCase;

class MetaModifiersTest extends TestCase
{
    public function test_balanced_returns_all_defaults(): void
    {
        $modifiers = MetaModifiers::for(SeasonMeta::BALANCED->value);

        foreach (MetaModifiers::KEYS as $key) {
            $this->assertArrayHasKey($key, $modifiers);
        }

        $this->assertSame(1.0, $modifiers['move_chance']);
        $this->assertSame(1.0, $modifiers['contest']);
        $this->assertSame(0.0, $modifiers['long_ball_skip']);
    }

    public function test_unknown_meta_falls_back_to_balanced(): void
    {
        $modifiers = MetaModifiers::for('tiki-taka');

        $this->assertSame(1.20, MetaModifiers::for(SeasonMeta::POSSESSION->value)['move_chance']);
        $this->assertSame(1.20, $modifiers['move_chance']);
    }

    public function test_high_press_has_higher_pressing_than_low_block(): void
    {
        $highPress = MetaModifiers::for(SeasonMeta::HIGH_PRESS->value);
        $lowBlock = MetaModifiers::for(SeasonMeta::LOW_BLOCK->value);

        $this->assertGreaterThan($lowBlock['pressing'], $highPress['pressing']);
        $this->assertGreaterThan($lowBlock['stamina_decay'], $highPress['stamina_decay']);
    }

    public function test_long_ball_has_skip_chance(): void
    {
        $modifiers = MetaModifiers::for(SeasonMeta::LONG_BALL->value);

        $this->assertSame(0.20, $modifiers['long_ball_skip']);
        $this->assertGreaterThan(1.0, $modifiers['miscontrol']);
    }

    public function test_legacy_attack_maps_to_balanced(): void
    {
        $modifiers = MetaModifiers::for('attack');

        $this->assertSame(1.0, $modifiers['move_chance']);
    }
}
