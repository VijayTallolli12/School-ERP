<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AcademicModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_admin_can_view_academic_module(): void
    {
        $this->seed();

        [$user, $school] = $this->adminAndSchool();

        $this->actingAs($user)
            ->withSession(['school_id' => $school->id])
            ->get(route('admin.academics.index'))
            ->assertOk()
            ->assertSee('Academic Management');
    }

    public function test_academic_datatables_return_seeded_records(): void
    {
        $this->seed();

        [$user, $school] = $this->adminAndSchool();

        $this->actingAs($user)
            ->withSession(['school_id' => $school->id])
            ->getJson(route('admin.academics.subjects.data'))
            ->assertOk()
            ->assertJsonPath('recordsTotal', 5);

        $this->actingAs($user)
            ->withSession(['school_id' => $school->id])
            ->getJson(route('admin.academics.class-subjects.data'))
            ->assertOk()
            ->assertJsonPath('recordsTotal', 25);
    }

    private function adminAndSchool(): array
    {
        $school = School::query()->where('code', 'DEMO')->firstOrFail();
        $user = User::query()->where('email', 'admin@example.com')->firstOrFail();

        app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);

        return [$user, $school];
    }
}
