# Davya CRM — Phase 1 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a Laravel + Filament CRM at `davyas.ipu.co.in` that replaces Zoho for Davya consultancy — 7 users, role-based access, 10-stage NewAdmission pipeline, student records with Zoho-style fields, per-round counselling history, payments with signed totals, Kanban board with live aggregates, dashboard alerts, encrypted `ipu_password`, daily MySQL backup to Google Drive.

**Architecture:** Monolithic Laravel 11 app using Filament v3 as the admin panel, Spatie for permissions + activity log, MySQL (`ipuc_davyafin` on IPU Hostinger) as data store, Livewire for reactive UI, Tailwind for styling. Deployed to a Hostinger subdomain via SSH `git pull` + composer + artisan.

**Tech Stack:** PHP 8.2+, Laravel 11, Filament v3, `spatie/laravel-permission`, `spatie/laravel-activitylog`, `masbug/flysystem-google-drive-ext`, MySQL 8, Tailwind, Livewire.

**Reference spec:** `docs/superpowers/specs/2026-04-16-davya-crm-phase1-design.md`.

**Estimated timeline:** 4–6 weeks, broken into 9 milestones (M1–M9). Each milestone ends with a green commit + manual-test checkpoint.

**Code location:** `/Users/Sumit/davya-crm/` (new dir, new private GitHub repo `davya-crm`). **This plan doc lives in the IPU repo for history; the app code is a separate repo.**

---

## Pre-flight checklist (do these before Task 1)

Confirm on your side; plan assumes all are done:

- [ ] PHP 8.2+ installed locally (`php --version`)
- [ ] Composer installed (`composer --version`)
- [ ] Node.js 20+ installed (`node --version`)
- [ ] MySQL client installed locally (`mysql --version`)
- [ ] IPU Hostinger cPanel access
- [ ] DB `ipuc_davyafin` created, user `ipuc_davyapp` with ALL PRIVILEGES, password in password manager
- [ ] cPanel API token rotated (was leaked earlier)
- [ ] Empty private GitHub repo `davya-crm` created at github.com/sumitdabass/davya-crm
- [ ] SSH key added to Hostinger for deployments (cPanel → SSH Access → Manage SSH Keys)
- [ ] Google Cloud project with Drive API enabled; OAuth 2.0 Client ID + refresh token generated for Drive access (we'll use these in M8)

---

## Milestone 1 — Scaffolding & local dev (Day 1–2, ~4 hours)

**Output:** Fresh Laravel + Filament app runs locally at `http://localhost:8000`, admin user can log in.

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

- [ ] **Step 2: Create an admin user for local testing**

```bash
php artisan make:filament-user
```

When prompted: Name=Sumit, Email=sumit@davya.local, Password=LocalDev123.

- [ ] **Step 3: Start dev server and verify login**

Run (one terminal): `php artisan serve`
Run (another terminal): `npm install && npm run dev`
Visit: `http://localhost:8000/admin`
Expected: Filament login page loads; sign-in with `sumit@davya.local` / `LocalDev123` shows empty admin dashboard.

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

- [ ] **Step 2: Add HasRoles trait to User model**

Edit `app/Models/User.php` — add:
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
```

**M1 checkpoint:** Local Filament panel loads, admin can log in, Spatie tables migrated. ✅

---

## Milestone 2 — Users schema + permissions (Day 2–4, ~6 hours)

**Output:** All 4 roles defined with policies, 7 seed users created, `UserResource` in Filament for Admin-only user management.

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
    $table->index('team_head_id');
});

// down()
Schema::table('users', function (Blueprint $table) {
    $table->dropForeign(['team_head_id']);
    $table->dropColumn(['team_head_id', 'is_freelancer', 'is_active']);
});
```

- [ ] **Step 3: Run migration**

Run: `php artisan migrate`
Expected: Columns added without error.

- [ ] **Step 4: Commit**

```bash
git add database/migrations/
git commit -m "feat(users): add team_head_id, is_freelancer, is_active"
```

### Task 2.2: Write failing tests for role gates

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

    public function test_admin_role_exists_after_seeding(): void
    {
        $this->seed(\Database\Seeders\RolesSeeder::class);
        $this->assertDatabaseHas('roles', ['name' => 'admin']);
        $this->assertDatabaseHas('roles', ['name' => 'head']);
        $this->assertDatabaseHas('roles', ['name' => 'member']);
        $this->assertDatabaseHas('roles', ['name' => 'freelancer']);
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

    public function test_kapil_is_freelancer_under_sumit(): void
    {
        $this->seed();
        $kapil = User::where('name', 'Kapil')->first();
        $sumit = User::where('name', 'Sumit')->first();
        $this->assertTrue($kapil->is_freelancer);
        $this->assertEquals($sumit->id, $kapil->team_head_id);
        $this->assertTrue($kapil->hasRole('freelancer'));
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

Run: `php artisan test --filter test_admin_role_exists_after_seeding`
Expected: PASS.

### Task 2.4: Create users seeder for 7 team members

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
        // Heads first (no team_head_id)
        $sumit = User::updateOrCreate(
            ['email' => 'sumit@davya.local'],
            ['name' => 'Sumit', 'password' => Hash::make('ChangeMe123'), 'is_freelancer' => false, 'is_active' => true]
        );
        $sonam = User::updateOrCreate(
            ['email' => 'sonam@davya.local'],
            ['name' => 'Sonam', 'password' => Hash::make('ChangeMe123'), 'is_freelancer' => false, 'is_active' => true]
        );
        $nikhil = User::updateOrCreate(
            ['email' => 'nikhil@davya.local'],
            ['name' => 'Nikhil', 'password' => Hash::make('ChangeMe123'), 'is_freelancer' => false, 'is_active' => true]
        );

        // Members under heads
        User::updateOrCreate(
            ['email' => 'nisha@davya.local'],
            ['name' => 'Nisha', 'password' => Hash::make('ChangeMe123'), 'team_head_id' => $nikhil->id, 'is_freelancer' => false, 'is_active' => true]
        );
        User::updateOrCreate(
            ['email' => 'poonam@davya.local'],
            ['name' => 'Poonam', 'password' => Hash::make('ChangeMe123'), 'team_head_id' => $sonam->id, 'is_freelancer' => false, 'is_active' => true]
        );
        User::updateOrCreate(
            ['email' => 'neetu@davya.local'],
            ['name' => 'Neetu', 'password' => Hash::make('ChangeMe123'), 'team_head_id' => $sonam->id, 'is_freelancer' => false, 'is_active' => true]
        );

        // Freelancer under Sumit
        User::updateOrCreate(
            ['email' => 'kapil@davya.local'],
            ['name' => 'Kapil', 'password' => Hash::make('ChangeMe123'), 'team_head_id' => $sumit->id, 'is_freelancer' => true, 'is_active' => true]
        );

        // Assign roles
        $sumit->syncRoles(['admin', 'head']);
        $sonam->syncRoles(['head']);
        $nikhil->syncRoles(['head']);
        User::whereIn('email', ['nisha@davya.local', 'poonam@davya.local', 'neetu@davya.local'])->get()->each(fn ($u) => $u->syncRoles(['member']));
        User::where('email', 'kapil@davya.local')->first()->syncRoles(['freelancer']);
    }
}
```

- [ ] **Step 3: Wire seeders into DatabaseSeeder**

Edit `database/seeders/DatabaseSeeder.php`:
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
git commit -m "feat(users): seed 4 roles + 7 team members with hierarchy"
```

### Task 2.5: Create `FilamentUser` gate for non-admin restrictions

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
Expected: FAIL — `canAccessPanel` not implemented.

- [ ] **Step 3: Implement canAccessPanel on User model**

Add to `app/Models/User.php`:
```php
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    // ... existing

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }
}
```

- [ ] **Step 4: Run test**

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Models/User.php tests/Feature/RolePermissionTest.php
git commit -m "feat(users): gate Filament access on is_active flag"
```

### Task 2.6: `UserResource` in Filament (Admin-only)

**Files:**
- Create: `app/Filament/Resources/UserResource.php` (+ pages)

- [ ] **Step 1: Generate UserResource**

```bash
php artisan make:filament-resource User --generate
```

- [ ] **Step 2: Restrict to admin in Resource policy**

Edit generated `app/Filament/Resources/UserResource.php`:
```php
public static function canViewAny(): bool { return auth()->user()?->hasRole('admin'); }
public static function canCreate(): bool { return auth()->user()?->hasRole('admin'); }
public static function canEdit($record): bool { return auth()->user()?->hasRole('admin'); }
public static function canDelete($record): bool { return auth()->user()?->hasRole('admin') && auth()->id() !== $record->id; }
```

In the form, add fields: `name`, `email`, `password` (hashed on save), `team_head_id` (Select of heads), `is_freelancer`, `is_active`, `roles` (Spatie select).

- [ ] **Step 3: Manual verify**

Run: `php artisan serve`
Visit: `http://localhost:8000/admin/users`
Expected: Sumit (admin) sees the Users page. Log in as Nikhil — /admin/users returns 403.

- [ ] **Step 4: Commit**

```bash
git add .
git commit -m "feat(users): UserResource admin-only CRUD"
```

**M2 checkpoint:** 7 users exist, roles assigned, Sumit can manage users, non-admin cannot. ✅

---

## Milestone 3 — Students CRUD + policy-based visibility (Day 4–8, ~10 hours)

**Output:** `students` table with ~30 fields, `Student` model, policy enforcing owner/team/all visibility, `StudentResource` in Filament with sectioned form and filtered list.

### Task 3.1: Students migration

**Files:**
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_create_students_table.php`

- [ ] **Step 1: Generate migration**

```bash
php artisan make:migration create_students_table
```

- [ ] **Step 2: Write the migration**

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

- [ ] **Step 3: Run migration**

Run: `php artisan migrate`
Expected: `students` table created without error.

- [ ] **Step 4: Commit**

```bash
git add database/migrations/
git commit -m "feat(students): create students table with 30 fields"
```

### Task 3.2: Student model with encrypted cast and relations

**Files:**
- Create: `app/Models/Student.php`

- [ ] **Step 1: Generate model**

```bash
php artisan make:model Student
```

- [ ] **Step 2: Write the model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Student extends Model
{
    use LogsActivity;

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

- [ ] **Step 3: Write test for encrypted field**

Create `tests/Feature/StudentModelTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_ipu_password_is_encrypted_at_rest_but_decrypted_on_access(): void
    {
        $this->seed();
        $sumit = User::where('email', 'sumit@davya.local')->first();
        $student = Student::create([
            'phone' => '9999999999',
            'name' => 'Test Student',
            'owner_id' => $sumit->id,
            'referrer_id' => $sumit->id,
            'lead_source' => 'Sumit',
            'ipu_password' => 'secret-pw',
        ]);
        $rawValue = DB::table('students')->where('id', $student->id)->value('ipu_password');
        $this->assertNotEquals('secret-pw', $rawValue);
        $this->assertEquals('secret-pw', $student->fresh()->ipu_password);
    }
}
```

- [ ] **Step 4: Run tests**

Run: `php artisan test --filter StudentModelTest`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Models/Student.php tests/Feature/StudentModelTest.php
git commit -m "feat(students): Student model with encrypted ipu_password + totals"
```

