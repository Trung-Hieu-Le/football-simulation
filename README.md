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

## Chạy tests

### Unit tests (không cần database)

Chạy toàn bộ unit tests:

```bash
php8.2 artisan test --testsuite=Unit
```

Hoặc dùng PHPUnit trực tiếp:

```bash
php8.2 vendor/bin/phpunit --testsuite=Unit
```

**Các test hiện có:**

| File | Nội dung |
|------|----------|
| `tests/Unit/RoundRobinServiceTest.php` | Lịch round-robin single leg (4 và 8 đội/group) |
| `tests/Unit/CupPotSeedingServiceTest.php` | Chia pot + 8 groups A–H (32 và 64 teams) |
| `tests/Unit/CupKnockoutServiceTest.php` | BRANCHES_32, thứ tự round knockout |

### Integration test (cần MySQL + đủ teams trong DB)

Sau `migrate:fresh --seed` (cần ít nhất 32 teams, seeder có 64):

```bash
# Test cup 32 teams
php8.2 artisan cup:self-test --teams=32

# Test cup 64 teams
php8.2 artisan cup:self-test --teams=64
```

Lệnh chạy trong transaction và **rollback** — không để lại data test.

### Feature tests

Feature tests mặc định cần database. Bật SQLite in-memory trong `phpunit.xml` nếu máy có `pdo_sqlite`:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

Rồi chạy:

```bash
php8.2 artisan test
```

## Cup mode — flow thủ công

1. `/cup/seasons/create` — chọn 32 hoặc 64 đội
2. Simulate group stage → **Advance to Knockout**
3. `/cup/seasons/{id}/eliminate` — bracket tree, simulate từng vòng
4. Thứ tự knockout: R16 → QF → Round of 8 → SF → **Third place** → **Final**

Chi tiết nghiệp vụ: xem `PLAN.md` mục 11.

## Tài liệu khác

- `PLAN.md` — spec đầy đủ
- `TESTING_CHECKLIST.md` — checklist smoke test UI
- `REFACTOR_SUMMARY.md` — tóm tắt refactor

## License

MIT (Laravel framework).
