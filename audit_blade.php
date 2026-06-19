<?php
$json = file_get_contents('routes.json');
$json = preg_replace('/^\xEF\xBB\xBF/', '', $json);
$data = json_decode($json, true);
if (!is_array($data)) {
    fwrite(STDERR, "JSON error: " . json_last_error_msg() . "\n");
    exit(1);
}
$registered = [];
foreach ($data as $r) {
    if (!empty($r['name'])) {
        $registered[$r['name']] = true;
    }
}

function collectBlades(string $root): array {
    $files = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
    foreach ($it as $f) {
        if (!$f->isFile() || !str_ends_with($f->getFilename(), '.blade.php')) continue;
        $path = str_replace('\\', '/', $f->getPathname());
        if (str_contains($root, 'app/Modules') || str_contains($root, 'app\\Modules')) {
            if (!preg_match('#/Modules/[^/]+/Views/#', $path)) continue;
        }
        $files[] = $path;
    }
    return $files;
}

$files = array_merge(
    collectBlades('resources/views'),
    collectBlades('app/Modules')
);
$files = array_unique($files);

$pattern = '/\broute\s*\(\s*([\'"])([^\'"]+)\1/s';
$bladeRoutes = [];
$dynamicRouteLines = [];

foreach ($files as $path) {
    $lines = file($path);
    $content = implode('', $lines);
    if (preg_match_all($pattern, $content, $m, PREG_OFFSET_CAPTURE)) {
        foreach ($m[2] as $match) {
            $name = $match[0];
            $bladeRoutes[$name][$path] = true;
        }
    }
    // dynamic route() first arg
    foreach ($lines as $num => $line) {
        if (preg_match('/\broute\s*\(\s*\$/', $line) || preg_match('/\broute\s*\(\s*[^\'"\$]/', $line)) {
            if (str_contains($line, 'route(')) {
                $dynamicRouteLines[] = ($num+1) . ':' . trim($line) . ' @ ' . $path;
            }
        }
    }
}

$missing = [];
foreach ($bladeRoutes as $name => $paths) {
    if (!isset($registered[$name])) {
        $missing[$name] = array_keys($paths);
    }
}
ksort($missing);

file_put_contents('routes.txt', implode("\n", array_keys($registered)) . "\n");

echo "REGISTERED=" . count($registered) . "\n";
echo "BLADE_FILES=" . count($files) . "\n";
echo "BLADE_ROUTE_LITERALS=" . count($bladeRoutes) . "\n";
echo "MISSING=" . count($missing) . "\n\n";

foreach ($missing as $name => $paths) {
    echo $name . "\n  " . implode("\n  ", $paths) . "\n";
}

// --- DataTable / lazyDT ---
$dtIssues = [];
foreach ($files as $path) {
    $content = file_get_contents($path);
    if (!str_contains($content, '.DataTable(')) continue;
    // split by script-ish regions: @push('scripts') ... @endpush
    if (preg_match_all('/@push\s*\(\s*[\'"]scripts[\'"]\s*\)(.*?)@endpush/s', $content, $blocks)) {
        foreach ($blocks[1] as $block) {
            if (!str_contains($block, '.DataTable(')) continue;
            $hasLazy = (bool)preg_match('/lazyDT\s*\(/', $block);
            if (!$hasLazy) {
                $dtIssues[] = ['file' => $path, 'issue' => 'DataTable in @push(scripts) without lazyDT() in same block'];
            }
        }
    }
    // also check inline script tags outside push
    if (preg_match_all('/<script[^>]*>(.*?)<\/script>/s', $content, $scripts)) {
        foreach ($scripts[1] as $block) {
            if (!str_contains($block, '.DataTable(')) continue;
            if (!preg_match('/lazyDT\s*\(/', $block)) {
                $dtIssues[] = ['file' => $path, 'issue' => 'DataTable in <script> without lazyDT() in same script block'];
            }
        }
    }
}
$dtIssues = array_unique(array_map('json_encode', array_map(fn($x)=>$x, $dtIssues)));
echo "\n=== DATATABLE_WITHOUT_LAZYDT ===\n";
foreach ($dtIssues as $j) {
    $x = json_decode($j, true);
    echo $x['file'] . " — " . $x['issue'] . "\n";
}

// --- brace balance in @push scripts ---
echo "\n=== SCRIPT_BRACE_ISSUES ===\n";
foreach ($files as $path) {
    if (!preg_match_all('/@push\s*\(\s*[\'"]scripts[\'"]\s*\)(.*?)@endpush/s', file_get_contents($path), $blocks)) continue;
    foreach ($blocks[1] as $bi => $block) {
        // strip blade/php for rough JS parse
        $js = preg_replace('/@\w+[^@]*/', '', $block);
        $js = preg_replace('/\{\{.*?\}\}/s', '""', $js);
        $js = preg_replace('/\{!!.*?!!\}/s', '""', $js);
        $open = substr_count($js, '{');
        $close = substr_count($js, '}');
        $openP = substr_count($js, '(');
        $closeP = substr_count($js, ')');
        if ($open !== $close || $openP !== $closeP) {
            echo $path . " block#" . ($bi+1) . " braces {$open}/{$close} parens {$openP}/{$closeP}\n";
        }
        // common typo: .DataTable( not inside async after lazyDT
        if (preg_match('/\.DataTable\s*\(/', $block) && preg_match('/await\s+window\.lazyDT\s*\(/', $block)) {
            // ok pattern exists somewhere
        }
    }
}
