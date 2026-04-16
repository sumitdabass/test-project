# Davya CRM — Phase 1 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a Laravel + Filament CRM at `davyas.ipu.co.in` that replaces Zoho for Davya consultancy — 7 users, role-based access, 10-stage NewAdmission pipeline, student records with Zoho-style fields, per-round counselling history, payments with signed totals, Kanban board with live aggregates, dashboard alerts, encrypted `ipu_password`, daily MySQL backup to Google Drive.

**Architecture:** Monolithic Laravel 11 app using Filament v3 as the admin panel, Spatie for permissions + activity log, MySQL (`ipuc_davyafin` on IPU Hostinger) as data store, Livewire for reactive UI, Tailwind for styling. Deployed to a Hostinger subdomain via SSH `git pull` + composer + artisan.

**Tech Stack:** PHP 8.2+, Laravel 11, Filament v3, `spatie/laravel-permission`, `spatie/laravel-activitylog`, `masbug/flysystem-google-drive-ext`, MySQL 8, Tailwind, Livewire.

**Reference spec:** `docs/superpowers/specs/2026-04-16-davya-crm-phase1-design.md`.

**Estimated effort:** ~61–80 hours across 9 milestones (M1–M9). Elapsed time depends on weekly capacity. **Key change from the original plan:** production deploys happen at every milestone, not only at the end. Hostinger-specific issues (PHP version, SSH, SSL, mysqldump path, Drive SDK) surface on day one, not in week five. Each milestone ends with a green commit + manual-test checkpoint + re-deploy + version tag.

**Code location:** `/Users/Sumit/davya-crm/` (new dir, new private GitHub repo `davya-crm`). **This plan doc lives in the IPU repo for history; the app code is a separate repo.**

**Revision history:**
- 2026-04-16 (v1): original plan — build all 9 milestones locally, deploy to prod only at the end.
- 2026-04-16 (v2): revised after design review. Changes: (1) deploy to Hostinger in M1; re-deploy every milestone. (2) Google Drive moved from M8 → M4 (no dual-disk migration). (3) Spec gaps closed — session timeouts, force password reset on first login, Show-Password action logging, DB user privilege narrowing, backup retention impl, freelancer policy test, `StudentFactory`. (4) Kanban plugin spike moved to M2 as sidebar so M6 isn't blocked by plugin choice. (5) M8 repurposed to backup retention + DB privilege narrowing. (6) M9 reduced to smoke-test matrix + release tag.

---

## Pre-flight checklist (do these before Task 1)

Confirm on your side; plan assumes all are done:

- [ ] PHP 8.2+ installed locally (`php --version`)
- [ ] Composer installed (`composer --version`)
- [ ] Node.js 20+ installed (`node --version`)
- [ ] MySQL client installed locally (`mysql --version`)
- [ ] IPU Hostinger cPanel access
- [ ] DB `ipuc_davyafin` created, user `ipuc_davyapp` with **privileges scoped to `ipuc_davyafin.*`** (not `*.*`; M8 re-verifies this), password in password manager
- [ ] cPanel API token rotated (was leaked earlier)
- [ ] Empty private GitHub repo `davya-crm` created at github.com/sumitdabass/davya-crm
- [ ] SSH key added to Hostinger for deployments (cPanel → SSH Access → Manage SSH Keys)
- [ ] Google Cloud project with Drive API enabled; OAuth 2.0 Client ID + refresh token generated for Drive access (needed by **M4** now, not M8)

---

## Milestone 1 — Scaffold, local dev, and first production deploy (~6–8 hours)

**Output:** Fresh Laravel + Filament app runs locally at `http://localhost:8000`, **AND** an empty Filament login is live at `https://davyas.ipu.co.in` over SSL. `DEPLOY.md` committed with re-deploy + rollback recipe. Tag `v0-scaffold` pushed.

### Task 1.1: Create Laravel project and initial commit

**Files:**
- Create: `/Users/Sumit/davya-crm/` (entire directory tree via `laravel new`)

- [ ] **Step 1: Run Laravel installer**

```bash
cd /Users/Sumit
composer create-project laravel/laravel davya-crm "^11.0"
cd davya-crm
```

Expected: Laravel 11 project created, `artisan` file present.

- [ ] **Step 2: Verify installer output**

Run: `php artisan --version`
Expected: `Laravel Framework 11.x.x`

- [ ] **Step 3: Initial commit**

```bash
git remote add origin git@github.com:sumitdabass/davya-crm.git
git add .
git commit -m "chore: initial Laravel 11 scaffold"
git branch -M main
git push -u origin main
```

### Task 1.2: Configure local `.env` for MySQL

**Files:**
- Modify: `/Users/Sumit/davya-crm/.env`

- [ ] **Step 1: Create local MySQL DB for dev**

```bash
mysql -u root -p -e "CREATE DATABASE davya_crm_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p -e "CREATE USER 'davya_dev'@'localhost' IDENTIFIED BY 'devpass123'; GRANT ALL ON davya_crm_dev.* TO 'davya_dev'@'localhost'; FLUSH PRIVILEGES;"
```

- [ ] **Step 2: Update `.env` with local DB creds**

Edit `.env`:
```
APP_NAME="Davya CRM"
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Kolkata

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=davya_crm_dev
DB_USERNAME=davya_dev
DB_PASSWORD=devpass123
```

Also update `config/app.php` — `'timezone' => 'Asia/Kolkata'`.

- [ ] **Step 3: Verify DB connection**

Run: `php artisan migrate:status`
Expected: "Migration table not found" (no error about connection).

- [ ] **Step 4: Run default migrations**

Run: `php artisan migrate`
Expected: Default Laravel tables created (users, migrations, etc.).

- [ ] **Step 5: Commit**

```bash
git add .env.example config/app.php
git commit -m "chore: configure local MySQL + IST timezone"
```

(Note: `.env` itself is gitignored — update `.env.example` to mirror keys.)

### Task 1.3: Install Filament v3

**Files:**
- Modify: `/Users/Sumit/davya-crm/composer.json`
- Create: `app/Providers/Filament/AdminPanelProvider.php`

- [ ] **Step 1: Install Filament**

```bash
composer require filament/filament:"^3.0" -W
php artisan filament:install --panels
```

When prompted for panel ID: `admin`.

- [ ] **Step 2: Create an admin user for local testing (throwaway)**

```bash
php artisan make:filament-user
```

Name=Sumit, Email=sumit@davya.local, Password=LocalDev123. This user is overwritten by M2 seeding.

- [ ] **Step 3: Start dev server and verify login**

Run: `php artisan serve` + `npm install && npm run dev`
Visit: `http://localhost:8000/admin`
Expected: Filament login page loads; sign-in shows empty admin dashboard.

- [ ] **Step 4: Commit**

```bash
git add .
git commit -m "feat: install Filament v3 admin panel"
```

### Task 1.4: Install supporting Spatie packages

**Files:**
- Modify: `composer.json`
- Create: Migration files via `spatie` publish commands

- [ ] **Step 1: Install Spatie permission and activity log**

```bash
composer require spatie/laravel-permission
composer require spatie/laravel-activitylog
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
php artisan migrate
```

Expected: `roles`, `permissions`, `role_has_permissions`, `model_has_roles`, `model_has_permissions`, `activity_log` tables created.

- [ ] **Step 2: Add HasRoles + LogsActivity to User model**

Edit `app/Models/User.php`:
```php
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'email'])->logOnlyDirty();
    }
}
```

- [ ] **Step 3: Commit**

```bash
git add .
git commit -m "feat: install Spatie permission + activity log"
git push
```

### Task 1.5: First deploy to `davyas.ipu.co.in`

**Files:**
- Hostinger: subdomain + cloned repo at `/home/<user>/davyas_public/`
- Local: create `DEPLOY.md` with the re-deploy procedure

- [ ] **Step 1: Create subdomain in cPanel**

cPanel → Subdomains → create `davyas` on `ipu.co.in`. Document root: `/home/<user>/davyas_public/public`.

