# Davya Finance — Phase 2 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add Slack → n8n → Laravel → MySQL finance ingestion to the existing `davya-crm` app: 3 authed HTTP endpoints that capture payments, expenses, and investments; a `LedgerRoutingService` that writes signed ledger rows implementing the Davya/head-split routing math; a fourth endpoint for n8n failure logging; an n8n workflow that glues Slack, Gemini 2.5 Flash, and the Laravel API.

**Architecture:** Piggyback on Phase 1's existing Laravel 11 + Filament codebase in the same repo (`davya-crm`). Reuse Phase 1's `users` and `payments` tables (extending columns, not duplicating). Add 4 new tables (`expenses`, `investments`, `ledger_entries`, `failed_extractions`). All ledger math lives in one service; all routes live under `X-Finance-Token` auth. n8n runs on Sumit's existing Hostinger n8n instance and only speaks HTTPS — no remote MySQL.

**Tech Stack:** PHP 8.5 / Laravel 11, PHPUnit, n8n Public API for workflow import, Slack Events API (bot scopes `channels:history`, `channels:read`, `chat:write`, `reactions:write`), Gemini 2.5 Flash with `responseSchema`.

**Reference spec:** `docs/superpowers/specs/2026-04-16-davya-finance-phase2-design.md`.

**Code location:** `/Users/Sumit/davya-crm/` (existing repo — Phase 2 is a follow-on, not a new project). Plan doc lives in `test-project` (IPU repo) for cross-project history.

**Estimated effort:** ~20–25 hours across 12 milestones. Each milestone ends with a green PHPUnit run + a commit.

**Conventions (matching Phase 1):**
- Controllers flat under `app/Http/Controllers/` with `Finance*` name prefix (no subfolder).
- FormRequests flat under `app/Http/Requests/`.
- Middleware flat under `app/Http/Middleware/`.
- Feature tests in `tests/Feature/`, unit tests in `tests/Unit/`.
- Commits use the same convention as Phase 1: type-scoped imperative (`feat(finance): ...`, `test(finance): ...`).
- Each commit adds both the test(s) and the implementation so the tree never has a failing `php artisan test` on main.

---

## Pre-flight checklist (before Task 1)

Sumit must complete these before any code is written — they need a browser + human accounts and can't be automated:

- [ ] Slack workspace → Apps → Build → create app "Davya Finance Bot"
- [ ] OAuth & Permissions → add Bot Token Scopes: `channels:history`, `channels:read`, `chat:write`, `reactions:write`
- [ ] Event Subscriptions → enable → subscribe bot to `message.channels`
- [ ] Install app to workspace → copy **Bot User OAuth Token** (starts with `xoxb-`)
- [ ] Create Slack channels **`#student-entries`** and **`#finance-log`** (or reuse existing); run `/invite @davya-finance-bot` in each
- [ ] Note the 2 channel IDs (each starts with `C`)
- [ ] Generate `FINANCE_CAPTURE_TOKEN`: `openssl rand -hex 16` — save the output
- [ ] Confirm the existing Gemini API key in n8n (used by KYNE) is reusable, or generate a new one
- [ ] Read the spec at `docs/superpowers/specs/2026-04-16-davya-finance-phase2-design.md` once end-to-end

**Outputs to have on hand when Task 11.1 starts:** bot token, 2 channel IDs, finance token, Gemini credential name in n8n.

---

## Milestone 1 — Schema migrations (≈2 hours)

**Output:** 6 migrations merged and applied locally; all Phase 1 tests still green; Phase 2 tables exist and are empty.

### Task 1.1: Add `split_pct` to `users`

**Files:**
- Create: `/Users/Sumit/davya-crm/database/migrations/2026_04_17_210000_add_split_pct_to_users.php`
- Create/modify: `/Users/Sumit/davya-crm/tests/Feature/UsersSplitPctColumnTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/UsersSplitPctColumnTest.php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UsersSplitPctColumnTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_table_has_split_pct_column_with_default_zero(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'split_pct'));
        $user = \App\Models\User::factory()->create();
        $this->assertSame(0, (int) $user->fresh()->split_pct);
    }
}
```

- [ ] **Step 2: Run to verify it fails**

```
cd /Users/Sumit/davya-crm && php artisan test --filter UsersSplitPctColumnTest
```
Expected: FAIL — column missing.

- [ ] **Step 3: Write the migration**

```php
<?php
// database/migrations/2026_04_17_210000_add_split_pct_to_users.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('split_pct')->default(0)->after('team_head_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('split_pct');
        });
    }
};
```

- [ ] **Step 4: Run test**

```
php artisan test --filter UsersSplitPctColumnTest
```
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
cd /Users/Sumit/davya-crm
git add database/migrations/2026_04_17_210000_add_split_pct_to_users.php tests/Feature/UsersSplitPctColumnTest.php
git commit -m "feat(finance): add split_pct column to users"
```

---

### Task 1.2: Add Slack dedup columns to `payments`, relax `recorded_by_user_id`

**Files:**
- Create: `database/migrations/2026_04_17_210100_add_slack_fields_to_payments.php`
- Create: `tests/Feature/PaymentsSchemaTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/PaymentsSchemaTest.php
namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PaymentsSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_payments_table_has_slack_columns(): void
    {
        $this->assertTrue(Schema::hasColumn('payments', 'slack_message_id'));
        $this->assertTrue(Schema::hasColumn('payments', 'raw_input'));
    }

    public function test_payments_recorded_by_user_id_is_nullable(): void
    {
        $this->seed();
        $sumit = User::where('email','sumit@davya.local')->first();
        $student = Student::create([
            'phone' => '9000000001', 'name' => 'T',
            'owner_id' => $sumit->id, 'referrer_id' => $sumit->id,
            'lead_source' => 'Sumit', 'stage' => 'Lead Captured',
        ]);
        $p = Payment::create([
            'student_id' => $student->id, 'type' => 'full',
            'amount' => 100, 'received_at' => now(),
            'recorded_by_user_id' => null,
            'slack_message_id' => 'C1.1111.1',
            'raw_input' => 'got 100 from T',
        ]);
        $this->assertNull($p->fresh()->recorded_by_user_id);
    }

    public function test_payments_slack_message_id_is_unique(): void
    {
        $this->seed();
        $sumit = User::where('email','sumit@davya.local')->first();
        $student = Student::create([
            'phone' => '9000000002','name' => 'T',
            'owner_id' => $sumit->id,'referrer_id' => $sumit->id,
            'lead_source'=>'Sumit','stage'=>'Lead Captured',
        ]);
        Payment::create([
            'student_id'=>$student->id,'type'=>'full','amount'=>100,
            'received_at'=>now(),'recorded_by_user_id'=>null,
            'slack_message_id'=>'C1.2222.1','raw_input'=>'x',
        ]);
        $this->expectException(QueryException::class);
        Payment::create([
            'student_id'=>$student->id,'type'=>'full','amount'=>200,
            'received_at'=>now(),'recorded_by_user_id'=>null,
            'slack_message_id'=>'C1.2222.1','raw_input'=>'y',
        ]);
    }
}
```

- [ ] **Step 2: Run to verify it fails**

```
php artisan test --filter PaymentsSchemaTest
```
Expected: FAIL — columns missing.

- [ ] **Step 3: Write the migration**

```php
<?php
// database/migrations/2026_04_17_210100_add_slack_fields_to_payments.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('slack_message_id', 50)->nullable()->after('recorded_by_user_id');
            $table->text('raw_input')->nullable()->after('slack_message_id');
            $table->unique('slack_message_id', 'payments_slack_message_id_unique');
            $table->foreignId('recorded_by_user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique('payments_slack_message_id_unique');
            $table->dropColumn(['slack_message_id', 'raw_input']);
            // NOTE: re-tightening NOT NULL on rollback would fail if any Slack-originated NULL rows exist.
            // We intentionally leave it nullable on rollback.
        });
    }
};
```

**doctrine/dbal is required for `->change()` on older Laravel.** Laravel 11 ships native `change()` support, so no composer change needed.

- [ ] **Step 4: Extend `Payment::$fillable` (or confirm `$guarded = []`)**

Open `app/Models/Payment.php`. If it uses `$guarded = []`, nothing to do. If it has `$fillable`, append `'slack_message_id'` and `'raw_input'`.

Quick check + patch if needed:

```bash
grep -E 'fillable|guarded' /Users/Sumit/davya-crm/app/Models/Payment.php
```

If the file has `protected $fillable = [...]` then add the two columns. If it has `protected $guarded = []` you're done. (Phase 1 convention per `LeadController.php:30-44` uses `Student::create(...)` on a `$guarded=[]` model, so Payment likely matches.)

- [ ] **Step 5: Run test**

```
php artisan test --filter PaymentsSchemaTest
```
Expected: PASS (3 tests).

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_04_17_210100_add_slack_fields_to_payments.php tests/Feature/PaymentsSchemaTest.php app/Models/Payment.php
git commit -m "feat(finance): add slack_message_id + raw_input to payments, relax recorded_by_user_id"
```

---

### Task 1.3: Create `expenses` table

**Files:**
- Create: `database/migrations/2026_04_17_210200_create_expenses_table.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/ExpensesSchemaTest.php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExpensesSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_expenses_table_has_required_columns(): void
    {
        $cols = ['id','amount','category','description','paid_at',
                 'slack_message_id','raw_input','created_at','updated_at'];
        foreach ($cols as $c) {
            $this->assertTrue(Schema::hasColumn('expenses', $c), "missing $c");
        }
    }
}
```

- [ ] **Step 2: Run — expect failure.**

```
php artisan test --filter ExpensesSchemaTest
```

- [ ] **Step 3: Write the migration**

```php
<?php
// database/migrations/2026_04_17_210200_create_expenses_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 12, 2);
            $table->string('category', 60)->nullable();
            $table->text('description')->nullable();
            $table->timestamp('paid_at');
            $table->string('slack_message_id', 50)->unique();
            $table->text('raw_input')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
```

- [ ] **Step 4: Run test — expect PASS.**

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_04_17_210200_create_expenses_table.php tests/Feature/ExpensesSchemaTest.php
git commit -m "feat(finance): create expenses table"
```

---

### Task 1.4: Create `investments` table

**Files:**
- Create: `database/migrations/2026_04_17_210300_create_investments_table.php`
- Create: `tests/Feature/InvestmentsSchemaTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InvestmentsSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_investments_table_has_required_columns(): void
    {
        $cols = ['id','asset_name','amount','direction','transacted_at',
                 'slack_message_id','raw_input','created_at','updated_at'];
        foreach ($cols as $c) {
            $this->assertTrue(Schema::hasColumn('investments', $c), "missing $c");
        }
    }
}
```

- [ ] **Step 2: Run — expect failure.**

- [ ] **Step 3: Write the migration**

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->string('asset_name', 80);
            $table->decimal('amount', 12, 2);
            $table->enum('direction', ['in', 'out']);
            $table->timestamp('transacted_at');
            $table->string('slack_message_id', 50)->unique();
            $table->text('raw_input')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
```

