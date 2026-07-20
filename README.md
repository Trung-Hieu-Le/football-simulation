# Football Simulation

Laravel app mô phỏng bóng đá với 2 mode: **League** (3 divisions) và **Cup** (32/64 teams, vòng bảng + knockout bracket).

Yêu cầu: **PHP 8.1+** (khuyến nghị `php8.2`).

## Cài đặt nhanh

```bash
cd football-simulation
php8.2 $(which composer) install
cp .env.example .env
php8.2 artisan key:generate

# Chỉnh DB_* trong .env (MySQL), rồi:
php8.2 artisan migrate:fresh --seed
php8.2 artisan serve
```

Mở http://localhost:8000

> **DB đã tồn tại từ trước rename `discipline` → `physical`:** chạy `php8.2 artisan migrate` (migration rename idempotent). Fresh install dùng cột `physical` từ đầu.

## Team stats (10)

`attack`, `defense`, `control`, `creative`, `pace`, `mental`, **`physical`**, `luck`, `stamina`, `goalkeeping`

- **physical**: contest, aerial, pressing strength; physical cao + defense thấp → dễ foul hơn
- **defense**: chặn tiến bóng, giảm foul, offside trap, save

## Chạy tests

### Unit tests (không cần database)

```bash
php8.2 artisan test --testsuite=Unit
# hoặc
php8.2 vendor/bin/phpunit --testsuite=Unit
```

| File | Nội dung |
|------|----------|
| `tests/Unit/ZoneHelpersTest.php` | Zone mirror 0–2 ↔ 8–10, shot bonus, midfield weight |
| `tests/Unit/BuildUpHandlerTest.php` | `moveBall` structure + bounds |
| `tests/Unit/ShotHandlerTest.php` | Clutch shot timing; OwnGoal đã bỏ |
| `tests/Unit/PenaltyCalculatorTest.php` | Shared penalty on-target / goal chance |
| `tests/Unit/MetaModifiersTest.php` | 8 meta + legacy map |
| `tests/Unit/RoundRobinServiceTest.php` | Lịch round-robin |
| `tests/Unit/CupPotSeedingServiceTest.php` | Pot + groups |
| `tests/Unit/CupKnockoutServiceTest.php` | Knockout branch order |
| `tests/Unit/EloRatingServiceTest.php` | ELO |
| `tests/Unit/MatchEventNormalizerTest.php` | Match events payload |

Chạy một file:

```bash
php8.2 artisan test --filter=ZoneHelpersTest
php8.2 artisan test tests/Unit/PenaltyCalculatorTest.php
```

### Balance predict script (không cần DB)

Ước lượng % công thức ở “midpoint” (giả sử `rand ≈ 50`) để soi move / foul / shot / goal **trước** khi sim trận thật. Không thay thế unit test và không chạy full match.

```bash
php8.2 scripts/predict_midpoint_balance.php
php8.2 scripts/predict_midpoint_balance.php | jq '.[0].chances_pct'
php8.2 scripts/predict_midpoint_balance.php | jq '.[0].diagnosis'
```

Output JSON gồm vài cặp attacker/defender (90 vs 88, gap 90 vs 80, …) và `diagnosis` flags (`goal_conversion_high`, `zone_mirror_ok`, …).

**Khi nào dùng:** sau khi sửa `SimulationConstants`, `StatsWeights`, `ZoneHelpers`, hoặc `MetaModifiers`.

### Integration test (cần MySQL + đủ teams)

Sau `migrate:fresh --seed` (≥ 32 teams):

```bash
php8.2 artisan cup:self-test --teams=32
php8.2 artisan cup:self-test --teams=64
```

Chạy trong transaction rồi **rollback** — không để lại data.

### Feature tests

Cần database. Có thể bật SQLite in-memory trong `phpunit.xml` nếu có `pdo_sqlite`, rồi:

```bash
php8.2 artisan test
```

## Điều chỉnh số bàn thắng (goals)

| Muốn | Sửa ở đâu |
|------|-----------|
| Ít / nhiều cơ hội sút | `SimulationConstants::SHOOT_DECISION_*`, `ZoneHelpers::SHOT_BONUS_BY_DISTANCE` |
| Khó tiến vào box | `ZoneHelpers::PROGRESS_DIFFICULTY_BY_DISTANCE` |
| On-target | `NORMAL_SHOT_ON_TARGET_CHANCE`, `SHOT_ATTACK_BONUS_MULTIPLIER` |
| Conversion (bàn từ OT) | `StatsWeights::SHOT_POWER_*` / `SAVE_POWER_*` |
| Meta | `MetaModifiers` key `shot_decision`, `counter_*` |

Single source zone: **`ZoneHelpers`** (không còn bảng zone trong `StatsWeights`).

## Cup mode — flow thủ công

1. `/cup/seasons/create` — chọn 32 hoặc 64 đội
2. Simulate group stage → **Advance to Knockout**
3. `/cup/seasons/{id}/eliminate` — bracket, simulate từng vòng
4. Thứ tự: R16 → QF → Round of 8 → SF → **Third place** → **Final**

Chi tiết nghiệp vụ: xem `PLAN.md` mục 11.

## Tài liệu khác

- `PLAN.md` — spec đầy đủ
- `TESTING_CHECKLIST.md` — checklist smoke test UI
- `REFACTOR_SUMMARY.md` — tóm tắt refactor

## License

MIT (Laravel framework).