- [ ] **Step 2: Enable AutoSSL (Let's Encrypt)**

cPanel → SSL/TLS Status → Run AutoSSL. Wait for cert (<5 min typically).

- [ ] **Step 3: Verify DNS + SSL**

```bash
dig davyas.ipu.co.in                 # resolves to Hostinger IP
curl -I https://davyas.ipu.co.in     # HTTP/2 response with valid cert
```

- [ ] **Step 4: SSH, clone, and install**

```bash
ssh <user>@ipu.co.in
cd /home/<user>
git clone git@github.com:sumitdabass/davya-crm.git davyas_public
cd davyas_public
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

- [ ] **Step 5: Fill production `.env` (skip Drive keys for now)**

```
APP_NAME="Davya CRM"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://davyas.ipu.co.in
APP_TIMEZONE=Asia/Kolkata

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=ipuc_davyafin
DB_USERNAME=ipuc_davyapp
DB_PASSWORD=<from-password-manager>
```

Drive keys land in `.env` during M4.

- [ ] **Step 6: Verify PHP 8.2+ and mysqldump path**

```bash
php --version                 # must be 8.2+; if not: cPanel → MultiPHP Manager
which mysqldump               # note path; needed in M8
```

- [ ] **Step 7: Migrate + cache**

```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
chmod -R 775 storage bootstrap/cache
```

- [ ] **Step 8: Verify `/admin` loads over HTTPS**

Visit `https://davyas.ipu.co.in/admin`. Expected: Filament login page renders. No admin user yet — that's M2.

- [ ] **Step 9: Write and commit `DEPLOY.md`**

Create `DEPLOY.md` in repo root:

```
# Deploy procedure (run from your laptop)
git push origin main

# Then on Hostinger:
ssh <user>@ipu.co.in
cd /home/<user>/davyas_public
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache
```

```bash
git add DEPLOY.md
git commit -m "docs(deploy): initial deploy playbook"
git push
```

### Task 1.6: Rollback recipe + v0 tag

- [ ] **Step 1: Tag the scaffold**

```bash
git tag v0-scaffold
git push origin v0-scaffold
```

- [ ] **Step 2: Append rollback section to `DEPLOY.md`**

```
## Rollback
1. SSH to Hostinger.
2. `cd /home/<user>/davyas_public`
3. `git fetch --tags`
4. `git reset --hard <last-good-tag>`        # e.g. v0-scaffold, v1-users, v2-students
5. `composer install --no-dev --optimize-autoloader`
6. `php artisan migrate:rollback --step=N --force`   # only if schema regressed
7. `php artisan config:cache && php artisan route:cache`

## Known-good tags
- v0-scaffold — M1 complete (empty Filament login)
- v1-users    — M2 complete (7 users + permissions + password reset + sessions)
- v2-students — M3 complete (students CRUD + policy + Show-Password logging)
- v3-payments — M4 complete (payments + Drive)
- v4-rounds   — M5 complete (round history + validators)
- v5-kanban   — M6 complete
- v6-dashboard— M7 complete
- v7-backup   — M8 complete
- v1.0.0      — M9 complete (release)
```

- [ ] **Step 3: Commit**

```bash
git add DEPLOY.md
git commit -m "docs(deploy): rollback recipe + tag ladder"
git push
```

**M1 checkpoint:** Local Filament panel loads; **`https://davyas.ipu.co.in/admin` loads over SSL in production**; Spatie tables migrated locally and in `ipuc_davyafin`; `DEPLOY.md` committed with re-deploy + rollback procedure; `v0-scaffold` tag pushed. ✅

---

## Milestone 2 — Users, permissions, auth hardening + Kanban spike (~8–10 hours)

**Output:** All 4 roles defined with policies, 7 seed users created with forced-password-reset flag, `UserResource` in Filament for Admin-only user management, session timeouts (2h idle / 7d absolute) enforced, Kanban plugin choice locked in `docs/DECISIONS.md`, re-deployed to prod.

### Task 2.1: Extend users migration

**Files:**
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_add_team_fields_to_users.php`

- [ ] **Step 1: Create migration**

```bash
php artisan make:migration add_team_fields_to_users
```

- [ ] **Step 2: Write the migration**

```php
// up()
Schema::table('users', function (Blueprint $table) {
    $table->foreignId('team_head_id')->nullable()->after('email')->constrained('users')->nullOnDelete();
    $table->boolean('is_freelancer')->default(false)->after('team_head_id');
    $table->boolean('is_active')->default(true)->after('is_freelancer');
    $table->boolean('must_change_password')->default(false)->after('password');
    $table->index('team_head_id');
});

// down()
Schema::table('users', function (Blueprint $table) {
    $table->dropForeign(['team_head_id']);
    $table->dropColumn(['team_head_id', 'is_freelancer', 'is_active', 'must_change_password']);
});
```

- [ ] **Step 3: Run migration**

Run: `php artisan migrate`
Expected: Columns added without error.

- [ ] **Step 4: Commit**

```bash
git add database/migrations/
git commit -m "feat(users): add team_head_id, is_freelancer, is_active, must_change_password"
```

### Task 2.2: Write failing tests for role gates (incl. freelancer parity)

**Files:**
- Create: `tests/Feature/RolePermissionTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_four_roles_exist_after_seeding(): void
    {
        $this->seed(\Database\Seeders\RolesSeeder::class);
        foreach (['admin','head','member','freelancer'] as $role) {
            $this->assertDatabaseHas('roles', ['name' => $role]);
        }
    }

    public function test_sumit_has_admin_and_head_roles_after_seeding(): void
    {
        $this->seed();
        $sumit = User::where('email', 'sumit@davya.local')->first();
        $this->assertTrue($sumit->hasRole('admin'));
        $this->assertTrue($sumit->hasRole('head'));
    }

    public function test_nisha_head_is_nikhil(): void
    {
        $this->seed();
        $nisha = User::where('name', 'Nisha')->first();
        $nikhil = User::where('name', 'Nikhil')->first();
        $this->assertEquals($nikhil->id, $nisha->team_head_id);
    }

    public function test_kapil_is_freelancer_under_sumit_with_no_sub_team(): void
    {
        $this->seed();
        $kapil = User::where('name', 'Kapil')->first();
        $sumit = User::where('name', 'Sumit')->first();
        $this->assertTrue($kapil->is_freelancer);
        $this->assertEquals($sumit->id, $kapil->team_head_id);
        $this->assertTrue($kapil->hasRole('freelancer'));
        $this->assertSame(0, User::where('team_head_id', $kapil->id)->count(), 'freelancer must have no sub-team');
    }
}
```

- [ ] **Step 2: Run to verify failure**

Run: `php artisan test --filter RolePermissionTest`
Expected: FAIL — seeders don't exist yet.

### Task 2.3: Create roles seeder

**Files:**
- Create: `database/seeders/RolesSeeder.php`

- [ ] **Step 1: Create the seeder**

```bash
php artisan make:seeder RolesSeeder
```

- [ ] **Step 2: Write the seeder**

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'head', 'member', 'freelancer'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}
```

- [ ] **Step 3: Run the seeder-only test**

Run: `php artisan test --filter test_four_roles_exist_after_seeding`
Expected: PASS.

### Task 2.4: Create users seeder for 7 team members (with `must_change_password = true`)

**Files:**
- Create: `database/seeders/UsersSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`

- [ ] **Step 1: Create UsersSeeder**

```bash
php artisan make:seeder UsersSeeder
```