### Task 3.3: StudentPolicy for role-based visibility

**Files:**
- Create: `app/Policies/StudentPolicy.php`
- Modify: `app/Providers/AuthServiceProvider.php`

- [ ] **Step 1: Write failing tests**

Create `tests/Feature/StudentPolicyTest.php`:
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_view_any_student(): void
    {
        $sumit = User::where('email', 'sumit@davya.local')->first();
        $nikhil = User::where('email', 'nikhil@davya.local')->first();
        $studentOfNikhil = Student::create([
            'phone' => '9111111111','name' => 'S','owner_id' => $nikhil->id,
            'referrer_id' => $nikhil->id,'lead_source' => 'Nikhil',
        ]);
        $this->assertTrue($sumit->can('view', $studentOfNikhil));
    }

    public function test_head_can_view_own_team_student(): void
    {
        $nikhil = User::where('email', 'nikhil@davya.local')->first();
        $nisha = User::where('email', 'nisha@davya.local')->first();
        $studentOfNisha = Student::create([
            'phone' => '9222222222','name' => 'S','owner_id' => $nisha->id,
            'referrer_id' => $nisha->id,'lead_source' => 'Nisha',
        ]);
        $this->assertTrue($nikhil->can('view', $studentOfNisha));
    }

    public function test_head_cannot_view_other_teams_student(): void
    {
        $nikhil = User::where('email', 'nikhil@davya.local')->first();
        $poonam = User::where('email', 'poonam@davya.local')->first(); // Sonam's team
        $studentOfPoonam = Student::create([
            'phone' => '9333333333','name' => 'S','owner_id' => $poonam->id,
            'referrer_id' => $poonam->id,'lead_source' => 'Poonam',
        ]);
        $this->assertFalse($nikhil->can('view', $studentOfPoonam));
    }

    public function test_member_can_only_view_own(): void
    {
        $nisha = User::where('email', 'nisha@davya.local')->first();
        $poonam = User::where('email', 'poonam@davya.local')->first();
        $nishaStudent = Student::create([
            'phone' => '9444444444','name' => 'S','owner_id' => $nisha->id,
            'referrer_id' => $nisha->id,'lead_source' => 'Nisha',
        ]);
        $poonamStudent = Student::create([
            'phone' => '9555555555','name' => 'S','owner_id' => $poonam->id,
            'referrer_id' => $poonam->id,'lead_source' => 'Poonam',
        ]);
        $this->assertTrue($nisha->can('view', $nishaStudent));
        $this->assertFalse($nisha->can('view', $poonamStudent));
    }

    public function test_member_cannot_transfer_ownership(): void
    {
        $nisha = User::where('email', 'nisha@davya.local')->first();
        $student = Student::create([
            'phone' => '9666666666','name' => 'S','owner_id' => $nisha->id,
            'referrer_id' => $nisha->id,'lead_source' => 'Nisha',
        ]);
        $this->assertFalse($nisha->can('transfer', $student));
    }
}
```

- [ ] **Step 2: Run to verify failure**

Run: `php artisan test --filter StudentPolicyTest`
Expected: FAIL — policy missing.

- [ ] **Step 3: Generate and implement the policy**

```bash
php artisan make:policy StudentPolicy --model=Student
```

Edit `app/Policies/StudentPolicy.php`:
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

- [ ] **Step 4: Run tests**

Run: `php artisan test --filter StudentPolicyTest`
Expected: PASS (all 5 tests).

- [ ] **Step 5: Commit**

```bash
git add .
git commit -m "feat(students): StudentPolicy with admin/head/member/freelancer visibility"
```

### Task 3.4: `StudentResource` with sectioned form

**Files:**
- Create: `app/Filament/Resources/StudentResource.php`

- [ ] **Step 1: Generate resource**

```bash
php artisan make:filament-resource Student --generate
```

- [ ] **Step 2: Replace form() method with sectioned layout**

Edit `StudentResource::form()`:
```php
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
            TextInput::make('ipu_password')
                ->password()
                ->revealable()  // Filament's built-in show/hide button
                ->helperText('Stored encrypted. Only visible when you click the eye icon.'),
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