- [ ] **Step 4: Run test — expect PASS.**

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_04_17_210300_create_investments_table.php tests/Feature/InvestmentsSchemaTest.php
git commit -m "feat(finance): create investments table"
```

---

### Task 1.5: Create `ledger_entries` table

**Files:**
- Create: `database/migrations/2026_04_17_210400_create_ledger_entries_table.php`
- Create: `tests/Feature/LedgerEntriesSchemaTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LedgerEntriesSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_ledger_entries_table_has_required_columns(): void
    {
        $cols = ['id','account','delta_amount','source_type','source_id','note','created_at'];
        foreach ($cols as $c) {
            $this->assertTrue(Schema::hasColumn('ledger_entries', $c), "missing $c");
        }
    }
}
```

- [ ] **Step 2: Run — expect failure.**

- [ ] **Step 3: Write the migration**

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->string('account', 60);
            $table->decimal('delta_amount', 12, 2);
            $table->enum('source_type', ['payment', 'expense', 'investment']);
            $table->unsignedBigInteger('source_id');
            $table->string('note', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['account', 'created_at'], 'idx_ledger_account_created');
            $table->index(['source_type', 'source_id'], 'idx_ledger_source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
```

- [ ] **Step 4: Run test — expect PASS.**

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_04_17_210400_create_ledger_entries_table.php tests/Feature/LedgerEntriesSchemaTest.php
git commit -m "feat(finance): create ledger_entries table"
```

---

### Task 1.6: Create `failed_extractions` table

**Files:**
- Create: `database/migrations/2026_04_17_210500_create_failed_extractions_table.php`
- Create: `tests/Feature/FailedExtractionsSchemaTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FailedExtractionsSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_extractions_table_has_required_columns(): void
    {
        $cols = ['id','slack_message_id','slack_channel','raw_input','error_reason','created_at'];
        foreach ($cols as $c) {
            $this->assertTrue(Schema::hasColumn('failed_extractions', $c), "missing $c");
        }
    }
}
```

- [ ] **Step 2: Run — expect failure.**

- [ ] **Step 3: Write the migration**

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('failed_extractions', function (Blueprint $table) {
            $table->id();
            $table->string('slack_message_id', 50);  // NOT unique — same msg may fail repeatedly
            $table->string('slack_channel', 60)->nullable();
            $table->text('raw_input')->nullable();
            $table->string('error_reason', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_extractions');
    }
};
```

- [ ] **Step 4: Run test — expect PASS.**

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_04_17_210500_create_failed_extractions_table.php tests/Feature/FailedExtractionsSchemaTest.php
git commit -m "feat(finance): create failed_extractions table"
```

### M1 checkpoint

- [ ] Run full suite:

```
php artisan test
```
Expected: all Phase 1 tests still green + 6 new schema tests pass. No regressions.

---

## Milestone 2 — Models (≈1.5 hours)

**Output:** 4 new Eloquent models with casts, relationships; Nikhil's `split_pct=60` seeded.

### Task 2.1: `Expense` model

**Files:**
- Create: `app/Models/Expense.php`
- Create: `database/factories/ExpenseFactory.php`
- Create: `tests/Unit/ExpenseModelTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Unit/ExpenseModelTest.php
namespace Tests\Unit;

use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_casts_amount_as_decimal_and_paid_at_as_datetime(): void
    {
        $e = Expense::create([
            'amount' => 5000,
            'category' => 'Marketing',
            'description' => 'fb ads',
            'paid_at' => '2026-04-17 10:00:00',
            'slack_message_id' => 'C2.1.1',
            'raw_input' => 'paid 5k fb ads',
        ]);
        $fresh = $e->fresh();
        $this->assertSame('5000.00', (string) $fresh->amount);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $fresh->paid_at);
    }
}
```

- [ ] **Step 2: Run — expect failure (no Expense model).**

- [ ] **Step 3: Create the model**

```php
<?php
// app/Models/Expense.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];
}
```

- [ ] **Step 4: Create the factory**

```php
<?php
// database/factories/ExpenseFactory.php
namespace Database\Factories;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'amount' => $this->faker->numberBetween(100, 50000),
            'category' => $this->faker->randomElement(['Marketing','Rent','Food','Office']),
            'description' => $this->faker->sentence(),
            'paid_at' => now(),
            'slack_message_id' => 'CTEST.'.$this->faker->unique()->numerify('##########.######'),
            'raw_input' => $this->faker->sentence(),
        ];
    }
}
```

- [ ] **Step 5: Run test — expect PASS.**

- [ ] **Step 6: Commit**

```bash
git add app/Models/Expense.php database/factories/ExpenseFactory.php tests/Unit/ExpenseModelTest.php
git commit -m "feat(finance): Expense model + factory"
```

---

### Task 2.2: `Investment` model

**Files:**
- Create: `app/Models/Investment.php`
- Create: `database/factories/InvestmentFactory.php`
- Create: `tests/Unit/InvestmentModelTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
namespace Tests\Unit;

use App\Models\Investment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestmentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_investment_enforces_direction_enum_via_db(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        Investment::create([
            'asset_name' => 'Tata',
            'amount' => 1000,
            'direction' => 'sideways',
            'transacted_at' => now(),
            'slack_message_id' => 'C3.1.1',
        ]);
    }

    public function test_investment_casts_and_accepts_both_directions(): void
    {
        $out = Investment::create([
            'asset_name' => 'Tata','amount' => 1000,
            'direction' => 'out','transacted_at' => now(),
            'slack_message_id' => 'C3.2.1',
        ]);
        $in = Investment::create([
            'asset_name' => 'Tata','amount' => 1200,
            'direction' => 'in','transacted_at' => now(),
            'slack_message_id' => 'C3.3.1',
        ]);
        $this->assertSame('out', $out->fresh()->direction);
        $this->assertSame('in', $in->fresh()->direction);
    }
}
```

- [ ] **Step 2: Run — expect failure.**

- [ ] **Step 3: Create the model**

```php
<?php
// app/Models/Investment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'amount'        => 'decimal:2',
        'transacted_at' => 'datetime',
    ];
}
```

- [ ] **Step 4: Create the factory**

```php
<?php
// database/factories/InvestmentFactory.php
namespace Database\Factories;

use App\Models\Investment;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvestmentFactory extends Factory
{
    protected $model = Investment::class;

    public function definition(): array
    {
        return [
            'asset_name' => $this->faker->randomElement(['Tata Motors','Real Estate #12','Binance BTC']),
            'amount'     => $this->faker->numberBetween(10000, 500000),
            'direction'  => $this->faker->randomElement(['in','out']),
            'transacted_at' => now(),
            'slack_message_id' => 'CTEST.'.$this->faker->unique()->numerify('##########.######'),
            'raw_input'  => $this->faker->sentence(),
        ];
    }
}
```

- [ ] **Step 5: Run test — expect PASS.**

- [ ] **Step 6: Commit**

```bash
git add app/Models/Investment.php database/factories/InvestmentFactory.php tests/Unit/InvestmentModelTest.php
git commit -m "feat(finance): Investment model + factory"
```

---

### Task 2.3: `LedgerEntry` model

**Files:**
- Create: `app/Models/LedgerEntry.php`
- Create: `tests/Unit/LedgerEntryModelTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
namespace Tests\Unit;

use App\Models\LedgerEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LedgerEntryModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_ledger_entry_casts_delta_as_decimal_and_accepts_negative(): void
    {
        $e = LedgerEntry::create([
            'account' => 'davya',
            'delta_amount' => -5000,
            'source_type' => 'expense',
            'source_id' => 1,
            'note' => 'expense: Marketing',
        ]);
        $this->assertSame('-5000.00', (string) $e->fresh()->delta_amount);
        $this->assertSame('davya', $e->fresh()->account);
    }
}
```

- [ ] **Step 2: Run — expect failure.**

- [ ] **Step 3: Create the model**

```php
<?php
// app/Models/LedgerEntry.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LedgerEntry extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'delta_amount' => 'decimal:2',
        'created_at'   => 'datetime',
    ];

    public static function balanceFor(string $account): string
    {
        return (string) (self::where('account', $account)->sum('delta_amount') ?: '0.00');
    }
}
```

- [ ] **Step 4: Run test — expect PASS.**

- [ ] **Step 5: Commit**

```bash
git add app/Models/LedgerEntry.php tests/Unit/LedgerEntryModelTest.php
git commit -m "feat(finance): LedgerEntry model with balanceFor helper"
```

---

### Task 2.4: `FailedExtraction` model

**Files:**
- Create: `app/Models/FailedExtraction.php`
- Create: `tests/Unit/FailedExtractionModelTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
namespace Tests\Unit;

