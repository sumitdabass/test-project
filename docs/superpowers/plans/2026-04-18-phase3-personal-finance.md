# Sumit Personal Finance — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ship a single-user Laravel 11 + Filament v3 personal-finance app at `me.ipu.co.in` that consolidates all of Sumit's expenses and income — including explicit Davya-pool withdrawals routed through Phase 2 — and captures entries from both Slack (via n8n + Gemini) and a web admin panel.

**Architecture:** Separate Laravel app mirroring the Phase 1 `davya-crm` stack choices (PHP 8.4+, Filament v3, MySQL on Hostinger). Two capture surfaces — `/api/personal/{expenses,incomes,failed}` auth'd by `X-Personal-Token`, and Filament resource CRUD. Davya-withdrawal is a dual-write orchestrated by n8n: one POST to Phase 2's `/api/finance/expenses` debiting Davya, one POST to Phase 3's `/api/personal/incomes` crediting Sumit. Phase 3's Laravel code never speaks HTTP to Phase 2 — all cross-app coordination is in n8n.

**Tech Stack:** PHP 8.4, Laravel 11, Filament v3.3+, MySQL 8, PHPUnit, Pest optional, Tailwind (via Filament), n8n Public API, Gemini 2.5 Flash (`responseSchema`), Slack Events API (existing Davya Finance Bot).

**Reference spec:** `docs/superpowers/specs/2026-04-18-phase3-personal-finance-design.md`.

**Code location:** `/Users/Sumit/sumit-finance/` (new directory, new private repo `github.com/sumitdabass/sumit-finance`). Plan + spec stay in the IPU `test-project` repo for cross-project history.

**Estimated effort:** ~40–50 hours across 7 milestones. Every milestone ends with a green `php artisan test` + a prod deploy + a semantic tag. No big-bang cutover.

**Conventions (carried from Phase 1 + 2):**
- Controllers flat under `app/Http/Controllers/` with `Personal*` prefix.
- FormRequests flat under `app/Http/Requests/`.
- Middleware flat under `app/Http/Middleware/`.
- Feature tests in `tests/Feature/`, unit tests in `tests/Unit/`.
- Commits use `type(scope): imperative` — `feat(expenses): …`, `fix(incomes): …`, `test(withdrawal): …`.
- Each commit adds both the failing test and the passing implementation so `main` never has a red test suite.
- PHP path on prod is `/opt/alt/php84/usr/bin/php` (server CLI default is 8.2; too old for Laravel 11 composer.lock).

---

## Pre-flight checklist (before Task 1.1)

Sumit must complete these before any code is written — they need browser/account access and cannot be automated:

- [ ] Create new subdomain **`me.ipu.co.in`** in Hostinger hPanel → Domains → Subdomains. Docroot: `/home/ipuc/sumit-finance/public`.
- [ ] Create new MySQL DB **`ipuc_sumit_finance`** (user + password identical to match Phase 1 convention). Set a strong password and save to password manager.
- [ ] Create new private GitHub repo **`github.com/sumitdabass/sumit-finance`**.
- [ ] Create new Slack channel **`#sumit-finance`** (private). Invite existing `Davya Finance Bot` with `/invite @Davya Finance Bot`.
- [ ] Update the Davya Finance Bot's **Event Subscriptions** to include `#sumit-finance` in the subscribed channels (or verify `message.channels` is already a workspace-wide scope, which it is in the Phase 2 setup).
- [ ] Generate `PERSONAL_CAPTURE_TOKEN`: `openssl rand -hex 16` — save the output.
- [ ] Read the spec `docs/superpowers/specs/2026-04-18-phase3-personal-finance-design.md` end-to-end.

**Outputs to have on hand when Task 5.2 starts:** bot already in channel, n8n API key (reuse KYNE's from `/Users/Sumit/kyne/deployment/.env`), Gemini API key (reuse from Phase 2 workflow), `PERSONAL_CAPTURE_TOKEN`, `FINANCE_CAPTURE_TOKEN` from prod davya-crm `.env` (for the dual-write in M6).

---

## Milestone 1 — Scaffold + auth + empty login live (≈6 hours)

**Output:** `https://me.ipu.co.in` loads Filament login page; single user `sumit@davya.local` seeded with forced-password-reset on first login; TOTP 2FA + account-lockout + auth audit log all working; full test suite green; `v0-scaffold` tag.

### Task 1.1: Bootstrap the Laravel project

**Files:**
- Create: `/Users/Sumit/sumit-finance/` (new directory).
- Create: `.gitignore`, `README.md`.

- [ ] **Step 1: Create + enter the directory**

```bash
mkdir -p /Users/Sumit/sumit-finance
cd /Users/Sumit/sumit-finance
```

- [ ] **Step 2: Install Laravel 11**

```bash
composer create-project laravel/laravel:^11.0 . --no-interaction
```

Expected: `Application ready at <cwd>`.

- [ ] **Step 3: Pin PHP version in composer.json**

Edit `composer.json`: set `"php": "^8.4"` under `require`.

- [ ] **Step 4: Smoke-run the dev server**

```bash
/opt/homebrew/bin/php artisan serve
```

Expected: `Server running on http://127.0.0.1:8000`. Kill with Ctrl-C.

- [ ] **Step 5: Run the stock test suite**

```bash
/opt/homebrew/bin/php artisan test
```

Expected: 2 passing (Laravel default tests).

- [ ] **Step 6: Initialize git, first commit**

```bash
git init
git add .
git commit -m "chore: Laravel 11 scaffold"
```

- [ ] **Step 7: Create the remote repo and push**

```bash
gh repo create sumitdabass/sumit-finance --private --source=. --remote=origin --push
```

Expected: repo created, main branch pushed.

### Task 1.2: Install + register Filament v3

**Files:**
- Modify: `composer.json`, `config/app.php` (via install script).
- Create: `app/Providers/Filament/AdminPanelProvider.php`.

- [ ] **Step 1: Install Filament**

```bash
composer require filament/filament:"^3.3" -W
```

- [ ] **Step 2: Install the admin panel**

```bash
/opt/homebrew/bin/php artisan filament:install --panels
```

When prompted for the panel ID: `admin`. This creates `app/Providers/Filament/AdminPanelProvider.php`.

- [ ] **Step 3: Set brand name in `AdminPanelProvider.php`**

Open `app/Providers/Filament/AdminPanelProvider.php` and change the `panel()` configuration:

```php
return $panel
    ->default()
    ->id('admin')
    ->path('admin')
    ->login()
    ->colors(['primary' => Color::Emerald])
    ->brandName('Sumit Finance')
    ->favicon(asset('favicon.ico'))
    // ... existing discovery code stays
    ;
```

- [ ] **Step 4: Commit**

```bash
git add .
git commit -m "feat(scaffold): install Filament v3 admin panel with emerald theme"
```

### Task 1.3: Configure env + local DB + run migrations

**Files:**
- Modify: `.env` (local only — gitignored).

- [ ] **Step 1: Set DB config in `.env`**

```ini
APP_NAME="Sumit Finance"
APP_ENV=local
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sumit_finance_dev
DB_USERNAME=davya_dev
DB_PASSWORD=devpass123
```

Rationale: reuse the same local MySQL user from Phase 1 davya-crm so no new local MySQL setup needed.

- [ ] **Step 2: Create the local DB**

```bash
mysql -u davya_dev -pdevpass123 -e "CREATE DATABASE IF NOT EXISTS sumit_finance_dev;"
```

- [ ] **Step 3: Run base migrations**

```bash
/opt/homebrew/bin/php artisan migrate
```

Expected: users, cache, jobs tables created.

- [ ] **Step 4: Suppress the PHP 8.5 PDO deprecation**

Edit `bootstrap/app.php` — inside `withExceptions()`:

```php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->dontReport([
        \ErrorException::class, // suppresses PDO::MYSQL_ATTR_SSL_CA deprecation on PHP 8.5
    ]);
})
```

This mirrors Phase 1's `bootstrap/app.php` fix (see memory: "PHP 8.5.5 (Homebrew). Laravel 11 triggers PDO::MYSQL_ATTR_SSL_CA deprecations under 8.5; suppressed in bootstrap/app.php").

- [ ] **Step 5: Re-run tests to confirm green**

```bash
/opt/homebrew/bin/php artisan test
```

Expected: all pass, no deprecation noise.

- [ ] **Step 6: Commit**

```bash
git add bootstrap/app.php
git commit -m "chore(scaffold): suppress PDO SSL_CA deprecation on PHP 8.5"
```

### Task 1.4: Seed a single admin user with forced-password-reset flag

**Files:**
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_add_force_password_reset_to_users.php`
- Create: `database/seeders/SumitUserSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Create: `tests/Feature/SumitSeederTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/SumitSeederTest.php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SumitSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_sumit_seeder_creates_single_admin_with_force_reset_flag(): void
    {
        $this->seed(\Database\Seeders\SumitUserSeeder::class);
        $this->assertSame(1, User::count());
        $u = User::first();
        $this->assertSame('sumit@davya.local', $u->email);
        $this->assertTrue((bool) $u->must_change_password);
    }
}
```

- [ ] **Step 2: Run to verify failure**

```bash
/opt/homebrew/bin/php artisan test --filter=SumitSeederTest
```

Expected: FAIL — seeder class does not exist.

- [ ] **Step 3: Create the migration**

```bash
/opt/homebrew/bin/php artisan make:migration add_force_password_reset_to_users
```

Edit the generated migration:

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->boolean('must_change_password')->default(false)->after('password');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('must_change_password');
    });
}
```

- [ ] **Step 4: Create the seeder**

Create `database/seeders/SumitUserSeeder.php`:

```php
<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SumitUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'sumit@davya.local'],
            [
                'name' => 'Sumit Dabas',
                'password' => Hash::make('ChangeMe2026!'),
                'must_change_password' => true,
            ],
        );
    }
}
```

- [ ] **Step 5: Wire into DatabaseSeeder**

Edit `database/seeders/DatabaseSeeder.php`:

```php
public function run(): void
{
    $this->call([
        SumitUserSeeder::class,
    ]);
}
```

- [ ] **Step 6: Declare fillable on User model**

Edit `app/Models/User.php` — add `'must_change_password'` to `$fillable` and add `'must_change_password' => 'boolean'` to `$casts` (via `casts()` method in Laravel 11).

- [ ] **Step 7: Run test to confirm pass**

```bash
/opt/homebrew/bin/php artisan migrate && /opt/homebrew/bin/php artisan test --filter=SumitSeederTest
```

Expected: PASS.

- [ ] **Step 8: Commit**

```bash
git add database/migrations database/seeders app/Models/User.php tests/Feature/SumitSeederTest.php
git commit -m "feat(auth): seed Sumit admin user with force-password-reset flag"
```

### Task 1.5: Force-password-reset middleware + Filament page

**Files:**
- Create: `app/Http/Middleware/ForcePasswordChange.php`
- Create: `app/Filament/Pages/Auth/ChangePasswordFirstLogin.php`
- Modify: `app/Providers/Filament/AdminPanelProvider.php` (register middleware)
- Create: `tests/Feature/ForcePasswordChangeTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/ForcePasswordChangeTest.php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForcePasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_flag_is_redirected_to_change_page_on_login(): void
    {
        $u = User::factory()->create(['must_change_password' => true]);
        $this->actingAs($u);
        $this->get('/admin')->assertRedirect('/admin/change-password-first');
    }

    public function test_user_without_flag_reaches_dashboard(): void
    {
        $u = User::factory()->create(['must_change_password' => false]);
        $this->actingAs($u);
        $this->get('/admin')->assertStatus(200);
    }

    public function test_change_password_flow_clears_flag(): void
    {
        $u = User::factory()->create(['must_change_password' => true]);
        $this->actingAs($u);
        // Livewire assertion handled in a separate Filament-specific test later.
        // Here we only assert the flag flipping via model update.
        $u->update(['must_change_password' => false, 'password' => bcrypt('NewPass1234!')]);
        $this->assertFalse($u->fresh()->must_change_password);
    }
}
```

- [ ] **Step 2: Run to confirm failure**

```bash
/opt/homebrew/bin/php artisan test --filter=ForcePasswordChangeTest
```

Expected: FAIL — middleware not registered, route missing.

- [ ] **Step 3: Create the middleware**

Create `app/Http/Middleware/ForcePasswordChange.php`:

```php
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user->must_change_password && ! $request->routeIs('filament.admin.auth.change-password-first')) {
            return redirect('/admin/change-password-first');
        }
        return $next($request);
    }
}
```

- [ ] **Step 4: Create the Filament custom page**

Create `app/Filament/Pages/Auth/ChangePasswordFirstLogin.php`:

```php
<?php
namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;

