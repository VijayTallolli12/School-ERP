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

        Log::info('Created storage directory.', ['directory' => $directory]);
    }

    $path = $file->store($directory, 'public');

    if (! is_string($path) || $path === '') {
        Log::error('Settings image upload failed.', [
            'directory' => $directory,
            'original_name' => $file->getClientOriginalName(),
            'disk' => 'public',
        ]);

        throw new RuntimeException('The image could not be saved. Please check the public storage disk.');
    }

    /*
    |--------------------------------------------------------------------------
    | Railway Fix
    |--------------------------------------------------------------------------
    | Copy uploaded file into public/storage manually because Railway
    | symlink support is unreliable with mounted volumes.
    |--------------------------------------------------------------------------
    */

    $source = storage_path('app/public/' . $path);

    $destination = public_path('storage/' . $path);

    if (! file_exists(dirname($destination))) {
        mkdir(dirname($destination), 0755, true);
    }

    copy($source, $destination);

    Log::info('Settings image uploaded.', [
        'path' => $path,
        'disk' => 'public',
        'public_destination' => $destination,
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