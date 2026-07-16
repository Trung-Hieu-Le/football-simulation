# Football Simulation Event Handler Refactoring

**Date:** 2026-07-16  
**Status:** ✅ Completed

## Overview

Refactored the match simulation event handling system from individual micro-event handlers to process-based handlers, improving code organization and making `luck` stat influence multiple special events instead of direct goal scoring.

---

## Architecture Changes

### Before: Micro-Event Approach
- SituationProcessor contained all ball movement logic
- Each micro-event (miscontrol, dribble, etc.) would have separate handlers
- `luck` stat used for direct `rollLuckyGoal()` function

### After: Process-Based Approach
- **BuildUpHandler:** All ball movement + special events (miscontrol, dribble, offside, tackle, pressing)
- **ShotHandler:** All shooting logic + special events (clutchShot, ownGoal)
- **FoulHandler:** All foul logic + threshold calculation
- **SituationProcessor:** Thin orchestrator (Foul → BuildUp → Shot/Counter)
- **RecordsMatchEvents trait:** Shared event recording logic

---

## New Files Created

### 1. `app/Services/Simulation/Concerns/RecordsMatchEvents.php`
**Purpose:** Trait for recording match events

**Methods:**
- `recordGoal(int $team, int $time, array &$matchData, string $type)`: Records goals with types: 'goal', 'penalty', 'freekick', 'own_goal'
- `recordTimelineEvent(int $time, string $event, array &$matchData)`: Records timeline events

**Usage:** Used by ShotHandler, FoulHandler, BuildUpHandler

---

### 2. `app/Services/Simulation/EventHandlers/BuildUpHandler.php`
**Purpose:** Handle all ball movement and build-up phase

**Main Method:**
```php
moveBall(
    int $fieldPosition,
    int $currentTeam,
    array $team1Stats,
    array $team2Stats,
    int $time,
    array &$matchData
): array
```

**Returns:** `['newPosition' => int, 'stolen' => bool, 'event' => string|null]`

**Special Events (luck-influenced):**
1. **Miscontrol** (`rollMiscontrol`)
   - Low control → higher chance
   - Bad luck increases chance
   - Base chance: 3%

2. **Dribble** (`rollDribble`)
   - High pace (>70) + creative → move +2 positions
   - Good luck increases chance
   - Base chance: 5%

3. **Offside** (`rollOffside`)
   - Only in attacking third (positions 8-10 for team1, 0-2 for team2)
   - Low discipline → higher chance
   - Bad luck increases chance
   - Ball returns to midfield (position 5)
   - Base chance: 4%

4. **Tackle** (`rollTackle`)
   - High defense → higher chance
   - Good luck increases chance
   - Base chance: 8%

5. **Pressing Steal** (`rollPressingSteal`)
   - High pace + discipline → higher chance
   - Good luck increases chance
   - Base chance: 6%

**Normal Ball Movement:**
- Build-up power: `control * 2.0 + creative * 1.0 + stamina * 0.6`
- Stop progress: `defense * 2.2 + discipline * 0.5`
- Zone difficulty applied
- Move distance: 1 (normal) or 2 (fast dribble)

---

### 3. Modified: `app/Services/Simulation/EventHandlers/ShotHandler.php`

**Changes:**
- Added `use RecordsMatchEvents` trait
- Removed `rollLuckyGoal()` method
- Removed old `recordGoal()` method (now in trait)
- Updated all `recordGoal()` calls to use new signature with type parameter

**New Special Events:**

1. **Clutch Shot** (`rollClutchShot`)
   - Only in last 5 minutes: 85-90, 115-120
   - High mental → higher chance
   - Good luck increases chance
   - Effect: +30% goal chance boost (capped at 95%)
   - Base chance: 3%

2. **Own Goal** (`tryOwnGoal`)
   - Only in penalty areas (positions 1, 9)
   - Low discipline → higher chance
   - Bad luck increases chance
   - Rare event
   - Base chance: 1%

**Integration:**
- Clutch shot checked before goal chance calculation
- Own goal checked at start of shot handling
- Both use `specialEventChance(luck)` for luck influence

---

### 4. Modified: `app/Services/Simulation/EventHandlers/FoulHandler.php`