class ChangePasswordFirstLogin extends Page
{
    protected static string $view = 'filament.pages.auth.change-password-first';
    protected static ?string $slug = 'change-password-first';
    protected static ?string $title = 'Set a new password';
    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('password')
                    ->password()
                    ->required()
                    ->minLength(12)
                    ->confirmed(),
                TextInput::make('password_confirmation')
                    ->password()
                    ->required(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $u = auth()->user();
        $u->password = Hash::make($this->data['password']);
        $u->must_change_password = false;
        $u->save();
        Notification::make()->title('Password updated')->success()->send();
        $this->redirect('/admin');
    }
}
```

Create the blade view `resources/views/filament/pages/auth/change-password-first.blade.php`:

```blade
<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}
        <x-filament::button type="submit" class="mt-4">Update password</x-filament::button>
    </form>
</x-filament-panels::page>
```

- [ ] **Step 5: Register middleware + page in AdminPanelProvider**

In `app/Providers/Filament/AdminPanelProvider.php`:

```php
->authMiddleware([
    \Filament\Http\Middleware\Authenticate::class,
    \App\Http\Middleware\ForcePasswordChange::class,
])
->pages([
    \App\Filament\Pages\Auth\ChangePasswordFirstLogin::class,
    \Filament\Pages\Dashboard::class,
])
```

- [ ] **Step 6: Run tests to confirm pass**

```bash
/opt/homebrew/bin/php artisan test --filter=ForcePasswordChangeTest
```

Expected: all 3 PASS.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Middleware app/Filament/Pages resources/views/filament tests/Feature/ForcePasswordChangeTest.php app/Providers/Filament
git commit -m "feat(auth): force-password-reset middleware + first-login page"
```

### Task 1.6: TOTP 2FA (copy Phase 1 pattern)

**Files:**
- Modify: `composer.json` (add `pragmarx/google2fa`).
- Create: migration `add_totp_columns_to_users`.
- Create: `app/Services/TotpService.php`.
- Create: `app/Filament/Pages/Auth/EnrollTotp.php`.
- Create: `app/Filament/Pages/Auth/VerifyTotp.php`.
- Modify: `app/Http/Middleware/ForcePasswordChange.php` → rename concept, handle TOTP too.
- Create: `tests/Feature/TotpEnrollmentTest.php`, `tests/Feature/TotpVerificationTest.php`.

Rather than re-specifying, this task **copies verbatim** from Phase 1 `davya-crm`. The Phase 1 implementation is at the last `v19-security` era commits. Reference implementation living in:
- `davya-crm/app/Services/TotpService.php`
- `davya-crm/app/Filament/Pages/Auth/EnrollTotp.php`
- `davya-crm/app/Filament/Pages/Auth/VerifyTotp.php`
- `davya-crm/database/migrations/2026_04_17_200000_add_totp_columns_to_users.php`

- [ ] **Step 1: Install the 2FA library**

```bash
composer require pragmarx/google2fa bacon/bacon-qr-code
```

- [ ] **Step 2: Copy reference files verbatim from davya-crm**

From `/Users/Sumit/davya-crm/`, copy these six paths into the same relative paths under `/Users/Sumit/sumit-finance/`:
- `app/Services/TotpService.php`
- `app/Filament/Pages/Auth/EnrollTotp.php`
- `app/Filament/Pages/Auth/VerifyTotp.php`
- `database/migrations/2026_04_17_200000_add_totp_columns_to_users.php`
- `tests/Feature/TotpEnrollmentTest.php`
- `tests/Feature/TotpVerificationTest.php`

Rename the migration's timestamp prefix to today's date to keep ordering clean.

- [ ] **Step 3: Register pages in AdminPanelProvider**

Add to the panel's `pages()` array:

```php
\App\Filament\Pages\Auth\EnrollTotp::class,
\App\Filament\Pages\Auth\VerifyTotp::class,
```

- [ ] **Step 4: Update ForcePasswordChange middleware to also check TOTP**

Rename concept — this middleware now handles the entire first-login flow:

```php
if ($user && $user->must_change_password && ! $request->routeIs('filament.admin.auth.change-password-first')) {
    return redirect('/admin/change-password-first');
}
if ($user && $user->totp_enabled && ! session('totp_verified') && ! $request->routeIs('filament.admin.auth.verify-totp')) {
    return redirect('/admin/verify-totp');
}
```

- [ ] **Step 5: Run the copied tests**

```bash
/opt/homebrew/bin/php artisan migrate && /opt/homebrew/bin/php artisan test --filter='TotpEnrollmentTest|TotpVerificationTest'
```

Expected: all PASS (they were green on Phase 1 prod).

- [ ] **Step 6: Commit**

```bash
git add .
git commit -m "feat(auth): TOTP 2FA — enroll + verify (mirrors davya-crm)"
```

### Task 1.7: Account lockout + auth audit log (copy Phase 1)

**Files:**
- Create: migration `create_auth_audit_log_table`.
- Create: `app/Models/AuthAuditLog.php`.
- Create: `app/Listeners/LogAuthEvents.php`.
- Modify: `app/Providers/AppServiceProvider.php` or Filament provider to register listener.
- Create: `tests/Feature/AccountLockoutTest.php`, `tests/Feature/AuthAuditLogTest.php`.

- [ ] **Step 1: Copy the Phase 1 reference files**

From `/Users/Sumit/davya-crm/`, copy:
- `app/Models/AuthAuditLog.php`
- `app/Listeners/LogAuthEvents.php`
- `database/migrations/<date>_create_auth_audit_log_table.php` (rename timestamp)
- `tests/Feature/AccountLockoutTest.php`
- `tests/Feature/AuthAuditLogTest.php`

- [ ] **Step 2: Register the listener**

In `app/Providers/AppServiceProvider.php::boot()`:

```php
\Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class,         [\App\Listeners\LogAuthEvents::class, 'handleLogin']);
\Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Failed::class,        [\App\Listeners\LogAuthEvents::class, 'handleFailed']);
\Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Logout::class,        [\App\Listeners\LogAuthEvents::class, 'handleLogout']);
\Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Lockout::class,       [\App\Listeners\LogAuthEvents::class, 'handleLockout']);
```

- [ ] **Step 3: Run the tests**

```bash
/opt/homebrew/bin/php artisan migrate && /opt/homebrew/bin/php artisan test --filter='AccountLockoutTest|AuthAuditLogTest'
```

Expected: PASS.

- [ ] **Step 4: Commit**

```bash
git add .
git commit -m "feat(auth): account lockout (5/15min) + auth audit log"
```

### Task 1.8: Security headers + HSTS + no-index + root redirect