Use statements at top:
```php
use Filament\Forms\Components\{Section, TextInput, Select, Toggle, DatePicker, DateTimePicker, Textarea};
use App\Models\User;
```

- [ ] **Step 3: Implement list table**

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

- [ ] **Step 4: Override `getEloquentQuery` to apply policy-based visibility**

Add to StudentResource:
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

- [ ] **Step 5: Manual verify in browser**

Run: `php artisan serve`
Log in as Sumit → /admin/students — see all students.
Log in as Nikhil — see only own + Nisha's students.
Log in as Nisha — see only own students.

- [ ] **Step 6: Commit**

```bash
git add app/Filament/Resources/StudentResource.php
git commit -m "feat(students): StudentResource form + list with visibility filter"
```

**M3 checkpoint:** Students can be created/edited/listed; visibility respects roles; ipu_password encrypted + revealable. ✅

---

## Milestone 4 — Payments (Day 8–10, ~6 hours)

**Output:** `payments` table + model, `PaymentResource` as relation manager under Student, signed totals, optional proof upload to local disk for now (Drive in M8).

### Task 4.1: Payments migration + model

**Files:**
- Create: migration, `app/Models/Payment.php`

- [ ] **Step 1: Generate migration**

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

- [ ] **Step 3: Generate model**

```bash
php artisan make:model Payment
```