- [ ] **Step 2: Write the seeder**

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $randomPw = fn () => Hash::make(bin2hex(random_bytes(8)));  // user resets on first login

        // Heads first (no team_head_id)
        $sumit = User::updateOrCreate(
            ['email' => 'sumit@davya.local'],
            ['name' => 'Sumit', 'password' => $randomPw(),
             'is_freelancer' => false, 'is_active' => true, 'must_change_password' => true]
        );
        $sonam = User::updateOrCreate(
            ['email' => 'sonam@davya.local'],
            ['name' => 'Sonam', 'password' => $randomPw(),
             'is_freelancer' => false, 'is_active' => true, 'must_change_password' => true]
        );
        $nikhil = User::updateOrCreate(
            ['email' => 'nikhil@davya.local'],
            ['name' => 'Nikhil', 'password' => $randomPw(),
             'is_freelancer' => false, 'is_active' => true, 'must_change_password' => true]
        );

        // Members under heads
        User::updateOrCreate(
            ['email' => 'nisha@davya.local'],
            ['name' => 'Nisha', 'password' => $randomPw(),
             'team_head_id' => $nikhil->id, 'is_freelancer' => false, 'is_active' => true, 'must_change_password' => true]
        );
        User::updateOrCreate(
            ['email' => 'poonam@davya.local'],
            ['name' => 'Poonam', 'password' => $randomPw(),
             'team_head_id' => $sonam->id, 'is_freelancer' => false, 'is_active' => true, 'must_change_password' => true]
        );
        User::updateOrCreate(
            ['email' => 'neetu@davya.local'],
            ['name' => 'Neetu', 'password' => $randomPw(),
             'team_head_id' => $sonam->id, 'is_freelancer' => false, 'is_active' => true, 'must_change_password' => true]
        );

        // Freelancer under Sumit
        User::updateOrCreate(
            ['email' => 'kapil@davya.local'],
            ['name' => 'Kapil', 'password' => $randomPw(),
             'team_head_id' => $sumit->id, 'is_freelancer' => true, 'is_active' => true, 'must_change_password' => true]
        );

        // Assign roles
        $sumit->syncRoles(['admin', 'head']);
        $sonam->syncRoles(['head']);
        $nikhil->syncRoles(['head']);
        User::whereIn('email', ['nisha@davya.local', 'poonam@davya.local', 'neetu@davya.local'])
            ->get()->each(fn ($u) => $u->syncRoles(['member']));
        User::where('email', 'kapil@davya.local')->first()->syncRoles(['freelancer']);
    }
}
```

- [ ] **Step 3: Wire seeders into DatabaseSeeder**

```php
public function run(): void
{
    $this->call([
        RolesSeeder::class,
        UsersSeeder::class,
    ]);
}
```

- [ ] **Step 4: Run all tests**

Run: `php artisan test --filter RolePermissionTest`
Expected: PASS (all 4 tests).

- [ ] **Step 5: Commit**

```bash
git add .
git commit -m "feat(users): seed 4 roles + 7 team members; random pw + must_change_password"
```

### Task 2.5: `FilamentUser` gate for non-admin restrictions

**Files:**
- Modify: `app/Models/User.php`

- [ ] **Step 1: Write failing test**

Add to `RolePermissionTest.php`:
```php
public function test_only_active_users_can_access_filament(): void
{
    $this->seed();
    $sumit = User::where('email', 'sumit@davya.local')->first();
    $this->assertTrue($sumit->canAccessPanel(\Filament\Facades\Filament::getPanel('admin')));
    $sumit->update(['is_active' => false]);
    $this->assertFalse($sumit->fresh()->canAccessPanel(\Filament\Facades\Filament::getPanel('admin')));
}
```

- [ ] **Step 2: Run to verify failure**

Run: `php artisan test --filter test_only_active_users_can_access_filament`
Expected: FAIL.

- [ ] **Step 3: Implement canAccessPanel**

```php
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    // ...
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }
}
```

- [ ] **Step 4: Run + commit**

```bash
php artisan test --filter test_only_active_users_can_access_filament
git add app/Models/User.php tests/Feature/RolePermissionTest.php
git commit -m "feat(users): gate Filament access on is_active"
```

### Task 2.6: `UserResource` in Filament (Admin-only)

**Files:**
- Create: `app/Filament/Resources/UserResource.php` (+ pages)

- [ ] **Step 1: Generate**

```bash
php artisan make:filament-resource User --generate
```

- [ ] **Step 2: Restrict to admin**

```php
public static function canViewAny(): bool { return auth()->user()?->hasRole('admin'); }
public static function canCreate(): bool { return auth()->user()?->hasRole('admin'); }
public static function canEdit($record): bool { return auth()->user()?->hasRole('admin'); }
public static function canDelete($record): bool { return auth()->user()?->hasRole('admin') && auth()->id() !== $record->id; }
```

Form: `name`, `email`, `password` (hashed on save), `team_head_id` (Select of heads), `is_freelancer`, `is_active`, `roles` (Spatie select).

- [ ] **Step 3: Manual verify + commit**

```bash
php artisan serve
# /admin/users: Sumit sees it; Nikhil → 403
git add .
git commit -m "feat(users): UserResource admin-only CRUD"
```

### Task 2.7: Session timeout (2h idle + 7d absolute)

**Files:**
- Edit: `.env`, `.env.example`
- Create: `app/Http/Middleware/AbsoluteSessionTimeout.php`
- Modify: `app/Http/Kernel.php`

- [ ] **Step 1: Idle timeout via `.env`**

```
SESSION_LIFETIME=120
SESSION_EXPIRE_ON_CLOSE=false
```

Also add to `.env.example`.

- [ ] **Step 2: Absolute cap middleware**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsoluteSessionTimeout
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) return $next($request);

        $loginAt = $request->session()->get('_login_at');
        if (! $loginAt) {
            $request->session()->put('_login_at', now()->timestamp);
            return $next($request);
        }
        if (now()->timestamp - $loginAt > 7 * 24 * 60 * 60) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('filament.admin.auth.login')
                ->with('status', 'Session expired (7-day max). Please log in again.');
        }
        return $next($request);
    }
}
```

Register in `app/Http/Kernel.php` under the `web` middleware group.

- [ ] **Step 3: Test**

```php
// tests/Feature/SessionTimeoutTest.php
public function test_user_is_logged_out_after_7_days(): void
{
    $this->seed();
    $sumit = User::where('email', 'sumit@davya.local')->first();
    $this->actingAs($sumit);
    session(['_login_at' => now()->subDays(8)->timestamp]);
    $this->get('/admin')->assertRedirect();
}
```

- [ ] **Step 4: Run + commit**

```bash
php artisan test --filter SessionTimeoutTest
git add .
git commit -m "feat(auth): 2h idle + 7d absolute session timeout"
```

### Task 2.8: Force password change on first login

**Files:**
- Create: `app/Http/Middleware/RequirePasswordChange.php`
- Create: `app/Filament/Pages/ChangePassword.php`
- Modify: `app/Http/Kernel.php`

- [ ] **Step 1: Middleware**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RequirePasswordChange
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()
            && Auth::user()->must_change_password
            && ! $request->routeIs('filament.admin.pages.change-password', 'filament.admin.auth.logout')) {
            return redirect()->route('filament.admin.pages.change-password');
        }
        return $next($request);
    }
}
```

Register in `web` group after `StartSession`.

- [ ] **Step 2: Filament page `ChangePassword`**

Custom Filament page (`app/Filament/Pages/ChangePassword.php`) with current / new / confirm. On submit:
1. Verify current password via `Hash::check`.
2. Update `password` + `must_change_password = false`.
3. Log activity `'password_changed'`.
4. Redirect to dashboard.

- [ ] **Step 3: Test**

```php
public function test_freshly_seeded_user_must_change_password(): void
{
    $this->seed();
    $sumit = User::where('email', 'sumit@davya.local')->first();
    $this->actingAs($sumit);
    $this->get('/admin')->assertRedirect(route('filament.admin.pages.change-password'));
}
```

- [ ] **Step 4: Run + commit**

```bash
php artisan test --filter test_freshly_seeded_user_must_change_password
git add .
git commit -m "feat(auth): force password change on first login"
```

### Task 2.9: Kanban plugin spike (sidebar, 1–2 h)

**Goal:** settle plugin-vs-custom now so M6 isn't gated on evaluation.

- [ ] **Step 1: Evaluate plugins**

Visit https://filamentphp.com/plugins?search=kanban. Shortlist: `flowforge/filament-kanban`, `relaticle/filament-kanban-board`. Criteria:
- Last commit date (<3 months = actively maintained)
- Supports drag-drop + customizable card content
- Compatible with Filament v3

- [ ] **Step 2: Install the chosen plugin in a throwaway branch**

```bash
git checkout -b spike-kanban
composer require <chosen-plugin>
# render empty kanban with stage enum; confirm drag-drop works
```

- [ ] **Step 3: Record decision**

Create `docs/DECISIONS.md`:

```
# 2026-04-XX — Kanban plugin choice
Decision: <plugin-name>  (or: build-custom)
Reason: last commit YYYY-MM-DD; drag-drop works; card content customizable via Blade slot.
Rejected: <other-plugin> because <reason>.
```

If plugin fits, merge spike → main. If not, discard branch (fall back to custom in M6).

- [ ] **Step 4: Re-deploy + tag**

```bash
git push
# SSH → follow DEPLOY.md
git tag v1-users
git push origin v1-users
```

**M2 checkpoint:** 7 users exist with all 4 roles (admin/head/member/freelancer); Sumit can manage users, non-admin cannot; seeded users forced to change password on first login; sessions time out at 2h idle / 7d absolute; Kanban plugin choice locked in `docs/DECISIONS.md`; re-deployed to `https://davyas.ipu.co.in`; `v1-users` tag pushed. ✅

---

## Milestone 3 — Students CRUD + policy + Show-Password logging (~12–14 hours)

**Output:** `students` table with ~30 fields, `Student` model + factory, policy enforcing owner / team / all visibility (admin / head / member / freelancer), member-transfer blocked, `StudentResource` in Filament with sectioned form and filtered list, Show-Password reveal logged to `activity_log`.

**Optional split into sub-sessions:** M3A = Task 3.1–3.2 (migration/model/factory), M3B = Task 3.3 (policy), M3C = Task 3.4–3.5 (resource + logging).

### Task 3.1: Students migration