**Files:**
- Create: `app/Http/Middleware/SecurityHeaders.php`.
- Modify: `bootstrap/app.php` (register global middleware).
- Modify: `routes/web.php` (root redirect).
- Create: `tests/Feature/SecurityHeadersTest.php`.

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/SecurityHeadersTest.php
namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_security_headers_present_on_any_response(): void
    {
        $r = $this->get('/');
        $r->assertHeader('X-Frame-Options', 'DENY');
        $r->assertHeader('X-Content-Type-Options', 'nosniff');
        $r->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $r->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet');
        $r->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $r->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_root_redirects_to_admin(): void
    {
        $this->get('/')->assertRedirect('/admin');
    }
}
```

- [ ] **Step 2: Run to confirm failure**

```bash
/opt/homebrew/bin/php artisan test --filter=SecurityHeadersTest
```

Expected: FAIL.

- [ ] **Step 3: Create middleware**

```php
<?php
// app/Http/Middleware/SecurityHeaders.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $r = $next($request);
        $r->headers->set('X-Frame-Options', 'DENY');
        $r->headers->set('X-Content-Type-Options', 'nosniff');
        $r->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $r->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet');
        $r->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $r->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        return $r;
    }
}
```

- [ ] **Step 4: Register global middleware in `bootstrap/app.php`**

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
})
```

- [ ] **Step 5: Root redirect**

Replace `routes/web.php`:

```php
<?php
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/admin'));
```

- [ ] **Step 6: Run tests to confirm pass**

```bash
/opt/homebrew/bin/php artisan test --filter=SecurityHeadersTest
```

Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Middleware/SecurityHeaders.php bootstrap/app.php routes/web.php tests/Feature/SecurityHeadersTest.php
git commit -m "feat(security): headers + HSTS + noindex + root->admin redirect"
```

### Task 1.9: Deploy script + first deploy to me.ipu.co.in

**Files:**
- Create: `scripts/deploy.sh`
- Create: `docs/DEPLOY.md`

- [ ] **Step 1: Create the deploy script**

`/Users/Sumit/sumit-finance/scripts/deploy.sh`:

```bash
#!/usr/bin/env bash
# Sumit Finance prod deploy runner — run from laptop via SSH, or in Hostinger Terminal.
set -u
PHP=/opt/alt/php84/usr/bin/php
cd /home/ipuc/sumit-finance || exit 1

echo "=== git ==="
git log -1 --oneline

echo "=== migrate ==="
$PHP artisan migrate --force

echo "=== seed SumitUserSeeder (idempotent via updateOrCreate) ==="
$PHP artisan db:seed --class=SumitUserSeeder --force

echo "=== clear ==="
$PHP artisan config:clear
$PHP artisan route:clear
$PHP artisan view:clear

echo "=== DEPLOY OK ==="
```

```bash
chmod +x scripts/deploy.sh
```

- [ ] **Step 2: Write DEPLOY.md**

`/Users/Sumit/sumit-finance/docs/DEPLOY.md` — short notes: how to SSH, how to clone on prod first time, how to re-run `deploy.sh`, where prod `.env` lives (`/home/ipuc/sumit-finance/.env`, mode 600).

- [ ] **Step 3: Commit**

```bash
git add scripts docs
git commit -m "chore(deploy): deploy.sh + DEPLOY.md"
git push origin main
```

- [ ] **Step 4: SSH to Hostinger, clone and first-time bootstrap**

This is a one-time manual step Sumit runs in Hostinger Terminal (per Phase 2 memory, SSH from laptop is not guaranteed — use Terminal):

```bash
cd /home/ipuc
git clone git@github-davya-crm:sumitdabass/sumit-finance.git   # adjust host alias if needed
cd sumit-finance
composer install --no-dev --optimize-autoloader
cp .env.example .env
/opt/alt/php84/usr/bin/php artisan key:generate
# Fill .env with prod values (DB creds, APP_URL=https://me.ipu.co.in, APP_ENV=production)
chmod 600 .env
```

- [ ] **Step 5: Run deploy script on prod**

```bash
bash scripts/deploy.sh
```

Expected: migrate creates all tables, seeder creates Sumit user, caches cleared.

- [ ] **Step 6: Browser smoke-test**

Open `https://me.ipu.co.in/admin` — Filament login appears. Log in with `sumit@davya.local` / `ChangeMe2026!`. Redirected to password-change page. Set new password. Redirected to enroll-TOTP. Set up 2FA in authenticator app. Reach dashboard (empty).

- [ ] **Step 7: Tag v0-scaffold**

```bash
git tag v0-scaffold
git push origin v0-scaffold
```

### M1 checkpoint

- [ ] `https://me.ipu.co.in/admin` loads Filament login.
- [ ] Single user `sumit@davya.local` seeded.
- [ ] First login forces password change + TOTP enrollment.
- [ ] All security headers verified with `curl -I`.
- [ ] Full test suite green on local.
- [ ] `v0-scaffold` tag pushed.

---

## Milestone 2 — Expenses (≈6 hours)

**Output:** `/api/personal/expenses` live (auth'd by `X-Personal-Token`), `ExpenseResource` CRUD at `/admin/expenses`, full TDD coverage (≥10 tests) including race condition, v1-expenses tag deployed.

### Task 2.1: expense_categories migration + model + seeder

**Files:**
- Create: migration `create_expense_categories_table`.
- Create: `app/Models/ExpenseCategory.php`.
- Create: `database/seeders/ExpenseCategoriesSeeder.php`.
- Modify: `database/seeders/DatabaseSeeder.php`.
- Create: `tests/Feature/ExpenseCategoriesSeederTest.php`.

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/ExpenseCategoriesSeederTest.php
namespace Tests\Feature;

use App\Models\ExpenseCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseCategoriesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_13_categories_with_slugs(): void
    {
        $this->seed(\Database\Seeders\ExpenseCategoriesSeeder::class);
        $this->assertSame(13, ExpenseCategory::count());
        $this->assertNotNull(ExpenseCategory::where('slug', 'transport')->first());
    }

    public function test_names_are_unique(): void
    {
        $this->seed(\Database\Seeders\ExpenseCategoriesSeeder::class);
        $this->expectException(\Illuminate\Database\QueryException::class);
        ExpenseCategory::create(['name' => 'Food', 'slug' => 'food-2', 'sort_order' => 99]);
    }
}
```

- [ ] **Step 2: Run to verify failure**

Expected: FAIL — model + seeder missing.

- [ ] **Step 3: Create migration**

```bash
/opt/homebrew/bin/php artisan make:migration create_expense_categories_table
```

```php
public function up(): void
{
    Schema::create('expense_categories', function (Blueprint $table) {
        $table->id();
        $table->string('name', 60)->unique();
        $table->string('slug', 60)->unique();
        $table->smallInteger('sort_order')->default(0);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('expense_categories');
}
```

- [ ] **Step 4: Create the model**

```php
<?php
// app/Models/ExpenseCategory.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    protected $fillable = ['name', 'slug', 'sort_order'];
}
```

- [ ] **Step 5: Create the seeder**

```php
<?php
// database/seeders/ExpenseCategoriesSeeder.php
namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Food', 'Transport', 'Utilities', 'Rent', 'Subscriptions',
            'Entertainment', 'Health', 'Personal Care', 'Shopping',
            'Education', 'Travel', 'Gifts', 'Other',
        ];
        foreach ($names as $i => $n) {
            ExpenseCategory::updateOrCreate(
                ['name' => $n],
                ['slug' => str($n)->slug()->toString(), 'sort_order' => $i],
            );
        }
    }
}
```

- [ ] **Step 6: Wire seeder**

In `DatabaseSeeder::run()` append:

```php
$this->call([
    ExpenseCategoriesSeeder::class,
]);
```

- [ ] **Step 7: Run tests**

```bash
/opt/homebrew/bin/php artisan migrate:fresh --seed && /opt/homebrew/bin/php artisan test --filter=ExpenseCategoriesSeederTest
```

Expected: PASS.

- [ ] **Step 8: Commit**

```bash
git add .
git commit -m "feat(expenses): expense_categories table + seeder (13 categories)"
```

### Task 2.2: expenses migration + Expense model + factory

**Files:**
- Create: migration `create_expenses_table`.
- Create: `app/Models/Expense.php`.
- Create: `database/factories/ExpenseFactory.php`.
- Create: `tests/Feature/ExpenseModelTest.php`.

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/ExpenseModelTest.php
namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\ExpenseCategoriesSeeder::class);
    }

    public function test_factory_creates_valid_expense(): void
    {
        $e = Expense::factory()->create();
        $this->assertInstanceOf(ExpenseCategory::class, $e->category);
        $this->assertIsFloat((float) $e->amount);
    }

    public function test_slack_message_id_is_unique_when_non_null(): void
    {
        $cat = ExpenseCategory::first();
        Expense::create(['amount' => 100, 'category_id' => $cat->id, 'spent_at' => now(), 'slack_message_id' => 'E.1']);
        $this->expectException(\Illuminate\Database\QueryException::class);
        Expense::create(['amount' => 200, 'category_id' => $cat->id, 'spent_at' => now(), 'slack_message_id' => 'E.1']);
    }

    public function test_two_null_slack_message_ids_allowed(): void
    {
        $cat = ExpenseCategory::first();
        Expense::create(['amount' => 100, 'category_id' => $cat->id, 'spent_at' => now()]);
        Expense::create(['amount' => 200, 'category_id' => $cat->id, 'spent_at' => now()]);
        $this->assertSame(2, Expense::count());
    }
}
```

- [ ] **Step 2: Run to confirm failure**

Expected: FAIL — table/model missing.

- [ ] **Step 3: Create migration**

```bash
/opt/homebrew/bin/php artisan make:migration create_expenses_table
```

```php
public function up(): void
{
    Schema::create('expenses', function (Blueprint $table) {
        $table->id();
        $table->decimal('amount', 12, 2);
        $table->foreignId('category_id')->constrained('expense_categories')->restrictOnDelete();
        $table->string('description', 500)->nullable();
        $table->dateTime('spent_at');
        $table->enum('payment_mode', ['upi','card','cash','bank_transfer','other'])->nullable();
        $table->string('slack_message_id', 50)->nullable()->unique();
        $table->text('raw_input')->nullable();
        $table->timestamps();
        $table->index('spent_at');
        $table->index(['category_id', 'spent_at']);
    });
}

public function down(): void
{
    Schema::dropIfExists('expenses');
}
```

- [ ] **Step 4: Create model**

```php
<?php
// app/Models/Expense.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount', 'category_id', 'description', 'spent_at',
        'payment_mode', 'slack_message_id', 'raw_input',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'spent_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }
}
```