Edit `app/Models/Payment.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $guarded = [];
    protected $casts = [
        'amount' => 'decimal:2',
        'received_at' => 'datetime',
    ];

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function recordedBy(): BelongsTo { return $this->belongsTo(User::class, 'recorded_by_user_id'); }

    protected static function booted(): void
    {
        static::saving(function (Payment $p) {
            // Refunds must be negative; others positive
            if ($p->type === 'refund' && $p->amount > 0) $p->amount = -abs($p->amount);
            if ($p->type !== 'refund' && $p->amount < 0) $p->amount = abs($p->amount);
        });
    }
}
```

- [ ] **Step 4: Write signed-total test**

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

- [ ] **Step 5: Run migrations + tests**

```bash
php artisan migrate
php artisan test --filter PaymentTotalsTest
```
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add .
git commit -m "feat(payments): migration + model with signed-amount saving hook"
```

### Task 4.2: Payments RelationManager on Student

**Files:**
- Create: `app/Filament/Resources/StudentResource/RelationManagers/PaymentsRelationManager.php`

- [ ] **Step 1: Generate relation manager**

```bash
php artisan make:filament-relation-manager StudentResource payments type
```

- [ ] **Step 2: Implement form + table**

Form fields: type (Select), amount (TextInput numeric), mode (Select), reference_number, received_at (DateTimePicker), proof_drive_url (FileUpload for now to local disk), notes, recorded_by_user_id (default auth()->id(), hidden).

Table columns: received_at, type (badge), amount (money INR), mode, recorded_by.name.

- [ ] **Step 3: Register in StudentResource**

```php
public static function getRelations(): array
{
    return [
        PaymentsRelationManager::class,
    ];
}
```

- [ ] **Step 4: Manual test**

Create a student → add advance ₹10,000 → add partial ₹20,000 → add refund ₹5,000. Verify student list shows `Received: ₹25,000`, `Pending: ₹25,000` (if deal_amount=50k).

- [ ] **Step 5: Commit**

```bash
git add .
git commit -m "feat(payments): relation manager on StudentResource"
```

**M4 checkpoint:** Payments can be added inline on a student page; totals update live. ✅

---

## Milestone 5 — Round history + business-rule validators (Day 10–14, ~8 hours)

**Output:** `round_history` table + relation manager, soft-warning banners when moving to next round with unpaid fee, hard-block validations.

### Task 5.1: Round history migration + model

**Files:**
- Migration, `app/Models/RoundHistory.php`

- [ ] **Step 1: Generate migration**

```bash
php artisan make:migration create_round_history_table
```

- [ ] **Step 2: Write migration**

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

- [ ] **Step 3: Run**

```bash
php artisan migrate
```

- [ ] **Step 4: Generate model**

```bash
php artisan make:model RoundHistory
```

Edit:
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

- [ ] **Step 5: Commit**

```bash
git add .
git commit -m "feat(round-history): migration + model"
```

### Task 5.2: Round history RelationManager

**Files:**
- Create: `app/Filament/Resources/StudentResource/RelationManagers/RoundHistoryRelationManager.php`

- [ ] **Step 1: Generate**

```bash
php artisan make:filament-relation-manager StudentResource roundHistory round_name
```

- [ ] **Step 2: Implement form + table**

Form: round_name (Select), allotted_college, allotted_course, seat_fee_amount, seat_fee_paid (Toggle), fee_paid_at (DateTimePicker visible only when paid), outcome (Select), notes.

Table: created_at, round_name (badge), allotted_college, outcome (badge, color-coded by outcome), seat_fee_paid (icon).

- [ ] **Step 3: Register**

Add `RoundHistoryRelationManager::class` to `StudentResource::getRelations()`.

- [ ] **Step 4: Commit**

```bash
git add .
git commit -m "feat(round-history): relation manager on Student"
```

### Task 5.3: Soft warning + hard block validations on stage transition

**Files:**
- Create: `app/Services/StageTransitionValidator.php`
- Modify: `StudentResource` form `beforeSave` hook

- [ ] **Step 1: Write failing test**

```php
// tests/Feature/StageTransitionTest.php
public function test_warning_when_entering_sliding_without_prior_allotment(): void
{
    $validator = new StageTransitionValidator();
    $student = Student::factory()->create();  // no round_history rows
    $warnings = $validator->forRoundChange($student, 'Online_Sliding');
    $this->assertContains('Not eligible for Sliding (no prior allotment)', $warnings);
}