use App\Models\FailedExtraction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FailedExtractionModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_multiple_failed_extractions_can_share_slack_message_id(): void
    {
        FailedExtraction::create([
            'slack_message_id' => 'C1.1.1',
            'slack_channel' => '#student-entries',
            'raw_input' => 'gobbledy',
            'error_reason' => 'gemini invalid JSON',
        ]);
        FailedExtraction::create([
            'slack_message_id' => 'C1.1.1',
            'slack_channel' => '#student-entries',
            'raw_input' => 'gobbledy',
            'error_reason' => 'second retry: still invalid',
        ]);
        $this->assertSame(2, FailedExtraction::count());
    }
}
```

- [ ] **Step 2: Run — expect failure.**

- [ ] **Step 3: Create the model**

```php
<?php
// app/Models/FailedExtraction.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FailedExtraction extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = ['created_at' => 'datetime'];
}
```

- [ ] **Step 4: Run test — expect PASS.**

- [ ] **Step 5: Commit**

```bash
git add app/Models/FailedExtraction.php tests/Unit/FailedExtractionModelTest.php
git commit -m "feat(finance): FailedExtraction model"
```

---

### Task 2.5: Seed Nikhil's `split_pct = 60`

**Files:**
- Modify: `database/seeders/UsersSeeder.php`
- Create: `tests/Feature/SplitPctSeedTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/SplitPctSeedTest.php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SplitPctSeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_nikhil_seed_sets_split_pct_to_60(): void
    {
        $this->seed();
        $this->assertSame(60, (int) User::where('email','nikhil@davya.local')->first()->split_pct);
    }

    public function test_other_users_split_pct_stays_zero_by_default(): void
    {
        $this->seed();
        foreach (['sumit','sonam','nisha','poonam','neetu','kapil'] as $slug) {
            $u = User::where('email', "$slug@davya.local")->first();
            $this->assertSame(0, (int) $u->split_pct, "$slug should be 0, got {$u->split_pct}");
        }
    }
}
```

- [ ] **Step 2: Run — expect failure (no split_pct set by seed).**

- [ ] **Step 3: Modify the seeder**

Open `database/seeders/UsersSeeder.php`. After Nikhil is `updateOrCreate`d, set his `split_pct`. The exact existing code shape may vary; patch in place:

```php
// after $nikhil = User::updateOrCreate(...)
$nikhil->update(['split_pct' => 60]);
```

If `updateOrCreate`'s first array uses `email`, you can fold it into the values array instead:

```php
$nikhil = User::updateOrCreate(
    ['email' => 'nikhil@davya.local'],
    [
        'name' => 'Nikhil',
        'password' => Hash::make('ChangeMe123'),
        'is_freelancer' => false,
        'is_active' => true,
        'split_pct' => 60,
    ]
);
```

Inspect the current seeder first (`cat database/seeders/UsersSeeder.php`) and patch whichever variant is present — **do not rewrite unrelated rows**.

- [ ] **Step 4: Run test — expect PASS (both).**

- [ ] **Step 5: Commit**

```bash
git add database/seeders/UsersSeeder.php tests/Feature/SplitPctSeedTest.php
git commit -m "feat(finance): seed Nikhil split_pct=60"
```

### M2 checkpoint

- [ ] Full suite: `php artisan test` — all Phase 1 + new model tests green.

---

## Milestone 3 — `LedgerRoutingService` (≈3 hours)

**Output:** Pure business-logic service implementing all routing math from spec §5.2, fully unit-tested.

### Task 3.1: Write the full unit-test suite for the service (all §5.2 branches)

**Files:**
- Create: `tests/Unit/LedgerRoutingServiceTest.php`

- [ ] **Step 1: Write the failing tests (one file, 11 test cases)**

```php
<?php
// tests/Unit/LedgerRoutingServiceTest.php
namespace Tests\Unit;

use App\Models\Expense;
use App\Models\Investment;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Services\Finance\LedgerRoutingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LedgerRoutingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected LedgerRoutingService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->svc = new LedgerRoutingService();
    }

    private function makeStudent(string $referrerEmail, string $phone): Student
    {
        $referrer = User::where('email', $referrerEmail)->firstOrFail();
        // owner_id is derived elsewhere — pick any valid user for tests
        return Student::create([
            'phone' => $phone, 'name' => 'T',
            'owner_id' => $referrer->team_head_id ?? $referrer->id,
            'referrer_id' => $referrer->id,
            'lead_source' => $referrer->name,
            'stage' => 'Lead Captured',
        ]);
    }

    private function makePayment(Student $student, float $amount): Payment
    {
        return Payment::create([
            'student_id' => $student->id,
            'type' => 'full',
            'amount' => $amount,
            'received_at' => now(),
            'recorded_by_user_id' => null,
            'slack_message_id' => uniqid('TEST.'),
            'raw_input' => 'test',
        ]);
    }

    public function test_freelancer_referral_routes_100pct_to_davya(): void
    {
        $student = $this->makeStudent('kapil@davya.local', '9100000001');
        $p = $this->makePayment($student, 30000);
        $rows = $this->svc->routePayment($p);
        $this->assertCount(1, $rows);
        $this->assertSame('davya', $rows[0]['account']);
        $this->assertSame('30000.00', (string) $rows[0]['delta_amount']);
    }

    public function test_head_with_0pct_split_routes_100pct_to_davya(): void
    {
        $student = $this->makeStudent('sumit@davya.local', '9100000002');
        $p = $this->makePayment($student, 40000);
        $rows = $this->svc->routePayment($p);
        $this->assertCount(1, $rows);
        $this->assertSame('davya', $rows[0]['account']);
        $this->assertSame('40000.00', (string) $rows[0]['delta_amount']);
    }

    public function test_head_with_60pct_split_routes_60_40(): void
    {
        $student = $this->makeStudent('nikhil@davya.local', '9100000003');
        $p = $this->makePayment($student, 50000);
        $rows = $this->svc->routePayment($p);
        $this->assertCount(2, $rows);
        // order: head first, davya second (per §5.2)
        $this->assertSame('nikhil', $rows[0]['account']);
        $this->assertSame('30000.00', (string) $rows[0]['delta_amount']);
        $this->assertSame('davya',  $rows[1]['account']);
        $this->assertSame('20000.00', (string) $rows[1]['delta_amount']);
    }

    public function test_member_referral_rolls_up_to_head_split(): void
    {
        // Nisha is a member under Nikhil (60%)
        $student = $this->makeStudent('nisha@davya.local', '9100000004');
        $p = $this->makePayment($student, 50000);
        $rows = $this->svc->routePayment($p);
        $this->assertCount(2, $rows);
        $this->assertSame('nikhil', $rows[0]['account']);
        $this->assertSame('30000.00', (string) $rows[0]['delta_amount']);
        $this->assertSame('davya', $rows[1]['account']);
        $this->assertSame('20000.00', (string) $rows[1]['delta_amount']);
    }

    public function test_member_under_0pct_head_routes_100_to_davya(): void
    {
        // Poonam is a member under Sonam (0%)
        $student = $this->makeStudent('poonam@davya.local', '9100000005');
        $p = $this->makePayment($student, 40000);
        $rows = $this->svc->routePayment($p);
        $this->assertCount(1, $rows);
        $this->assertSame('davya', $rows[0]['account']);
        $this->assertSame('40000.00', (string) $rows[0]['delta_amount']);
    }

    public function test_ledger_account_names_are_always_lowercase(): void
    {
        $student = $this->makeStudent('nikhil@davya.local', '9100000006');
        $p = $this->makePayment($student, 10000);
        $rows = $this->svc->routePayment($p);
        foreach ($rows as $r) {
            $this->assertSame(strtolower($r['account']), $r['account']);
        }
    }

    public function test_split_math_rounds_to_two_decimals_preserving_total(): void
    {
        // 60% of 33333 = 19999.80; davya = 13333.20; sum = 33333.00 exactly
        $student = $this->makeStudent('nikhil@davya.local', '9100000007');
        $p = $this->makePayment($student, 33333);
        $rows = $this->svc->routePayment($p);
        $sum = array_sum(array_map(fn ($r) => (float) $r['delta_amount'], $rows));
        $this->assertEqualsWithDelta(33333.00, $sum, 0.001);
    }

    public function test_expense_debits_davya(): void
    {
        $e = Expense::create([
            'amount' => 5000, 'category' => 'Marketing',
            'paid_at' => now(), 'slack_message_id' => 'E.1',
            'raw_input' => 'fb ads',
        ]);
        $rows = $this->svc->routeExpense($e);
        $this->assertCount(1, $rows);
        $this->assertSame('davya', $rows[0]['account']);
        $this->assertSame('-5000.00', (string) $rows[0]['delta_amount']);
    }

    public function test_investment_out_debits_davya(): void
    {
        $i = Investment::create([
            'asset_name' => 'Tata', 'amount' => 100000,
            'direction' => 'out', 'transacted_at' => now(),
            'slack_message_id' => 'I.1',
        ]);
        $rows = $this->svc->routeInvestment($i);
        $this->assertCount(1, $rows);
        $this->assertSame('davya', $rows[0]['account']);
        $this->assertSame('-100000.00', (string) $rows[0]['delta_amount']);
    }

    public function test_investment_in_credits_davya(): void
    {
        $i = Investment::create([
            'asset_name' => 'Tata', 'amount' => 120000,
            'direction' => 'in', 'transacted_at' => now(),
            'slack_message_id' => 'I.2',
        ]);
        $rows = $this->svc->routeInvestment($i);
        $this->assertCount(1, $rows);
        $this->assertSame('davya', $rows[0]['account']);
        $this->assertSame('120000.00', (string) $rows[0]['delta_amount']);
    }

    public function test_returned_rows_include_source_type_and_source_id(): void
    {
        $student = $this->makeStudent('nikhil@davya.local', '9100000008');
        $p = $this->makePayment($student, 10000);
        $rows = $this->svc->routePayment($p);
        foreach ($rows as $r) {
            $this->assertSame('payment', $r['source_type']);
            $this->assertSame($p->id, $r['source_id']);
            $this->assertArrayHasKey('note', $r);
        }
    }
}
```

- [ ] **Step 2: Run — expect FAIL (service doesn't exist).**

```
php artisan test --filter LedgerRoutingServiceTest
```

### Task 3.2: Implement `LedgerRoutingService`

**Files:**
- Create: `app/Services/Finance/LedgerRoutingService.php`

- [ ] **Step 1: Write the service**

```php
<?php
// app/Services/Finance/LedgerRoutingService.php
namespace App\Services\Finance;

use App\Models\Expense;
use App\Models\Investment;
use App\Models\Payment;
use App\Models\User;

class LedgerRoutingService
{
    public const DAVYA_ACCOUNT = 'davya';