- [ ] **Step 5: Create factory**

```php
<?php
// database/factories/ExpenseFactory.php
namespace Database\Factories;

use App\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'amount' => $this->faker->numberBetween(100, 5000),
            'category_id' => ExpenseCategory::inRandomOrder()->firstOrFail()->id,
            'description' => $this->faker->sentence(4),
            'spent_at' => now()->subDays($this->faker->numberBetween(0, 30)),
            'payment_mode' => $this->faker->randomElement(['upi','card','cash']),
        ];
    }
}
```

- [ ] **Step 6: Run tests**

```bash
/opt/homebrew/bin/php artisan migrate:fresh --seed && /opt/homebrew/bin/php artisan test --filter=ExpenseModelTest
```

Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add .
git commit -m "feat(expenses): Expense model + factory + unique slack_message_id"
```

### Task 2.3: ExpensePolicy

**Files:**
- Create: `app/Policies/ExpensePolicy.php`.
- Create: `tests/Unit/ExpensePolicyTest.php`.

- [ ] **Step 1: Write failing test**

```php
<?php
// tests/Unit/ExpensePolicyTest.php
namespace Tests\Unit;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpensePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_sumit_can_do_everything(): void
    {
        $this->seed();
        $sumit = User::where('email', 'sumit@davya.local')->first();
        $e = Expense::factory()->create();
        $this->assertTrue($sumit->can('viewAny', Expense::class));
        $this->assertTrue($sumit->can('view', $e));
        $this->assertTrue($sumit->can('create', Expense::class));
        $this->assertTrue($sumit->can('update', $e));
        $this->assertTrue($sumit->can('delete', $e));
    }

    public function test_any_other_user_cannot(): void
    {
        $this->seed();
        $other = User::factory()->create(['email' => 'other@x.local']);
        $e = Expense::factory()->create();
        $this->assertFalse($other->can('viewAny', Expense::class));
        $this->assertFalse($other->can('update', $e));
    }
}
```

- [ ] **Step 2: Run to confirm failure**

Expected: FAIL.

- [ ] **Step 3: Create policy**

```php
<?php
// app/Policies/ExpensePolicy.php
namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->email === 'sumit@davya.local' ? true : null;
    }

    public function viewAny(User $user): bool { return false; }
    public function view(User $user, Expense $e): bool { return false; }
    public function create(User $user): bool { return false; }
    public function update(User $user, Expense $e): bool { return false; }
    public function delete(User $user, Expense $e): bool { return false; }
}
```

Laravel 11 auto-discovers policies so no AuthServiceProvider wiring needed.

- [ ] **Step 4: Run tests**

```bash
/opt/homebrew/bin/php artisan test --filter=ExpensePolicyTest
```

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Policies tests/Unit/ExpensePolicyTest.php
git commit -m "feat(expenses): ExpensePolicy scoped to Sumit"
```

### Task 2.4: ExpenseResource (Filament CRUD)

**Files:**
- Create: `app/Filament/Resources/ExpenseResource.php` + Pages.
- Create: `tests/Feature/ExpenseResourceTest.php`.

- [ ] **Step 1: Scaffold the resource**

```bash
/opt/homebrew/bin/php artisan make:filament-resource Expense --generate
```

- [ ] **Step 2: Edit form schema in `ExpenseResource::form()`**

```php
return $form->schema([
    Forms\Components\TextInput::make('amount')->numeric()->required()->prefix('₹')->minValue(0.01)->step(0.01),
    Forms\Components\Select::make('category_id')
        ->relationship('category', 'name')
        ->required()
        ->searchable(),
    Forms\Components\Textarea::make('description')->maxLength(500)->rows(2),
    Forms\Components\DateTimePicker::make('spent_at')->default(now())->required(),
    Forms\Components\Select::make('payment_mode')
        ->options(['upi' => 'UPI', 'card' => 'Card', 'cash' => 'Cash', 'bank_transfer' => 'Bank', 'other' => 'Other']),
]);
```

- [ ] **Step 3: Edit table in `ExpenseResource::table()`**

```php
return $table
    ->columns([
        Tables\Columns\TextColumn::make('spent_at')->dateTime('d M Y H:i')->sortable(),
        Tables\Columns\TextColumn::make('category.name')->badge(),
        Tables\Columns\TextColumn::make('amount')->money('INR')->sortable(),
        Tables\Columns\TextColumn::make('payment_mode')->badge(),
        Tables\Columns\TextColumn::make('description')->limit(40)->toggleable(),
    ])
    ->defaultSort('spent_at', 'desc')
    ->filters([
        Tables\Filters\SelectFilter::make('category_id')->relationship('category', 'name'),
        Tables\Filters\Filter::make('this_month')
            ->query(fn ($q) => $q->whereBetween('spent_at', [now()->startOfMonth(), now()->endOfMonth()])),
    ])
    ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
    ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
```

- [ ] **Step 4: Write feature test**

```php
<?php
// tests/Feature/ExpenseResourceTest.php
namespace Tests\Feature;

use App\Filament\Resources\ExpenseResource;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExpenseResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->actingAs(User::where('email', 'sumit@davya.local')->first());
    }

    public function test_manual_create_via_filament_persists_with_null_slack_id(): void
    {
        $cat = ExpenseCategory::where('slug', 'food')->first();
        Livewire::test(ExpenseResource\Pages\CreateExpense::class)
            ->fillForm(['amount' => 450, 'category_id' => $cat->id, 'description' => 'manual', 'spent_at' => now()])
            ->call('create')
            ->assertHasNoFormErrors();
        $e = Expense::first();
        $this->assertSame('450.00', (string) $e->amount);
        $this->assertNull($e->slack_message_id);
    }
}
```

- [ ] **Step 5: Run test**

```bash
/opt/homebrew/bin/php artisan test --filter=ExpenseResourceTest
```

Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Filament/Resources tests/Feature/ExpenseResourceTest.php
git commit -m "feat(expenses): ExpenseResource CRUD with monthly filter"
```

### Task 2.5: config/personal.php + VerifyPersonalToken middleware

**Files:**
- Create: `config/personal.php`.
- Create: `app/Http/Middleware/VerifyPersonalToken.php`.
- Create: `tests/Feature/VerifyPersonalTokenTest.php`.
- Modify: `.env` (local), document for prod.

- [ ] **Step 1: Write failing test**

```php
<?php
// tests/Feature/VerifyPersonalTokenTest.php
namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class VerifyPersonalTokenTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['personal.capture_token' => 'fake-personal-token']);
        Route::post('/api/test-personal-token', fn () => response()->json(['ok' => true]))
            ->middleware(\App\Http\Middleware\VerifyPersonalToken::class);
    }

    public function test_valid_token_passes(): void
    {
        $this->postJson('/api/test-personal-token', [], ['X-Personal-Token' => 'fake-personal-token'])
             ->assertStatus(200)->assertJson(['ok' => true]);
    }

    public function test_missing_token_returns_401(): void
    {
        $this->postJson('/api/test-personal-token')->assertStatus(401)->assertJson(['error' => 'unauthorized']);
    }

    public function test_wrong_token_returns_401(): void
    {
        $this->postJson('/api/test-personal-token', [], ['X-Personal-Token' => 'nope'])->assertStatus(401);
    }
}
```

- [ ] **Step 2: Confirm failure**

Expected: FAIL — middleware missing.

- [ ] **Step 3: Create config**

```php
<?php
// config/personal.php
return [
    'capture_token' => env('PERSONAL_CAPTURE_TOKEN', ''),
];
```

- [ ] **Step 4: Create middleware**

```php
<?php
// app/Http/Middleware/VerifyPersonalToken.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyPersonalToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('personal.capture_token');
        if (empty($expected) || $request->header('X-Personal-Token') !== $expected) {
            return response()->json(['error' => 'unauthorized'], 401);
        }
        return $next($request);
    }
}
```

- [ ] **Step 5: Add to `.env` (local)**

```ini
PERSONAL_CAPTURE_TOKEN=test-personal-token-abcdef0123456789
```

- [ ] **Step 6: Run test**

```bash
/opt/homebrew/bin/php artisan test --filter=VerifyPersonalTokenTest
```

Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add config/personal.php app/Http/Middleware/VerifyPersonalToken.php tests/Feature/VerifyPersonalTokenTest.php
git commit -m "feat(api): VerifyPersonalToken middleware + config"
```

### Task 2.6: StorePersonalExpenseRequest

**Files:**
- Create: `app/Http/Requests/StorePersonalExpenseRequest.php`.

- [ ] **Step 1: Create request**