**Files:**
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_create_students_table.php`

- [ ] **Step 1: Generate**

```bash
php artisan make:migration create_students_table
```

- [ ] **Step 2: Write migration**

```php
Schema::create('students', function (Blueprint $table) {
    $table->id();
    $table->string('phone', 15)->unique();
    $table->string('name', 120);
    $table->string('father_name', 120)->nullable();
    $table->string('phone_2', 15)->nullable();
    $table->foreignId('owner_id')->constrained('users');
    $table->foreignId('referrer_id')->constrained('users');
    $table->enum('stage', [
        'Lead Captured','Meeting Scheduled','Meeting Done','Onboarded',
        'University Registration','Counselling In Progress','Seat Allotted',
        'Full Payment Received','Admission Confirmed','Closed',
    ])->default('Lead Captured');
    $table->string('lead_source', 60);
    $table->enum('student_response', ['Ready','Not Interested','Needs Time'])->nullable();
    $table->string('exam_appeared', 40)->nullable();
    $table->string('twelfth_marks', 20)->nullable();
    $table->enum('category', ['Delhi','Outside'])->nullable();
    $table->string('course', 80)->nullable();
    $table->string('preference_r1', 120)->nullable();
    $table->string('preference_r2', 120)->nullable();
    $table->string('preference_r3', 120)->nullable();
    $table->decimal('deal_amount', 12, 2)->nullable();
    $table->enum('plan', ['Online','Offline','All'])->nullable();
    $table->boolean('is_ipu_registered')->nullable();
    $table->string('ipu_user_id', 60)->nullable();
    $table->text('ipu_password')->nullable();  // encrypted in model cast
    $table->string('current_round', 40)->nullable();
    $table->boolean('seat_fee_due')->default(false);
    $table->string('final_college', 120)->nullable();
    $table->string('final_course', 120)->nullable();
    $table->date('admission_date')->nullable();
    $table->dateTime('meeting_date')->nullable();
    $table->string('meeting_location', 120)->nullable();
    $table->boolean('address_sent')->nullable();
    $table->boolean('office_visit')->nullable();
    $table->enum('close_reason', ['Not Interested','Backed Out — Forfeit','Backed Out — Partial Refund','Completed','Other'])->nullable();
    $table->decimal('refund_amount', 12, 2)->nullable();
    $table->text('re_entry_reason')->nullable();
    $table->text('description')->nullable();
    $table->text('extra_notes')->nullable();
    $table->timestamps();

    $table->index(['owner_id', 'stage']);
    $table->index('stage');
});
```

- [ ] **Step 3: Run + commit**

```bash
php artisan migrate
git add database/migrations/
git commit -m "feat(students): create students table with 30 fields"
```

### Task 3.2: Student model + factory + encrypted-cast test

**Files:**
- Create: `app/Models/Student.php`, `database/factories/StudentFactory.php`
- Create: `tests/Feature/StudentModelTest.php`

- [ ] **Step 1: Generate model + factory**

```bash
php artisan make:model Student --factory
```

- [ ] **Step 2: Write the model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Student extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'ipu_password' => 'encrypted',
        'deal_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'is_ipu_registered' => 'boolean',
        'seat_fee_due' => 'boolean',
        'address_sent' => 'boolean',
        'office_visit' => 'boolean',
        'admission_date' => 'date',
        'meeting_date' => 'datetime',
    ];

    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'owner_id'); }
    public function referrer(): BelongsTo { return $this->belongsTo(User::class, 'referrer_id'); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function roundHistory(): HasMany { return $this->hasMany(RoundHistory::class); }

    public function getTotalReceivedAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function getPendingAmountAttribute(): float
    {
        return (float) ($this->deal_amount ?? 0) - $this->total_received;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }
}
```

- [ ] **Step 3: Write factory**

`database/factories/StudentFactory.php`:
```php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'phone' => fake()->unique()->numerify('9#########'),
            'name' => fake()->name(),
            'owner_id' => User::factory(),
            'referrer_id' => User::factory(),
            'lead_source' => 'Factory',
            'stage' => 'Lead Captured',
        ];
    }
}
```

- [ ] **Step 4: Write encrypted-cast test**

```php
// tests/Feature/StudentModelTest.php
public function test_ipu_password_is_encrypted_at_rest_but_decrypted_on_access(): void
{
    $this->seed();
    $sumit = User::where('email', 'sumit@davya.local')->first();
    $student = Student::create([
        'phone' => '9999999999', 'name' => 'Test Student',
        'owner_id' => $sumit->id, 'referrer_id' => $sumit->id,
        'lead_source' => 'Sumit', 'ipu_password' => 'secret-pw',
    ]);
    $rawValue = DB::table('students')->where('id', $student->id)->value('ipu_password');
    $this->assertNotEquals('secret-pw', $rawValue);
    $this->assertEquals('secret-pw', $student->fresh()->ipu_password);
}
```

- [ ] **Step 5: Run + commit**

```bash
php artisan test --filter StudentModelTest
git add .
git commit -m "feat(students): Student model + factory + encrypted ipu_password"
```

### Task 3.3: StudentPolicy with all 4 role tests (incl. member-transfer block)

**Files:**
- Create: `app/Policies/StudentPolicy.php`
- Modify: `app/Providers/AuthServiceProvider.php`
- Create: `tests/Feature/StudentPolicyTest.php`

- [ ] **Step 1: Write failing tests (6 tests covering all roles + transfer)**

```php
<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void { parent::setUp(); $this->seed(); }

    public function test_admin_can_view_any_student(): void
    {
        $sumit = User::where('email','sumit@davya.local')->first();
        $nikhil = User::where('email','nikhil@davya.local')->first();
        $s = Student::create(['phone'=>'9111111111','name'=>'S','owner_id'=>$nikhil->id,'referrer_id'=>$nikhil->id,'lead_source'=>'Nikhil']);
        $this->assertTrue($sumit->can('view', $s));
    }

    public function test_head_can_view_own_team_student(): void
    {
        $nikhil = User::where('email','nikhil@davya.local')->first();
        $nisha = User::where('email','nisha@davya.local')->first();
        $s = Student::create(['phone'=>'9222222222','name'=>'S','owner_id'=>$nisha->id,'referrer_id'=>$nisha->id,'lead_source'=>'Nisha']);
        $this->assertTrue($nikhil->can('view', $s));
    }

    public function test_head_cannot_view_other_teams_student(): void
    {
        $nikhil = User::where('email','nikhil@davya.local')->first();
        $poonam = User::where('email','poonam@davya.local')->first(); // Sonam's team
        $s = Student::create(['phone'=>'9333333333','name'=>'S','owner_id'=>$poonam->id,'referrer_id'=>$poonam->id,'lead_source'=>'Poonam']);
        $this->assertFalse($nikhil->can('view', $s));
    }

    public function test_member_can_only_view_own(): void
    {
        $nisha = User::where('email','nisha@davya.local')->first();
        $poonam = User::where('email','poonam@davya.local')->first();
        $own = Student::create(['phone'=>'9444444444','name'=>'S','owner_id'=>$nisha->id,'referrer_id'=>$nisha->id,'lead_source'=>'Nisha']);
        $other = Student::create(['phone'=>'9555555555','name'=>'S','owner_id'=>$poonam->id,'referrer_id'=>$poonam->id,'lead_source'=>'Poonam']);
        $this->assertTrue($nisha->can('view', $own));
        $this->assertFalse($nisha->can('view', $other));
    }

    public function test_freelancer_can_only_view_own(): void
    {
        $kapil = User::where('email','kapil@davya.local')->first();
        $sumit = User::where('email','sumit@davya.local')->first();
        $own = Student::create(['phone'=>'9600000001','name'=>'S','owner_id'=>$kapil->id,'referrer_id'=>$kapil->id,'lead_source'=>'Kapil']);
        $other = Student::create(['phone'=>'9600000002','name'=>'S','owner_id'=>$sumit->id,'referrer_id'=>$sumit->id,'lead_source'=>'Sumit']);
        $this->assertTrue($kapil->can('view', $own));
        $this->assertFalse($kapil->can('view', $other));
    }

    public function test_member_cannot_transfer_ownership(): void
    {
        $nisha = User::where('email','nisha@davya.local')->first();
        $s = Student::create(['phone'=>'9666666666','name'=>'S','owner_id'=>$nisha->id,'referrer_id'=>$nisha->id,'lead_source'=>'Nisha']);
        $this->assertFalse($nisha->can('transfer', $s));
    }
}
```

- [ ] **Step 2: Run to verify failure**

Run: `php artisan test --filter StudentPolicyTest`
Expected: FAIL — policy missing.

- [ ] **Step 3: Generate + implement policy**

```bash
php artisan make:policy StudentPolicy --model=Student
```

```php
<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool { return true; }

    public function view(User $user, Student $student): bool
    {
        if ($user->hasRole('admin')) return true;
        if ($student->owner_id === $user->id) return true;
        if ($user->hasRole('head')) {
            $teamIds = User::where('team_head_id', $user->id)->pluck('id')->toArray();
            $teamIds[] = $user->id;
            return in_array($student->owner_id, $teamIds, true);
        }
        return false;
    }

    public function create(User $user): bool { return $user->is_active; }
    public function update(User $user, Student $student): bool { return $this->view($user, $student); }
    public function delete(User $user, Student $student): bool
    {
        return $user->hasRole('admin') || ($user->hasRole('head') && $this->view($user, $student));
    }

    public function transfer(User $user, Student $student): bool
    {
        return $user->hasRole('admin') || ($user->hasRole('head') && $this->view($user, $student));
    }
}
```

- [ ] **Step 4: Run + commit**

```bash
php artisan test --filter StudentPolicyTest     # 6/6 pass
git add .
git commit -m "feat(students): StudentPolicy covering all 4 roles + transfer block"
```

### Task 3.4: `StudentResource` with sectioned form

**Files:**
- Create: `app/Filament/Resources/StudentResource.php`

- [ ] **Step 1: Generate**

```bash
php artisan make:filament-resource Student --generate
```

- [ ] **Step 2: Sectioned form**