public function test_close_stage_requires_close_reason(): void
{
    $validator = new StageTransitionValidator();
    $student = Student::factory()->create(['close_reason' => null]);
    $errors = $validator->forStageChange($student, 'Closed');
    $this->assertContains('close_reason is required when moving to Closed', $errors);
}
```

- [ ] **Step 2: Implement `StageTransitionValidator`**

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

- [ ] **Step 3: Run tests**

```bash
php artisan test --filter StageTransitionTest
```
Expected: PASS.

- [ ] **Step 4: Wire into StudentResource via beforeSave**

In `StudentResource::form()`, add after the form array a `->afterStateUpdated()` on `stage` field that runs the validator and surfaces warnings via Filament notifications (non-blocking). Hard errors use `Filament\Forms\Components\ValidationException` on save.

- [ ] **Step 5: Manual verify**

Create a student, set stage to Closed without close_reason → save blocked with error.
Move a student to Sliding without any round_history → yellow warning toast appears, save succeeds.

- [ ] **Step 6: Commit**

```bash
git add .
git commit -m "feat(stages): soft warnings + hard blocks for transitions"
```

**M5 checkpoint:** Round history works; business rules surface correctly. ✅

---

## Milestone 6 — Kanban board (Day 14–18, ~10 hours)

**Output:** Custom Kanban page at `/admin/kanban` with 10 columns, each showing Deal / Received / Pending / Count aggregates, drag-drop between columns with rule validation.

### Task 6.1: Install Kanban plugin or build custom

**Files:**
- Modify: `composer.json`

- [ ] **Step 1: Evaluate available Filament Kanban plugins**

Check: https://filamentphp.com/plugins?search=kanban. Top candidates: `flowforge/filament-kanban`, `relaticle/filament-kanban-board`. Install the one with drag-drop + customizable cards.

Decision heuristic: prefer actively maintained (last commit < 3 months) and supports custom card content.

```bash
composer require <chosen-plugin>
php artisan vendor:publish --tag="<plugin-config>"
```

If no plugin fits, fall back to custom Livewire page (see Task 6.2 fallback).

- [ ] **Step 2: Commit**

```bash
git add composer.json composer.lock config/
git commit -m "feat(kanban): install Filament Kanban plugin"
```

### Task 6.2: Kanban page with aggregates

**Files:**
- Create: `app/Filament/Pages/StudentKanbanPage.php`

- [ ] **Step 1: Generate page**

```bash
php artisan make:filament-page StudentKanbanPage
```

- [ ] **Step 2: Implement columns + aggregates**

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
            'id' => $stage,
            'title' => $stage,
            'count' => $rows->count(),
            'deal' => $deal,
            'received' => $received,
            'pending' => $deal - $received,
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

- [ ] **Step 3: Create Blade view with column headers**

Create `resources/views/filament/pages/student-kanban-page.blade.php`:
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

- [ ] **Step 4: Add drag-drop via Livewire + SortableJS**

Install SortableJS in `resources/js/app.js`:
```js
import Sortable from 'sortablejs';
window.Sortable = Sortable;
```

In the Blade, add Alpine + Livewire binding to move a card between columns and emit `changeStage` event. Livewire method `changeStage($studentId, $newStage)` runs `StageTransitionValidator` and either updates `student.stage` or surfaces warnings.

- [ ] **Step 5: Manual verify**

`php artisan serve` → /admin/kanban — see all 10 columns with live aggregates. Drag a card → stage updates.

- [ ] **Step 6: Commit**

```bash
git add .
git commit -m "feat(kanban): 10-column Kanban with aggregates + drag-drop"
```

**M6 checkpoint:** Kanban board works, aggregates correct, drag-drop triggers validator. ✅

---

## Milestone 7 — Dashboard widgets (Day 18–21, ~6 hours)

**Output:** Filament dashboard shows 4 widgets: Seat Fee Pending, Re-entry Candidates, Stuck Leads (>14 days), Pipeline Summary.

### Task 7.1: Seat Fee Pending widget

**Files:**
- Create: `app/Filament/Widgets/SeatFeePendingWidget.php`

- [ ] **Step 1: Generate**

```bash
php artisan make:filament-widget SeatFeePendingWidget --table
```

- [ ] **Step 2: Implement**

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

- [ ] **Step 3: Commit**

```bash
git add .
git commit -m "feat(dashboard): SeatFeePendingWidget"
```

### Task 7.2: Re-entry Candidates widget

Similar pattern: query `round_history` where `outcome = 'Kicked Out — Fee Unpaid'`, latest per student.

- [ ] **Step 1-3:** Implement + commit.

### Task 7.3: Stuck Leads widget

Query students where `updated_at < now()->subDays(14)` and `stage NOT IN ('Admission Confirmed', 'Closed')`.

- [ ] **Step 1-3:** Implement + commit.

### Task 7.4: Pipeline Summary widget

Stats widget: count per stage + sum of deal_amount per stage. Use `Filament\Widgets\StatsOverviewWidget`.

- [ ] **Step 1-3:** Implement + commit.

### Task 7.5: Register all 4 widgets on dashboard

- [ ] **Step 1: Edit `app/Providers/Filament/AdminPanelProvider.php`**

```php
->widgets([
    Widgets\AccountWidget::class,
    \App\Filament\Widgets\PipelineSummaryWidget::class,
    \App\Filament\Widgets\SeatFeePendingWidget::class,
    \App\Filament\Widgets\ReEntryCandidatesWidget::class,
    \App\Filament\Widgets\StuckLeadsWidget::class,
])
```

- [ ] **Step 2: Manual verify + commit**

**M7 checkpoint:** Dashboard shows 4 widgets with real data. ✅

---

## Milestone 8 — Google Drive integration for payment proofs (Day 21–23, ~5 hours)

**Output:** Payment proof uploads go to `Davya CRM / Payment Proofs/` folder in Drive; URL stored in DB.

### Task 8.1: Install Google Drive flysystem adapter

- [ ] **Step 1: Install**

```bash
composer require masbug/flysystem-google-drive-ext
```

- [ ] **Step 2: Add filesystem disk config**

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

- [ ] **Step 3: Register disk in `AppServiceProvider::boot()`**

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

- [ ] **Step 4: Update `.env.example` with keys**

### Task 8.2: Point payment `proof_drive_url` FileUpload at Drive disk

- [ ] **Step 1: Modify `PaymentsRelationManager` form**

Change the `FileUpload::make('proof_drive_url')` to `->disk('drive')->directory('Payment Proofs')`.

- [ ] **Step 2: Manual test**

Upload a proof → verify file appears in Drive folder → verify URL stored in DB.

- [ ] **Step 3: Commit**

```bash
git add .
git commit -m "feat(payments): Google Drive uploads for proof files"
```

**M8 checkpoint:** Proof uploads land in Drive, URLs persisted. ✅

---

## Milestone 9 — Backup cron + Production deploy (Day 23–28, ~10 hours)

**Output:** Subdomain `davyas.ipu.co.in` serves the app with SSL; daily backup cron running; all 7 users can log in from production.

### Task 9.1: Backup PHP cron script

**Files:**
- Create: `scripts/backup.php` (or `app/Console/Commands/BackupCommand.php`)

- [ ] **Step 1: Generate Artisan command**

```bash
php artisan make:command BackupDatabase
```

- [ ] **Step 2: Implement**

```php
public function handle()
{
    $filename = 'davyafin-'.now()->format('Y-m-d').'.sql.gz';
    $local = storage_path("app/backups/{$filename}");
    $host = config('database.connections.mysql.host');
    $db = config('database.connections.mysql.database');
    $user = config('database.connections.mysql.username');
    $pass = config('database.connections.mysql.password');
    @mkdir(dirname($local), 0755, true);
    exec("mysqldump -h{$host} -u{$user} -p{$pass} {$db} | gzip > {$local}", $out, $exit);
    if ($exit !== 0) { $this->error('Dump failed'); return 1; }
    Storage::disk('drive')->putFileAs('Backups', new \Illuminate\Http\File($local), $filename);
    // Retention: delete local > 7d, Drive > 30d
    // ...
    return 0;
}
```

- [ ] **Step 3: Add to Kernel schedule**

Edit `app/Console/Kernel.php`:
```php
$schedule->command('backup:database')->dailyAt('02:00');
```

- [ ] **Step 4: Test locally**

Run: `php artisan backup:database`
Expected: dump file appears in storage + Drive.

- [ ] **Step 5: Commit**

```bash
git add .
git commit -m "feat(backup): daily mysqldump + Drive upload artisan command"
```

### Task 9.2: Create subdomain in cPanel

- [ ] **Step 1: cPanel → Subdomains → Create `davyas` on `ipu.co.in`**

Document root: `/home/<user>/davyas_public/public`.

- [ ] **Step 2: Enable AutoSSL** (cPanel → SSL/TLS Status → Run AutoSSL).

- [ ] **Step 3: Verify DNS**

Run: `dig davyas.ipu.co.in` — should resolve to Hostinger IP.

### Task 9.3: Deploy code to Hostinger

- [ ] **Step 1: SSH into Hostinger**

```bash
ssh <user>@ipu.co.in
```

- [ ] **Step 2: Clone repo**

```bash
cd /home/<user>
git clone git@github.com:sumitdabass/davya-crm.git davyas_public
cd davyas_public
```

- [ ] **Step 3: Install composer deps (production mode)**

```bash
composer install --no-dev --optimize-autoloader
```

- [ ] **Step 4: Create production `.env`**

Copy `.env.example` → `.env`, fill in:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://davyas.ipu.co.in`
- DB creds (ipuc_davyafin / ipuc_davyapp / password from pw manager)
- Drive keys

