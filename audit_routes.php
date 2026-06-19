<?php
$registered = [];
$data = json_decode(file_get_contents('routes.json'), true);
foreach ($data as $r) {
    if (!empty($r['name'])) {
        foreach (explode("\n", trim($r['name'])) as $n) {
            $n = trim($n);
            if ($n !== '') $registered[$n] = true;
        }
    }
}

$dirs = ['resources/views', 'app/Modules'];
$bladeRoutes = []; // name => [files]
$pattern = '/\broute\s*\(\s*([\'"])([^\'"]+)\1/s';

$iterator = function ($dir) use (&$iterator, &$bladeRoutes, $pattern) {
    if (!is_dir($dir)) return;
    foreach (scandir($dir) as $f) {
        if ($f === '.' || $f === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $f;
        if (is_dir($path)) {
            if (str_ends_with($path, 'Views') || str_contains($path, 'views') || str_contains($path, 'Modules')) {
                $iterator($path);
            } else {
                $iterator($path);
            }
        } elseif (str_ends_with($f, '.blade.php')) {
            $content = file_get_contents($path);
            if (preg_match_all($pattern, $content, $m, PREG_OFFSET_CAPTURE)) {
                foreach ($m[2] as $match) {
                    $name = $match[0];
                    $rel = str_replace('\\', '/', $path);
                    $bladeRoutes[$name][$rel] = true;
                }
            }
        }
    }
};

foreach ($dirs as $d) $iterator($d);

$missing = [];
foreach ($bladeRoutes as $name => $files) {
    if (!isset($registered[$name])) {
        $missing[$name] = array_keys($files);
    }
}
ksort($missing);

echo "REGISTERED_COUNT=" . count($registered) . PHP_EOL;
echo "BLADE_ROUTE_NAMES=" . count($bladeRoutes) . PHP_EOL;
echo "MISSING_COUNT=" . count($missing) . PHP_EOL;
echo "---MISSING---" . PHP_EOL;
foreach ($missing as $name => $files) {
    echo $name . "\t" . implode(', ', $files) . PHP_EOL;
}
