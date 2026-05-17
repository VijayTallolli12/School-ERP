<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StudentModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_admin_can_view_students_module(): void
    {
        $this->seed();

        $school = School::query()->where('code', 'DEMO')->firstOrFail();
        $user = User::query()->where('email', 'admin@example.com')->firstOrFail();

        app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);

        $this->actingAs($user)
            ->withSession(['school_id' => $school->id])
            ->get(route('admin.students.index'))
            ->assertOk()
            ->assertSee('Student Management');
    }

    public function test_students_datatable_returns_seeded_students(): void
    {
        $this->seed();

        $school = School::query()->where('code', 'DEMO')->firstOrFail();
        $user = User::query()->where('email', 'admin@example.com')->firstOrFail();

        app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);

        $this->actingAs($user)
            ->withSession(['school_id' => $school->id])
            ->getJson(route('admin.students.data'))
            ->assertOk()
            ->assertJsonPath('recordsTotal', 12);
    }
}