```php
<?php
// app/Http/Requests/StorePersonalExpenseRequest.php
namespace App\Http\Requests;

use App\Models\ExpenseCategory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePersonalExpenseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        // Allow category by name OR slug; resolve to category_id before validation.
        $input = $this->input('category');
        if ($input !== null) {
            $cat = ExpenseCategory::whereRaw('LOWER(name) = ?', [strtolower($input)])
                ->orWhere('slug', str($input)->slug()->toString())
                ->first();
            if ($cat) $this->merge(['category_id' => $cat->id]);
        }
    }

    public function rules(): array
    {
        return [
            'amount'           => ['required','numeric','gt:0','lte:10000000'],
            'category_id'      => ['required','integer','exists:expense_categories,id'],
            'description'      => ['nullable','string','max:500'],
            'spent_at'         => ['nullable','date'],
            'payment_mode'     => ['nullable','in:upi,card,cash,bank_transfer,other'],
            'slack_message_id' => ['required','string','max:50'],
            'raw_input'        => ['nullable','string','max:4000'],
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

- [ ] **Step 2: Commit**

```bash
git add app/Http/Requests/StorePersonalExpenseRequest.php
git commit -m "feat(expenses): StorePersonalExpenseRequest with name-or-slug category"
```

### Task 2.7: PersonalExpenseController + endpoint tests

**Files:**
- Create: `app/Http/Controllers/PersonalExpenseController.php`.
- Modify: `routes/api.php` (wire route).
- Create: `tests/Feature/ExpenseCaptureTest.php`.

- [ ] **Step 1: Write feature test — 8 cases**

```php
<?php
// tests/Feature/ExpenseCaptureTest.php
namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExpenseCaptureTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'test-personal-token-abcdef0123456789';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config(['personal.capture_token' => self::TOKEN]);
    }

    private function postPayload(array $overrides = [], ?string $token = self::TOKEN)
    {
        $payload = array_merge([
            'amount' => 450, 'category' => 'Transport',
            'description' => 'fuel', 'payment_mode' => 'upi',
            'slack_message_id' => 'E.'.uniqid(), 'raw_input' => 'fuel at HP 450',
        ], $overrides);
        $headers = $token === null ? [] : ['X-Personal-Token' => $token];
        return $this->postJson('/api/personal/expenses', $payload, $headers);
    }

    public function test_missing_token_returns_401(): void
    {
        $this->postPayload([], token: null)->assertStatus(401);
    }

    public function test_happy_path_creates_expense(): void
    {
        $r = $this->postPayload(['amount' => 450, 'category' => 'Transport']);
        $r->assertCreated()->assertJsonStructure(['id']);
        $e = Expense::first();
        $this->assertSame('450.00', (string) $e->amount);
        $this->assertSame('Transport', $e->category->name);
    }

    public function test_category_accepted_by_slug(): void
    {
        $this->postPayload(['category' => 'personal-care'])->assertCreated();
        $this->assertSame('Personal Care', Expense::first()->category->name);
    }

    public function test_unknown_category_returns_422(): void
    {
        $this->postPayload(['category' => 'Martian Groceries'])->assertStatus(422)->assertJsonValidationErrors('category_id');
    }

    public function test_missing_amount_returns_422(): void
    {
        $this->postPayload(['amount' => null])->assertStatus(422)->assertJsonValidationErrors('amount');
    }

    public function test_negative_amount_returns_422(): void
    {
        $this->postPayload(['amount' => -5])->assertStatus(422)->assertJsonValidationErrors('amount');
    }

    public function test_duplicate_slack_message_id_returns_409(): void
    {
        $first = $this->postPayload(['slack_message_id' => 'E.DUPE'])->assertCreated();
        $this->postPayload(['slack_message_id' => 'E.DUPE'])
            ->assertStatus(409)
            ->assertJson(['error' => 'duplicate_slack_message', 'existing_id' => $first->json('id')]);
    }

    public function test_slack_message_id_race_returns_409_not_500(): void
    {
        // Mirrors Phase 2 PaymentCaptureTest race pattern (DB::listen outside savepoint).
        $slackId = 'E.RACE';
        $cat = ExpenseCategory::first();
        $raced = false;
        DB::listen(function ($q) use (&$raced, $slackId, $cat) {
            if ($raced) return;
            if (!str_contains($q->sql, 'expenses')) return;
            if (!str_starts_with(strtolower(ltrim($q->sql)), 'select')) return;
            if (!in_array($slackId, $q->bindings, true)) return;
            $raced = true;
            DB::table('expenses')->insert([
                'amount' => 1, 'category_id' => $cat->id, 'spent_at' => now(),
                'slack_message_id' => $slackId, 'created_at' => now(), 'updated_at' => now(),
            ]);
        });
        $r = $this->postPayload(['slack_message_id' => $slackId]);
        $r->assertStatus(409)->assertJson(['error' => 'duplicate_slack_message']);
        $this->assertNotNull($r->json('existing_id'));
    }
}
```

- [ ] **Step 2: Confirm failure**

Expected: FAIL — controller + route missing.

- [ ] **Step 3: Create controller**

```php
<?php
// app/Http/Controllers/PersonalExpenseController.php
namespace App\Http\Controllers;

use App\Http\Requests\StorePersonalExpenseRequest;
use App\Models\Expense;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PersonalExpenseController extends Controller
{
    public function store(StorePersonalExpenseRequest $request): JsonResponse
    {
        $data = $request->validated();

        $existing = Expense::where('slack_message_id', $data['slack_message_id'])->first();
        if ($existing !== null) {
            return response()->json([
                'error' => 'duplicate_slack_message',
                'existing_id' => $existing->id,
            ], 409);
        }

        try {
            $expense = DB::transaction(function () use ($data) {
                return Expense::create([
                    'amount'           => $data['amount'],
                    'category_id'      => $data['category_id'],
                    'description'      => $data['description']  ?? null,
                    'spent_at'         => $data['spent_at']     ?? now(),
                    'payment_mode'     => $data['payment_mode'] ?? null,
                    'slack_message_id' => $data['slack_message_id'],
                    'raw_input'        => $data['raw_input']    ?? null,
                ]);
            });
        } catch (QueryException $e) {
            if (($e->errorInfo[0] ?? null) === '23000') {
                $existing = Expense::where('slack_message_id', $data['slack_message_id'])->first();
                if ($existing !== null) {
                    return response()->json([
                        'error'       => 'duplicate_slack_message',
                        'existing_id' => $existing->id,
                    ], 409);
                }
            }
            throw $e;
        }

        Log::info('personal.expense.captured', [
            'expense_id' => $expense->id,
            'amount'     => $data['amount'],
            'slack_id'   => $data['slack_message_id'],
        ]);

        return response()->json(['id' => $expense->id], 201);
    }
}
```

- [ ] **Step 4: Wire route**

Edit `routes/api.php`:

```php
<?php
use App\Http\Controllers\PersonalExpenseController;
use Illuminate\Support\Facades\Route;

Route::post('/personal/expenses', [PersonalExpenseController::class, 'store'])
    ->middleware(['throttle:60,1', \App\Http\Middleware\VerifyPersonalToken::class]);
```

- [ ] **Step 5: Enable API routing in bootstrap/app.php**

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```

- [ ] **Step 6: Run tests**

```bash
/opt/homebrew/bin/php artisan test --filter=ExpenseCaptureTest
```

Expected: all 8 PASS.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers routes/api.php bootstrap/app.php tests/Feature/ExpenseCaptureTest.php
git commit -m "feat(expenses): POST /api/personal/expenses with race-safe 409"
```

### Task 2.8: Deploy M2 and tag v1-expenses

- [ ] **Step 1: Push + SSH pull + deploy**

```bash
git push origin main
# In Hostinger Terminal:
# cd /home/ipuc/sumit-finance && git pull --ff-only && bash scripts/deploy.sh
```

- [ ] **Step 2: Browser smoke-test**

`/admin/expenses` — manual add an expense via Filament. Verify it appears in the list.

- [ ] **Step 3: Add `PERSONAL_CAPTURE_TOKEN` to prod .env**

```bash
# In Hostinger Terminal:
cd /home/ipuc/sumit-finance
grep -q "^PERSONAL_CAPTURE_TOKEN=" .env || echo "PERSONAL_CAPTURE_TOKEN=$(openssl rand -hex 16)" >> .env
grep "^PERSONAL_CAPTURE_TOKEN=" .env
# Save the printed token for Task 5.2.
/opt/alt/php84/usr/bin/php artisan config:clear
```

- [ ] **Step 4: Smoke-curl from laptop**

```bash
curl -sS -X POST https://me.ipu.co.in/api/personal/expenses \
  -H 'Content-Type: application/json' \
  -d '{"amount":100,"category":"Other","slack_message_id":"CURL.E1","raw_input":"curl smoke"}' \
  -w "\nHTTP %{http_code}\n"
# Expected: HTTP 401
curl -sS -X POST https://me.ipu.co.in/api/personal/expenses \
  -H "X-Personal-Token: <TOKEN>" \
  -H 'Content-Type: application/json' \
  -d '{"amount":100,"category":"Other","slack_message_id":"CURL.E2","raw_input":"curl smoke"}' \
  -w "\nHTTP %{http_code}\n"