    /**
     * Compute ledger_entries rows for a Payment. Returns an array of
     * associative arrays shaped ['account','delta_amount','source_type','source_id','note'].
     * Does NOT persist — the controller writes them inside a transaction.
     *
     * @return array<int, array<string, mixed>>
     */
    public function routePayment(Payment $payment): array
    {
        $referrer = $payment->student->referrer;

        if ($referrer === null) {
            // Walk-in or unresolved — safest behaviour: full amount to Davya.
            return [$this->row(self::DAVYA_ACCOUNT, $payment->amount, 'payment', $payment->id, 'no referrer')];
        }

        if ((bool) $referrer->is_freelancer) {
            return [$this->row(self::DAVYA_ACCOUNT, $payment->amount, 'payment', $payment->id, 'freelancer referral')];
        }

        $head = $referrer->team_head_id !== null
            ? User::find($referrer->team_head_id)
            : $referrer;

        if ($head === null || (int) $head->split_pct === 0) {
            $whoseSplit = $head ? $head->name : 'unknown';
            return [$this->row(self::DAVYA_ACCOUNT, $payment->amount, 'payment', $payment->id, "head {$whoseSplit} has 0% split")];
        }

        $headShare  = round(((float) $payment->amount) * ((int) $head->split_pct) / 100, 2);
        $davyaShare = round(((float) $payment->amount) - $headShare, 2);

        return [
            $this->row(strtolower($head->name), $headShare,  'payment', $payment->id, "head share {$head->split_pct}%"),
            $this->row(self::DAVYA_ACCOUNT,     $davyaShare, 'payment', $payment->id, 'davya share'),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function routeExpense(Expense $expense): array
    {
        $note = 'expense'.($expense->category ? ": {$expense->category}" : '');
        return [$this->row(self::DAVYA_ACCOUNT, -$expense->amount, 'expense', $expense->id, $note)];
    }

    /** @return array<int, array<string, mixed>> */
    public function routeInvestment(Investment $inv): array
    {
        $sign = $inv->direction === 'in' ? 1 : -1;
        $delta = $sign * (float) $inv->amount;
        $note = "investment {$inv->direction}: {$inv->asset_name}";
        return [$this->row(self::DAVYA_ACCOUNT, $delta, 'investment', $inv->id, $note)];
    }

    /** @return array<string, mixed> */
    private function row(string $account, float|string $delta, string $sourceType, int $sourceId, string $note): array
    {
        return [
            'account'      => $account,
            'delta_amount' => $delta,
            'source_type'  => $sourceType,
            'source_id'    => $sourceId,
            'note'         => $note,
        ];
    }
}
```

- [ ] **Step 2: Run tests**

```
php artisan test --filter LedgerRoutingServiceTest
```
Expected: PASS (11 tests).

- [ ] **Step 3: Commit**

```bash
git add app/Services/Finance/LedgerRoutingService.php tests/Unit/LedgerRoutingServiceTest.php
git commit -m "feat(finance): LedgerRoutingService with head split + freelancer + expense + investment routing"
```

### M3 checkpoint

- [ ] `php artisan test` fully green.

---

## Milestone 4 — Auth middleware + config (≈40 min)

### Task 4.1: `config/finance.php` + env placeholder

**Files:**
- Create: `config/finance.php`
- Modify: `/Users/Sumit/davya-crm/.env.example`

- [ ] **Step 1: Create config**

```php
<?php
// config/finance.php
return [
    'capture_token' => env('FINANCE_CAPTURE_TOKEN'),
];
```

- [ ] **Step 2: Update `.env.example`**

Append (don't modify existing `LEAD_CAPTURE_TOKEN` line):

```
FINANCE_CAPTURE_TOKEN=
```

- [ ] **Step 3: Set local token in `.env`**

```bash
cd /Users/Sumit/davya-crm
echo "FINANCE_CAPTURE_TOKEN=$(openssl rand -hex 16)" >> .env
```

(Don't commit the `.env`; it's gitignored.)

- [ ] **Step 4: Commit config + example**

```bash
git add config/finance.php .env.example
git commit -m "feat(finance): config/finance.php + FINANCE_CAPTURE_TOKEN env"
```

### Task 4.2: `VerifyFinanceToken` middleware

**Files:**
- Create: `app/Http/Middleware/VerifyFinanceToken.php`
- Create: `tests/Feature/VerifyFinanceTokenTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/VerifyFinanceTokenTest.php
namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class VerifyFinanceTokenTest extends TestCase
{
    private const TOKEN = 'test-finance-token-abcdef0123456789';

    protected function setUp(): void
    {
        parent::setUp();
        config(['finance.capture_token' => self::TOKEN]);
        Route::post('/__test-finance', fn () => response()->json(['ok' => true]))
            ->middleware(\App\Http\Middleware\VerifyFinanceToken::class);
    }

    public function test_valid_token_passes(): void
    {
        $this->postJson('/__test-finance', [], ['X-Finance-Token' => self::TOKEN])
             ->assertOk()->assertJson(['ok' => true]);
    }

    public function test_missing_token_returns_401(): void
    {
        $this->postJson('/__test-finance', [])->assertStatus(401)->assertJson(['error'=>'unauthorized']);
    }

    public function test_wrong_token_returns_401(): void
    {
        $this->postJson('/__test-finance', [], ['X-Finance-Token' => 'wrong'])
             ->assertStatus(401)->assertJson(['error'=>'unauthorized']);
    }
}
```

- [ ] **Step 2: Run — expect failure (middleware class missing).**

- [ ] **Step 3: Create the middleware**

```php
<?php
// app/Http/Middleware/VerifyFinanceToken.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyFinanceToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('finance.capture_token');
        $provided = (string) $request->header('X-Finance-Token', '');

        if ($expected === '' || ! hash_equals($expected, $provided)) {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        return $next($request);
    }
}
```

- [ ] **Step 4: Run test — expect PASS (3 cases).**

- [ ] **Step 5: Commit**

```bash
git add app/Http/Middleware/VerifyFinanceToken.php tests/Feature/VerifyFinanceTokenTest.php
git commit -m "feat(finance): VerifyFinanceToken middleware"
```

---

## Milestone 5 — `POST /api/finance/payments` (≈3 hours)

**Output:** One authed endpoint that accepts Slack-shaped payment JSON, creates or matches a Student, writes Payment + ledger_entries in a transaction, handles 401/409/422.

### Task 5.1: `StoreFinancePaymentRequest`

**Files:**
- Create: `app/Http/Requests/StoreFinancePaymentRequest.php`

- [ ] **Step 1: Write the class**

```php
<?php
// app/Http/Requests/StoreFinancePaymentRequest.php
namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreFinancePaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'student_phone' => $this->digitsOnly($this->input('student_phone')),
        ]);
    }

    public function rules(): array
    {
        return [
            'student_phone'    => ['required', 'string', 'regex:/^\d{10}$/'],
            'amount'           => ['required', 'numeric', 'gt:0', 'lte:10000000'],
            'student_name'     => ['nullable', 'string', 'max:120'],
            'referrer_name'    => ['nullable', 'string', 'max:60'],
            'is_partial'       => ['nullable', 'boolean'],
            'received_at'      => ['nullable', 'date'],
            'slack_message_id' => ['required', 'string', 'max:50'],
            'raw_input'        => ['nullable', 'string', 'max:4000'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'The given data was invalid.',
            'errors'  => $validator->errors(),
        ], 422));
    }

    private function digitsOnly(?string $v): ?string
    {
        if ($v === null || $v === '') return null;
        $d = preg_replace('/\D+/', '', $v);
        if (strlen($d) === 12 && str_starts_with($d, '91')) $d = substr($d, 2);
        return $d;
    }
}
```

(No test for the FormRequest in isolation — it's exercised by Task 5.3 feature tests.)

### Task 5.2: `FinancePaymentController`

**Files:**
- Create: `app/Http/Controllers/FinancePaymentController.php`

- [ ] **Step 1: Write the controller**

```php
<?php
// app/Http/Controllers/FinancePaymentController.php
namespace App\Http\Controllers;

use App\Http\Requests\StoreFinancePaymentRequest;
use App\Models\LedgerEntry;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Services\Finance\LedgerRoutingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinancePaymentController extends Controller
{
    public function store(StoreFinancePaymentRequest $request, LedgerRoutingService $routing): JsonResponse
    {
        $data = $request->validated();

        // Idempotency: same slack_message_id seen before?
        $existing = Payment::where('slack_message_id', $data['slack_message_id'])->first();
        if ($existing !== null) {
            return response()->json([
                'error'       => 'duplicate_slack_message',
                'existing_id' => $existing->id,
            ], 409);
        }

        // Resolve or create student
        $student = Student::where('phone', $data['student_phone'])->first();
        if ($student === null) {
            // Require referrer_name for new phones
            if (empty($data['referrer_name'])) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors'  => ['referrer_name' => ['Referrer is required for new students.']],
                ], 422);
            }
            [$referrerId, $ownerId] = $this->deriveOwnership($data['referrer_name']);
            if ($referrerId === null && strtolower($data['referrer_name']) !== 'walk-in / self') {
                // Unknown referrer name (not Walk-in) → 422, no insert
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors'  => ['referrer_name' => ["Unknown referrer '{$data['referrer_name']}'."]],
                ], 422);
            }
            $student = Student::create([
                'phone'       => $data['student_phone'],
                'name'        => $data['student_name'] ?? null,
                'owner_id'    => $ownerId,
                'referrer_id' => $referrerId,
                'lead_source' => $data['referrer_name'],
                'stage'       => 'Lead Captured',
            ]);
        }

        // Build Payment + ledger rows atomically
        $type = ($data['is_partial'] ?? false) ? 'partial' : 'full';

        $result = DB::transaction(function () use ($data, $student, $type, $routing) {
            $payment = Payment::create([
                'student_id'         => $student->id,
                'type'               => $type,
                'amount'             => $data['amount'],
                'received_at'        => $data['received_at'] ?? now(),
                'recorded_by_user_id'=> null,
                'slack_message_id'   => $data['slack_message_id'],
                'raw_input'          => $data['raw_input'] ?? null,
            ]);

            $ledger = $routing->routePayment($payment);
            foreach ($ledger as $row) {
                LedgerEntry::create($row);
            }

            return ['payment' => $payment, 'ledger_count' => count($ledger)];
        });

        Log::info('finance.payment.captured', [
            'payment_id'  => $result['payment']->id,
            'student_id'  => $student->id,
            'slack_id'    => $data['slack_message_id'],
            'ledger_rows' => $result['ledger_count'],
        ]);

        return response()->json([
            'id'             => $result['payment']->id,
            'ledger_entries' => $result['ledger_count'],
        ], 201);
    }

    /**
     * Same logic as LeadController::deriveOwnership. Duplicated intentionally —
     * pulling it into a trait/service isn't justified by a single extra caller.
     *
     * @return array{0: ?int, 1: int}
     */
    private function deriveOwnership(string $referrerName): array
    {
        if (strtolower($referrerName) === 'walk-in / self') {
            return [null, $this->adminId()];
        }
        $referrer = User::whereRaw('LOWER(name) = ?', [strtolower($referrerName)])->first();
        if ($referrer === null) {
            return [null, $this->adminId()];
        }
        $ownerId = $referrer->team_head_id ?? $referrer->id;
        return [$referrer->id, $ownerId];
    }

    private function adminId(): int
    {
        return User::role('admin')->firstOrFail()->id;
    }
}
```

### Task 5.3: `PaymentCaptureTest` feature tests

**Files:**
- Create: `tests/Feature/PaymentCaptureTest.php`

- [ ] **Step 1: Write the full test file**

```php
<?php
// tests/Feature/PaymentCaptureTest.php
namespace Tests\Feature;