Then: `php artisan key:generate`, `php artisan config:cache`, `php artisan route:cache`.

- [ ] **Step 5: Run migrations + seed**

```bash
php artisan migrate --force
php artisan db:seed --force
```

- [ ] **Step 6: Set storage permissions**

```bash
chmod -R 775 storage bootstrap/cache
```

- [ ] **Step 7: Verify site**

Visit: `https://davyas.ipu.co.in/admin`
Expected: Filament login. Log in as Sumit (email: sumit@davya.local, pw: ChangeMe123) — change password immediately.

### Task 9.4: Production cron

- [ ] **Step 1: cPanel → Cron Jobs → add**

```
* * * * * php /home/<user>/davyas_public/artisan schedule:run >> /dev/null 2>&1
```

- [ ] **Step 2: Verify backup runs at 02:00 IST**

Check storage/logs/laravel.log tomorrow morning.

### Task 9.5: Acceptance checklist

- [ ] All 7 users log in successfully
- [ ] Sumit sees all students; Nikhil sees own+Nisha's; Nisha sees only own
- [ ] Create student → add payment → view Kanban → drag to next stage
- [ ] Upload payment proof → file appears in Drive
- [ ] Close a student without reason → blocked
- [ ] Move to Sliding without allotment → warning shown
- [ ] Seat Fee Pending widget lists the right rows
- [ ] Backup file appears in Drive after 02:00 IST
- [ ] Mobile: open /admin/students on phone → list + form usable

