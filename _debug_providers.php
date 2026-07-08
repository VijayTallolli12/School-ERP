<?php

echo "=== SERVICE PROVIDERS CHECK ===\n\n";

echo "1. Check if AppServiceProvider is loaded:\n";
$appLoaded = app()->providerIsLoaded('App\Providers\AppServiceProvider');
echo "   AppServiceProvider loaded: " . ($appLoaded ? 'YES' : 'NO') . "\n";

echo "\n2. Check if Spatie's PermissionServiceProvider is loaded:\n";
$spatieLoaded = app()->providerIsLoaded('Spatie\Permission\PermissionServiceProvider');
echo "   PermissionServiceProvider loaded: " . ($spatieLoaded ? 'YES' : 'NO') . "\n";

echo "\n3. Check PermissionRegistrar singleton:\n";
$registrar1 = app(Spatie\Permission\PermissionRegistrar::class);
$registrar2 = app(Spatie\Permission\PermissionRegistrar::class);
echo "   Same instance: " . ($registrar1 === $registrar2 ? 'YES' : 'NO') . "\n";

echo "\n4. Set team ID and verify it persists:\n";
$registrar1->setPermissionsTeamId(999);
echo "   Instance 1 team ID: " . $registrar1->getPermissionsTeamId() . "\n";
echo "   Instance 2 team ID: " . $registrar2->getPermissionsTeamId() . "\n";

echo "\n5. List loaded providers:\n";
$providers = app()->getLoadedProviders();
foreach ($providers as $provider => $loaded) {
    if ($loaded && (str_contains($provider, 'Spatie') || str_contains($provider, 'App\\Providers'))) {
        echo "   - $provider\n";
    }
}

echo "\n6. Check all registered service providers:\n";
$allProviders = app()->getProviders(\Illuminate\Support\ServiceProvider::class);
foreach ($allProviders as $p) {
    $class = get_class($p);
    if (str_contains($class, 'Spatie') || str_contains($class, 'App\\Providers')) {
        echo "   - $class\n";
    }
}

echo "\n7. Test Gate before callbacks:\n";
$gate = app(\Illuminate\Contracts\Auth\Access\Gate::class);
$reflection = new ReflectionClass($gate);
$prop = $reflection->getProperty('beforeCallbacks');
$prop->setAccessible(true);
$callbacks = $prop->getValue($gate);
echo "   Number of before callbacks: " . count($callbacks) . "\n";
foreach ($callbacks as $i => $cb) {
    $closureRef = new ReflectionFunction($cb);
    $closureFile = $closureRef->getFileName();
    $closureLine = $closureRef->getStartLine();
    echo "   Callback $i: $closureFile:$closureLine\n";
}

echo "\n8. What does app()->version() return?\n";
echo "   Version: " . app()->version() . "\n";

echo "\n9. Check if bootstrap/app.php is being used:\n";
echo "   base_path: " . base_path() . "\n";
echo "   providers defined in bootstrap/app.php: \n";
$appContent = file_get_contents(base_path('bootstrap/app.php'));
if (preg_match('/withProviders\(([^)]+)\)/s', $appContent, $matches)) {
    echo "   Content: " . substr($matches[0], 0, 200) . "\n";
}