```php
use Filament\Forms\Components\{Section, TextInput, Select, Toggle, DatePicker, DateTimePicker, Textarea};
use App\Models\User;

public static function form(Form $form): Form
{
    return $form->schema([
        Section::make('Identity')->schema([
            TextInput::make('phone')->required()->unique(ignoreRecord: true)->tel(),
            TextInput::make('name')->required(),
            TextInput::make('father_name'),
            TextInput::make('phone_2')->tel(),
        ])->columns(2),

        Section::make('Source & Owner')->schema([
            Select::make('owner_id')->relationship('owner', 'name')->required()->searchable(),
            Select::make('referrer_id')->relationship('referrer', 'name')->required()->searchable(),
            Select::make('lead_source')->options(fn () => User::pluck('name', 'name')->toArray() + ['Other' => 'Other'])->required(),
        ])->columns(3),

        Section::make('Stage & Response')->schema([
            Select::make('stage')->options([
                'Lead Captured' => 'Lead Captured', 'Meeting Scheduled' => 'Meeting Scheduled',
                'Meeting Done' => 'Meeting Done', 'Onboarded' => 'Onboarded',
                'University Registration' => 'University Registration',
                'Counselling In Progress' => 'Counselling In Progress',
                'Seat Allotted' => 'Seat Allotted', 'Full Payment Received' => 'Full Payment Received',
                'Admission Confirmed' => 'Admission Confirmed', 'Closed' => 'Closed',
            ])->required(),
            Select::make('student_response')->options([
                'Ready' => 'Ready', 'Not Interested' => 'Not Interested', 'Needs Time' => 'Needs Time',
            ]),
        ])->columns(2),

        Section::make('Academic')->schema([
            TextInput::make('exam_appeared'),
            TextInput::make('twelfth_marks'),
            Select::make('category')->options(['Delhi' => 'Delhi', 'Outside' => 'Outside']),
        ])->columns(3),

        Section::make('Preferences')->schema([
            TextInput::make('course'),
            TextInput::make('preference_r1')->label('R1'),
            TextInput::make('preference_r2')->label('R2'),
            TextInput::make('preference_r3')->label('R3'),
        ])->columns(4),

        Section::make('Deal')->schema([
            TextInput::make('deal_amount')->numeric()->prefix('₹'),
            Select::make('plan')->options(['Online' => 'Online', 'Offline' => 'Offline', 'All' => 'All']),
        ])->columns(2),

        Section::make('Counselling')->schema([
            Toggle::make('is_ipu_registered'),
            TextInput::make('ipu_user_id'),
            // NOTE: simple `->revealable()` replaced by logged action in Task 3.5
            TextInput::make('ipu_password')->password()
                ->helperText('Stored encrypted. Revealing is logged to activity_log.'),
            TextInput::make('current_round'),
            Toggle::make('seat_fee_due')->disabled(),
        ])->columns(2),

        Section::make('Final')->schema([
            TextInput::make('final_college'),
            TextInput::make('final_course'),
            DatePicker::make('admission_date'),
        ])->columns(3),

        Section::make('Logistics')->schema([
            DateTimePicker::make('meeting_date'),
            TextInput::make('meeting_location'),
            Toggle::make('address_sent'),
            Toggle::make('office_visit'),
        ])->columns(2),

        Section::make('Closure')->schema([
            Select::make('close_reason')->options([
                'Not Interested' => 'Not Interested',
                'Backed Out — Forfeit' => 'Backed Out — Forfeit',
                'Backed Out — Partial Refund' => 'Backed Out — Partial Refund',
                'Completed' => 'Completed',
                'Other' => 'Other',
            ]),
            TextInput::make('refund_amount')->numeric()->prefix('₹'),
            Textarea::make('re_entry_reason')->rows(2),
        ])->columns(2),

        Section::make('Notes')->schema([
            Textarea::make('description')->rows(3),
            Textarea::make('extra_notes')->rows(3),
        ])->columns(1),
    ]);
}
```

- [ ] **Step 3: List table**

```php
public static function table(Table $table): Table
{
    return $table->columns([
        TextColumn::make('phone')->searchable(),
        TextColumn::make('name')->searchable(),
        TextColumn::make('owner.name')->label('Owner'),
        TextColumn::make('stage')->badge(),
        TextColumn::make('deal_amount')->money('INR'),
        TextColumn::make('total_received')->money('INR')->label('Received'),
        TextColumn::make('pending_amount')->money('INR')->label('Pending'),
        TextColumn::make('updated_at')->dateTime('d M Y H:i')->sortable(),
    ])->filters([
        SelectFilter::make('owner_id')->relationship('owner', 'name'),
        SelectFilter::make('stage')->options([ /* same list */ ]),
        SelectFilter::make('plan')->options(['Online'=>'Online','Offline'=>'Offline','All'=>'All']),
    ]);
}
```

- [ ] **Step 4: Policy-scoped query**

```php
public static function getEloquentQuery(): Builder
{
    $user = auth()->user();
    $query = parent::getEloquentQuery();
    if ($user->hasRole('admin')) return $query;
    if ($user->hasRole('head')) {
        $teamIds = User::where('team_head_id', $user->id)->pluck('id')->toArray();
        $teamIds[] = $user->id;
        return $query->whereIn('owner_id', $teamIds);
    }
    return $query->where('owner_id', $user->id);
}
```

- [ ] **Step 5: Manual verify**

Sumit → all. Nikhil → own + Nisha's. Nisha → own only. Kapil → own only.

- [ ] **Step 6: Commit**

```bash
git add app/Filament/Resources/StudentResource.php
git commit -m "feat(students): StudentResource form + list with visibility filter"
```

### Task 3.5: Log every `ipu_password` reveal

**Files:**
- Create: `app/Actions/RevealIpuPassword.php`
- Modify: `StudentResource` form — replace `->revealable()` with logged action
- Create: `tests/Feature/ShowPasswordLoggingTest.php`

- [ ] **Step 1: Write failing test**

```php
<?php

namespace Tests\Feature;

use App\Actions\RevealIpuPassword;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ShowPasswordLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_revealing_ipu_password_writes_activity_log_entry(): void
    {
        $this->seed();
        $sumit = User::where('email','sumit@davya.local')->first();
        $student = Student::create([
            'phone'=>'9888888888','name'=>'PwTest',
            'owner_id'=>$sumit->id,'referrer_id'=>$sumit->id,
            'lead_source'=>'Sumit','ipu_password'=>'secret',
        ]);

        $this->actingAs($sumit);
        $revealed = (new RevealIpuPassword)($student);
        $this->assertEquals('secret', $revealed);

        $activity = Activity::where('event', 'ipu_password_revealed')->latest()->first();
        $this->assertNotNull($activity);
        $this->assertEquals($sumit->id, $activity->causer_id);
        $this->assertEquals($student->id, $activity->subject_id);
    }
}
```

- [ ] **Step 2: Implement action**

```php
<?php

namespace App\Actions;

use App\Models\Student;
use Illuminate\Support\Facades\Gate;

class RevealIpuPassword
{
    public function __invoke(Student $student): string
    {
        Gate::authorize('view', $student);

        activity()
            ->performedOn($student)
            ->causedBy(auth()->user())
            ->event('ipu_password_revealed')
            ->log('ipu_password_revealed');

        return $student->ipu_password;
    }
}
```

- [ ] **Step 3: Wire into form**

Replace the `ipu_password` TextInput in `StudentResource` with:

```php
TextInput::make('ipu_password')
    ->password()
    ->suffixAction(
        \Filament\Forms\Components\Actions\Action::make('reveal')
            ->icon('heroicon-o-eye')
            ->action(function ($record, $set) {
                $revealed = (new \App\Actions\RevealIpuPassword)($record);
                $set('ipu_password', $revealed);
            })
            ->visible(fn ($record) => $record !== null)
    )
    ->helperText('Stored encrypted. Revealing is logged to activity_log.')
```

- [ ] **Step 4: Run + commit + re-deploy**

```bash
php artisan test --filter ShowPasswordLoggingTest
git add .
git commit -m "feat(students): log ipu_password reveal action"
git push
# SSH → DEPLOY.md steps
git tag v2-students
git push origin v2-students
```

**M3 checkpoint:** Students created/edited/listed; visibility respects all 4 roles; member cannot transfer ownership; `ipu_password` encrypted + revealable via logged action; `StudentFactory` exists; `Activity` rows written on reveal. Re-deployed to prod. ✅

---

## Milestone 4 — Payments + Google Drive integration (~10–12 hours)

**Output:** `payments` table + model, `PaymentsRelationManager` under Student, signed totals, **proof uploads go directly to Google Drive** from the first upload. No local-disk → Drive migration mid-project.

### Task 4.1: Payments migration + model

**Files:**
- Create: migration, `app/Models/Payment.php`

- [ ] **Step 1: Generate**

```bash
php artisan make:migration create_payments_table
```

- [ ] **Step 2: Write migration**

```php
Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->enum('type', ['advance','partial','full','refund']);
    $table->decimal('amount', 12, 2);  // signed: refund negative
    $table->enum('mode', ['cash','upi','bank_transfer','card','cheque','other'])->nullable();
    $table->string('reference_number', 80)->nullable();
    $table->dateTime('received_at');
    $table->string('proof_drive_url', 500)->nullable();
    $table->text('notes')->nullable();
    $table->foreignId('recorded_by_user_id')->constrained('users');
    $table->timestamps();
    $table->index(['student_id', 'received_at']);
});
```

- [ ] **Step 3: Model**