use App\Models\LedgerEntry;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PaymentCaptureTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'test-finance-token-abcdef0123456789';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config(['finance.capture_token' => self::TOKEN]);
        // Register route for this test run (task 9 wires it permanently in routes/api.php)
        Route::post('/api/finance/payments', [\App\Http\Controllers\FinancePaymentController::class, 'store'])
            ->middleware(\App\Http\Middleware\VerifyFinanceToken::class);
    }

    private function post(array $overrides = [], ?string $token = self::TOKEN)
    {
        $payload = array_merge([
            'student_phone'    => '9100000001',
            'amount'           => 50000,
            'student_name'     => 'Priya Verma',
            'referrer_name'    => 'Nisha',
            'is_partial'       => false,
            'slack_message_id' => 'C1.'.uniqid(),
            'raw_input'        => '50k from priya via nisha',
        ], $overrides);
        $headers = $token === null ? [] : ['X-Finance-Token' => $token];
        return $this->postJson('/api/finance/payments', $payload, $headers);
    }

    // --- auth ---

    public function test_missing_token_returns_401(): void
    {
        $this->post([], token: null)->assertStatus(401);
        $this->assertSame(0, Payment::count());
        $this->assertSame(0, LedgerEntry::count());
    }

    public function test_wrong_token_returns_401(): void
    {
        $this->post([], token: 'nope')->assertStatus(401);
    }

    // --- happy path ---

    public function test_new_student_with_member_referrer_creates_payment_and_two_ledger_rows(): void
    {
        $resp = $this->post(['amount' => 50000, 'referrer_name' => 'Nisha']);
        $resp->assertCreated()->assertJsonStructure(['id','ledger_entries']);
        $this->assertSame(2, $resp->json('ledger_entries'));

        $student = Student::where('phone','9100000001')->first();
        $this->assertNotNull($student);
        $this->assertSame('Nisha', User::find($student->referrer_id)->name);

        $ledger = LedgerEntry::orderBy('id')->get();
        $this->assertCount(2, $ledger);
        $this->assertSame('nikhil', $ledger[0]->account);
        $this->assertSame('30000.00', (string) $ledger[0]->delta_amount);
        $this->assertSame('davya',  $ledger[1]->account);
        $this->assertSame('20000.00', (string) $ledger[1]->delta_amount);
    }

    public function test_existing_student_ignores_request_referrer_name(): void
    {
        // Seed a student whose referrer is Sonam (0% head), then post a payment claiming Nisha as referrer.
        $sonam = User::where('email','sonam@davya.local')->first();
        Student::create([
            'phone' => '9100000099', 'name' => 'Existing',
            'owner_id' => $sonam->id, 'referrer_id' => $sonam->id,
            'lead_source' => 'Sonam', 'stage' => 'Lead Captured',
        ]);
        $this->post(['student_phone' => '9100000099','referrer_name' => 'Nisha','amount' => 40000])
             ->assertCreated();
        // Because the student's stored referrer is Sonam (0% split), ALL 40k goes to davya.
        $rows = LedgerEntry::all();
        $this->assertCount(1, $rows);
        $this->assertSame('davya', $rows[0]->account);
        $this->assertSame('40000.00', (string) $rows[0]->delta_amount);
    }

    public function test_freelancer_referral_routes_to_davya_only(): void
    {
        $this->post(['student_phone' => '9100000002','referrer_name' => 'Kapil','amount' => 30000])
             ->assertCreated();
        $rows = LedgerEntry::all();
        $this->assertCount(1, $rows);
        $this->assertSame('davya', $rows[0]->account);
        $this->assertSame('30000.00', (string) $rows[0]->delta_amount);
    }

    // --- idempotency ---

    public function test_duplicate_slack_message_id_returns_409_with_existing_id(): void
    {
        $first = $this->post(['slack_message_id' => 'DUPE.1'])->assertCreated();
        $firstId = $first->json('id');
        $second = $this->post(['student_phone' => '9100000003','slack_message_id' => 'DUPE.1']);
        $second->assertStatus(409);
        $second->assertJson(['error' => 'duplicate_slack_message', 'existing_id' => $firstId]);
        $this->assertSame(1, Payment::where('slack_message_id','DUPE.1')->count());
    }

    // --- validation ---

    public function test_missing_amount_returns_422(): void
    {
        $this->post(['amount' => null])->assertStatus(422)->assertJsonValidationErrors('amount');
    }

    public function test_missing_phone_returns_422(): void
    {
        $this->post(['student_phone' => ''])->assertStatus(422)->assertJsonValidationErrors('student_phone');
    }

    public function test_new_student_without_referrer_name_returns_422(): void
    {
        $this->post(['student_phone' => '9100000004','referrer_name' => null])
             ->assertStatus(422)
             ->assertJsonValidationErrors('referrer_name');
    }

    public function test_unknown_referrer_for_new_student_returns_422(): void
    {
        $this->post(['student_phone' => '9100000005','referrer_name' => 'SomeRandomName'])
             ->assertStatus(422)
             ->assertJsonValidationErrors('referrer_name');
        $this->assertSame(0, Payment::count());
    }
}
```

- [ ] **Step 2: Run — expect PASS** (controller is already written in 5.2).

```
php artisan test --filter PaymentCaptureTest
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Requests/StoreFinancePaymentRequest.php \
        app/Http/Controllers/FinancePaymentController.php \
        tests/Feature/PaymentCaptureTest.php
git commit -m "feat(finance): POST /api/finance/payments with routing + idempotency"
```

### M5 checkpoint

- [ ] `php artisan test` all green.

---

## Milestone 6 — `POST /api/finance/expenses` (≈1.5 hours)

### Task 6.1: FormRequest + controller + feature test

**Files:**
- Create: `app/Http/Requests/StoreExpenseRequest.php`
- Create: `app/Http/Controllers/FinanceExpenseController.php`
- Create: `tests/Feature/ExpenseCaptureTest.php`

- [ ] **Step 1: Write the FormRequest**

```php
<?php
// app/Http/Requests/StoreExpenseRequest.php
namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'amount'           => ['required', 'numeric', 'gt:0', 'lte:10000000'],
            'category'         => ['nullable', 'string', 'max:60'],
            'description'      => ['nullable', 'string', 'max:4000'],
            'paid_at'          => ['nullable', 'date'],
            'slack_message_id' => ['required', 'string', 'max:50'],
            'raw_input'        => ['nullable', 'string', 'max:4000'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'The given data was invalid.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
```

- [ ] **Step 2: Write the controller**

```php
<?php
// app/Http/Controllers/FinanceExpenseController.php
namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Models\Expense;
use App\Models\LedgerEntry;
use App\Services\Finance\LedgerRoutingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class FinanceExpenseController extends Controller
{
    public function store(StoreExpenseRequest $request, LedgerRoutingService $routing): JsonResponse
    {
        $data = $request->validated();

        $existing = Expense::where('slack_message_id', $data['slack_message_id'])->first();
        if ($existing !== null) {
            return response()->json([
                'error' => 'duplicate_slack_message',
                'existing_id' => $existing->id,
            ], 409);
        }

        $result = DB::transaction(function () use ($data, $routing) {
            $expense = Expense::create([
                'amount'           => $data['amount'],
                'category'         => $data['category']    ?? null,
                'description'      => $data['description'] ?? null,
                'paid_at'          => $data['paid_at']     ?? now(),
                'slack_message_id' => $data['slack_message_id'],
                'raw_input'        => $data['raw_input']   ?? null,
            ]);
            $rows = $routing->routeExpense($expense);
            foreach ($rows as $r) LedgerEntry::create($r);
            return ['expense' => $expense, 'ledger_count' => count($rows)];
        });

        return response()->json([
            'id' => $result['expense']->id,
            'ledger_entries' => $result['ledger_count'],
        ], 201);
    }
}
```

- [ ] **Step 3: Write the feature test**

```php
<?php
// tests/Feature/ExpenseCaptureTest.php
namespace Tests\Feature;

use App\Models\Expense;
use App\Models\LedgerEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ExpenseCaptureTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'test-finance-token-abcdef0123456789';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config(['finance.capture_token' => self::TOKEN]);
        Route::post('/api/finance/expenses', [\App\Http\Controllers\FinanceExpenseController::class, 'store'])
            ->middleware(\App\Http\Middleware\VerifyFinanceToken::class);
    }

    private function post(array $overrides = [], ?string $token = self::TOKEN)
    {
        $payload = array_merge([
            'amount' => 5000,
            'category' => 'Marketing',
            'description' => 'fb ads April',
            'paid_at' => '2026-04-17T10:00:00+05:30',
            'slack_message_id' => 'E.'.uniqid(),
            'raw_input' => 'paid 5k for fb ads',
        ], $overrides);
        $headers = $token === null ? [] : ['X-Finance-Token' => $token];
        return $this->postJson('/api/finance/expenses', $payload, $headers);
    }

    public function test_happy_path_creates_expense_and_ledger_row(): void
    {
        $this->post()->assertCreated()->assertJson(['ledger_entries' => 1]);
        $e = Expense::first();
        $this->assertNotNull($e);
        $l = LedgerEntry::first();
        $this->assertSame('davya', $l->account);
        $this->assertSame('-5000.00', (string) $l->delta_amount);
        $this->assertSame('expense', $l->source_type);
        $this->assertSame($e->id, $l->source_id);
    }

    public function test_missing_token_returns_401(): void
    {
        $this->post([], token: null)->assertStatus(401);
    }

    public function test_missing_amount_returns_422(): void
    {
        $this->post(['amount' => null])->assertStatus(422)->assertJsonValidationErrors('amount');
    }

    public function test_missing_slack_message_id_returns_422(): void
    {
        $this->post(['slack_message_id' => null])->assertStatus(422)->assertJsonValidationErrors('slack_message_id');
    }

    public function test_duplicate_slack_message_id_returns_409(): void
    {
        $first = $this->post(['slack_message_id' => 'E.DUPE']);
        $first->assertCreated();
        $this->post(['slack_message_id' => 'E.DUPE'])
            ->assertStatus(409)
            ->assertJson(['error' => 'duplicate_slack_message', 'existing_id' => $first->json('id')]);
    }
}
```

- [ ] **Step 4: Run — expect PASS.**

```
php artisan test --filter ExpenseCaptureTest
```

- [ ] **Step 5: Commit**

```bash
git add app/Http/Requests/StoreExpenseRequest.php \
        app/Http/Controllers/FinanceExpenseController.php \
        tests/Feature/ExpenseCaptureTest.php
