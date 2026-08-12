<?php

namespace Tests\Feature;

use App\Core\Tenant\SchoolContext;
use App\Modules\AiAssistant\Erp\ErpQueryExecutor;
use App\Modules\AiAssistant\Erp\ErpToolRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Guards against re-introducing the original taxonomy-drift bug.
 *
 * Every registered AI tool must have a valid permission definition, and every
 * configured AI permission must reference an existing tool.
 */
class AiRegistryConsistencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\SchoolSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\AdminUserSeeder::class);
        $this->seed(\Database\Seeders\AcademicStructureSeeder::class);

        $school = \App\Models\School::query()->where('code', 'DEMO')->firstOrFail();
        app(SchoolContext::class)->set($school->id);
        app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);
    }

    private function registry(): ErpToolRegistry
    {
        return app(ErpToolRegistry::class);
    }

    private function toolMatchesPattern(string $tool, string $pattern): bool
    {
        return $pattern === '*' || \Illuminate\Support\Str::is($pattern, $tool);
    }

    public function test_every_registered_tool_has_a_valid_permission_definition(): void
    {
        $rolePerms = config('ai.role_permissions', []);
        $this->assertNotEmpty($rolePerms, 'config/ai.php role_permissions must be configured.');

        $allTools = array_merge($this->registry()->toolNames(), $this->registry()->actionToolNames());

        foreach ($allTools as $tool) {
            $matched = false;
            foreach ($rolePerms as $role => $patterns) {
                foreach ($patterns as $pattern) {
                    if ($this->toolMatchesPattern($tool, $pattern)) {
                        $matched = true;
                        break 2;
                    }
                }
            }
            $this->assertTrue($matched, "Tool '{$tool}' has no permission definition in config/ai.php.");
        }
    }

    public function test_every_configured_permission_references_an_existing_tool(): void
    {
        $rolePerms = config('ai.role_permissions', []);
        $allTools = array_merge($this->registry()->toolNames(), $this->registry()->actionToolNames());

        foreach ($rolePerms as $role => $patterns) {
            foreach ($patterns as $pattern) {
                if ($pattern === '*') {
                    continue;
                }
                $matched = false;
                foreach ($allTools as $tool) {
                    if ($this->toolMatchesPattern($tool, $pattern)) {
                        $matched = true;
                        break;
                    }
                }
                $this->assertTrue($matched, "Permission pattern '{$pattern}' for role '{$role}' references no registered tool.");
            }
        }
    }

    public function test_every_tool_has_valid_structure(): void
    {
        $registry = $this->registry();

        foreach ($registry->all() as $name => $cfg) {
            $this->assertArrayHasKey('description', $cfg, "Tool '{$name}' missing description.");
            $this->assertNotEmpty($cfg['description'], "Tool '{$name}' has empty description.");
            $this->assertArrayHasKey('params', $cfg, "Tool '{$name}' missing params.");
            $this->assertIsArray($cfg['params'], "Tool '{$name}' params must be an array.");
            $this->assertArrayHasKey('handler', $cfg, "Tool '{$name}' missing handler.");
            $this->assertNotEmpty($cfg['handler'], "Tool '{$name}' has empty handler.");
            $this->assertArrayHasKey('method', $cfg, "Tool '{$name}' missing method.");
            $this->assertNotEmpty($cfg['method'], "Tool '{$name}' has empty method.");
            $this->assertArrayHasKey('result_type', $cfg, "Tool '{$name}' missing result_type.");
            $this->assertContains($cfg['result_type'], ['list', 'count', 'single', 'summary'], "Tool '{$name}' invalid result_type.");
        }

        foreach ($registry->actionTools() as $name => $cfg) {
            $this->assertNotEmpty($cfg['description'], "Action '{$name}' missing description.");
            $this->assertContains($cfg['type'], ['agent', 'service'], "Action '{$name}' invalid type.");
        }
    }

    public function test_every_handler_and_method_exists(): void
    {
        $problems = app(ErpQueryExecutor::class)->validateTools();
        $this->assertSame([], $problems, "Registry/handler validation failed:\n" . implode("\n", $problems));
    }

    public function test_no_duplicate_or_overlapping_tool_names(): void
    {
        $registry = $this->registry();
        $queries = $registry->toolNames();
        $actions = $registry->actionToolNames();

        $this->assertCount(count($queries), array_unique($queries), 'Duplicate query tool names.');
        $this->assertCount(count($actions), array_unique($actions), 'Duplicate action tool names.');

        $overlap = array_intersect($queries, $actions);
        $this->assertSame([], $overlap, 'A tool cannot be both a query tool and an action tool.');
    }

    public function test_roles_in_config_are_valid_roles(): void
    {
        $validRoles = [
            'Super Admin', 'School Admin', 'Principal', 'HR', 'Accountant',
            'Teacher', 'Parent', 'Student', 'Librarian', 'Staff', 'Receptionist',
            'Payroll Manager', 'Driver',
        ];

        foreach (array_keys(config('ai.role_permissions', [])) as $role) {
            $this->assertContains($role, $validRoles, "Unknown role '{$role}' in config/ai.php role_permissions.");
        }
    }

    public function test_teacher_scope_is_enforced_on_queries(): void
    {
        // A Teacher's queries must be restricted to their class sections.
        $school = \App\Models\School::query()->where('code', 'DEMO')->firstOrFail();
        $user = \App\Models\User::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Scoped Teacher',
            'email' => 'scoped.teacher@test.com',
            'phone' => '9876500999',
            'password' => bcrypt('password'),
            'status' => 'active',
            'current_school_id' => $school->id,
        ]);
        $user->schools()->syncWithoutDetaching([$school->id => ['status' => 'active', 'is_primary' => true]]);
        $user->assignRole('Teacher');

        $teacher = \App\Modules\Teachers\Models\Teacher::query()->create([
            'school_id' => $school->id,
            'user_id' => $user->id,
            'uuid' => (string) Str::uuid(),
            'first_name' => 'Scoped',
            'last_name' => 'Teacher',
            'employee_id' => 'T-SCOPED-1',
            'status' => 'active',
        ]);

        $classSections = \App\Modules\Academics\Models\ClassSection::query()
            ->where('school_id', $school->id)
            ->get();

        $this->assertGreaterThanOrEqual(2, $classSections->count());
        $teacher->classSections()->sync([$classSections[0]->id]);

        auth()->login($user);

        $scoper = app(\App\Modules\AiAssistant\Services\RoleDataScoper::class);
        $filters = $scoper->getScopeFilters($user);
        $this->assertSame([$classSections[0]->id], $filters['class_section_ids'] ?? null, 'Teacher scope should contain their class section.');

        // Authorization: teacher may query exams (allowed), but NOT payroll.
        $this->assertTrue($scoper->isIntentAllowed($user, 'exam.search'));
        $this->assertFalse($scoper->isIntentAllowed($user, 'payroll.latest_run'));
        $this->assertFalse($scoper->isIntentAllowed($user, 'leave.pending'));
    }
}