**Changes:**
- Added `use RecordsMatchEvents` trait
- Moved `calculateFoulThreshold()` from SituationProcessor

**New Method:**
```php
public function calculateFoulThreshold(float $discipline): float
```
- Discipline reduces foul chance
- Returns threshold clamped between FOUL_CHANCE_MIN (3) and FOUL_CHANCE_MAX (12)

---

### 5. Refactored: `app/Services/Simulation/SituationProcessor.php`

**New Role:** Thin orchestrator (no complex logic)

**Flow:**
1. Check foul → `FoulHandler::handleFoul()`
2. Move ball → `BuildUpHandler::moveBall()`
3. If stolen → `CounterAttackHandler::attemptCounterAttack()`
4. If in shooting position → shot decision → `ShotHandler::handleShot()`
5. Else → continue possession

**Removed:**
- `moveBall()` logic (moved to BuildUpHandler)
- `calculateFoulThreshold()` (moved to FoulHandler)
- `getZoneDifficulty()` (moved to BuildUpHandler)

**Injected Handlers:**
- BuildUpHandler
- ShotHandler
- FoulHandler
- CounterAttackHandler

---

## Weight Adjustments

### `app/Constants/StatsWeights.php`

**Increased secondary stat weights for more noticeable effects:**

| Weight | Old Value | New Value | Change |
|--------|-----------|-----------|--------|
| `BUILD_UP_CREATIVE_WEIGHT` | 0.8 | 1.0 | +25% |
| `STOP_DISCIPLINE_WEIGHT` | 0.4 | 0.5 | +25% |
| `SHOT_POWER_MENTAL_WEIGHT` | 0.4 | 0.5 | +25% |
| `SAVE_POWER_MENTAL_WEIGHT` | 0.3 | 0.4 | +33% |

### `app/Constants/SimulationConstants.php`

| Constant | Old Value | New Value | Change |
|----------|-----------|-----------|--------|
| `SHOOT_DECISION_MENTAL_MULTIPLIER` | 0.06 | 0.08 | +33% |

**Reasoning:** Secondary stats now have more noticeable effects while primary stats remain dominant.

---

## Tests Created

### 1. `tests/Unit/BuildUpHandlerTest.php`

**Tests:**
- `test_moveball_returns_expected_structure()`: Verifies return structure
- `test_moveball_position_stays_within_bounds()`: Ensures positions stay 0-10
- `test_high_luck_increases_special_events()`: Verifies luck influence

### 2. `tests/Unit/ShotHandlerTest.php`

**Tests:**
- `test_clutch_shot_only_triggers_in_last_minutes()`: Verifies timing
- `test_high_mental_increases_clutch_shot_chance()`: Verifies mental influence
- `test_own_goal_only_in_penalty_areas()`: Verifies position restriction
- `test_low_discipline_increases_own_goal_chance()`: Verifies discipline influence
- `test_handle_shot_returns_expected_structure()`: Verifies return structure

**Note:** Tests require PHP 8.2+ (sandbox has 7.4). Run locally with: `php artisan test`

---

## Luck Integration

**Old Behavior:**
- `luck` → `rollLuckyGoal()` → direct goal (rare)

**New Behavior:**
- `luck` influences multiple special events via `specialEventChance(luck)`:
  - Miscontrol (bad luck)
  - Dribble (good luck)
  - Offside (bad luck)
  - Tackle (good luck)
  - Pressing steal (good luck)
  - Clutch shot (good luck)
  - Own goal (bad luck)

**Formula:** `chance = baseChance ± (luckModifier * specialEventChance(luck))`

**Benefits:**
- More realistic luck influence
- Luck affects gameplay throughout match, not just goals
- Better balance with other stats

---

## Match Data Structure

**Enhanced `matchData` array now includes:**

```php
[
    'goals' => [
        [
            'minute' => int,
            'team_id' => int,
            'type' => 'goal'|'penalty'|'freekick'|'own_goal',
            'label' => "45' Team Name (P/F/OG)"
        ],
        ...
    ],
    'specialEvents' => [
        "45': GOAL by Team1!",
        "67': Clutch shot by Team1!",
        "78': Miscontrol by Team2",
        "82': Offside by Team1",
        ...
    ],
    // ... existing stats (scores, shots, fouls, possession)
]
```

