<?php

namespace Tests\Feature;

use App\Core\Tenant\SchoolContext;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * COMPLETE STAFF ROLE ACCESS AUDIT — Implementation tests.
 *
 * The School ERP web application is an internal staff application.
 * Parents and Students use the mobile app / portal and MUST NOT access
 * the administrative web ERP. Every approved internal staff role must be
 * able to log in, resolve its school context, and reach a dashboard.
 */
class StaffRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\SchoolSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\AdminUserSeeder::class);

        $this->school = School::query()->where('code', 'DEMO')->firstOrFail();
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);
        app(SchoolContext::class)->set($this->school->id);
    }

    // ─── Helpers ───────────────────────────────────────────────────────

    private function makeUser(string $email, string $role): User
    {
        $user = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => $role,
            'email' => $email,
            'phone' => '9876500001',
            'password' => Hash::make('password'),
            'status' => 'active',
            'current_school_id' => $this->school->id,
            'email_verified_at' => now(),
        ]);

        $user->schools()->syncWithoutDetaching([
            $this->school->id => ['status' => 'active', 'is_primary' => true],
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);
        $user->assignRole($role);

        return $user;
    }

    private function loginViaWeb(User $user): \Illuminate\Testing\TestResponse
    {
        return $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }

    // ─── Approved internal staff roles ─────────────────────────────────

    /**
     * Every approved internal staff role can authenticate and reach the ERP
     * dashboard. The dashboard builder must resolve for each role.
     */
    public function test_every_internal_staff_role_can_login_and_reach_dashboard(): void
    {
        $this->withoutMiddleware([
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
        ]);

        $staffRoles = [
            'Super Admin',
            'School Admin',
            'Principal',
            'Teacher',
            'Accountant',
            'Librarian',
            'Receptionist',
            'HR',
            'Payroll Manager',
            'Driver',
            'Staff',
        ];

        foreach ($staffRoles as $role) {
            $user = $this->makeUser(str_replace(' ', '.', strtolower($role)).'.access@test.com', $role);

            $this->loginViaWeb($user)
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('admin.dashboard'));

            // A real authenticated GET to the dashboard must resolve a builder
            // (no 403 'Your role does not have access to any dashboard').
            $this->actingAs($user)
                ->withSession(['school_id' => $this->school->id])
                ->get(route('admin.dashboard'))
                ->assertOk();

            // Isolate each iteration: log out and reset the login throttle so the
            // next role starts from a clean, unauthenticated session.
            \Illuminate\Support\Facades\Auth::guard('web')->logout();
            $this->app['session']->flush();
        }
    }

    // ─── External roles are blocked ────────────────────────────────────

    public function test_parent_cannot_login_to_web_erp(): void
    {
        $parent = $this->makeUser('parent.blocked@test.com', 'Parent');

        $this->loginViaWeb($parent)
            ->assertSessionHasErrors('email');

        $this->assertGuest('web');
    }

    public function test_student_cannot_login_to_web_erp(): void
    {
        $student = $this->makeUser('student.blocked@test.com', 'Student');

        $this->loginViaWeb($student)
            ->assertSessionHasErrors('email');

        $this->assertGuest('web');
    }

    public function test_parent_blocked_from_admin_routes_via_middleware(): void
    {
        $parent = $this->makeUser('parent.route@test.com', 'Parent');

        $this->actingAs($parent)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this->actingAs($parent)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.students.index'))
            ->assertForbidden();
    }

    public function test_student_blocked_from_admin_routes_via_middleware(): void
    {
        $student = $this->makeUser('student.route@test.com', 'Student');

        $this->actingAs($student)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_parent_blocked_from_reports_routes(): void
    {
        $parent = $this->makeUser('parent.reports@test.com', 'Parent');

        $this->actingAs($parent)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('reports.attendance.index'))
            ->assertForbidden();
    }

    // ─── Module access boundaries ──────────────────────────────────────

    public function test_accountant_has_no_payroll_access(): void
    {
        $accountant = $this->makeUser('acct.access@test.com', 'Accountant');

        $this->actingAs($accountant)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.payroll.index'))
            ->assertForbidden();

        $this->actingAs($accountant)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.roles.index'))
            ->assertForbidden();

        $this->actingAs($accountant)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.settings.index'))
            ->assertForbidden();
    }

    public function test_librarian_has_no_fees_or_payroll_access(): void
    {
        $librarian = $this->makeUser('lib.access@test.com', 'Librarian');

        $this->actingAs($librarian)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.fees.index'))
            ->assertForbidden();

        $this->actingAs($librarian)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.payroll.index'))
            ->assertForbidden();

        $this->actingAs($librarian)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.roles.index'))
            ->assertForbidden();
    }

    public function test_teacher_has_no_payroll_or_access_control(): void
    {
        $teacher = $this->makeUser('teacher.access@test.com', 'Teacher');

        $this->actingAs($teacher)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.payroll.index'))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.roles.index'))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.settings.index'))
            ->assertForbidden();
    }

    public function test_hr_has_no_fees_or_payroll_access(): void
    {
        $hr = $this->makeUser('hr.access@test.com', 'HR');

        $this->actingAs($hr)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.fees.index'))
            ->assertForbidden();

        $this->actingAs($hr)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.payroll.index'))
            ->assertForbidden();
    }

    public function test_receptionist_has_no_payroll_or_fees_access(): void
    {
        $receptionist = $this->makeUser('recep.access@test.com', 'Receptionist');

        $this->actingAs($receptionist)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.payroll.index'))
            ->assertForbidden();

        $this->actingAs($receptionist)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.fees.index'))
            ->assertForbidden();
    }

    public function test_driver_only_has_transport_and_dashboard(): void
    {
        $driver = $this->makeUser('driver.access@test.com', 'Driver');

        $this->actingAs($driver)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.transport.index'))
            ->assertOk();

        $this->actingAs($driver)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.payroll.index'))
            ->assertForbidden();

        $this->actingAs($driver)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.students.index'))
            ->assertForbidden();
    }

    // ─── Principal / School Admin boundaries ───────────────────────────

    public function test_principal_has_no_system_settings_or_access_control(): void
    {
        $principal = $this->makeUser('principal.access@test.com', 'Principal');

        $this->actingAs($principal)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.settings.index'))
            ->assertForbidden();

        $this->actingAs($principal)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.roles.index'))
            ->assertForbidden();

        $this->actingAs($principal)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_school_admin_has_full_school_administration(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->actingAs($admin)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.users.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.roles.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.settings.index'))
            ->assertOk();
    }

    public function test_super_admin_retains_full_access(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@example.com')->firstOrFail();

        $this->actingAs($superAdmin)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->actingAs($superAdmin)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.roles.index'))
            ->assertOk();

        $this->actingAs($superAdmin)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.payroll.index'))
            ->assertOk();

        $this->actingAs($superAdmin)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.settings.index'))
            ->assertOk();
    }

    // ─── AI Agents gating ──────────────────────────────────────────────

    public function test_ai_agents_limited_to_senior_roles(): void
    {
        $teacher = $this->makeUser('teacher.agents@test.com', 'Teacher');
        $this->actingAs($teacher)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.agents.index'))
            ->assertForbidden();

        $accountant = $this->makeUser('acct.agents@test.com', 'Accountant');
        $this->actingAs($accountant)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.agents.index'))
            ->assertForbidden();

        $superAdmin = User::query()->where('email', 'superadmin@example.com')->firstOrFail();
        $this->actingAs($superAdmin)
            ->withSession(['school_id' => $this->school->id])
            ->get(route('admin.agents.index'))
            ->assertOk();
    }
}
