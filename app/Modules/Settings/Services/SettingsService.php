<?php

namespace App\Modules\Settings\Services;

use App\Models\School;
use App\Modules\Settings\Repositories\SettingsRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class SettingsService
{
    public function __construct(private readonly SettingsRepositoryInterface $settings) {}

    public function currentSchool(): School
    {
        return $this->settings->currentSchool();
    }

    public function update(School $school, array $data): School
    {
        $currentSettings = $school->settings ?? [];

        $attributes = [
            'name' => $data['school']['name'],
            'address' => $data['school']['address'] ?? null,
            'phone' => $data['school']['phone'] ?? null,
            'email' => $data['school']['email'] ?? null,
            'timezone' => $data['system']['timezone'],
            'currency' => $data['system']['currency'],
            'date_format' => $data['system']['date_format'],
        ];

        if (isset($data['school']['logo']) && $data['school']['logo'] instanceof UploadedFile) {
            $attributes['logo_path'] = $this->storeImage($data['school']['logo'], 'settings/schools', $school->logo_path);
        }

        $settings = array_replace_recursive($currentSettings, [
            'school' => [
                'website' => $data['school']['website'] ?? null,
                'principal_name' => $data['school']['principal_name'] ?? null,
                'favicon_path' => Arr::get($currentSettings, 'school.favicon_path'),
            ],
            'academic' => [
                'current_academic_year_id' => $data['academic']['current_academic_year_id'] ?? null,
                'grading_system' => $data['academic']['grading_system'],
                'attendance' => [
                    'default_status' => $data['academic']['attendance']['default_status'],
                    'minimum_percentage' => $data['academic']['attendance']['minimum_percentage'],
                    'allow_late_marking' => (bool) ($data['academic']['attendance']['allow_late_marking'] ?? false),
                ],
            ],
            'email' => [
                'smtp_host' => $data['email']['smtp_host'] ?? null,
                'smtp_port' => $data['email']['smtp_port'] ?? null,
                'smtp_username' => $data['email']['smtp_username'] ?? null,
                'smtp_password' => Arr::get($currentSettings, 'email.smtp_password'),
                'smtp_encryption' => $data['email']['smtp_encryption'] ?? null,
            ],
            'payment' => [
                'razorpay' => [
                    'enabled' => (bool) ($data['payment']['razorpay']['enabled'] ?? false),
                    'key' => $data['payment']['razorpay']['key'] ?? null,
                    'secret' => Arr::get($currentSettings, 'payment.razorpay.secret'),
                ],
                'stripe' => [
                    'enabled' => (bool) ($data['payment']['stripe']['enabled'] ?? false),
                    'key' => $data['payment']['stripe']['key'] ?? null,
                    'secret' => Arr::get($currentSettings, 'payment.stripe.secret'),
                ],
            ],
        ]);

        if (isset($data['school']['favicon']) && $data['school']['favicon'] instanceof UploadedFile) {
            $settings['school']['favicon_path'] = $this->storeImage($data['school']['favicon'], 'settings/schools', Arr::get($currentSettings, 'school.favicon_path'));
        }

        if (! empty($data['email']['smtp_password'])) {
            $settings['email']['smtp_password'] = $data['email']['smtp_password'];
        }

        if (! empty($data['payment']['razorpay']['secret'])) {
            $settings['payment']['razorpay']['secret'] = $data['payment']['razorpay']['secret'];
        }

        if (! empty($data['payment']['stripe']['secret'])) {
            $settings['payment']['stripe']['secret'] = $data['payment']['stripe']['secret'];
        }

        return $this->settings->update($school, $attributes, $settings);
    }

    private function storeImage(UploadedFile $file, string $directory, ?string $oldPath = null): string
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($directory)) {
            if (! $disk->makeDirectory($directory)) {
                Log::error('Settings image upload failed: could not create directory.', [
                    'directory' => $directory,
                    'disk_root' => config('filesystems.disks.public.root'),
                ]);

                throw new RuntimeException('The storage directory could not be created. Please check filesystem permissions.');
            }

            Log::info('Created storage directory.', [
                'directory' => $directory,
            ]);
        }

        $path = $file->store($directory, 'public');

        if (! is_string($path) || $path === '') {
            Log::error('Settings image upload failed.', [
                'directory' => $directory,
                'original_name' => $file->getClientOriginalName(),
                'disk' => 'public',
            ]);

            throw new RuntimeException('The image could not be saved.');
        }

        /*
        |--------------------------------------------------------------------------
        | Railway Fix
        |--------------------------------------------------------------------------
        */

        $source = storage_path('app/public/' . $path);

        $destination = public_path('storage/' . $path);

        if (! file_exists(dirname($destination))) {
            mkdir(dirname($destination), 0755, true);
        }

        copy($source, $destination);

        Log::info('Image copied to public storage.', [
            'source' => $source,
            'destination' => $destination,
        ]);

        if ($oldPath && $disk->exists($oldPath)) {

            $disk->delete($oldPath);

            $oldPublicFile = public_path('storage/' . $oldPath);

            if (file_exists($oldPublicFile)) {
                unlink($oldPublicFile);
            }
        }

        return $path;
    }
}
