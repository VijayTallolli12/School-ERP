<?php

namespace Tests\Feature;

use App\Core\Tenant\SchoolContext;
use App\Models\School;
use App\Models\User;
use App\Modules\Teachers\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LibraryBorrowerSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_library_teacher_borrower_search_finds_teacher_by_name_and_employee_id(): void
    {
        $this->seed(\Database\Seeders\SchoolSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\AdminUserSeeder::class);

        $school = School::query()->where('code', 'DEMO')->firstOrFail();
        $user = User::query()->where('email', 'admin@example.com')->firstOrFail();

        app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);
        app(SchoolContext::class)->set($school->id);

        $teacher = Teacher::query()->create([
            'school_id' => $school->id,
            'uuid' => (string) Str::uuid(),
            'employee_id' => 'T-AISHA-001',
            'first_name' => 'Aisha',
            'last_name' => 'Khan',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->withSession(['school_id' => $school->id])
            ->getJson(route('admin.library.search.teachers', ['q' => 'Aisha']))
            ->assertOk()
            ->assertJsonPath('results.0.id', $teacher->id)
            ->assertJsonPath('results.0.text', 'Aisha Khan (T-AISHA-001)');

        $this->actingAs($user)
            ->withSession(['school_id' => $school->id])
            ->getJson(route('admin.library.search.teachers', ['q' => 'T-AISHA']))
            ->assertOk()
            ->assertJsonPath('results.0.id', $teacher->id)
            ->assertJsonPath('results.0.text', 'Aisha Khan (T-AISHA-001)');
    }
}
