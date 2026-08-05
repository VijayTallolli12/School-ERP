<?php

namespace Tests\Feature;

use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BrandingApiTest extends TestCase
{
    use RefreshDatabase;

    private const BRANDING_KEYS = [
        'school_name',
        'school_logo',
        'favicon',
        'primary_color',
        'secondary_color',
        'school_website',
        'school_address',
        'school_phone',
        'app_name',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\SchoolSeeder::class);
    }

    private function demoSchool(): School
    {
        return School::query()->where('code', 'DEMO')->firstOrFail();
    }

    private function makeSchool(array $overrides = []): School
    {
        return School::query()->create(array_merge([
            'uuid' => (string) Str::uuid(),
            'code' => 'SCH-'.Str::upper(Str::random(4)),
            'name' => 'Test School',
            'slug' => Str::slug('Test School '.Str::random(4)),
            'email' => 'test@school.test',
            'phone' => '+1 555 000 1111',
            'address' => 'Test Campus',
            'city' => 'Test City',
            'state' => 'Test State',
            'country' => 'India',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'status' => 'active',
        ], $overrides));
    }

    public function test_response_structure_is_unchanged(): void
    {
        $school = $this->demoSchool();

        $this->getJson('http://localhost/api/v1/branding?school_id='.$school->id)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'message', 'data'])
            ->assertJsonStructure(['data' => array_fill_keys(self::BRANDING_KEYS, [])]);
    }

    public function test_default_branding_when_no_school_id(): void
    {
        $response = $this->getJson('http://localhost/api/v1/branding')
            ->assertOk();

        $data = $response->json('data');

        $this->assertSame(config('app.name', 'School ERP'), $data['school_name']);
        $this->assertNotNull($data['school_logo']);
        $this->assertNotNull($data['favicon']);
        $this->assertStringStartsWith('http', $data['school_logo']);
        $this->assertStringStartsWith('http', $data['favicon']);
    }

    public function test_default_branding_for_missing_school(): void
    {
        $response = $this->getJson('http://localhost/api/v1/branding?school_id=999999')
            ->assertOk();

        $data = $response->json('data');

        $this->assertNotNull($data['school_logo']);
        $this->assertNotNull($data['favicon']);
        $this->assertStringStartsWith('http', $data['school_logo']);
        $this->assertStringStartsWith('http', $data['favicon']);
    }

    public function test_missing_logo_returns_default_school_logo(): void
    {
        $school = $this->demoSchool();
        $school->forceFill(['logo_path' => null, 'settings' => null])->save();

        $response = $this->getJson('http://localhost/api/v1/branding?school_id='.$school->id)
            ->assertOk();

        $data = $response->json('data');

        $this->assertStringContainsString('/images/school-logo.png', $data['school_logo']);
        $this->assertStringStartsWith('http', $data['school_logo']);
    }

    public function test_missing_favicon_returns_default_favicon(): void
    {
        $school = $this->demoSchool();
        $school->forceFill(['logo_path' => null, 'settings' => null])->save();

        $response = $this->getJson('http://localhost/api/v1/branding?school_id='.$school->id)
            ->assertOk();

        $data = $response->json('data');

        $this->assertStringContainsString('/favicon.ico', $data['favicon']);
        $this->assertStringStartsWith('http', $data['favicon']);
    }

    public function test_school_assets_are_served_from_storage(): void
    {
        $school = $this->makeSchool([
            'logo_path' => 'settings/schools/logo-test.png',
            'settings' => ['school' => ['favicon_path' => 'settings/schools/favicon-test.ico']],
        ]);

        $response = $this->getJson('http://localhost/api/v1/branding?school_id='.$school->id)
            ->assertOk();

        $data = $response->json('data');

        $this->assertStringContainsString('/storage/settings/schools/logo-test.png', $data['school_logo']);
        $this->assertStringContainsString('/storage/settings/schools/favicon-test.ico', $data['favicon']);
    }

    public function test_production_uses_domain_host(): void
    {
        $school = $this->demoSchool();

        $response = $this->getJson('https://erp.example.com/api/v1/branding?school_id='.$school->id)
            ->assertOk();

        $data = $response->json('data');

        $this->assertStringStartsWith('https://erp.example.com/', $data['school_logo']);
        $this->assertStringStartsWith('https://erp.example.com/', $data['favicon']);
    }

    public function test_local_development_uses_request_host_ip(): void
    {
        $school = $this->makeSchool(['logo_path' => 'settings/schools/logo-local.png']);

        $response = $this->getJson('http://192.168.1.50:8000/api/v1/branding?school_id='.$school->id)
            ->assertOk();

        $data = $response->json('data');

        $this->assertStringStartsWith('http://192.168.1.50:8000/storage/settings/schools/logo-local.png', $data['school_logo']);
        $this->assertStringStartsWith('http://192.168.1.50:8000/', $data['favicon']);
    }

    public function test_image_urls_never_return_localhost(): void
    {
        $school = $this->makeSchool(['logo_path' => 'settings/schools/logo-dev.png']);

        $response = $this->getJson('http://localhost:8000/api/v1/branding?school_id='.$school->id)
            ->assertOk();

        $data = $response->json('data');

        foreach (['school_logo', 'favicon'] as $key) {
            $host = parse_url($data[$key], PHP_URL_HOST);

            $this->assertNotContains(strtolower((string) $host), ['localhost', '127.0.0.1'], $key.' must not use a localhost host.');
            $this->assertNotFalse(filter_var($host, FILTER_VALIDATE_IP), $key.' host must be a reachable IP.');
        }
    }

    public function test_x_school_id_header_is_supported(): void
    {
        $school = $this->demoSchool();

        $this->getJson('http://localhost/api/v1/branding', ['X-School-Id' => $school->id])
            ->assertOk()
            ->assertJsonPath('data.school_name', 'Demo Public School');
    }
}