```bash
php artisan make:model Payment
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $guarded = [];
    protected $casts = ['amount' => 'decimal:2', 'received_at' => 'datetime'];

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function recordedBy(): BelongsTo { return $this->belongsTo(User::class, 'recorded_by_user_id'); }

    protected static function booted(): void
    {
        static::saving(function (Payment $p) {
            if ($p->type === 'refund' && $p->amount > 0) $p->amount = -abs($p->amount);
            if ($p->type !== 'refund' && $p->amount < 0) $p->amount = abs($p->amount);
        });
    }
}
```

- [ ] **Step 4: Signed-total test**

```php
// tests/Feature/PaymentTotalsTest.php
public function test_refunds_subtract_from_total(): void
{
    $this->seed();
    $sumit = User::where('email','sumit@davya.local')->first();
    $student = Student::create([
        'phone'=>'9777777777','name'=>'T','owner_id'=>$sumit->id,'referrer_id'=>$sumit->id,
        'lead_source'=>'Sumit','deal_amount'=>50000,
    ]);
    Payment::create(['student_id'=>$student->id,'type'=>'advance','amount'=>10000,'received_at'=>now(),'recorded_by_user_id'=>$sumit->id]);
    Payment::create(['student_id'=>$student->id,'type'=>'partial','amount'=>20000,'received_at'=>now(),'recorded_by_user_id'=>$sumit->id]);
    Payment::create(['student_id'=>$student->id,'type'=>'refund','amount'=>5000,'received_at'=>now(),'recorded_by_user_id'=>$sumit->id]);
    $this->assertEquals(25000, $student->fresh()->total_received);
    $this->assertEquals(25000, $student->fresh()->pending_amount);
}
```

- [ ] **Step 5: Run + commit**

```bash
php artisan migrate
php artisan test --filter PaymentTotalsTest
git add .
git commit -m "feat(payments): migration + model with signed-amount saving hook"
```

### Task 4.2: Install Google Drive flysystem adapter

**Files:**
- Modify: `composer.json`, `config/filesystems.php`, `app/Providers/AppServiceProvider.php`, `.env.example`, `.env`

- [ ] **Step 1: Install**

```bash
composer require masbug/flysystem-google-drive-ext
```

- [ ] **Step 2: Disk config**

Edit `config/filesystems.php`:
```php
'drive' => [
    'driver' => 'google',
    'clientId' => env('GOOGLE_DRIVE_CLIENT_ID'),
    'clientSecret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
    'refreshToken' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
    'folderId' => env('GOOGLE_DRIVE_FOLDER'),
],
```

- [ ] **Step 3: Register driver in `AppServiceProvider::boot()`**

```php
\Storage::extend('google', function ($app, $config) {
    $options = [];
    if (! empty($config['teamDriveId'] ?? null)) {
        $options['teamDriveId'] = $config['teamDriveId'];
    }
    $client = new \Google\Client();
    $client->setClientId($config['clientId']);
    $client->setClientSecret($config['clientSecret']);
    $client->refreshToken($config['refreshToken']);
    $service = new \Google\Service\Drive($client);
    $adapter = new \Masbug\Flysystem\GoogleDriveAdapter($service, $config['folderId'] ?? 'root', $options);
    return new \League\Flysystem\Filesystem($adapter);
});
```

- [ ] **Step 4: Update `.env.example` + local `.env`**

```
GOOGLE_DRIVE_CLIENT_ID=
GOOGLE_DRIVE_CLIENT_SECRET=
GOOGLE_DRIVE_REFRESH_TOKEN=
GOOGLE_DRIVE_FOLDER=<folder-id-of-Davya-CRM-root>
```

- [ ] **Step 5: Smoke test locally**

```bash
php artisan tinker
>>> \Storage::disk('drive')->put('smoke-test.txt', 'hello')
>>> \Storage::disk('drive')->get('smoke-test.txt')   // "hello"
>>> \Storage::disk('drive')->delete('smoke-test.txt')
```

Expected: file appears in the Drive folder, then deletes cleanly.

- [ ] **Step 6: Commit**

```bash
git add .
git commit -m "feat(storage): Google Drive flysystem adapter"
```

### Task 4.3: PaymentsRelationManager (proofs → Drive from day 1)

**Files:**
- Create: `app/Filament/Resources/StudentResource/RelationManagers/PaymentsRelationManager.php`

- [ ] **Step 1: Generate**

```bash
php artisan make:filament-relation-manager StudentResource payments type
```

- [ ] **Step 2: Form + table**

Form fields:
- `type` (Select), `amount` (TextInput numeric prefix ₹), `mode` (Select), `reference_number`
- `received_at` (DateTimePicker, default `now()`)
- **`proof_drive_url`** (`FileUpload`) → **`->disk('drive')->directory('Payment Proofs')`**
- `notes` (Textarea)
- `recorded_by_user_id` (Hidden, default `auth()->id()`)

Table columns: received_at, type (badge), amount (money INR), mode, recorded_by.name. Add "Open proof" link action when `proof_drive_url` present.

- [ ] **Step 3: Register in StudentResource**

```php
public static function getRelations(): array
{
    return [
        PaymentsRelationManager::class,
    ];
}
```

- [ ] **Step 4: Manual test (local)**

Create student → add advance ₹10,000 with a PDF proof → verify file in Drive `Davya CRM / Payment Proofs/` → verify URL in `payments.proof_drive_url`. Add refund ₹5,000 → `total_received` updates signed.

- [ ] **Step 5: Commit + re-deploy + tag**

```bash
git add .
git commit -m "feat(payments): relation manager with Drive-backed proofs"
git push

# SSH → follow DEPLOY.md
# Fill GOOGLE_DRIVE_* env vars in prod .env
# php artisan config:cache
# Verify prod upload works

git tag v3-payments
git push origin v3-payments
```

**M4 checkpoint:** Payments added inline; totals update live (signed); proofs land in Drive `Davya CRM / Payment Proofs/` folder from day one; URLs persisted; no dual-disk migration pending. Re-deployed to prod. ✅

---

## Milestone 5 — Round history + business-rule validators (~7–9 hours)

**Output:** `round_history` table + relation manager, soft-warning banners when moving to next round with unpaid fee, hard-block validations for close/re-entry.

### Task 5.1: Round history migration + model

**Files:**
- Migration, `app/Models/RoundHistory.php`

- [ ] **Step 1: Generate**

```bash
php artisan make:migration create_round_history_table
```

- [ ] **Step 2: Migration**

```php
Schema::create('round_history', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->enum('round_name', [
        'Online_R1','Online_R2','Online_R3','Online_Sliding','Online_Reporting',
        'S2_R1','S2_R3','Offline_R1','Offline_R2',
    ]);
    $table->string('allotted_college', 120)->nullable();
    $table->string('allotted_course', 120)->nullable();
    $table->decimal('seat_fee_amount', 12, 2)->nullable();
    $table->boolean('seat_fee_paid')->default(false);
    $table->timestamp('fee_paid_at')->nullable();
    $table->enum('outcome', [
        'Not Allotted','Allotted — Fee Pending','Allotted — Fee Paid',
        'Kicked Out — Fee Unpaid','Allotted — Frozen (Final)',
    ]);
    $table->text('notes')->nullable();
    $table->timestamps();
    $table->index(['student_id', 'created_at']);
});
```

- [ ] **Step 3: Run + generate model**

```bash
php artisan migrate
php artisan make:model RoundHistory
```

```php
class RoundHistory extends Model
{
    protected $table = 'round_history';
    protected $guarded = [];
    protected $casts = [
        'seat_fee_paid' => 'boolean',
        'fee_paid_at' => 'datetime',
        'seat_fee_amount' => 'decimal:2',
    ];
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
}
```

- [ ] **Step 4: Commit**

```bash
git add .
git commit -m "feat(round-history): migration + model"
```

### Task 5.2: Round history RelationManager

- [ ] **Step 1: Generate**

```bash
php artisan make:filament-relation-manager StudentResource roundHistory round_name
```

- [ ] **Step 2: Form + table**

Form: `round_name` (Select), `allotted_college`, `allotted_course`, `seat_fee_amount`, `seat_fee_paid` (Toggle), `fee_paid_at` (DateTimePicker visible only when paid), `outcome` (Select), `notes`.

Table: `created_at`, `round_name` (badge), `allotted_college`, `outcome` (badge, color-coded), `seat_fee_paid` (icon).

- [ ] **Step 3: Register + commit**

Add `RoundHistoryRelationManager::class` to `StudentResource::getRelations()`.

```bash
git add .
git commit -m "feat(round-history): relation manager on Student"
```

### Task 5.3: Stage-transition validators

**Files:**
- Create: `app/Services/StageTransitionValidator.php`
- Modify: `StudentResource` form
- Create: `tests/Feature/StageTransitionTest.php`

- [ ] **Step 1: Failing tests**