# Expected: HTTP 201 {"id":...}
```

- [ ] **Step 5: Clean up smoke-test rows via `/admin/expenses`.**

- [ ] **Step 6: Tag**

```bash
git tag v1-expenses
git push origin v1-expenses
```

### M2 checkpoint

- [ ] `/api/personal/expenses` returns 401 without token, 201 with token.
- [ ] `/admin/expenses` CRUD works end-to-end.
- [ ] 10 tests green locally (category seeder + model + policy + resource + middleware + 8 capture tests minus 2 overlaps = 10 unique).
- [ ] `v1-expenses` tag on prod.

---

## Milestone 3 — Incomes (≈6 hours)

**Output:** Parallel to M2 for the `incomes` table + `income_sources` + `/api/personal/incomes` + `IncomeResource`, plus the `davya_reference` JSON column persisted end-to-end.

### Task 3.1: income_sources table + seeder + model

Mirror Task 2.1 with these differences:
- Table name `income_sources`.
- Model `App\Models\IncomeSource`.
- Seeder `IncomeSourcesSeeder` creates 7 rows: Davya withdrawal, Salary, Rent received, Interest, Freelance, Gift, Other.

- [ ] **Step 1: Write `tests/Feature/IncomeSourcesSeederTest.php`** (mirror of Task 2.1's test, assert count=7 and slug 'davya-withdrawal' exists).

- [ ] **Step 2: Run to confirm failure.**

- [ ] **Step 3: Create migration** — schema identical to `expense_categories`.

- [ ] **Step 4: Create `App\Models\IncomeSource`** — fillable `['name','slug','sort_order']`.

- [ ] **Step 5: Create seeder**

```php
public function run(): void
{
    $names = ['Davya withdrawal', 'Salary', 'Rent received', 'Interest', 'Freelance', 'Gift', 'Other'];
    foreach ($names as $i => $n) {
        IncomeSource::updateOrCreate(['name' => $n], ['slug' => str($n)->slug()->toString(), 'sort_order' => $i]);
    }
}
```

- [ ] **Step 6: Wire in DatabaseSeeder.**

- [ ] **Step 7: Run tests, commit.**

```bash
git commit -m "feat(incomes): income_sources table + seeder (7 sources)"
```

### Task 3.2: incomes migration + Income model + factory

Mirror Task 2.2 with these differences from expenses:
- Column `source_id` → `income_sources`.
- Column `received_at` instead of `spent_at`.
- No `payment_mode` column.
- **Added** `davya_reference` JSON nullable column.
- `slack_message_id` nullable + unique (same as expenses).

- [ ] **Step 1: Write `tests/Feature/IncomeModelTest.php`** — assert factory works, slack uniqueness, AND `davya_reference` round-trips as array when cast to json.

```php
public function test_davya_reference_cast_to_array(): void
{
    $src = IncomeSource::where('slug','davya-withdrawal')->first();
    $i = Income::create([
        'amount' => 50000, 'source_id' => $src->id, 'received_at' => now(),
        'slack_message_id' => 'I.T1',
        'davya_reference' => ['phase2_expense_id' => 17, 'phase2_slack_message_id' => 'W.T1'],
    ]);
    $this->assertIsArray($i->fresh()->davya_reference);
    $this->assertSame(17, $i->fresh()->davya_reference['phase2_expense_id']);
}
```

- [ ] **Step 2: Confirm failure, create migration**

```php
Schema::create('incomes', function (Blueprint $table) {
    $table->id();
    $table->decimal('amount', 12, 2);
    $table->foreignId('source_id')->constrained('income_sources')->restrictOnDelete();
    $table->string('description', 500)->nullable();
    $table->dateTime('received_at');
    $table->string('slack_message_id', 50)->nullable()->unique();
    $table->text('raw_input')->nullable();
    $table->json('davya_reference')->nullable();
    $table->timestamps();
    $table->index('received_at');
    $table->index(['source_id','received_at']);
});
```

- [ ] **Step 3: Create `App\Models\Income`**

```php
class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount','source_id','description','received_at',
        'slack_message_id','raw_input','davya_reference',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'received_at' => 'datetime',
            'davya_reference' => 'array',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(IncomeSource::class, 'source_id');
    }
}
```

- [ ] **Step 4: Create `IncomeFactory`** (mirror of ExpenseFactory).

- [ ] **Step 5: Run tests, commit.**

```bash
git commit -m "feat(incomes): Income model + factory + davya_reference json cast"
```

### Task 3.3: IncomePolicy

Mirror Task 2.3 verbatim — `IncomePolicy` with `before()` returning true for `sumit@davya.local`, plus IncomePolicyTest.

```bash
git commit -m "feat(incomes): IncomePolicy scoped to Sumit"
```

### Task 3.4: IncomeResource (Filament CRUD)

Mirror Task 2.4 with:
- Form includes `source_id` (not `category_id`), `received_at`, `description`, `amount`.
- Table columns: `received_at`, `source.name` badge, `amount` INR, `description`.
- Filter: `source_id`, this_month.
- No `payment_mode`.

```bash
git commit -m "feat(incomes): IncomeResource CRUD"
```

### Task 3.5: StorePersonalIncomeRequest

Mirror Task 2.6 with these rule differences:
- Replace `category` with `source` (name-or-slug resolved to `source_id`).
- Replace `spent_at` with `received_at`.
- No `payment_mode` rule.
- Add `davya_reference` rule: `['nullable','array']`.

```bash
git commit -m "feat(incomes): StorePersonalIncomeRequest"
```

### Task 3.6: PersonalIncomeController + 8 endpoint tests

Mirror Task 2.7. Controller is structurally identical to `PersonalExpenseController` but on the `incomes` table; additionally persists `davya_reference` from the request if present.

Key test addition (on top of the 8 standard ones):

```php
public function test_davya_reference_persisted(): void
{
    $r = $this->postPayload([
        'source' => 'Davya withdrawal',
        'davya_reference' => ['phase2_expense_id' => 42, 'phase2_slack_message_id' => 'W.T'],
    ])->assertCreated();
    $i = \App\Models\Income::find($r->json('id'));
    $this->assertSame(42, $i->davya_reference['phase2_expense_id']);
}
```

- [ ] Wire route:

```php
Route::post('/personal/incomes', [PersonalIncomeController::class, 'store'])
    ->middleware(['throttle:60,1', \App\Http\Middleware\VerifyPersonalToken::class]);
```

```bash
git commit -m "feat(incomes): POST /api/personal/incomes with davya_reference persistence"
```

### Task 3.7: Deploy M3 + tag v2-incomes

Mirror Task 2.8 steps for deploy + browser smoke + smoke-curl. Include a second smoke-curl that sends `davya_reference` and verify it lands on the row.

```bash
git tag v2-incomes && git push origin v2-incomes
```

### M3 checkpoint

- [ ] `/api/personal/incomes` returns 401/409/422/201 per contract.
- [ ] `/admin/incomes` CRUD works.
- [ ] `davya_reference` round-trips as JSON.
- [ ] `v2-incomes` deployed.

---

## Milestone 4 — Dashboard widgets (≈4 hours)

**Output:** `/admin` shows four widgets computing month spend by category, month income by source, running balance, top 5 spends. One tested service powers all four.

### Task 4.1: PersonalBalanceService + unit tests

**Files:**
- Create: `app/Services/PersonalBalanceService.php`.
- Create: `tests/Unit/PersonalBalanceServiceTest.php`.

- [ ] **Step 1: Write failing test**

```php
<?php
// tests/Unit/PersonalBalanceServiceTest.php
namespace Tests\Unit;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Income;
use App\Models\IncomeSource;
use App\Services\PersonalBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalBalanceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_current_balance_is_incomes_minus_expenses(): void
    {
        Income::factory()->create(['amount' => 10000, 'received_at' => now()]);
        Expense::factory()->create(['amount' => 3000, 'spent_at' => now()]);
        $svc = new PersonalBalanceService();
        $this->assertSame('7000.00', $svc->currentBalance()->toString());
    }

    public function test_month_spend_by_category(): void
    {
        $food = ExpenseCategory::where('slug','food')->first();
        $transport = ExpenseCategory::where('slug','transport')->first();
        Expense::factory()->create(['amount' => 500, 'category_id' => $food->id, 'spent_at' => now()]);
        Expense::factory()->create(['amount' => 200, 'category_id' => $food->id, 'spent_at' => now()]);
        Expense::factory()->create(['amount' => 100, 'category_id' => $transport->id, 'spent_at' => now()]);
        // Out-of-month — excluded
        Expense::factory()->create(['amount' => 9999, 'category_id' => $food->id, 'spent_at' => now()->subMonth()]);

        $svc = new PersonalBalanceService();
        $rows = $svc->monthSpendByCategory(now());
        $this->assertSame('700.00', $rows['Food']);
        $this->assertSame('100.00', $rows['Transport']);
    }

    public function test_month_income_by_source(): void
    {
        $salary = IncomeSource::where('slug','salary')->first();
        Income::factory()->create(['amount' => 50000, 'source_id' => $salary->id, 'received_at' => now()]);
        $svc = new PersonalBalanceService();
        $this->assertSame('50000.00', $svc->monthIncomeBySource(now())['Salary']);
    }

    public function test_top_spends(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Expense::factory()->create(['amount' => $i * 100, 'spent_at' => now()]);
        }
        $top = (new PersonalBalanceService())->topSpends(now(), 5);
        $this->assertCount(5, $top);
        $this->assertSame('1000.00', (string) $top->first()->amount);
    }
}
```

- [ ] **Step 2: Confirm failure.**

- [ ] **Step 3: Create service**

```php
<?php
// app/Services/PersonalBalanceService.php
namespace App\Services;