git commit -m "feat(finance): POST /api/finance/expenses"
```

---

## Milestone 7 — `POST /api/finance/investments` (≈1.5 hours)

### Task 7.1: FormRequest + controller + feature test

**Files:**
- Create: `app/Http/Requests/StoreInvestmentRequest.php`
- Create: `app/Http/Controllers/FinanceInvestmentController.php`
- Create: `tests/Feature/InvestmentCaptureTest.php`

- [ ] **Step 1: Write the FormRequest**

```php
<?php
// app/Http/Requests/StoreInvestmentRequest.php
namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreInvestmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'asset_name'       => ['required', 'string', 'max:80'],
            'amount'           => ['required', 'numeric', 'gt:0', 'lte:100000000'],
            'direction'        => ['required', 'in:in,out'],
            'transacted_at'    => ['nullable', 'date'],
            'slack_message_id' => ['required', 'string', 'max:50'],
            'raw_input'        => ['nullable', 'string', 'max:4000'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'The given data was invalid.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
```

- [ ] **Step 2: Write the controller**

```php
<?php
// app/Http/Controllers/FinanceInvestmentController.php
namespace App\Http\Controllers;

use App\Http\Requests\StoreInvestmentRequest;
use App\Models\Investment;
use App\Models\LedgerEntry;
use App\Services\Finance\LedgerRoutingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class FinanceInvestmentController extends Controller
{
    public function store(StoreInvestmentRequest $request, LedgerRoutingService $routing): JsonResponse
    {
        $data = $request->validated();

        $existing = Investment::where('slack_message_id', $data['slack_message_id'])->first();
        if ($existing !== null) {
            return response()->json([
                'error' => 'duplicate_slack_message',
                'existing_id' => $existing->id,
            ], 409);
        }

        $result = DB::transaction(function () use ($data, $routing) {
            $inv = Investment::create([
                'asset_name'       => $data['asset_name'],
                'amount'           => $data['amount'],
                'direction'        => $data['direction'],
                'transacted_at'    => $data['transacted_at'] ?? now(),
                'slack_message_id' => $data['slack_message_id'],
                'raw_input'        => $data['raw_input'] ?? null,
            ]);
            $rows = $routing->routeInvestment($inv);
            foreach ($rows as $r) LedgerEntry::create($r);
            return ['investment' => $inv, 'ledger_count' => count($rows)];
        });

        return response()->json([
            'id' => $result['investment']->id,
            'ledger_entries' => $result['ledger_count'],
        ], 201);
    }
}
```

- [ ] **Step 3: Write the feature test**

```php
<?php
// tests/Feature/InvestmentCaptureTest.php
namespace Tests\Feature;

use App\Models\Investment;
use App\Models\LedgerEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class InvestmentCaptureTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'test-finance-token-abcdef0123456789';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config(['finance.capture_token' => self::TOKEN]);
        Route::post('/api/finance/investments', [\App\Http\Controllers\FinanceInvestmentController::class, 'store'])
            ->middleware(\App\Http\Middleware\VerifyFinanceToken::class);
    }

    private function post(array $overrides = [], ?string $token = self::TOKEN)
    {
        $payload = array_merge([
            'asset_name' => 'Tata Motors',
            'amount' => 100000,
            'direction' => 'out',
            'transacted_at' => '2026-04-17T09:00:00+05:30',
            'slack_message_id' => 'I.'.uniqid(),
            'raw_input' => 'bought 100k tata motors',
        ], $overrides);
        $headers = $token === null ? [] : ['X-Finance-Token' => $token];
        return $this->postJson('/api/finance/investments', $payload, $headers);
    }

    public function test_direction_out_debits_davya(): void
    {
        $this->post(['direction' => 'out', 'amount' => 100000])->assertCreated();
        $l = LedgerEntry::first();
        $this->assertSame('davya', $l->account);
        $this->assertSame('-100000.00', (string) $l->delta_amount);
    }

    public function test_direction_in_credits_davya(): void
    {
        $this->post(['direction' => 'in', 'amount' => 120000])->assertCreated();
        $l = LedgerEntry::first();
        $this->assertSame('davya', $l->account);
        $this->assertSame('120000.00', (string) $l->delta_amount);
    }

    public function test_invalid_direction_returns_422(): void
    {
        $this->post(['direction' => 'sideways'])->assertStatus(422)->assertJsonValidationErrors('direction');
    }

    public function test_missing_asset_name_returns_422(): void
    {
        $this->post(['asset_name' => null])->assertStatus(422)->assertJsonValidationErrors('asset_name');
    }

    public function test_missing_token_returns_401(): void
    {
        $this->post([], token: null)->assertStatus(401);
    }

    public function test_duplicate_slack_message_id_returns_409(): void
    {
        $first = $this->post(['slack_message_id' => 'I.DUPE']);
        $first->assertCreated();
        $this->post(['slack_message_id' => 'I.DUPE'])->assertStatus(409);
    }
}
```

- [ ] **Step 4: Run — expect PASS.**

- [ ] **Step 5: Commit**

```bash
git add app/Http/Requests/StoreInvestmentRequest.php \
        app/Http/Controllers/FinanceInvestmentController.php \
        tests/Feature/InvestmentCaptureTest.php
git commit -m "feat(finance): POST /api/finance/investments"
```

---

## Milestone 8 — `POST /api/finance/failed` (≈45 min)

### Task 8.1: FormRequest + controller + feature test

**Files:**
- Create: `app/Http/Requests/StoreFailedExtractionRequest.php`
- Create: `app/Http/Controllers/FinanceFailedController.php`
- Create: `tests/Feature/FinanceFailedTest.php`

- [ ] **Step 1: Write the FormRequest**

```php
<?php
// app/Http/Requests/StoreFailedExtractionRequest.php
namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreFailedExtractionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'slack_message_id' => ['required', 'string', 'max:50'],
            'slack_channel'    => ['nullable', 'string', 'max:60'],
            'raw_input'        => ['nullable', 'string', 'max:4000'],
            'error_reason'     => ['required', 'string', 'max:255'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'The given data was invalid.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
```

- [ ] **Step 2: Write the controller**

```php
<?php
// app/Http/Controllers/FinanceFailedController.php
namespace App\Http\Controllers;

use App\Http\Requests\StoreFailedExtractionRequest;
use App\Models\FailedExtraction;
use Illuminate\Http\JsonResponse;

class FinanceFailedController extends Controller
{
    public function store(StoreFailedExtractionRequest $request): JsonResponse
    {
        $row = FailedExtraction::create($request->validated());
        return response()->json(['id' => $row->id], 201);
    }
}
```

- [ ] **Step 3: Write the test**

```php
<?php
// tests/Feature/FinanceFailedTest.php
namespace Tests\Feature;

use App\Models\FailedExtraction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class FinanceFailedTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'test-finance-token-abcdef0123456789';

    protected function setUp(): void
    {
        parent::setUp();
        config(['finance.capture_token' => self::TOKEN]);
        Route::post('/api/finance/failed', [\App\Http\Controllers\FinanceFailedController::class, 'store'])
            ->middleware(\App\Http\Middleware\VerifyFinanceToken::class);
    }

    public function test_allows_repeated_slack_message_ids(): void
    {
        $post = fn (array $p) => $this->postJson('/api/finance/failed', $p, ['X-Finance-Token' => self::TOKEN]);
        $post(['slack_message_id' => 'C1.1.1','error_reason' => 'gemini invalid json'])->assertCreated();
        $post(['slack_message_id' => 'C1.1.1','error_reason' => 'retry: still invalid'])->assertCreated();
        $this->assertSame(2, FailedExtraction::count());
    }

    public function test_missing_token_returns_401(): void
    {
        $this->postJson('/api/finance/failed', ['slack_message_id'=>'x','error_reason'=>'y'])->assertStatus(401);
    }

    public function test_missing_error_reason_returns_422(): void
    {
        $this->postJson('/api/finance/failed', ['slack_message_id'=>'x'], ['X-Finance-Token' => self::TOKEN])
             ->assertStatus(422)->assertJsonValidationErrors('error_reason');
    }
}
```

- [ ] **Step 4: Run — expect PASS.**

- [ ] **Step 5: Commit**

```bash
git add app/Http/Requests/StoreFailedExtractionRequest.php \
        app/Http/Controllers/FinanceFailedController.php \
        tests/Feature/FinanceFailedTest.php
git commit -m "feat(finance): POST /api/finance/failed for n8n failure arm"
```

---

## Milestone 9 — Wire routes + integration smoke (≈1.5 hours)

### Task 9.1: Wire 4 routes in `routes/api.php`

**Files:**
- Modify: `routes/api.php`

- [ ] **Step 1: Replace `routes/api.php` contents**

```php
<?php

use App\Http\Controllers\LeadController;
use App\Http\Controllers\FinancePaymentController;
use App\Http\Controllers\FinanceExpenseController;
use App\Http\Controllers\FinanceInvestmentController;
use App\Http\Controllers\FinanceFailedController;
use App\Http\Middleware\VerifyLeadToken;
use App\Http\Middleware\VerifyFinanceToken;
use Illuminate\Support\Facades\Route;

// Phase 1 — Lead Capture
Route::post('/leads', [LeadController::class, 'store'])
    ->middleware([VerifyLeadToken::class, 'throttle:60,1']);

// Phase 2 — Finance
Route::prefix('finance')
    ->middleware([VerifyFinanceToken::class, 'throttle:60,1'])
    ->group(function () {
        Route::post('/payments',    [FinancePaymentController::class,    'store']);
        Route::post('/expenses',    [FinanceExpenseController::class,    'store']);
        Route::post('/investments', [FinanceInvestmentController::class, 'store']);
        Route::post('/failed',      [FinanceFailedController::class,     'store']);
    });
```

- [ ] **Step 2: Remove the in-test `Route::post(...)` setUp lines**

In each of the 4 feature test files (`PaymentCaptureTest`, `ExpenseCaptureTest`, `InvestmentCaptureTest`, `FinanceFailedTest`), delete the `Route::post(...)` block inside `setUp()`. The permanent route in `routes/api.php` now covers them. Keep the `config(['finance.capture_token' => self::TOKEN])` line.

**Subtle point:** the test's `postJson('/api/finance/...')` path is prefixed by Laravel's api routing automatically. The inline `Route::post('/api/finance/...')` versions used during M5–M8 explicitly included the `/api` prefix for isolation. Once real routes exist they're registered under the `api` prefix in `bootstrap/app.php:withRouting(apiPrefix:'api')`, so the test URLs stay identical.

- [ ] **Step 3: Run**

```
php artisan test
```
Expected: ALL Finance feature tests still green without the in-test route registration.

- [ ] **Step 4: Commit**

```bash
git add routes/api.php tests/Feature/PaymentCaptureTest.php \
        tests/Feature/ExpenseCaptureTest.php \
        tests/Feature/InvestmentCaptureTest.php \
        tests/Feature/FinanceFailedTest.php