- [ ] **Step 6: Tag release**

```bash
git tag v1.0.0
git push origin v1.0.0
```

**M9 checkpoint:** Production app live at davyas.ipu.co.in, all acceptance tests pass. 🎉

---

## Self-review notes (internal)

Spec coverage check — each spec section mapped to a task:

| Spec § | Covered in |
|---|---|
| §2 Scope (in) | M1–M9 |
| §3 Stack | M1, M1.4, M8 |
| §4 Hierarchy + roles | M2 |
| §5 Pipeline & stages | M3.1 (enum), M5 (validators), M6 (Kanban) |
| §6 Data model | M2 (users), M3.1 (students), M4 (payments), M5.1 (round_history), M1.4 (activity_log) |
| §7 Filament resources | M2.6, M3.4, M4.2, M5.2 |
| §8 Kanban | M6 |
| §9 Security | M3.2 (encrypted cast), M3.3 (policy), M1.4 (activity log) |
| §10 Deployment | M9.2, M9.3 |
| §11 Error handling | M3.4 (form validation), M5.3 (validators) |
| §12 Testing | Scattered: M2.2, M3.2, M3.3, M4.1, M5.3 |
| §13 Backup | M9.1 |
| §14 Done definition | M9.5 |

No placeholders. No TBDs.

---

## Plan complete

**Plan saved to:** `docs/superpowers/plans/2026-04-16-davya-crm-phase1.md`

Two execution options:

1. **Subagent-Driven (recommended)** — I dispatch a fresh subagent per task, review between tasks, fast iteration.
2. **Inline Execution** — Execute tasks in this session using the executing-plans skill, batch execution with checkpoints.

Which approach?