```php
public function test_warning_when_entering_sliding_without_prior_allotment(): void
{
    $validator = new StageTransitionValidator();
    $student = Student::factory()->create();
    $warnings = $validator->forRoundChange($student, 'Online_Sliding');
    $this->assertContains('Not eligible for Sliding (no prior allotment).', $warnings);
}

public function test_close_stage_requires_close_reason(): void
{
    $validator = new StageTransitionValidator();
    $student = Student::factory()->create(['close_reason' => null]);
    $errors = $validator->forStageChange($student, 'Closed');
    $this->assertContains('close_reason is required when moving to Closed.', $errors);
}
```

- [ ] **Step 2: Implement validator**

```php
<?php

namespace App\Services;

use App\Models\Student;

class StageTransitionValidator
{
    /** @return string[] soft warnings */
    public function forRoundChange(Student $student, string $newRound): array
    {
        $warnings = [];
        $latest = $student->roundHistory()->latest()->first();
        if ($latest && str_starts_with($latest->outcome, 'Allotted — Fee Pending')) {
            $warnings[] = "Seat fee unpaid for {$latest->round_name}. Continue anyway?";
        }
        if ($newRound === 'Online_Sliding') {
            $hasPrior = $student->roundHistory()
                ->where('outcome', 'like', 'Allotted%')
                ->exists();
            if (! $hasPrior) {
                $warnings[] = 'Not eligible for Sliding (no prior allotment).';
            }
        }
        return $warnings;
    }

    /** @return string[] hard errors */
    public function forStageChange(Student $student, string $newStage): array
    {
        $errors = [];
        if ($newStage === 'Closed' && empty($student->close_reason)) {
            $errors[] = 'close_reason is required when moving to Closed.';
        }
        if ($student->getOriginal('stage') === 'Closed' && $newStage !== 'Closed' && empty($student->re_entry_reason)) {
            $errors[] = 're_entry_reason is required when re-opening a closed student.';
        }
        return $errors;
    }
}
```

- [ ] **Step 3: Run + wire into StudentResource**

Run: `php artisan test --filter StageTransitionTest` → PASS.

In `StudentResource::form()`, on `stage` field: `->afterStateUpdated()` runs `forStageChange()` + surfaces warnings via Filament notifications (non-blocking). On save, hard errors throw `ValidationException`.

- [ ] **Step 4: Manual verify**

Set stage to Closed without reason → blocked. Move to Sliding with no history → yellow warning.

- [ ] **Step 5: Commit + re-deploy + tag**

```bash
git add .
git commit -m "feat(stages): soft warnings + hard blocks for transitions"
git push
# SSH → DEPLOY.md
git tag v4-rounds
git push origin v4-rounds
```

**M5 checkpoint:** Round history works; business rules surface correctly. Re-deployed to prod. ✅

---

## Milestone 6 — Kanban board (~8–12 hours)

**Output:** Kanban page at `/admin/kanban` with 10 columns, each showing Deal / Received / Pending / Count aggregates, drag-drop between columns with rule validation. Plugin-vs-custom decision was locked in M2 (`docs/DECISIONS.md`).

### Task 6.1: Install (or scaffold custom) per M2 decision

- [ ] **Step 1: Follow the decision**

Read `docs/DECISIONS.md` for the M2 Kanban verdict.

**If plugin:**
```bash
composer require <chosen-plugin>
php artisan vendor:publish --tag="<plugin-config>"
```

**If custom:** skip to Task 6.2, Step 3 (Blade + SortableJS).

- [ ] **Step 2: Commit**

```bash
git add composer.json composer.lock config/
git commit -m "feat(kanban): install per M2 plugin decision"
```

### Task 6.2: Kanban page with aggregates

**Files:**
- Create: `app/Filament/Pages/StudentKanbanPage.php`
- Create: `resources/views/filament/pages/student-kanban-page.blade.php`

- [ ] **Step 1: Generate page**

```bash
php artisan make:filament-page StudentKanbanPage
```

- [ ] **Step 2: Columns + aggregates**

```php
protected function getColumns(): array
{
    $stages = [
        'Lead Captured','Meeting Scheduled','Meeting Done','Onboarded',
        'University Registration','Counselling In Progress','Seat Allotted',
        'Full Payment Received','Admission Confirmed','Closed',
    ];
    $visible = $this->scopedStudentsQuery();
    return collect($stages)->map(function ($stage) use ($visible) {
        $rows = (clone $visible)->where('stage', $stage)->get();
        $deal = $rows->sum('deal_amount');
        $received = $rows->sum(fn ($s) => $s->total_received);
        return [
            'id' => $stage, 'title' => $stage, 'count' => $rows->count(),
            'deal' => $deal, 'received' => $received, 'pending' => $deal - $received,
            'cards' => $rows->map(fn ($s) => [
                'id' => $s->id, 'name' => $s->name, 'phone' => $s->phone,
                'owner' => $s->owner->name, 'deal' => $s->deal_amount,
                'pending' => $s->pending_amount, 'current_round' => $s->current_round,
            ])->all(),
        ];
    })->all();
}

protected function scopedStudentsQuery()
{
    $u = auth()->user();
    $q = Student::query();
    if ($u->hasRole('admin')) return $q;
    if ($u->hasRole('head')) {
        $teamIds = User::where('team_head_id', $u->id)->pluck('id')->toArray();
        $teamIds[] = $u->id;
        return $q->whereIn('owner_id', $teamIds);
    }
    return $q->where('owner_id', $u->id);
}
```

- [ ] **Step 3: Blade view**

```blade
<x-filament-panels::page>
    <div class="flex overflow-x-auto gap-3">
        @foreach($this->getColumns() as $col)
            <div class="min-w-[280px] bg-gray-50 rounded p-2">
                <div class="font-bold text-sm">{{ $col['title'] }}</div>
                <div class="text-xs text-gray-600">
                    Deal: ₹{{ number_format($col['deal']) }} ·
                    Rcvd: ₹{{ number_format($col['received']) }} ·
                    Pending: ₹{{ number_format($col['pending']) }} ·
                    {{ $col['count'] }} students
                </div>
                <div class="mt-2 space-y-2">
                    @foreach($col['cards'] as $card)
                        <div class="bg-white p-2 rounded shadow-sm text-xs" data-id="{{ $card['id'] }}">
                            <div class="font-semibold">{{ $card['name'] }}</div>
                            <div>{{ $card['phone'] }} · {{ $card['owner'] }}</div>
                            <div>Deal ₹{{ number_format($card['deal']) }} · Pending ₹{{ number_format($card['pending']) }}</div>
                            @if($card['current_round'])
                                <div class="text-gray-500">Round: {{ $card['current_round'] }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
```

- [ ] **Step 4: Drag-drop**

If plugin: follow its drag-drop docs.
If custom: SortableJS in `resources/js/app.js`, Alpine + Livewire `changeStage($studentId, $newStage)` method that runs `StageTransitionValidator` and updates or warns.

- [ ] **Step 5: Manual verify**

`/admin/kanban`: 10 columns with correct aggregates. Drag card → stage updates → validator runs.

- [ ] **Step 6: Commit + re-deploy + tag**

```bash
git add .
git commit -m "feat(kanban): 10-column Kanban with aggregates + drag-drop"
git push
# SSH → DEPLOY.md
git tag v5-kanban
git push origin v5-kanban
```

**M6 checkpoint:** Kanban board works; aggregates correct; drag-drop triggers validator. Re-deployed to prod. ✅

---

## Milestone 7 — Dashboard widgets (~5–7 hours)

**Output:** Filament dashboard shows 4 widgets: Seat Fee Pending, Re-entry Candidates, Stuck Leads, Pipeline Summary.

### Task 7.1: Seat Fee Pending widget

- [ ] **Step 1: Generate + implement**

```bash
php artisan make:filament-widget SeatFeePendingWidget --table
```

```php
protected function getTableQuery(): Builder
{
    return RoundHistory::query()
        ->where('outcome', 'Allotted — Fee Pending')
        ->where('seat_fee_paid', false)
        ->with('student');
}

protected function getTableColumns(): array
{
    return [
        TextColumn::make('student.name'),
        TextColumn::make('round_name')->badge(),
        TextColumn::make('allotted_college'),
        TextColumn::make('seat_fee_amount')->money('INR'),
        TextColumn::make('created_at')->since()->label('Pending since'),
    ];
}
```

- [ ] **Step 2: Commit**

```bash
git commit -m "feat(dashboard): SeatFeePendingWidget"
```

### Task 7.2: Re-entry Candidates widget

Query `round_history` where `outcome = 'Kicked Out — Fee Unpaid'`, latest per student.

- [ ] Implement + commit.

### Task 7.3: Stuck Leads widget

Query students where `updated_at < now()->subDays(14)` and `stage NOT IN ('Admission Confirmed', 'Closed')`.

- [ ] Implement + commit.

### Task 7.4: Pipeline Summary widget

`Filament\Widgets\StatsOverviewWidget`: count per stage + sum of `deal_amount` per stage.

- [ ] Implement + commit.

### Task 7.5: Register widgets + re-deploy

- [ ] **Step 1: Register in `AdminPanelProvider`**