git commit -m "feat(finance): wire /api/finance/{payments,expenses,investments,failed} routes"
```

### Task 9.2: `BalanceReconstructionTest`

**Files:**
- Create: `tests/Feature/BalanceReconstructionTest.php`

- [ ] **Step 1: Write the test**

```php
<?php
// tests/Feature/BalanceReconstructionTest.php
namespace Tests\Feature;

use App\Models\LedgerEntry;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BalanceReconstructionTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'test-finance-token-abcdef0123456789';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config(['finance.capture_token' => self::TOKEN]);
    }

    private function pay(string $phone, string $referrer, float $amount): void
    {
        $this->postJson('/api/finance/payments', [
            'student_phone' => $phone, 'amount' => $amount, 'referrer_name' => $referrer,
            'student_name' => 'S '.$phone,
            'slack_message_id' => 'BR.P.'.uniqid(),
            'raw_input' => "got {$amount} from {$referrer}",
        ], ['X-Finance-Token' => self::TOKEN])->assertCreated();
    }
    private function expense(float $amount, string $cat): void
    {
        $this->postJson('/api/finance/expenses', [
            'amount' => $amount, 'category' => $cat,
            'slack_message_id' => 'BR.E.'.uniqid(),
        ], ['X-Finance-Token' => self::TOKEN])->assertCreated();
    }
    private function invest(float $amount, string $direction, string $asset): void
    {
        $this->postJson('/api/finance/investments', [
            'amount' => $amount, 'direction' => $direction, 'asset_name' => $asset,
            'slack_message_id' => 'BR.I.'.uniqid(),
        ], ['X-Finance-Token' => self::TOKEN])->assertCreated();
    }

    public function test_mixed_sequence_produces_correct_balances(): void
    {
        // 3 payments via Nikhil's team → each splits 60/40
        $this->pay('9200000001', 'Nisha',  50000);   // nikhil +30000, davya +20000
        $this->pay('9200000002', 'Nikhil', 40000);   // nikhil +24000, davya +16000
        $this->pay('9200000003', 'Nisha',  30000);   // nikhil +18000, davya +12000

        // 2 payments via Sonam's team → 0% head → 100% davya
        $this->pay('9200000011', 'Poonam', 45000);   // davya +45000
        $this->pay('9200000012', 'Sonam',  35000);   // davya +35000

        // 1 payment via freelancer → 100% davya
        $this->pay('9200000021', 'Kapil',  25000);   // davya +25000

        // 2 expenses
        $this->expense(5000,  'Marketing');           // davya -5000
        $this->expense(12000, 'Rent');                // davya -12000

        // 2 investments
        $this->invest(100000, 'out', 'Tata Motors');  // davya -100000
        $this->invest(8000,   'in',  'Binance');      // davya +8000

        $davya  = (float) LedgerEntry::balanceFor('davya');
        $nikhil = (float) LedgerEntry::balanceFor('nikhil');

        // davya = 20000+16000+12000+45000+35000+25000-5000-12000-100000+8000 = 44000
        $this->assertEqualsWithDelta(44000.00, $davya, 0.01);
        // nikhil = 30000+24000+18000 = 72000
        $this->assertEqualsWithDelta(72000.00, $nikhil, 0.01);
    }
}
```

- [ ] **Step 2: Run — expect PASS.**

```
php artisan test --filter BalanceReconstructionTest
```

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/BalanceReconstructionTest.php
git commit -m "test(finance): end-to-end balance reconstruction across 10 mixed transactions"
```

### M9 checkpoint

- [ ] Full suite: `php artisan test` green. Expect ≈45 new Phase 2 tests added to Phase 1's ~91.

---

## Milestone 10 — Production deploy (≈1 hour)

### Task 10.1: Push + SSH deploy + prod seeders

- [ ] **Step 1: Push main**

```bash
cd /Users/Sumit/davya-crm
git push origin main
```

- [ ] **Step 2: SSH + deploy**

Use the path discipline from Phase 1: CLI default PHP is 8.2 on prod; use `/opt/alt/php84/usr/bin/php`.

```bash
ssh ipuc@ipu.co.in '
  cd /home/ipuc/davya-crm &&
  git pull --ff-only origin main &&
  /opt/alt/php84/usr/bin/php artisan migrate --force &&
  /opt/alt/php84/usr/bin/php artisan db:seed --class=UsersSeeder --force &&
  /opt/alt/php84/usr/bin/php artisan config:clear &&
  /opt/alt/php84/usr/bin/php artisan route:clear
'
```

Expected output: 6 migrations ran, seeder updated Nikhil's `split_pct`.

- [ ] **Step 3: Add `FINANCE_CAPTURE_TOKEN` to prod `.env`**

```bash
ssh ipuc@ipu.co.in 'grep -q "^FINANCE_CAPTURE_TOKEN=" /home/ipuc/davya-crm/.env || echo "FINANCE_CAPTURE_TOKEN=$(openssl rand -hex 16)" >> /home/ipuc/davya-crm/.env'
ssh ipuc@ipu.co.in 'grep "^FINANCE_CAPTURE_TOKEN=" /home/ipuc/davya-crm/.env'
```

Copy the printed token value to your password manager — you'll paste it into n8n at Task 11.3.

- [ ] **Step 4: Verify via live curl (payments route, expect 401 first, then 201)**

```bash
curl -sS -X POST https://davyas.ipu.co.in/api/finance/payments \
  -H "Content-Type: application/json" \
  -d '{"student_phone":"9999000001","amount":100,"referrer_name":"Nisha","slack_message_id":"CURL.1","raw_input":"curl smoke"}' \
  -w "\nHTTP %{http_code}\n"
# Expected: HTTP 401 (missing token)

curl -sS -X POST https://davyas.ipu.co.in/api/finance/payments \
  -H "X-Finance-Token: <TOKEN from step 3>" \
  -H "Content-Type: application/json" \
  -d '{"student_phone":"9999000001","amount":100,"referrer_name":"Nisha","slack_message_id":"CURL.2","raw_input":"curl smoke"}' \
  -w "\nHTTP %{http_code}\n"
# Expected: HTTP 201 {"id":...,"ledger_entries":2}
```

- [ ] **Step 5: Clean up the smoke-test row**

Log into `https://davyas.ipu.co.in/admin/students` and delete the "S 9999000001" student (leaves a small smoke-test trail otherwise).

- [ ] **Step 6: Tag deploy**

```bash
git tag v1.1.0-finance-api
git push origin v1.1.0-finance-api
```

### M10 checkpoint

- [ ] Prod responds 401 without token, 201 with token; student row visible in CRM; balances reconstructable via `SELECT account, SUM(delta_amount) FROM ledger_entries GROUP BY account`.

---

## Milestone 11 — n8n workflow build + import (≈3 hours)

### Task 11.1: Author `docs/n8n-finance-workflow.json`

**Files:**
- Create: `/Users/Sumit/davya-crm/docs/n8n-finance-workflow.json`

- [ ] **Step 1: Build the workflow JSON**

Use the Davyas Lead Capture workflow (`/Users/Sumit/davya-crm/docs/n8n-lead-capture-workflow.json`) as a structural reference. The finance workflow has 8 nodes:

1. **Slack Trigger** — `n8n-nodes-base.slackTrigger`, event `message`, channels array with the 2 channel IDs (leave placeholder `REPLACE_CHANNEL_IDS` — bound at import time)
2. **Gemini Chat** — `@n8n/n8n-nodes-langchain.lmChatGoogleGemini` with model `gemini-2.5-flash`, temperature 0.1, response format `JSON`, inline responseSchema from spec §7.1, system prompt from spec §7.2
3. **Code: Dispatch by category** — validates channel↔category (reject Investment on #student-entries), selects endpoint URL + payload
4. **HTTP Request** — `n8n-nodes-base.httpRequest`, method POST, URL from step 3 expression, headers `X-Finance-Token` via `$vars.financeToken`
5. **IF: statusCode == 201** — `n8n-nodes-base.if`
6. **Slack ✅ Reaction** (true arm) — `n8n-nodes-base.slack` operation `reaction.add`, emoji `white_check_mark`, channel + ts from Slack Trigger
7. **IF: statusCode == 409** (false arm from step 5) — silent skip on true; on false proceed to step 8
8. **HTTP Request: POST /api/finance/failed** + **Slack thread reply** — parallel. Record the failure AND reply in the message thread.

Full JSON is long; rather than inlining a 200-line blob here, **build it in n8n UI first** (following the structure above), export via `File → Download`, then place the file at the path above. The workflow will be imported back via API in Task 11.2 — the JSON becomes the source of truth.

- [ ] **Step 2: Commit the JSON**

```bash
cd /Users/Sumit/davya-crm
git add docs/n8n-finance-workflow.json
git commit -m "feat(finance): n8n workflow JSON for Slack→CRM ingestion"
git push
```

### Task 11.2: Import the workflow via n8n API

- [ ] **Step 1: Sanity-check the JSON shape**

```bash
python3 -c "
import json
d = json.load(open('/Users/Sumit/davya-crm/docs/n8n-finance-workflow.json'))
print('name:', d.get('name'))
print('nodes:', len(d.get('nodes',[])))
print('active:', d.get('active'))
assert 'nodes' in d and 'connections' in d, 'malformed'
"
```

- [ ] **Step 2: Create empty shell in n8n, grab the id**

Create a new blank workflow in the n8n UI named "Davya Finance — Slack → CRM" and copy its ID from the URL (`/workflow/<id>`).

- [ ] **Step 3: PUT the local JSON**

```bash
set -a; source /Users/Sumit/kyne/deployment/.env; set +a
WF_ID="<ID from step 2>"
python3 <<PY > /tmp/finance_put.json
import json
d = json.load(open('/Users/Sumit/davya-crm/docs/n8n-finance-workflow.json'))
print(json.dumps({
    "name": d["name"],
    "nodes": d["nodes"],
    "connections": d["connections"],
    "settings": {"executionOrder": "v1"},
}))
PY
curl -sS -X PUT -H "X-N8N-API-KEY: $N8N_API_KEY" -H "Content-Type: application/json" \
  --data @/tmp/finance_put.json \
  "$N8N_BASE_URL/api/v1/workflows/$WF_ID" \
  | python3 -c "import sys,json; r=json.load(sys.stdin); print('nodes:', len(r.get('nodes',[])))"
```

Expected: `nodes: 8`.

### Task 11.3: Bind credentials + channel IDs in n8n UI

The following needs the browser — same story as Davyas Lead Capture activation.

- [ ] **Step 1: Open the workflow**

`https://n8n.srv1117424.hstgr.cloud/workflow/<WF_ID>`

- [ ] **Step 2: Bind Slack credential**

- Click Slack Trigger node → Credentials → Add new **Slack API** credential → paste bot token from pre-flight → save.
- In the node: set the 2 channel IDs (from pre-flight) in the channels array.

- [ ] **Step 3: Bind Gemini credential**

- Click Gemini Chat node → Credentials → select the KYNE-reused credential (or add new) → save.

- [ ] **Step 4: Bind HTTP Request finance token**

- Click HTTP Request (main) node → Send Headers → ensure `X-Finance-Token` value equals the token from Task 10.3 step 3.
- Same for the "failed" HTTP Request node in the failure arm.

- [ ] **Step 5: Save + Activate**

- Save the workflow.
- Flip Active toggle. Errors will surface immediately — credential mismatches, channel ID typos, etc. Resolve in-UI.

### M11 checkpoint

- [ ] Workflow active. `curl` the n8n executions list — should be empty until a first Slack message arrives.

```bash
set -a; source /Users/Sumit/kyne/deployment/.env; set +a
curl -sS -H "X-N8N-API-KEY: $N8N_API_KEY" \
  "$N8N_BASE_URL/api/v1/executions?workflowId=<WF_ID>&limit=5" | python3 -m json.tool
```

---

## Milestone 12 — Acceptance + `v2.0.0` tag (≈1 hour)

### Task 12.1: Smoke test — 10 messages per category

In Slack, post the 30 test messages below. Wait a minute between waves so the Trigger polls in order. After each wave, confirm via the n8n executions list + prod DB.

**Payments in `#student-entries`** (8 realistic + 2 edge-case):

1. `got 50k from priya 9200001001, ref nisha`
2. `received 35000 from rohit 9200001002 via sonam`
3. `50k from aman 9200001003, ref nikhil`
4. `priya paid 20k more, pending 30k` (partial — won't match phone, relies on Gemini's student_name-only extraction; may fail with 422 → intentional: teach Sumit to include phone)
5. `got 25k from kavya 9200001004, kapil referred her`
6. `45k received from sneha 9200001005, poonam's referral`
7. `30k full payment from aditya 9200001006, ref neetu`
8. `partial 10k from harsh 9200001007, ref nikhil`
9. `duplicate: 50k from priya 9200001001, ref nisha` (expect 409)
10. `15k from tanvi 9200001008 via unknown_name` (expect 422 → `failed_extractions` row)

**Expenses in `#finance-log`** (10):

1. `paid 5k for fb ads`
2. `office rent 25000`
3. `team lunch 1800 food`
4. `printer ink 1200`
5. `domain renewal 800`
6. `hired freelancer for design: 8000`
7. `12500 for client gift`
8. `tax deposit 45000`
9. `electricity bill 3200`
10. `courier 450`

**Investments in `#finance-log`** (10):

1. `bought 50k tata motors`
2. `invested 100k in real estate #12`
3. `infosys dividend received 4200`
4. `sold reliance shares 22000 gain`
5. `binance btc buy 25000`
6. `rental income received 18000`
7. `mutual fund SIP 10000`
8. `sold tata motors 58000`
9. `bought gold etf 15000`
10. `debenture interest 5000`

- [ ] **Step 1: Post the messages**

In the Slack workspace, post them across a 5-minute window.

- [ ] **Step 2: Verify via n8n**

```bash
set -a; source /Users/Sumit/kyne/deployment/.env; set +a
curl -sS -H "X-N8N-API-KEY: $N8N_API_KEY" \
  "$N8N_BASE_URL/api/v1/executions?workflowId=<WF_ID>&limit=50" | python3 -c "
import sys, json
d = json.load(sys.stdin)
oks  = sum(1 for e in d['data'] if e['status'] == 'success')
errs = sum(1 for e in d['data'] if e['status'] == 'error')
print(f'success: {oks}  error: {errs}')"
```

Expected: most successes; a handful of deliberate 4xx show as successful executions in n8n (because the IF node branches to the failure arm, which still exits cleanly).

- [ ] **Step 3: Verify DB balances on prod**

```bash
ssh ipuc@ipu.co.in '
  /opt/alt/php84/usr/bin/php /home/ipuc/davya-crm/artisan tinker --execute="
    echo \"davya  = \" . \App\Models\LedgerEntry::balanceFor(\"davya\") . \"\n\";
    echo \"nikhil = \" . \App\Models\LedgerEntry::balanceFor(\"nikhil\") . \"\n\";
  "
'
```

Expected: values match what the Payments + Expenses + Investments above should sum to. Any mismatch → inspect `ledger_entries`:

```bash
ssh ipuc@ipu.co.in '
  /opt/alt/php84/usr/bin/php /home/ipuc/davya-crm/artisan tinker --execute="
    foreach(\App\Models\LedgerEntry::orderBy(\"id\")->get() as \$e) {
      echo \"#{\$e->id} \" . \$e->account . \"  \" . \$e->delta_amount . \"  \" . \$e->source_type . \":\" . \$e->source_id . \"\n\";
    }
  "
'
```

- [ ] **Step 4: Verify Slack feedback**

Spot-check 2 successful payments — they should have a ✅ reaction on the original Slack message.
Spot-check 1 deliberate-fail (`9200001008 via unknown_name`) — it should have a ❌ thread reply with the reason.

### Task 12.2: Backup sanity check

- [ ] **Step 1: Manually trigger a dump to confirm new tables are included**

```bash
ssh ipuc@ipu.co.in '
  /opt/alt/php84/usr/bin/php /home/ipuc/davya-crm/artisan backup:database --skip-drive
'
ssh ipuc@ipu.co.in 'ls -lht /home/ipuc/davya-crm/storage/app/backups/ | head -3'
```

- [ ] **Step 2: Grep the dump for Phase 2 tables**

```bash
ssh ipuc@ipu.co.in '
  LATEST=$(ls -t /home/ipuc/davya-crm/storage/app/backups/*.sql.gz | head -1)
  zcat "$LATEST" | grep -E "CREATE TABLE.*(expenses|investments|ledger_entries|failed_extractions)" | wc -l
'
```

Expected: `4` (one CREATE TABLE line per new table).

### Task 12.3: Documentation + tag

**Files:**
- Create: `/Users/Sumit/davya-crm/docs/FINANCE_API.md`

- [ ] **Step 1: Write `FINANCE_API.md`**

Minimum content: 4 sections (Payments, Expenses, Investments, Failed). For each: endpoint URL, required headers, request body schema (same table as spec §8.1–8.4), response codes, example `curl`.

Structure it like `/Users/Sumit/davya-crm/docs/LEAD_CAPTURE_API.md` (if it exists; otherwise invent a clean layout with the sections above).

- [ ] **Step 2: Commit docs**

```bash
cd /Users/Sumit/davya-crm
git add docs/FINANCE_API.md
git commit -m "docs(finance): FINANCE_API.md for n8n / future integrators"
git push
```

- [ ] **Step 3: Tag `v2.0.0`**

```bash
git tag -a v2.0.0 -m "Phase 2: Finance ingestion live (Slack + n8n + Gemini + Laravel API + ledger)"
git push origin v2.0.0
```

### M12 checkpoint

- [ ] Both balances (`davya`, `nikhil`) match expectations.
- [ ] Slack feedback working (✅ on success, ❌ thread on fail).
- [ ] Backup contains all 4 new tables.
- [ ] `docs/FINANCE_API.md` committed.
- [ ] `v2.0.0` tag pushed. 🎉

---

## Self-review (internal — not for the engineer)

**Spec coverage:**

| Spec § | Covered in |
|---|---|
| §0 revision notes | context only |
| §1 purpose | M12 acceptance |
| §2 scope in | M1–M12 |
| §2 scope out | no task (explicit deferrals) |
| §3 architecture | M1 (schema), M3 (service), M5–M8 (endpoints), M11 (n8n) |
| §4 stack | Pre-flight, M10 (env), M11 (Gemini) |
| §5.1 hierarchy | Phase 1 already seeds users; Task 2.5 adds Nikhil's split_pct |
| §5.2 routing rules | Task 3.1 (10 unit tests) + 3.2 (service impl) |
| §5.3 worked examples | Task 3.1 (direct assertions on Nikhil/Sonam/Kapil rows) |
| §5.4 balance queries | Task 2.3 (`LedgerEntry::balanceFor`), Task 9.2 (E2E assertion), Task 12.1 step 3 |
| §6.1 existing tables | Tasks 1.1, 1.2 |
| §6.2 expenses | Tasks 1.3, 2.1 |
| §6.3 investments | Tasks 1.4, 2.2 |
| §6.4 ledger_entries | Tasks 1.5, 2.3 |
| §6.5 failed_extractions | Tasks 1.6, 2.4 |
| §7 Gemini extraction | Task 11.1 (prompt + schema live in workflow JSON) |
| §8.1 payments endpoint | Tasks 5.1–5.3 |
| §8.2 expenses endpoint | Task 6.1 |
| §8.3 investments endpoint | Task 7.1 |
| §8.4 failed endpoint | Task 8.1 |
| §8.5 recorded_by nullability | Task 1.2 |
| §8.6 response codes | Tasks 5.3, 6.1, 7.1, 8.1 feature tests |
| §9 n8n workflow | Tasks 11.1–11.3 |
| §10 Slack pre-flight | Pre-flight checklist |
| §11 error handling | Tasks 5.3, 6.1, 7.1 (401/409/422 tests); Task 11.1 (Slack reply arms) |
| §12 security | Task 4.1 (token), Task 10.3 (env), spec inherits Phase 1 DB user scope |
| §13 testing | Tasks 3.1, 5.3, 6.1, 7.1, 8.1, 9.2 |
| §14 Definition of Done | M12 checkpoint items map 1:1 to the §14 checklist |

No gaps. No placeholders. Type/method names consistent (`LedgerRoutingService::routePayment`, `routeExpense`, `routeInvestment` used uniformly across tasks 3.1, 3.2, 5.2, 6.1, 7.1).

---

## Plan complete

**Plan saved to:** `docs/superpowers/plans/2026-04-17-davya-finance-phase2.md`.

Two execution options:

1. **Subagent-Driven (recommended)** — I dispatch a fresh subagent per task, review between tasks, fast iteration.
2. **Inline Execution** — Execute tasks in this session using the executing-plans skill, batch execution with checkpoints.

Which approach?
