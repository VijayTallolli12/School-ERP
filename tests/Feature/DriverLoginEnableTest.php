<?php

namespace Tests\Feature;

use App\Core\Tenant\SchoolContext;
use App\Models\School;
use App\Models\User;
use App\Modules\Transport\Models\Driver;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\SchoolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DriverLoginEnableTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SchoolSeeder::class);
        $this->seed(PermissionSeeder::class);
        $this->seed(AdminUserSeeder::class);

        $this->school = School::query()->where('code', 'DEMO')->firstOrFail();
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);
        app(SchoolContext::class)->set($this->school->id);

        $this->admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
    }

    private function actingAsAdmin(): self
    {
        return $this->actingAs($this->admin)->withSession(['school_id' => $this->school->id]);
    }

    private function driverPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ramesh Kumar',
            'mobile' => '9876500001',
            'license_number' => 'DL-2026-IND-001',
            'license_expiry_date' => now()->addYears(5)->toDateString(),
            'address' => 'Street 12, City',
            'status' => 'active',
            'enable_login' => 1,
            'email' => 'ramesh.driver@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ], $overrides);
    }

    private function createDriver(array $overrides = []): Driver
    {
        $response = $this->actingAsAdmin()
            ->postJson(route('admin.transport.drivers.store'), $this->driverPayload($overrides))
            ->assertOk();

        return Driver::query()->findOrFail($response->json('data.id'));
    }

    public function test_create_driver_with_login_creates_user_role_and_link(): void
    {
        $driver = $this->createDriver();

        $user = User::query()->where('email', 'ramesh.driver@example.com')->first();

        $this->assertNotNull($user, 'User should be created.');
        $this->assertSame($user->id, $driver->user_id);
        $this->assertSame('active', $user->status);
        $this->assertSame($this->school->id, $user->current_school_id);
        $this->assertTrue(Hash::check('secret123', $user->password), 'Password must be stored hashed (bcrypt), not plain text.');
        $this->assertTrue($user->hasRole('Driver'), 'Driver role should be assigned (school scoped).');
        $this->assertTrue($user->schools()->whereKey($this->school->id)->exists(), 'User should be attached to the school.');
    }

    public function test_driver_login_role_is_scoped_to_school(): void
    {
        $driver = $this->createDriver();

        $user = User::query()->where('email', 'ramesh.driver@example.com')->first();
        $role = Role::query()->where('name', 'Driver')->where('school_id', $this->school->id)->first();

        $this->assertNotNull($role, 'School-scoped Driver role should exist.');
        $this->assertTrue($user->hasRole($role), 'User should hold the school-scoped Driver role.');
    }

    public function test_create_driver_without_login_creates_no_user(): void
    {
        $driver = $this->createDriver(['enable_login' => 0]);

        $this->assertNull($driver->user_id);
        $this->assertDatabaseMissing('users', ['email' => 'ramesh.driver@example.com']);
    }

    public function test_create_driver_login_requires_email_and_password(): void
    {
        $this->actingAsAdmin()
            ->postJson(route('admin.transport.drivers.store'), $this->driverPayload(['email' => '', 'password' => '']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_create_driver_prevents_duplicate_email(): void
    {
        $this->createDriver();

        $this->actingAsAdmin()
            ->postJson(route('admin.transport.drivers.store'), $this->driverPayload(['license_number' => 'DL-2026-IND-002']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_update_driver_with_login_updates_existing_user(): void
    {
        $driver = $this->createDriver();
        $user = User::query()->where('email', 'ramesh.driver@example.com')->first();
        $oldHash = $user->password;

        $this->actingAsAdmin()
            ->putJson(route('admin.transport.drivers.update', $driver), [
                'name' => 'Ramesh Kumar Updated',
                'mobile' => '9876500001',
                'license_number' => 'DL-2026-IND-001',
                'license_expiry_date' => now()->addYears(5)->toDateString(),
                'status' => 'active',
                'enable_login' => 1,
                'email' => 'ramesh.updated@example.com',
            ])
            ->assertOk();

        $user->refresh();

        $this->assertSame('ramesh.updated@example.com', $user->email);
        $this->assertSame('Ramesh Kumar Updated', $user->name);
        $this->assertSame($oldHash, $user->password, 'Password should stay unchanged when left blank.');
    }

    public function test_update_driver_that_enables_login_creates_user(): void
    {
        $driver = $this->createDriver(['enable_login' => 0]);
        $this->assertNull($driver->user_id);

        $this->actingAsAdmin()
            ->putJson(route('admin.transport.drivers.update', $driver), [
                'name' => 'Ramesh Kumar',
                'mobile' => '9876500001',
                'license_number' => 'DL-2026-IND-001',
                'license_expiry_date' => now()->addYears(5)->toDateString(),
                'status' => 'active',
                'enable_login' => 1,
                'email' => 'ramesh.later@example.com',
                'password' => 'newsecret',
                'password_confirmation' => 'newsecret',
            ])
            ->assertOk();

        $driver->refresh();

        $this->assertNotNull($driver->user_id, 'Driver should be linked to the newly created user.');
        $user = User::query()->find($driver->user_id);
        $this->assertTrue($user->hasRole('Driver'));
        $this->assertTrue(Hash::check('newsecret', $user->password));
    }

    public function test_update_driver_with_login_disabled_keeps_user_linked(): void
    {
        $driver = $this->createDriver();
        $originalUserId = $driver->user_id;
        $this->assertNotNull($originalUserId);

        $this->actingAsAdmin()
            ->putJson(route('admin.transport.drivers.update', $driver), [
                'name' => 'Ramesh Kumar',
                'mobile' => '9876500001',
                'license_number' => 'DL-2026-IND-001',
                'license_expiry_date' => now()->addYears(5)->toDateString(),
                'status' => 'active',
                'enable_login' => 0,
            ])
            ->assertOk();

        $driver->refresh();

        $this->assertSame($originalUserId, $driver->user_id, 'Disabling login must not delete or unlink the user.');
        $this->assertDatabaseHas('users', ['id' => $originalUserId]);
    }

    public function test_reset_driver_password(): void
    {
        $driver = $this->createDriver();
        $user = User::query()->find($driver->user_id);

        $this->actingAsAdmin()
            ->putJson(route('admin.transport.drivers.reset-password', $driver), [
                'password' => 'New@Passw0rd1',
                'password_confirmation' => 'New@Passw0rd1',
            ])
            ->assertOk();

        $user->refresh();
        $this->assertTrue(Hash::check('New@Passw0rd1', $user->password));
        $this->assertFalse(Hash::check('secret123', $user->password));
    }

    public function test_reset_password_fails_for_driver_without_login(): void
    {
        $driver = $this->createDriver(['enable_login' => 0]);

        $this->actingAsAdmin()
            ->putJson(route('admin.transport.drivers.reset-password', $driver), [
                'password' => 'New@Passw0rd1',
                'password_confirmation' => 'New@Passw0rd1',
            ])
            ->assertUnprocessable();
    }
}