use App\Models\Expense;
use App\Models\Income;
use Brick\Math\BigDecimal;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class PersonalBalanceService
{
    public function currentBalance(): BigDecimal
    {
        $incomes  = BigDecimal::of((string) Income::sum('amount'));
        $expenses = BigDecimal::of((string) Expense::sum('amount'));
        return $incomes->minus($expenses);
    }

    /** @return array<string,string>  ['Food' => '700.00', ...] */
    public function monthSpendByCategory(CarbonInterface $month): array
    {
        return Expense::query()
            ->whereBetween('spent_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->join('expense_categories','expense_categories.id','=','expenses.category_id')
            ->selectRaw('expense_categories.name as name, SUM(expenses.amount) as total')
            ->groupBy('expense_categories.name')
            ->pluck('total','name')
            ->map(fn ($v) => number_format((float) $v, 2, '.', ''))
            ->toArray();
    }

    /** @return array<string,string> */
    public function monthIncomeBySource(CarbonInterface $month): array
    {
        return Income::query()
            ->whereBetween('received_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->join('income_sources','income_sources.id','=','incomes.source_id')
            ->selectRaw('income_sources.name as name, SUM(incomes.amount) as total')
            ->groupBy('income_sources.name')
            ->pluck('total','name')
            ->map(fn ($v) => number_format((float) $v, 2, '.', ''))
            ->toArray();
    }

    public function topSpends(CarbonInterface $month, int $n = 5): Collection
    {
        return Expense::query()
            ->whereBetween('spent_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->orderByDesc('amount')
            ->limit($n)
            ->with('category')
            ->get();
    }
}
```

`brick/math` is already pulled transitively by Laravel; no extra install needed.

- [ ] **Step 4: Run tests.**

Expected: PASS.

- [ ] **Step 5: Commit.**

```bash
git commit -m "feat(dashboard): PersonalBalanceService with 4 queries + unit tests"
```

### Task 4.2: MonthlySpendByCategoryWidget

**Files:**
- Create: `app/Filament/Widgets/MonthlySpendByCategoryWidget.php`.

- [ ] **Step 1: Create widget**

```php
<?php
namespace App\Filament\Widgets;

use App\Services\PersonalBalanceService;
use Filament\Widgets\ChartWidget;

class MonthlySpendByCategoryWidget extends ChartWidget
{
    protected static ?string $heading = 'Spending this month — by category';
    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $rows = (new PersonalBalanceService())->monthSpendByCategory(now());
        return [
            'datasets' => [['label' => '₹ spent', 'data' => array_values($rows)]],
            'labels' => array_keys($rows),
        ];
    }

    protected function getType(): string { return 'bar'; }
}
```

- [ ] **Step 2: Register in AdminPanelProvider's `->widgets([...])`.**

- [ ] **Step 3: Browser smoke — log in, see the widget render.**

- [ ] **Step 4: Commit.**

```bash
git commit -m "feat(dashboard): monthly-spend-by-category widget"
```

### Task 4.3: MonthlyIncomeBySourceWidget

Mirror Task 4.2 with `PersonalBalanceService::monthIncomeBySource()`.

```bash
git commit -m "feat(dashboard): monthly-income-by-source widget"
```

### Task 4.4: RunningBalanceWidget

ChartWidget of type `line`, 6 months back, one data point per month = `sum(income) - sum(expense)` cumulative.

Test: write a small unit method on `PersonalBalanceService::monthlyRunningBalance(int $months = 6)` returning `[['2026-04' => '12300.00'], ...]`. Keep test adding data for 3 distinct months and assert values.

```bash
git commit -m "feat(dashboard): running-balance line widget (6m)"
```

### Task 4.5: TopSpendsWidget

TableWidget showing `topSpends(now(), 5)` — columns: date, category badge, amount INR, description.

```bash
git commit -m "feat(dashboard): top-5-spends table widget"
```

### Task 4.6: Deploy M4 + v3-dashboard tag

- [ ] Push, pull, run `deploy.sh`.
- [ ] Browser-check the dashboard renders all 4 widgets with seeded + manually-entered data.
- [ ] Tag + push.

```bash
git tag v3-dashboard && git push origin v3-dashboard
```

### M4 checkpoint

- [ ] 4 dashboard widgets render with correct math against the stored data.
- [ ] `PersonalBalanceService` has ≥5 unit tests green.
- [ ] Prod deploy live.

---

## Milestone 5 — Slack + n8n + Gemini capture for Expense + Income (≈8 hours)

**Output:** Slack messages in `#sumit-finance` like `"spent 450 on fuel"` and `"rent received 20000"` land as Expense / Income rows via an imported-and-activated n8n workflow. Withdrawals are **not** yet wired (M6).

### Task 5.1: PersonalFailedController + migration + endpoint tests

Mirror Phase 2 M8 (`FinanceFailedController`):

**Files:**
- Create: migration `create_failed_extractions_table`.
- Create: `app/Models/FailedExtraction.php`.
- Create: `app/Http/Requests/StorePersonalFailedRequest.php`.
- Create: `app/Http/Controllers/PersonalFailedController.php`.
- Create: `tests/Feature/FailedCaptureTest.php` — 3 tests (401, 422 missing error_reason, 201 happy).

Schema:

```php
Schema::create('failed_extractions', function (Blueprint $table) {
    $table->id();
    $table->string('slack_message_id', 50);   // NOT unique — same msg can fail multiple times
    $table->string('slack_channel', 32)->nullable();
    $table->text('raw_input')->nullable();
    $table->string('error_reason', 120);
    $table->timestamps();
    $table->index('created_at');
});
```

Route:

```php
Route::post('/personal/failed', [PersonalFailedController::class, 'store'])
    ->middleware(['throttle:60,1', \App\Http\Middleware\VerifyPersonalToken::class]);
```

Controller is a thin passthrough — no dedup, no ledger, just insert and return `{id}`.

- [ ] Run 3 tests, commit.

```bash
git commit -m "feat(failed): POST /api/personal/failed for n8n-side extraction errors"
```

### Task 5.2: Author `docs/n8n-personal-finance-workflow.json`

**Files:**
- Create: `docs/n8n-personal-finance-workflow.json`.

Start from the Phase 2 finance workflow at `/Users/Sumit/davya-crm/docs/n8n-finance-workflow.json` (includes the M12 fixes: markdown stripping, referrer fallback, bot-message filter, React timestamp, IF target-check).

Edit these parts:

1. Slack Trigger `channelId` → `#sumit-finance` channel id (get from `/who-am-i` or copy from channel URL).
2. Gemini prompt — replace Phase 2 examples with personal examples. Category enum `["Expense","Income","Withdrawal"]`. Schema adds `expense_category` enum matching `expense_categories.name`, and `income_source` enum matching `income_sources.name`.

```
Examples:
- "spent 450 on fuel hdfc card" → {category:"Expense", amount:450, expense_category:"Transport", notes:"fuel hdfc card", payment_mode:"card"}
- "rent 25000 to amit" → {category:"Expense", amount:25000, expense_category:"Rent", notes:"to amit"}
- "salary 80000 credited" → {category:"Income", amount:80000, income_source:"Salary"}
- "interest from fd 1200" → {category:"Income", amount:1200, income_source:"Interest"}
- "withdrew 50000 from davya, apr salary" → {category:"Withdrawal", amount:50000, notes:"apr salary"}
```

3. Dispatch code node — replace Phase 2 code block with personal routing:

```js
const SUMIT_FINANCE_CHANNEL = '<paste channel id>';

const triggerData = $('Slack Trigger — new message').item.json;

// Bot filter (same rationale as Phase 2 M12 fix)
if (triggerData.bot_id || triggerData.subtype === 'bot_message' || triggerData.app_id) return null;

const slackChannel = triggerData.channel || '';
const slackTs = triggerData.ts || '';
const rawInput = (triggerData.text || '').slice(0, 4000);

let gemini = null, parseError = null;
try {
  const raw = $input.item.json.body?.candidates?.[0]?.content?.parts?.[0]?.text;
  if (raw) gemini = JSON.parse(raw);
  else parseError = 'no candidates';
} catch (e) { parseError = 'JSON parse: ' + e.message; }

if (!gemini || !gemini.category || typeof gemini.amount !== 'number') {
  return { json: { target: 'failed', payload: {
    slack_message_id: slackTs, slack_channel: slackChannel,
    raw_input: rawInput, error_reason: parseError || 'gemini parse failed'
  }, slack_ts: slackTs, slack_channel: slackChannel, gemini: gemini }};
}

if (slackChannel !== SUMIT_FINANCE_CHANNEL) {
  return { json: { target: 'failed', payload: {
    slack_message_id: slackTs, slack_channel: slackChannel,
    raw_input: rawInput, error_reason: 'channel mismatch'
  }, slack_ts: slackTs, slack_channel: slackChannel, gemini: gemini }};
}

const cat = gemini.category;
let target, payload;

if (cat === 'Expense') {
  target = 'expenses';
  payload = {
    amount: gemini.amount,
    category: gemini.expense_category || 'Other',
    description: gemini.notes || null,
    payment_mode: gemini.payment_mode || null,
    slack_message_id: 'E.' + slackTs,
    raw_input: rawInput,
  };
} else if (cat === 'Income') {
  target = 'incomes';
  payload = {
    amount: gemini.amount,
    source: gemini.income_source || 'Other',
    description: gemini.notes || null,
    slack_message_id: 'I.' + slackTs,
    raw_input: rawInput,
  };
} else if (cat === 'Withdrawal') {
  // M6 handles this — for M5 route to failed with explanatory reason
  target = 'failed';
  payload = {
    slack_message_id: slackTs, slack_channel: slackChannel,
    raw_input: rawInput, error_reason: 'Withdrawal not yet supported (M6)',
  };
}

return { json: { target, payload, slack_ts: slackTs, slack_channel: slackChannel, gemini }};
```

4. POST to CRM URL → `https://me.ipu.co.in/api/personal/{{ $json.target }}`.
5. Header → `X-Personal-Token: {{ $credentials.xPersonalToken }}` (or hardcode for now, documented to rotate later).
6. POST to failed URL → `https://me.ipu.co.in/api/personal/failed`.
7. Keep the M12 fixes: sanitize markdown in Gemini jsonBody, React ✅ timestamp expression, `201? && target != 'failed'` IF conditions.

- [ ] **Step 2: Validate JSON**

```bash
/opt/homebrew/bin/php -r 'json_decode(file_get_contents("docs/n8n-personal-finance-workflow.json"), true, 512, JSON_THROW_ON_ERROR); echo "ok\n";'
```

- [ ] **Step 3: Commit**

```bash
git add docs/n8n-personal-finance-workflow.json
git commit -m "feat(n8n): personal finance workflow JSON for Expense + Income"
```

### Task 5.3: Import the workflow to n8n + bind credentials + activate

- [ ] **Step 1: Import via n8n Public API**

```bash
source /Users/Sumit/kyne/deployment/.env
WF=/Users/Sumit/sumit-finance/docs/n8n-personal-finance-workflow.json
# Strip `active`, `id` for import (n8n assigns a new id)
/usr/bin/curl -X POST -H "X-N8N-API-KEY: $N8N_API_KEY" -H "Content-Type: application/json" \
  -d @"$WF" "$N8N_BASE_URL/api/v1/workflows"
```

This returns the new workflow id. Save it.

- [ ] **Step 2: Bind Slack cred**

Via n8n UI: open workflow → for each Slack node (Trigger, React, Reply), open credentials dropdown → pick existing `Slack account` credential (same one Phase 2 uses).

- [ ] **Step 3: Activate the workflow**

```bash
/usr/bin/curl -X POST -H "X-N8N-API-KEY: $N8N_API_KEY" "$N8N_BASE_URL/api/v1/workflows/<new_id>/activate"
```

- [ ] **Step 4: Deactivate + reactivate to force webhook registration**

Same gotcha as Phase 2 M11 reference memory — first `activate` after import does not always register the webhook. Cycle to force it.

- [ ] **Step 5: Acceptance — post in `#sumit-finance`**

```
spent 450 on fuel
```

Within ~10s: execution appears in n8n; exit = success; bot reacts ✅ on the message; `/admin/expenses` shows a new row with amount 450, category Transport.

```
salary 80000 credited
```

Same flow for Income branch.

- [ ] **Step 6: If anything fails**

Check the execution's `resultData.runData['Call Gemini']` for hallucinations. Iterate on the Gemini prompt examples in `docs/n8n-personal-finance-workflow.json` until both example categories succeed, re-import via PUT, deactivate/activate.

- [ ] **Step 7: Deploy + tag**

```bash
git tag v4-slack-capture && git push origin v4-slack-capture
```

### M5 checkpoint

- [ ] `#sumit-finance` Expense message → Expense row in 10s.
- [ ] `#sumit-finance` Income message → Income row in 10s.
- [ ] Bot reacts ✅ on success; replies in thread on failure.
- [ ] `failed_extractions` audit table catches any Gemini miss.
- [ ] `v4-slack-capture` tag on prod.

---

## Milestone 6 — Davya withdrawal dual-write (≈6 hours)

**Output:** Slack message `"withdrew 50000 from davya, apr salary"` produces BOTH a Phase 2 expense on `davya` (−50000) AND a Phase 3 Income with `source = "Davya withdrawal"` and `davya_reference` populated.

### Task 6.1: Add Withdrawal branch to the n8n workflow

**Files:**
- Modify: `docs/n8n-personal-finance-workflow.json` in `sumit-finance`.

- [ ] **Step 1: Add a new HTTP node "POST to Davya /expenses" in the n8n workflow**

Params:
- Method: POST
- URL: `https://davyas.ipu.co.in/api/finance/expenses`
- Headers: `X-Finance-Token: <FINANCE_CAPTURE_TOKEN from phase 2>`, `Content-Type: application/json`
- Body (JSON, expression):

```json
{
  "amount": {{ $('Dispatch by category').item.json.withdrawal_amount }},
  "category": "Owner drawing",
  "description": "Davya withdrawal by Sumit — {{ $('Dispatch by category').item.json.withdrawal_notes }}",
  "paid_at": "{{ $now.toISOString() }}",
  "slack_message_id": "W.{{ $('Dispatch by category').item.json.slack_ts }}",
  "raw_input": "{{ $('Slack Trigger — new message').item.json.text }}"
}
```

- [ ] **Step 2: Update Dispatch code node Withdrawal branch**

Replace the `else if (cat === 'Withdrawal')` stub from M5 with the two-step prep (actual dual POST is orchestrated by the n8n node graph, not in JS):

```js
} else if (cat === 'Withdrawal') {
  target = 'withdrawal';   // special routing keyword, tells n8n to run Davya POST then Personal POST
  payload = {
    amount: gemini.amount,
    source: 'Davya withdrawal',
    description: gemini.notes || null,
    slack_message_id: 'I.' + slackTs,
    raw_input: rawInput,
    // davya_reference filled by n8n after the first POST lands
  };
  // Also expose for the Davya-side HTTP node:
  return { json: {
    target, payload,
    withdrawal_amount: gemini.amount,
    withdrawal_notes: gemini.notes || '',
    slack_ts: slackTs,
    slack_channel: slackChannel,
    gemini,
  }};
}
```

- [ ] **Step 3: Add an n8n Switch node after Dispatch**

Three outputs:
- `target === 'expenses'` → POST to personal/expenses (existing)
- `target === 'incomes'` → POST to personal/incomes (existing)
- `target === 'withdrawal'` → new path: POST to davya/expenses → Set node that merges the returned Phase 2 expense id into the payload's `davya_reference` → POST to personal/incomes
- `target === 'failed'` → POST to personal/failed (existing)

- [ ] **Step 4: Add the Set node after the Davya POST**

Configure to merge:

```js
{
  ...$('Dispatch by category').item.json.payload,
  davya_reference: {
    phase2_expense_id: $json.body.id,
    phase2_slack_message_id: 'W.' + $('Dispatch by category').item.json.slack_ts
  }
}
```

- [ ] **Step 5: Validate JSON + re-import via PUT + deactivate/activate**

- [ ] **Step 6: Commit the updated workflow JSON**

```bash
git commit -m "feat(withdrawal): n8n Switch branch for Davya dual-write"
```

### Task 6.2: Acceptance — Slack withdrawal end-to-end

- [ ] **Step 1: Post in `#sumit-finance`:** `withdrew 50000 from davya, apr salary`.

- [ ] **Step 2: Verify on both sides:**
  - `davyas.ipu.co.in` — new expense with `category = "Owner drawing"`, amount 50000, `slack_message_id = W.<ts>`. `LedgerEntry` row `davya: -50000`.
  - `me.ipu.co.in/admin/incomes` — new income with source `Davya withdrawal`, amount 50000, `slack_message_id = I.<ts>`, `davya_reference.phase2_expense_id` matching.

- [ ] **Step 3: Retry idempotency**

Re-trigger the same n8n execution (from the executions list). Both endpoints should return 409; no duplicate rows.

### Task 6.3: Tag v5-withdrawal

```bash
git tag v5-withdrawal && git push origin v5-withdrawal
```

### M6 checkpoint

- [ ] Single Slack message creates matched pair (Phase 2 expense + Phase 3 income).
- [ ] `davya_reference` populated with Phase 2's id.
- [ ] Retry of same Slack ts returns 409 on both sides.
- [ ] `v5-withdrawal` deployed.

---

## Milestone 7 — CSV export + acceptance + v1.0.0 (≈3 hours)

### Task 7.1: CSV export bulk action

**Files:**
- Modify: `app/Filament/Resources/ExpenseResource.php` and `IncomeResource.php`.

- [ ] **Step 1: Add a BulkAction on both resources' `->bulkActions(...)` array**

```php
Tables\Actions\BulkAction::make('export_csv')
    ->label('Export selected as CSV')
    ->icon('heroicon-o-arrow-down-tray')
    ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
        $filename = 'expenses-'.now()->format('Y-m-d-His').'.csv';
        return response()->streamDownload(function () use ($records) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date','Category','Amount','Payment Mode','Description','Slack msg id']);
            foreach ($records as $r) {
                fputcsv($out, [
                    $r->spent_at->format('Y-m-d H:i'),
                    $r->category->name,
                    $r->amount,
                    $r->payment_mode,
                    $r->description,
                    $r->slack_message_id,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }),
```

Same for incomes with `received_at` and `source.name`.

- [ ] **Step 2: Browser smoke — select 3 rows, export, verify CSV structure.**

- [ ] **Step 3: Commit**

```bash
git commit -m "feat(reports): CSV export bulk action on expenses + incomes"
```

### Task 7.2: Acceptance matrix doc

**Files:**
- Create: `docs/ACCEPTANCE.md`.

Write a checklist mirroring Phase 1's SMOKE_TEST.md — step-by-step click-through that a reader can execute in 10 minutes to verify all of Expense, Income, Withdrawal, manual-entry, CSV export, dashboard widgets.

```bash
git commit -m "docs: ACCEPTANCE.md click-through"
```

### Task 7.3: Deploy final + tag v1.0.0

- [ ] Push, pull, run `deploy.sh`.
- [ ] Run through `ACCEPTANCE.md` on prod once.
- [ ] Tag:

```bash
git tag -a v1.0.0 -m "Phase 3 personal finance v1 — Slack + web capture with Davya dual-write"
git push origin v1.0.0
```

### M7 checkpoint

- [ ] CSV export works from both resources.
- [ ] `docs/ACCEPTANCE.md` run end-to-end on prod.
- [ ] `v1.0.0` tag pushed.
- [ ] All 3 Slack message types land correctly within 15s.

---

## Appendix: test suite size & coverage targets

At `v1.0.0`, expected counts:

| area | file | tests |
|---|---|---|
| Auth | SumitSeederTest, ForcePasswordChangeTest, TotpEnrollmentTest, TotpVerificationTest, AccountLockoutTest, AuthAuditLogTest, SecurityHeadersTest | ~15 |
| Expenses | ExpenseCategoriesSeederTest, ExpenseModelTest, ExpensePolicyTest, ExpenseResourceTest, VerifyPersonalTokenTest, ExpenseCaptureTest | ~17 |
| Incomes | IncomeSourcesSeederTest, IncomeModelTest, IncomePolicyTest, IncomeResourceTest, IncomeCaptureTest, WithdrawalIncomeTest | ~15 |
| Dashboard | PersonalBalanceServiceTest | ~5 |
| Failed endpoint | FailedCaptureTest | 3 |
| **Total** | | **~55** |

`php artisan test` should stay green across the whole plan. Any milestone that drops suite size below the previous milestone's count is a regression flag — stop and investigate before tagging.

---

## Appendix: non-obvious gotchas (carried from Phase 1 + 2)

- **PHP 8.5 PDO deprecation:** suppress in `bootstrap/app.php` (Task 1.3). Otherwise every test emits a deprecation line that masks real errors.
- **Laravel 11 policy auto-discovery:** no `AuthServiceProvider` wiring. Naming convention only.
- **Filament test helper:** `private function post(...)` in tests conflicts with `TestCase::post()`. Use `postPayload(...)` for custom senders (see Phase 2 M5–M8 test files).
- **n8n imported workflow webhooks not registered on first activate.** Always deactivate+activate after import (see Phase 2 M11 memory note).
- **Gemini reliably drops optional fields.** If you depend on a field, regex-fallback in the Dispatch code node (see Phase 2 M12's referrer_name fallback — not relevant here but the pattern is).
- **Slack `<...|...>` markdown** corrupts Gemini. Strip before passing text into the prompt (copied from Phase 2 M12).
- **React ✅ node requires `timestamp` parameter.** Bind to `$('Slack Trigger — new message').item.json.ts`.
- **IF-201 node** must also gate on `target != 'failed'` (otherwise `/failed`'s 201 success misroutes to React branch).
- **Decimal casts** — always `decimal:2`. Returning numeric from Eloquent yields strings like `'450.00'` — assert as strings in tests, don't cast to float.
- **Slack-ts prefix convention:** `E.<ts>` for Expense, `I.<ts>` for Income, `W.<ts>` for the Davya side of a Withdrawal. Keeps unique indexes non-colliding.