```php
->widgets([
    Widgets\AccountWidget::class,
    \App\Filament\Widgets\PipelineSummaryWidget::class,
    \App\Filament\Widgets\SeatFeePendingWidget::class,
    \App\Filament\Widgets\ReEntryCandidatesWidget::class,
    \App\Filament\Widgets\StuckLeadsWidget::class,
])
```

- [ ] **Step 2: Commit + re-deploy + tag**

```bash
git add .
git commit -m "feat(dashboard): register 4 widgets"
git push
# SSH → DEPLOY.md
git tag v6-dashboard
git push origin v6-dashboard
```

**M7 checkpoint:** Dashboard shows 4 widgets with real data. Re-deployed to prod. ✅

---

## Milestone 8 — Backup retention + DB user privilege narrowing (~3–4 hours)

**Output:** Daily `mysqldump` to Drive with **7-day local / 30-day Drive retention** (no placeholders), MySQL user `ipuc_davyapp` scoped to `ipuc_davyafin.*` only (not `*.*`), cPanel cron wired.

### Task 8.1: Backup command with real retention

**Files:**
- Create: `app/Console/Commands/BackupDatabase.php`
- Modify: `app/Console/Kernel.php`

- [ ] **Step 1: Generate + implement**

```bash
php artisan make:command BackupDatabase
```

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Finder\Finder;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database';
    protected $description = 'mysqldump + upload to Google Drive with retention (7d local / 30d Drive)';

    public function handle(): int
    {
        $filename = 'davyafin-'.now()->format('Y-m-d-His').'.sql.gz';
        $localDir = storage_path('app/backups');
        @mkdir($localDir, 0755, true);
        $local = "{$localDir}/{$filename}";

        $host = config('database.connections.mysql.host');
        $db   = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');

        $cmd = sprintf(
            'mysqldump --single-transaction --quick -h%s -u%s -p%s %s | gzip > %s',
            escapeshellarg($host), escapeshellarg($user),
            escapeshellarg($pass), escapeshellarg($db),
            escapeshellarg($local)
        );
        exec($cmd, $out, $exit);
        if ($exit !== 0) { $this->error('mysqldump failed'); return 1; }

        Storage::disk('drive')->putFileAs('Backups', new File($local), $filename);
        $this->info("Uploaded {$filename} to Drive.");

        $this->pruneLocal($localDir);
        $this->pruneDrive();
        return 0;
    }

    private function pruneLocal(string $dir): void
    {
        $cutoff = now()->subDays(7)->timestamp;
        foreach ((new Finder)->files()->in($dir)->name('davyafin-*.sql.gz') as $file) {
            if ($file->getMTime() < $cutoff) {
                @unlink($file->getRealPath());
                $this->line("Pruned local: {$file->getFilename()}");
            }
        }
    }

    private function pruneDrive(): void
    {
        $cutoff = now()->subDays(30);
        foreach (Storage::disk('drive')->files('Backups') as $path) {
            $mtime = Storage::disk('drive')->lastModified($path);
            if ($mtime && \Carbon\Carbon::createFromTimestamp($mtime)->lt($cutoff)) {
                Storage::disk('drive')->delete($path);
                $this->line("Pruned Drive: {$path}");
            }
        }
    }
}
```

- [ ] **Step 2: Schedule in `app/Console/Kernel.php`**

```php
$schedule->command('backup:database')->dailyAt('02:00');
```

- [ ] **Step 3: Test locally**

```bash
php artisan backup:database
```

Expected: dump in `storage/app/backups/`, file in Drive `Backups/`, no errors. Re-run → no duplication.

- [ ] **Step 4: Commit**

```bash
git add .
git commit -m "feat(backup): daily mysqldump → Drive with 7d/30d retention"
```

### Task 8.2: Narrow DB user privileges in production

- [ ] **Step 1: Verify current grants**

From cPanel → phpMyAdmin or SSH mysql:

```sql
SHOW GRANTS FOR 'ipuc_davyapp'@'localhost';
```

If grant is `*.*` or broader than `ipuc_davyafin.*`, fix it:

```sql
REVOKE ALL PRIVILEGES ON *.* FROM 'ipuc_davyapp'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP, REFERENCES
  ON ipuc_davyafin.* TO 'ipuc_davyapp'@'localhost';
FLUSH PRIVILEGES;
```

`DROP` is needed for Laravel migrations that drop columns/tables during rollback.

- [ ] **Step 2: Verify app still works after narrowing**

```bash
ssh <user>@ipu.co.in
cd /home/<user>/davyas_public
php artisan migrate:status
php artisan backup:database        # should still succeed
```

If anything breaks, widen the grant minimally and retry.

- [ ] **Step 3: Set up production cron (cPanel → Cron Jobs)**

```
* * * * * cd /home/<user>/davyas_public && php artisan schedule:run >> /dev/null 2>&1
```

- [ ] **Step 4: Verify backup fires at 02:00 IST**

Next morning: check `storage/logs/laravel.log` + Drive `Backups/` folder for a new `davyafin-YYYY-MM-DD-*.sql.gz`.

- [ ] **Step 5: Commit + re-deploy + tag**

```bash
git add .
git commit -m "ops(db): document privilege narrowing + cron install"
git push
# SSH → DEPLOY.md
git tag v7-backup
git push origin v7-backup
```

**M8 checkpoint:** `backup:database` runs cleanly; local + Drive retention proven; MySQL user scoped to `ipuc_davyafin.*`; prod cron fires nightly. ✅

---

## Milestone 9 — Smoke test + v1.0.0 release (~2–3 hours)

**Output:** End-to-end smoke test passes; `v1.0.0` tag pushed. Production has been live since M1 — this milestone only verifies the full flow and tags the release.

### Task 9.1: Full-flow smoke test

Work through this checklist on `https://davyas.ipu.co.in/admin` with the 7 seeded users (after they complete first-login password change):

- [ ] All 7 users can log in
- [ ] First-login password change enforced for a fresh user
- [ ] Session expires after 2h idle; cap at 7d absolute
- [ ] Sumit (admin) sees all students; Nikhil (head) sees own + Nisha's; Nisha (member) sees only own; Kapil (freelancer) sees only own
- [ ] Create student → add payment → view Kanban → drag to next stage (validator runs)
- [ ] Upload payment proof → file appears in Drive `Davya CRM / Payment Proofs/`
- [ ] Move to Closed without reason → hard-blocked
- [ ] Move to Sliding without allotment → yellow warning
- [ ] Reveal `ipu_password` → new row in `activity_log` with `event = 'ipu_password_revealed'`
- [ ] Seat Fee Pending widget lists correct rows
- [ ] Re-entry Candidates widget lists correct rows
- [ ] Stuck Leads widget lists the 14-day-stale students
- [ ] Mobile (iPhone + Android): `/admin/students` list + form usable
- [ ] Backup file present in Drive `Backups/` from last 02:00 IST run
- [ ] Rollback drill: tag current state, push a no-op commit, roll back via DEPLOY.md recipe, confirm app still works

### Task 9.2: Tag v1.0.0

- [ ] **Step 1: Tag + push**

```bash
git tag v1.0.0
git push origin v1.0.0
```

- [ ] **Step 2: Update `docs/DECISIONS.md`** with any outstanding caveats and a short "what's in v1.0.0" note.

**M9 checkpoint:** Smoke-test matrix green; `v1.0.0` tag on GitHub; Sumit signs off on spec § 14 acceptance. 🎉

---

## Self-review notes (internal)

Spec coverage check — each spec section mapped to a task:

| Spec § | Covered in |
|---|---|
| §2 Scope (in) | M1–M9 |
| §3 Stack | M1.1–M1.4 (Laravel + Filament + Spatie), M4.2 (Drive) |
| §4 Hierarchy + roles | M2.1–M2.6 |
| §5 Pipeline & stages | M3.1 (enum), M5 (validators), M6 (Kanban) |
| §6 Data model | M2 (users), M3.1 (students), M4.1 (payments), M5.1 (round_history), M1.4 (activity_log) |
| §7 Filament resources | M2.6, M3.4, M4.3, M5.2 |
| §8 Kanban | M2.9 (spike), M6 (build) |
| §9 Security — encrypted cast | M3.2 |
| §9 Security — policy | M3.3 |
| §9 Security — activity log | M1.4, **M3.5 (Show-Password logged)** |
| §9 Security — session + DB privilege | **M2.7 (sessions), M8.2 (DB privileges)** |
| §9 Security — password rotation | **M2.8 (force change on first login)** |
| §10 Deployment | M1.5 (initial deploy), re-deploy per milestone |
| §11 Error handling | M3.4 (form validation), M5.3 (validators) |
| §12 Testing | M2.2, M2.5, M2.7, M2.8, M3.2, M3.3, M3.5, M4.1, M5.3 |
| §13 Backup | M8.1 (with real retention) |
| §14 Done definition | M9.1 (smoke test) |

No placeholders. No TBDs.

---

## Plan complete

**Plan saved to:** `docs/superpowers/plans/2026-04-16-davya-crm-phase1.md`

Two execution options:

1. **Subagent-Driven (recommended)** — dispatch a fresh subagent per task, review between tasks, fast iteration.
2. **Inline Execution** — execute tasks in this session using the executing-plans skill.

Which approach?
