# Testing Checklist

> **Note:** Refactor hoàn thành theo PLAN.md. Testing checklist này giúp verify toàn bộ chức năng.

## Setup

```bash
# 1. Chạy migrations mới
php artisan migrate:fresh

# 2. Seed data
php artisan db:seed

# 3. Verify
- 4 regions
- 64 teams với ELO = 1000
```

## Phase 1: Basic Functionality

### Teams Management ✅
- [ ] Truy cập `/teams`
- [ ] View danh sách 64 teams
- [ ] Edit team stats (attack, defense, control, creative, pace, mental, physical, luck, stamina, goalkeeping)
- [ ] Verify ELO default = 1000
- [ ] Form default = 50
- [ ] Test "Reset All ELO" button → all teams ELO = 1000
- [ ] Test "Reset All Form" button → all teams form = 50

### Home Page ✅
- [ ] Truy cập `/`
- [ ] View top 10 ELO teams
- [ ] Links to League/Cup work
- [ ] Quick actions functional

## Phase 2: League Mode

### Create Season ✅
- [ ] Truy cập `/league/seasons/create`
- [ ] Select 12, 24, 36, 48, hoặc 60 teams (divisible by 12)
- [ ] Select meta (possession, counter, pressing, etc.)
- [ ] Create season → verify:
  - 3 divisions created
  - Teams distributed evenly
  - Matches generated (round-robin)
  - Standings initialized

### Simulate Matches ✅
- [ ] View season `/league/seasons/{id}`
- [ ] Click "Simulate All Matches"
- [ ] Verify:
  - Match scores populated
  - Standings updated (points, goals, possession, fouls)
  - Team forms updated (±5)
  - **ELO ratings updated** (winner +points, loser -points)

### Division Results ✅
- [ ] After all matches simulated
- [ ] Click "Calculate Results"
- [ ] Verify positions table:
  - Champion (position 1 in each division)
  - Promoted (top 25% in division 2 & 3)
  - Relegated (bottom 25% in division 1 & 2)
  - Stay (middle 50%)

### Statistics ✅
- [ ] View `/league/seasons/{id}/statistics`
- [ ] Check:
  - Top scorers
  - Top possession
  - Most fouls
  - Best defense
- [ ] View `/league/statistics/all-time`
- [ ] Check:
  - All-time champions
  - Top 20 ELO teams
  - Most wins

## Phase 3: Cup Mode

### Create Season ✅
- [ ] Truy cập `/cup/seasons/create`
- [ ] Select 32 or 64 teams
- [ ] Verify **Pot Seeding**:
  - Teams sorted by ELO
  - Divided into 4 pots (8 or 16 teams each)
  - Groups drawn (A-H for 32, A-P for 64)
  - Each group has 1 team from each pot

### Group Stage ✅
- [ ] View season `/cup/seasons/{id}`
- [ ] Groups displayed (4 teams each)
- [ ] Simulate group matches
- [ ] Verify standings:
  - Points, goal difference
  - ELO updates after each match
  - Top 2 teams per group advance

### Knockout Stage ✅
- [ ] Click "Advance to Knockout"
- [ ] Verify `/cup/seasons/{id}/eliminate`:
  - Round of 16 matches created
  - Teams paired according to **BRANCHES formula** (preserved from SeasonCupController:430-435)
- [ ] Simulate Round of 16
- [ ] Verify **Auto-update**:
  - Winners advance to Quarter Finals
  - Bracket auto-populated
- [ ] Continue simulating:
  - Quarter Finals → Semi Finals
  - Semi Finals → Final + 3rd Place
- [ ] Verify final results:
  - Champion
  - Runner-up
  - 3rd place
  - 4th place
- [ ] All positions updated in `cup_positions` table

### Extra Time & Penalties ✅
- [ ] In knockout stage, if match ends in draw:
  - Extra time (30 min) simulated
  - If still draw → Penalty shootout
  - Winner determined

## Phase 4: Simulation Quality

### 10 Stats Integration ✅
- [ ] Create teams with different stat profiles:
  - High attack, low defense
  - High control, low pace
  - Balanced
- [ ] Simulate matches
- [ ] Verify results reflect team profiles:
  - High attack team scores more
  - High defense team concedes less
  - High control team has higher possession

### Match Events ✅
- [ ] View match details (if view created)
- [ ] Check `specialEvents` logged:
  - Goals (normal, penalty, free kick)
  - Fouls
  - Possession percentages
  - Shots on target

### Meta Effects ✅
- [ ] Create seasons with different metas
- [ ] Verify meta bonuses applied:
  - `possession` → control +8%, attack/defense -8%
  - `counter` → pace +8%, control -8%
  - `pressing` → defense +8%, stamina_factor -8%
  - etc. (see SimulationConstants)

### ELO System ✅
- [ ] After multiple matches, verify:
  - Strong team beats weak team → small ELO gain
  - Weak team beats strong team → large ELO gain
  - Draws result in small ELO changes
  - ELO trends upward for winning teams
  - K-factor = 32 (see EloRatingService)

## Phase 5: Edge Cases

### League Edge Cases
- [ ] All teams have 0 points → tie-breaking by goal difference
- [ ] Promotion/relegation with 12 teams → 3 teams (25%) promoted/relegated
- [ ] Season with 60 teams → 20 teams per division

### Cup Edge Cases
- [ ] 32 teams → 8 groups of 4, then R16, QF, SF, Final
- [ ] 64 teams → 16 groups of 4, then R32, R16, QF, SF, Final
- [ ] Group tie-breaker: points → GD → goals scored → form
- [ ] Penalty shootout: 5 rounds, then sudden death

## Phase 6: Performance

- [ ] Simulate all matches in a 60-team league season (180 matches) → complete in <30s
- [ ] Simulate all matches in a 64-team cup season (96 group + 32 knockout) → complete in <20s

## Phase 7: Data Integrity

### Database Checks
```sql
-- All teams have valid ELO
SELECT COUNT(*) FROM teams WHERE elo IS NULL OR elo < 100;
-- Should return 0

-- All standings sum to team count
SELECT season_id, COUNT(*) FROM league_standings GROUP BY season_id;
-- Should match teams_count in league_seasons

-- All positions have valid results
SELECT result, COUNT(*) FROM league_positions WHERE result NOT IN ('champion', 'promoted', 'relegated', 'stay');
-- Should return 0

-- Cup knockout winners valid
SELECT COUNT(*) FROM cup_eliminate_stage_matches WHERE winner_id NOT IN (team1_id, team2_id) AND winner_id IS NOT NULL;
-- Should return 0
```

## Known Issues / Future Improvements

1. **Views:** Chỉ có basic views cho index pages. Cần thêm:
   - Season detail views (divisions, groups)
   - Match detail views
   - Statistics detail views
   - Create/edit forms cho seasons

2. **Validation:** Cần thêm validation cho:
   - Team stats (1-100 range)
   - Season creation (duplicate check)

3. **UI/UX:** Enhance với:
   - Match result animations
   - Live simulation progress
   - Better team color display

4. **Testing:** Cần automated tests:
   - Unit tests for simulation logic
   - Integration tests for controllers
   - Feature tests for full workflows

---

**Status:** Refactor complete ✅  
**Date:** 2026-07-06  
**Next Steps:** Complete detailed views, add validation, write automated tests