---

## Benefits of Refactoring

### 1. **Better Code Organization**
- Clear separation of concerns: BuildUp, Shot, Foul, Counter
- SituationProcessor is now a simple orchestrator
- Easier to understand flow

### 2. **Improved Maintainability**
- Each handler responsible for one process
- RecordsMatchEvents trait reduces duplication
- Special events grouped with their related process

### 3. **More Realistic Simulation**
- Luck influences many events, not just goals
- Special events add variety (miscontrol, dribble, offside, etc.)
- Secondary stats have noticeable effects

### 4. **Better Testability**
- Each handler can be tested independently
- Special events have clear test cases
- Easier to verify stat influences

### 5. **Extensibility**
- Easy to add new special events to existing handlers
- Clear pattern for luck-influenced events
- Simple to adjust weights and probabilities

---

## Migration Guide

### For Developers Using This Simulation:

1. **No API Changes:** `SituationProcessor::processSituation()` signature unchanged
2. **New Event Types:** Check for new event types in `matchData['specialEvents']`
3. **Own Goals:** Check for `type: 'own_goal'` in `matchData['goals']`
4. **Timeline Events:** Display `matchData['specialEvents']` for match commentary

### Testing Locally:

```bash
cd /home/hieult/Documents/personal/football-simulation-0

# Run all tests
php artisan test

# Run specific handler tests
php artisan test --filter=BuildUpHandlerTest
php artisan test --filter=ShotHandlerTest

# Check for syntax errors
php -l app/Services/Simulation/EventHandlers/*.php
php -l app/Services/Simulation/Concerns/*.php
```

---

## Future Enhancements

### Potential Additions:

1. **More Special Events:**
   - Injury (rare, stamina-influenced)
   - Weather influence (if Weather enum re-added)
   - Set piece variations (corners, throw-ins)

2. **Event Chains:**
   - Successful dribble → higher shot chance
   - Miscontrol → counter attack bonus
   - Tackle → momentum shift

3. **Player-Level Events:**
   - Individual player performance tracking
   - Man of the match calculations
   - Player ratings based on events

4. **Advanced Luck Mechanics:**
   - Momentum system (lucky events increase momentum)
   - Clutch time multiplier (luck more effective in last 10 minutes)
   - Bad luck streaks (multiple miscontrols increase next control check)

---

## Files Modified Summary

### Created (4 files):
1. `app/Services/Simulation/Concerns/RecordsMatchEvents.php`
2. `app/Services/Simulation/EventHandlers/BuildUpHandler.php`
3. `tests/Unit/BuildUpHandlerTest.php`
4. `tests/Unit/ShotHandlerTest.php`

### Modified (5 files):
1. `app/Services/Simulation/SituationProcessor.php` (refactored to thin orchestrator)
2. `app/Services/Simulation/EventHandlers/ShotHandler.php` (added clutch/own goal, removed rollLuckyGoal)
3. `app/Services/Simulation/EventHandlers/FoulHandler.php` (added calculateFoulThreshold)
4. `app/Constants/StatsWeights.php` (rebalanced secondary stats)
5. `app/Constants/SimulationConstants.php` (adjusted mental multiplier)

### Documentation:
1. `PLAN.md` (updated with refactoring details)
2. `_REFACTORING_SUMMARY.md` (this file)

---

## Checklist

- [x] Create RecordsMatchEvents trait
- [x] Refactor ShotHandler to use trait
- [x] Remove rollLuckyGoal
- [x] Create BuildUpHandler with special events
- [x] Expand ShotHandler with clutchShot and ownGoal
- [x] Move calculateFoulThreshold to FoulHandler
- [x] Refactor SituationProcessor to thin orchestrator
- [x] Rebalance secondary stats weights
- [x] Create unit tests for BuildUpHandler
- [x] Create unit tests for ShotHandler
- [x] Update PLAN.md
- [x] Create refactoring summary document

---

**All tasks completed successfully!** ✅

The simulation now has a cleaner architecture with process-based handlers, luck influences multiple realistic events, and secondary stats have more noticeable effects while primary stats remain dominant.
