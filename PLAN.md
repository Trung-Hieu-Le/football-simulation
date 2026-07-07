# Football Simulation — Refactor Plan

Tài liệu này mô tả toàn bộ kế hoạch refactor dự án Laravel `football-simulation`. Developer đọc xong có thể triển khai độc lập mà không cần hỏi thêm context.

**Tham chiếu code hiện tại:**
- Logic simulation: `app/Services/MatchSimulationService.php`
- League (cũ: tier): `app/Http/Controllers/Tier/`
- Cup: `app/Http/Controllers/Cup/`
- Schema mẫu: `database/football-simulation.sql`

---

## Mục lục

1. [Mục tiêu](#1-mục-tiêu)
2. [Quy ước đặt tên](#2-quy-ước-đặt-tên)
3. [Cấu trúc thư mục mục tiêu](#3-cấu-trúc-thư-mục-mục-tiêu)
4. [Database schema](#4-database-schema)
5. [Enums](#5-enums)
6. [Match simulation spec](#6-match-simulation-spec)
7. [Services](#7-services)
8. [Controllers & Routes](#8-controllers--routes)
9. [Views & UI](#9-views--ui)
10. [League mode — nghiệp vụ](#10-league-mode--nghiệp-vụ)
11. [Cup mode — nghiệp vụ](#11-cup-mode--nghiệp-vụ)
12. [ELO rating](#12-elo-rating)
13. [Seeders & migration](#13-seeders--migration)
14. [Thứ tự triển khai](#14-thứ-tự-triển-khai)
15. [Testing checklist](#15-testing-checklist)
16. [Mapping code cũ → mới](#16-mapping-code-cũ--mới)

---

## 1. Mục tiêu

| Hạng mục | Mô tả |
|----------|-------|
| Simulation | Dùng **10 stats** mới, logic theo spec (xem mục 6) |
| League mode | 3 **division**, số đội **chia hết cho 12**, 25% lên/xuống hạng mỗi division |
| Cup mode | **32 hoặc 64** team, vòng loại + knockout, auto-gen bracket |
| Kiến trúc | Tách constants, enums, services; bỏ logic lặp |
| Database | Xóa migrations cũ, tạo migrations mới hoàn toàn |
| ELO | Điểm ELO trên `teams`, dùng statistics + chia pot cup |
| UI | Header thống nhất, 1 trang `/teams`, partial match dùng chung |

---

## 2. Quy ước đặt tên

### 2.1 Mode prefix: `league` / `cup` (không dùng `tier`)

| Tầng | Tên cũ | Tên mới |
|------|--------|---------|
| URL | `/tier/*` | `/league/*` |
| DB tables | `tier_*` | `league_*` |
| Controllers | `App\Http\Controllers\Tier\*` | `App\Http\Controllers\League\*` |
| Views | `resources/views/tier/` | `resources/views/league/` |
| Route names | `tier.*`, `seasons.*` | `league.*` |

### 2.2 Division (3 hạng trong League)

Khái niệm "tier" trong game → đổi thành **`division`**:

- Column DB: `division` (thay `tier`)
- Giá trị: `division1`, `division2`, `division3`
- Enum: `DivisionLevel`
- UI label: "Hạng 1", "Hạng 2", "Hạng 3"

### 2.3 Bảng group teams — thống nhất `{mode}_group_teams`

| Mode | Tên cũ | Tên mới |
|------|--------|---------|
| League | `tier_team_groups` | `league_group_teams` |
| Cup | `cup_group_teams` | `cup_group_teams` (giữ) |

### 2.4 Pattern đặt tên bảng

```
{mode}_seasons
{mode}_group_teams
{mode}_matches              (league only)
{mode}_group_stage_matches  (cup only)
{mode}_eliminate_stage_matches (cup only)
{mode}_standings
{mode}_positions
```

---

## 3. Cấu trúc thư mục mục tiêu

```
football-simulation/
├── _spec/
│   └── match_simulation_logic.md      # Spec chi tiết simulation (tạo từ mục 6)
├── app/
│   ├── Constants/
│   │   ├── SimulationConstants.php
│   │   ├── FieldPositions.php
│   │   └── StatsWeights.php
│   ├── Enums/
│   │   ├── SeasonMeta.php             # Đã có
│   │   ├── Weather.php
│   │   ├── MatchResult.php
│   │   ├── DivisionLevel.php
│   │   ├── LeagueSeasonResult.php
│   │   └── CupSeasonResult.php
│   ├── Http/Controllers/
│   │   ├── HomeController.php
│   │   ├── TeamController.php         # Unified /teams
│   │   ├── League/
│   │   │   ├── SeasonController.php
│   │   │   ├── MatchController.php
│   │   │   └── StatisticController.php
│   │   └── Cup/
│   │       ├── SeasonController.php
│   │       ├── MatchController.php
│   │       ├── EliminateController.php
│   │       ├── EliminateMatchController.php
│   │       └── StatisticController.php
│   ├── Models/
│   │   ├── Team.php
│   │   ├── Region.php
│   │   ├── League/
│   │   │   ├── Season.php
│   │   │   ├── Match.php
│   │   │   ├── Standing.php
│   │   │   ├── Position.php
│   │   │   └── GroupTeam.php
│   │   └── Cup/
│   │       ├── Season.php
│   │       ├── GroupStageMatch.php
│   │       ├── EliminateMatch.php
│   │       ├── Standing.php
│   │       ├── Position.php
│   │       └── GroupTeam.php
│   └── Services/
│       ├── Simulation/
│       │   ├── BaseSimulationService.php
│       │   ├── SituationProcessor.php
│       │   ├── MatchSimulator.php
│       │   ├── PenaltyShootoutService.php
│       │   └── EventHandlers/
│       │       ├── ShotHandler.php
│       │       ├── FoulHandler.php
│       │       └── CounterAttackHandler.php
│       ├── EloRatingService.php
│       ├── CupPotSeedingService.php
│       ├── CupKnockoutService.php       # Bracket + auto-update
│       ├── MatchHistoryService.php      # Refactor từ file hiện tại
│       ├── EliminateMatchService.php    # Refactor từ file hiện tại
│       └── ErrorLogService.php          # Giữ nguyên
├── database/
│   ├── migrations/                    # Migrations mới (xóa hết cũ)
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── RegionSeeder.php
│       └── TeamSeeder.php
└── resources/views/
    ├── layouts/
    │   └── app.blade.php                # Header chung
    ├── home.blade.php
    ├── teams/
    │   └── index.blade.php
    ├── partials/
    │   ├── match-card.blade.php
    │   ├── match-result.blade.php       # Scoreboard sau simulate
    │   ├── standings-table.blade.php
    │   └── team-badge.blade.php         # Gradient team name
    ├── league/
    │   ├── layouts/app.blade.php
    │   ├── seasons/ ...
    │   └── statistics/ ...
    └── cup/
        ├── layouts/app.blade.php
        ├── seasons/ ...
        ├── eliminate/ ...
        └── statistics/ ...
```

---

## 4. Database schema

Xóa toàn bộ migrations cũ. Tạo migrations mới theo thứ tự dưới đây.

### 4.1 `regions`

| Column | Type | Notes |
|--------|------|-------|
| id | int, PK | Không auto-increment (seed cố định 1–4) |
| name | varchar(45) | |
| shortname | varchar(45) | JP, EN, ID, DV |
| description | varchar(45), nullable | |

### 4.2 `teams`

| Column | Type | Default | Notes |
|--------|------|---------|-------|
| id | int, PK, AI | | |
| name | varchar(255) | | |
| color_1, color_2, color_3 | varchar(10) | #000000 | |
| attack | int | 50 | |
| defense | int | 50 | |
| control | int | 50 | |
| creative | int | 50 | |
| pace | int | 50 | |
| mental | int | 50 | |
| discipline | int | 50 | |
| luck | int | 50 | |
| stamina | int | 50 | |
| goalkeeping | int | 50 | |
| form | int | 50 | Cập nhật sau trận (+5/-5, clamp 5–100) |
| **elo** | int | **1000** | **MỚI** |
| region | int, FK → regions.id | | |
| shirt_type | varchar(45), nullable | | |
| created_at, updated_at | timestamp | | |

### 4.3 `league_seasons`

| Column | Type | Notes |
|--------|------|-------|
| id | int, PK, AI | |
| season | int | Số mùa (1, 2, 3...) |
| teams_count | int | Phải chia hết cho 12 |
| meta | varchar(45) | `SeasonMeta` enum value |
| created_at | timestamp | |

### 4.4 `league_group_teams`

| Column | Type | Notes |
|--------|------|-------|
| id | int, PK, AI | |
| season_id | int, FK | |
| group | varchar(255) | `division1`, `division2`, `division3` |
| team_ids | varchar(1015) | JSON array team IDs |
| created_at | timestamp | |

### 4.5 `league_matches`

| Column | Type | Default | Notes |
|--------|------|---------|-------|
| id | bigint, PK, AI | | |
| season_id | int, FK | | |
| division | varchar(45) | | Thay `tier` |
| round | int | | |
| team1_id, team2_id | int | | |
| team1_score, team2_score | int | 0 | null = chưa đấu |
| team1_possession, team2_possession | int | 50 | % |
| team1_foul, team2_foul | int | 0 | |
| created_at, updated_at | timestamp | | |

Index: `(season_id, division)`

### 4.6 `league_standings`

| Column | Type | Notes |
|--------|------|-------|
| id | int, PK, AI | |
| team_id | int, FK | |
| season_id | int, FK | |
| division | varchar(45) | |
| match_played | int | default 0 |
| goal_scored, goal_conceded | int | default 0 |
| goal_difference | int | default 0 |
| average_possession | double | default 50 |
| foul | int | default 0 |
| points | int | default 0 |
| win, draw, lose | int | default 0 |
| created_at, updated_at | timestamp | |

Index: `(season_id, team_id)`

### 4.7 `league_positions`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned, PK, AI | |
| league_standing_id | bigint unsigned, FK | |
| season_id | bigint unsigned, FK | |
| position | int | default 0 |
| result | varchar(45), nullable | `LeagueSeasonResult` |
| created_at, updated_at | timestamp | |

Index: `(season_id)`

### 4.8 `cup_seasons`

Giống `league_seasons`, thêm validate `teams_count` ∈ {32, 64}.

### 4.9 `cup_group_teams`

| Column | Type | Notes |
|--------|------|-------|
| id | int, PK, AI | |
| season_id | int, FK | |
| group | varchar(255) | A, B, C... (8 groups cho 32 team) |
| team_ids | varchar(1015) | JSON array |
| created_at, updated_at | timestamp | |

### 4.10 `cup_group_stage_matches`

Giống schema hiện tại (`cup_group_stage_matches` trong SQL dump).

### 4.11 `cup_eliminate_stage_matches`

| Column | Type | Notes |
|--------|------|-------|
| id | int, PK, AI | |
| season_id | int, FK | |
| round | varchar(45) | `round_of_32`, `round_of_16`, `quarter_finals`, `semi_finals`, `third_place`, `final` |
| branch | varchar(45) | 1–4 |
| team1_id, team2_id | int, nullable | |
| team1_score, team2_score | tinyint, nullable | |
| team1_possession, team2_possession | int | default 50 |
| team1_foul, team2_foul | int | default 0 |
| winner_id | int, nullable | |
| created_at, updated_at | timestamp | |

### 4.12 `cup_standings` / `cup_positions`

Giống league standings/positions, thêm column `group` trên standings.

`cup_positions.result` default = `group_stage`, cast `CupSeasonResult`.

---

## 5. Enums

### 5.1 `SeasonMeta` (đã có)

```
possession, counter, pressing, tiki-taka, long_ball,
build_up, low_block, high_risk, high_line
```

Bonus +8% / penalty -8% lên stats tương ứng (xem mục 6.3).

### 5.2 `Weather` (mới — optional phase 2)

```
clear, rainy, snowy, windy
```

Ảnh hưởng `pace`, `stamina` khi simulate (có thể implement sau).

### 5.3 `MatchResult` (cho ELO)

```php
enum MatchResult: float {
    case WIN  = 1.0;
    case DRAW = 0.5;
    case LOSE = 0.0;
}
```

### 5.4 `DivisionLevel`

```php
enum DivisionLevel: string {
    case DIVISION1 = 'division1';
    case DIVISION2 = 'division2';
    case DIVISION3 = 'division3';
}
```

### 5.5 `LeagueSeasonResult` → `league_positions.result`

```php
enum LeagueSeasonResult: string {
    case CHAMPION  = 'champion';   // division1, hạng 1
    case PROMOTED  = 'promoted';   // top 25%, trừ division1
    case RELEGATED = 'relegated';  // bottom 25%, trừ division3
    case STAY      = 'stay';
}
```

### 5.6 `CupSeasonResult` → `cup_positions.result`

```php
enum CupSeasonResult: string {
    case GROUP_STAGE   = 'group_stage';    // default
    case ROUND_OF_32   = 'round_of_32';
    case ROUND_OF_16   = 'round_of_16';
    case QUARTER_FINAL = 'quarter_finals';
    case SEMI_FINAL    = 'semi_finals';
    case CHAMPION      = 'champion';
    case RUNNER_UP     = 'runner_up';
    case THIRD_PLACE   = '3rd_place';
    case FOURTH_PLACE  = '4th_place';
}
```

**Gán result (logic hiện tại):**
- Group stage: default `group_stage`
- Knockout thua: result = tên round bị loại
- Final thắng/thua: `champion` / `runner_up`
- Third place match: `3rd_place` / `4th_place`

---

## 6. Match simulation spec

> File đầy đủ: `_spec/match_simulation_logic.md` — tạo từ nội dung mục này.

### 6.1 Mười stats

| Stat | Vai trò |
|------|---------|
| attack | Shot power, on-target chance |
| defense | Chặn tiến bóng, steal |
| control | Possession, build-up |
| creative | Build-up, free kick, counter |
| pace | Move nhanh (+2 ô), pressing |
| mental | Shot decision, penalty, save |
| discipline | Giảm foul, pressing |
| luck | Special event (lucky goal) |
| stamina | Fatigue resistance, build-up |
| goalkeeping | Save shot, penalty save |

**Không dùng `form` trong công thức simulate** — `form` chỉ cập nhật sau trận (history).

### 6.2 Thời gian & tình huống

```
SITUATIONS_PER_MINUTE = 3
Fulltime  = 90 phút = 270 tình huống
Extra time = 30 phút = 90 tình huống (tổng 360)

Kickoff:
  - Hiệp 1: team1
  - Hiệp 2: team2
  - ET hiệp 1: team1
  - ET hiệp 2: team2
```

### 6.3 Sân bóng (11 vị trí, index 0–10)

```
0  = Goal team1
1  = Penalty area team1
2  = Final third team1
3  = Midfield low
4  = Midfield high
5  = Midfield (center)
6  = Midfield low team2
7  = Midfield high team2
8  = Final third team2
9  = Penalty area team2
10 = Goal team2

SHOOTING_POSITIONS = [0, 1, 2, 8, 9, 10]
```

Team1 tấn công về phía index tăng (→ 10). Team2 tấn công về phía index giảm (→ 0).

### 6.4 Stamina & meta

**Phase decay:**
```
HALF_1_DECAY = 0.05
HALF_2_DECAY = 0.12
EXTRA_1_DECAY = 0.20
EXTRA_2_DECAY = 0.30

staminaFactor = 1 - (phaseDecay * (1 - (stamina/100)^0.65))
```

Áp dụng lên: attack, defense, control, pace, creative, goalkeeping.

**SeasonMeta:** `META_BONUS = 1.08`, `META_PENALTY = 0.92` — xem `applyMetaFactors()` trong `MatchSimulationService.php`.

### 6.5 Flow mỗi tình huống

```
1. Foul check
   foulThreshold = BASE_FOUL_CHANCE(7) - discipline*0.08
   clamp [3, 12]
   → Foul ở penalty area (pos 1,9): 40% penalty, else free kick

2. Move ball
   buildUpPower = control*1.4 + creative*1.5 + stamina*0.5
   stopProgress = defense*1.8 + discipline*0.6
   moveChance = buildUp / (buildUp + stopProgress * zoneDifficulty)
   pace > 70: 15% chance move +2 ô

3. Shooting decision (ở SHOOTING_POSITIONS)
   shotChance = 8 + zoneBonus + attack*0.18 + mental*0.10
   max 55%

4. Shot
   onTargetChance = 40 + distanceBonus + attack*0.3, clamp [15, 90]
   goalChance = shotPower / (shotPower + savePower)
   shotPower = attack*2 + mental*0.8
   savePower = goalkeeping*2 + mental*0.4

5. Steal / counter
   controlRatio steal, counter sau shot miss 30%, steal 50%
```

### 6.6 Zone tables

**ZONE_SHOT_BONUS** (index 1–9): 30, 18, 8, -5, -20, -5, 8, 18, 30

**ZONE_PROGRESS_DIFFICULTY**: 2.5, 2.0, 1.5, 1.2, 1.0, 1.2, 1.5, 2.0, 2.5

### 6.7 Penalty shootout

- 5 lượt mỗi đội, team2 sút trước lượt 1
- Sudden death nếu hòa
- `successChance = attackPower / (attackPower + defensePower)` (+ mental*0.1)

### 6.8 Output `matchData` sau simulate

```php
[
    'team1_score', 'team2_score',
    'team1_fouls', 'team2_fouls',
    'team1_possession', 'team2_possession',  // raw count, convert → %
    'team1_shots', 'team2_shots',
    'team1_shots_on_target', 'team2_shots_on_target',
    'specialEvents' => ["45': GOAL by TeamA!", ...],
]
```

---

## 7. Services

### 7.1 Simulation layer (tách từ `MatchSimulationService`)

| Class | Trách nhiệm |
|-------|-------------|
| `BaseSimulationService` | `calculateTeamStats()`, `applyMetaFactors()`, `getKickoffTeam()` |
| `SituationProcessor` | `processSituation()`, `moveBall()` |
| `ShotHandler` | `handleShot()`, shot formulas |
| `FoulHandler` | `handleFoul()`, `handleFreeKick()`, `handlePenalty()` |
| `CounterAttackHandler` | `handleCounterAttackAfterMiss()` |
| `MatchSimulator` | `simulateFullTime()`, `simulateExtraTime()` — inject handlers |
| `PenaltyShootoutService` | `simulatePenaltyShootout()` |

Constants chuyển sang `app/Constants/*`.

### 7.2 `EloRatingService`

```php
class EloRatingService {
    public const K_FACTOR = 32;
    public const DEFAULT_ELO = 1000;

    public function expectedScore(int $ratingA, int $ratingB): float {
        return 1 / (1 + pow(10, ($ratingB - $ratingA) / 400));
    }

    public function calculateChange(int $teamElo, int $opponentElo, float $actualScore): int {
        $expected = $this->expectedScore($teamElo, $opponentElo);
        return (int) round(self::K_FACTOR * ($actualScore - $expected));
    }

    public function updateAfterMatch(Team $team1, Team $team2, MatchResult $resultForTeam1): void {
        // team1 actual: 1/0.5/0, team2 = 1 - team1 (draw: 0.5 cả hai)
        $change = $this->calculateChange($team1->elo, $team2->elo, $resultForTeam1->value);
        $team1->elo += $change;
        $team2->elo -= $change;
        $team1->save();
        $team2->save();
    }

    public function resetAll(): void {
        Team::query()->update(['elo' => self::DEFAULT_ELO]);
    }
}
```

**Gọi sau mỗi trận:** league match, cup group, cup knockout (không gọi cho penalty shootout từng kick — chỉ sau trận, thắng = 1 thua = 0).

### 7.3 `CupPotSeedingService`

```php
class CupPotSeedingService {
  /**
   * @return array<int, Collection<Team>> pots 1..4
   */
  public function assignPots(Collection $teams, int $teamCount): array {
      assert(in_array($teamCount, [32, 64]));
      $perPot = $teamCount / 4;  // 8 hoặc 16
      $sorted = $teams->sortByDesc('elo')->values();
      return [
          1 => $sorted->slice(0, $perPot),
          2 => $sorted->slice($perPot, $perPot),
          3 => $sorted->slice($perPot * 2, $perPot),
          4 => $sorted->slice($perPot * 3, $perPot),
      ];
  }

  /**
   * Rút thăm: mỗi group nhận 1 team từ mỗi pot (cân bằng).
   * 32 team → 8 groups (A–H), 4 team/group
   * 64 team → 16 groups (A–P), 4 team/group
   */
  public function drawGroups(array $pots, int $teamCount): array { ... }
}
```

**Thay thế logic cũ** (`assignTeamsToGroups` sort by `form`) → sort by **ELO**.

### 7.4 `CupKnockoutService`

Trách nhiệm:
- `isGroupStageComplete($seasonId): bool`
- `syncKnockoutBracket($seasonId): void` — gọi sau mỗi trận group stage
- `createEliminateStage($seasonId)` — logic từ `SeasonCupController::createEliminateStage()`

**Bracket round_of_32 (32 team, 8 groups):**

```
Branch 1: A1-B4, D2-G3, E1-F4, H2-C3
Branch 2: A4-H1, C2-F3, D1-E4, G2-B3
Branch 3: C1-D4, A3-F2, G1-H4, B2-E3
Branch 4: F1-G4, A2-D3, B1-C4, E2-H3
```

Key format: `{Group}{Position}` — position 1–4 từ `cup_positions` sau group stage.

**Các round tiếp theo (placeholder, team null):**
```
round_of_16: 8 matches
quarter_finals: 4
semi_finals: 2
third_place: 1
final: 1
```

**Auto-update:** Sau mỗi group match simulate → `syncKnockoutBracket()`. Khi group stage xong, tạo/update `cup_eliminate_stage_matches` với team IDs chính xác từ standings.

### 7.5 `MatchHistoryService`

Refactor, đổi tên method:
- `updateLeagueHistory()` (cũ: `updateTierHistory`)
- `updateGroupStageHistory()` — giữ
- `updateEliminateHistory()` — giữ
- `updateStandings($seasonId, $type)` — giữ, đổi `tier` → `league`

Sau update history → gọi `EloRatingService::updateAfterMatch()`.

---

## 8. Controllers & Routes

### 8.1 Routes mục tiêu (`routes/web.php`)

```php
// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Teams (unified)
Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
Route::put('/teams/{id}', [TeamController::class, 'update'])->name('teams.update');
Route::post('/teams/reset-form', [TeamController::class, 'resetForm'])->name('teams.resetForm');
Route::post('/teams/reset-all', [TeamController::class, 'resetAll'])->name('teams.resetAll');
// resetAll: form → default stats + elo = 1000

// League
Route::prefix('league')->name('league.')->group(function () {
    Route::get('/', [League\SeasonController::class, 'index'])->name('seasons.index');
    Route::get('/seasons/create', [League\SeasonController::class, 'create'])->name('seasons.create');
    Route::post('/seasons', [League\SeasonController::class, 'store'])->name('seasons.store');
    Route::get('/seasons/{id}', [League\SeasonController::class, 'show'])->name('seasons.show');
    Route::delete('/seasons/{id}', [League\SeasonController::class, 'destroy'])->name('seasons.destroy');
    Route::get('/seasons-destroy-all', [League\SeasonController::class, 'destroyAll'])->name('seasons.destroy_all');
    Route::get('/matches/{id}', [League\SeasonController::class, 'listMatches'])->name('matches.show');
    Route::get('/histories/{id}', [League\SeasonController::class, 'showStatistics'])->name('histories.show');
    Route::post('/seasons/simulate', [League\MatchController::class, 'simulateMatch'])->name('seasons.simulate');
    Route::get('/statistics', [League\StatisticController::class, 'index'])->name('statistics.index');
});

// Cup
Route::prefix('cup')->name('cup.')->group(function () {
    Route::get('/', [Cup\SeasonController::class, 'index'])->name('seasons.index');
    Route::get('/seasons/create', [Cup\SeasonController::class, 'create'])->name('seasons.create');
    Route::post('/seasons', [Cup\SeasonController::class, 'store'])->name('seasons.store');
    Route::get('/seasons/{id}', [Cup\SeasonController::class, 'show'])->name('seasons.show');
    Route::delete('/seasons/{id}', [Cup\SeasonController::class, 'destroy'])->name('seasons.destroy');
    Route::get('/seasons-destroy-all', [Cup\SeasonController::class, 'destroyAll'])->name('seasons.destroy_all');
    Route::get('/matches/{id}', [Cup\SeasonController::class, 'listMatches'])->name('matches.show');
    Route::get('/histories/{id}', [Cup\SeasonController::class, 'showStatistics'])->name('histories.show');
    Route::post('/seasons/simulate', [Cup\MatchController::class, 'simulateMatch'])->name('seasons.simulate');
    Route::get('/statistics', [Cup\StatisticController::class, 'index'])->name('statistics.index');
});

Route::prefix('cup/eliminate')->name('cup.eliminate.')->group(function () {
    Route::get('/view/{season}', [Cup\EliminateController::class, 'view'])->name('view');
    Route::post('/simulate', [Cup\EliminateMatchController::class, 'simulateMatch'])->name('simulate');
    Route::get('/statistics/{seasonId}', [Cup\EliminateController::class, 'teamStatistics'])->name('statistics');
});
```

### 8.2 Controller actions chính

**`League\SeasonController@store`:**
1. Validate `teams_count % 12 == 0`
2. Nếu season > 1: load promoted/relegated từ `league_positions` season trước
3. Chia team vào 3 division đều nhau
4. Tạo `league_group_teams`, `league_standings`, `league_matches` (round-robin mỗi division)

**`League\SeasonController@show` (khi hết trận):**
1. Tính promotion/relegation
2. Gán `LeagueSeasonResult` vào `league_positions`

**`Cup\SeasonController@store`:**
1. Validate `teams_count` ∈ {32, 64}
2. `CupPotSeedingService::assignPots()` + `drawGroups()`
3. Tạo group standings + matches (round-robin per group)

**`Cup\MatchController@simulateMatch`:**
1. Simulate group matches
2. Update standings
3. `CupKnockoutService::syncKnockoutBracket($seasonId)`

---

## 9. Views & UI

### 9.1 Header chung (`layouts/app.blade.php`)

```
[Football Sim]  Home | Teams
```

### 9.2 Home (`home.blade.php`)

Hai card/button:
- **League Mode** → `/league/seasons`
- **Cup Mode** → `/cup/seasons`

### 9.3 Mode layout nav

**League:** Seasons | Statistics  
**Cup:** Seasons | Statistics

*(History là per-season qua breadcrumb, không cần top nav riêng)*

### 9.4 Cup season breadcrumb

```html
<ol class="breadcrumb">
  <li><a href="/cup/seasons/{id}">Bảng Xếp Hạng Vòng Loại</a></li>
  <li><a href="/cup/eliminate/view/{id}">Vòng Knock-Out</a></li>
  <li><a href="/cup/matches/{id}">Lịch Thi Đấu</a></li>
  <li><a href="/cup/histories/{id}">Thống Kê Mùa Giải</a></li>
</ol>
```

### 9.5 Shared partials

| Partial | Dùng cho |
|---------|----------|
| `partials/team-badge.blade.php` | `@props(['name', 'color1', 'color2', 'color3'])` |
| `partials/match-card.blade.php` | Bảng trận (next/completed) — thay `cup/partials/match.blade.php` |
| `partials/match-result.blade.php` | Scoreboard sau simulate (possession, shots, events) |
| `partials/standings-table.blade.php` | BXH với highlight promotion/relegation |

### 9.6 League standings highlight

```
division1 rank 1     → table-warning (champion)
top 25% (not div1)   → table-success (promoted)
bottom 25% (not div3)→ table-danger (relegated)
```

### 9.7 Teams page (`/teams`)

- List 64 teams, edit stats inline
- Hiển thị cột **ELO**
- Filter theo region
- Actions: Reset form (stats default), Reset all (stats + elo 1000)
- **Xóa** `tier/teams` và `cup/teams` views/controllers

---

## 10. League mode — nghiệp vụ

### 10.1 Ràng buộc

- `teams_count` ∈ {12, 24, 36, 48, 60, ...} — chia hết cho 12
- 3 division, mỗi division = `teams_count / 3` đội
- Mỗi division: round-robin `(n-1)` vòng, mỗi vòng đấu cả 3 division (ưu tiên division3 trước khi simulate — giữ logic `orderBy division desc`)

### 10.2 Promotion / Relegation

```php
$teamsPerDivision = $season->teams_count / 3;
$promoCount = (int) floor($teamsPerDivision * 0.25);

// division1: rank 1 → champion; bottom promoCount → relegated → division2
// division2: top promoCount → promoted; bottom promoCount → relegated
// division3: top promoCount → promoted
// còn lại → stay
```

### 10.3 Season tiếp theo

Khi `store()` season mới:
1. Load `league_positions` season trước
2. `promoted` → lên division trên
3. `relegated` → xuống division dưới
4. `stay` → giữ division
5. Fill slot trống bằng team mới (nếu có) theo ELO hoặc random

### 10.4 Điểm

- Thắng: 3 điểm, Hòa: 1, Thua: 0
- Xếp hạng: points → goal_difference → goal_scored

---

## 11. Cup mode — nghiệp vụ

### 11.1 Ràng buộc

- `teams_count` = **32** hoặc **64** only
- 32 team: 8 groups (A–H), 4 team/group
- 64 team: 16 groups (A–P), 4 team/group

### 11.2 Pot seeding (khi tạo season)

1. Lấy top N teams theo ELO (N = 32 hoặc 64)
2. Chia 4 pot đều nhau
3. Draw: mỗi group 1 team từ pot 1, 1 từ pot 2, 1 từ pot 3, 1 từ pot 4 (shuffle trong pot)

### 11.3 Group stage

- Round-robin trong mỗi group (6 trận/group cho 4 team)
- Top 2 (hoặc top 4 cho knockout 32) — **hiện tại dùng top 4** cho round_of_32
- Sau mỗi trận: update `cup_standings`, `cup_positions` (position trong group)

### 11.4 Knockout auto-update

```
Sau simulate group match:
  IF all group matches have scores:
    CupKnockoutService::createEliminateStage(seasonId)
  ELSE:
    // Optional: pre-create empty bracket, fill teams as positions finalize
    // Hoặc chỉ tạo khi group stage hoàn tất (đơn giản hơn)
```

Khi group standings thay đổi (position 1–4), nếu knockout đã tồn tại → **re-sync** `team1_id`/`team2_id` ở round_of_32 theo bracket formula.

### 11.5 Knockout simulate

- Hòa → extra time → penalty shootout (logic hiện tại `EliminateMatchController`)
- Winner advance qua `EliminateMatchService::handleNextMatch()`
- Loser semi → third_place match

### 11.6 Cup standings highlight (group)

- Top 2 (hoặc 4): `table-success` — vùng đi tiếp

---

## 12. ELO rating

| Sự kiện | Hành vi |
|---------|---------|
| Sau mỗi trận | Update ELO cả 2 team |
| Delete 1 season | **Không** reset ELO |
| Delete all seasons | Reset ELO → 1000 |
| Reset teams | Reset stats + ELO → 1000 |
| Cup pot seeding | Sort ELO desc |
| Statistics page | Hiển thị + sort theo ELO |

---

## 13. Seeders & migration

### 13.1 Xóa migrations cũ

```
database/migrations/2026_01_28_000000_update_teams_stats_columns.php
database/migrations/2026_06_24_000000_add_goalkeeping_and_luck_to_teams.php
```

### 13.2 Tạo migrations mới (thứ tự)

```
2026_07_06_000001_create_regions_table.php
2026_07_06_000002_create_teams_table.php
2026_07_06_000003_create_league_seasons_table.php
2026_07_06_000004_create_league_group_teams_table.php
2026_07_06_000005_create_league_matches_table.php
2026_07_06_000006_create_league_standings_table.php
2026_07_06_000007_create_league_positions_table.php
2026_07_06_000008_create_cup_seasons_table.php
2026_07_06_000009_create_cup_group_teams_table.php
2026_07_06_000010_create_cup_group_stage_matches_table.php
2026_07_06_000011_create_cup_eliminate_stage_matches_table.php
2026_07_06_000012_create_cup_standings_table.php
2026_07_06_000013_create_cup_positions_table.php
```

### 13.3 Seed data

**RegionSeeder** — từ `football-simulation.sql`:
```
(1, 'Nhật Bản', 'JP'),
(2, 'Ngoại Quốc', 'EN'),
(3, 'Indonesia', 'ID'),
(4, 'Dev_is', 'DV')
```

**TeamSeeder** — 64 teams từ SQL dump, thêm `elo = 1000` mỗi team.

### 13.4 Chạy migration

```bash
# Backup trước
mysqldump -u root -p football_simulation > backup_$(date +%Y%m%d).sql

# Fresh migrate + seed
php artisan migrate:fresh --seed
```

---

## 14. Thứ tự triển khai

| Phase | Công việc | Phụ thuộc |
|-------|-----------|-----------|
| **1** | Tạo `_spec/match_simulation_logic.md` | — |
| **2** | Constants + Enums | — |
| **3** | Migrations mới + Seeders | — |
| **4** | Eloquent Models | Phase 3 |
| **5** | Refactor Simulation services | Phase 2 |
| **6** | `EloRatingService`, `CupPotSeedingService`, `CupKnockoutService` | Phase 4 |
| **7** | `MatchHistoryService` refactor + ELO integration | Phase 5, 6 |
| **8** | Controllers League (rename từ Tier) | Phase 4, 7 |
| **9** | Controllers Cup refactor | Phase 4, 6, 7 |
| **10** | Routes + `TeamController` unified | Phase 8, 9 |
| **11** | Views: layouts, home, partials, league, cup | Phase 10 |
| **12** | Xóa code cũ (`Tier/*`, duplicate teams views) | Phase 11 |
| **13** | Testing theo checklist | Phase 12 |

---

## 15. Testing checklist

### League
- [ ] Tạo season 12, 24, 36, 48, 60 teams
- [ ] 3 division, số đội đều nhau
- [ ] Simulate theo round, division3 trước
- [ ] BXH đúng điểm, GD
- [ ] Hết season: champion, promoted, relegated, stay đúng 25%
- [ ] Season mới: carry-over division đúng
- [ ] ELO thay đổi sau trận

### Cup
- [ ] Tạo season 32 và 64 teams
- [ ] 4 pot × 8 (hoặc 16) team theo ELO
- [ ] Mỗi group có 1 team từ mỗi pot
- [ ] Group stage simulate + BXH
- [ ] Knockout auto tạo khi group xong
- [ ] Bracket round_of_32 đúng formula A1-B4...
- [ ] Knockout simulate → champion, runner_up, 3rd, 4th
- [ ] Breadcrumb 4 mục hoạt động
- [ ] ELO thay đổi sau trận

### Shared
- [ ] `/` home chọn mode
- [ ] `/teams` duy nhất, edit stats, hiện ELO
- [ ] Reset all → elo 1000
- [ ] Statistics league + cup hiển thị ELO
- [ ] Partial match-card dùng chung
- [ ] `php artisan migrate:fresh --seed` chạy sạch

---

## 16. Mapping code cũ → mới

| Cũ | Mới |
|----|-----|
| `tier_seasons` | `league_seasons` |
| `tier_team_groups` | `league_group_teams` |
| `tier_matches` | `league_matches` |
| `tier_standings` | `league_standings` |
| `tier_positions` | `league_positions` |
| column `tier` | column `division` |
| `Tier\SeasonTierController` | `League\SeasonController` |
| `Tier\MatchTierController` | `League\MatchController` |
| `Tier\StatisticTierController` | `League\StatisticController` |
| `Tier\TeamTierController` | `TeamController` (shared) |
| `Cup\TeamCupController` | *(xóa, dùng TeamController)* |
| `MatchSimulationService` (monolith) | `Services/Simulation/*` |
| `updateTierHistory()` | `updateLeagueHistory()` |
| Cup assign by `form` | Cup assign by `elo` + pots |
| Manual eliminate create | Auto `syncKnockoutBracket()` |

---

## Phụ lục: Files tham chiếu khi implement

| File | Nội dung cần port |
|------|-------------------|
| `app/Services/MatchSimulationService.php` | Toàn bộ simulation logic |
| `app/Http/Controllers/Tier/SeasonTierController.php` | League season, promotion, match schedule |
| `app/Http/Controllers/Cup/SeasonCupController.php` | Cup season, groups, knockout bracket |
| `app/Services/MatchHistoryService.php` | Standings update |
| `app/Services/EliminateMatchService.php` | Knockout advance, result titles |
| `app/Http/Controllers/Cup/EliminateMatchCupController.php` | Knockout simulate + ET + pens |
| `database/football-simulation.sql` | Schema + seed data mẫu |
| `app/Enums/SeasonMeta.php` | Meta values |

---

*Cập nhật: 2026-07-06*
