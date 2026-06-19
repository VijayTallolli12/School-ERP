<?php
$files = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('resources/views'));
foreach ($it as $f) {
    if ($f->isFile() && str_ends_with($f->getFilename(), '.blade.php')) $files[] = $f->getPathname();
}
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app/Modules'));
foreach ($it as $f) {
    if (!$f->isFile() || !str_ends_with($f->getFilename(), '.blade.php')) continue;
    $p = str_replace('\\','/',$f->getPathname());
    if (!preg_match('#/Modules/[^/]+/Views/#', $p)) continue;
    $files[] = $f->getPathname();
}
$issues = [];
foreach ($files as $path) {
    $c = file_get_contents($path);
    if (!str_contains($c, '.DataTable(')) continue;
    if (!preg_match_all('/@push\s*\(\s*[\'"]scripts[\'"]\s*\)(.*?)@endpush/s', $c, $m)) continue;
    foreach ($m[1] as $block) {
        if (!preg_match('/\.DataTable\s*\(/', $block)) continue;
        // each DataTable call - check preceding 500 chars for await window.lazyDT
        $offset = 0;
        while (preg_match('/\.DataTable\s*\(/', $block, $dm, PREG_OFFSET_CAPTURE, $offset)) {
            $pos = $dm[0][1];
            $before = substr($block, max(0, $pos - 800), $pos);
            $fnStart = strrpos($before, 'function');
            $asyncStart = strrpos($before, 'async');
            $lazyInScope = (bool)preg_match('/await\s+window\.lazyDT\s*\(|await\s+lazyDT\s*\(/', $before);
            if (!$lazyInScope) {
                $line = substr_count(substr($block, 0, $pos), "\n") + 1;
                $issues[] = str_replace('\\','/',$path) . ":@push scripts ~line $line .DataTable( without await lazyDT in preceding scope";
            }
            $offset = $pos + 1;
        }
    }
}
echo implode("\n", array_unique($issues)) . "\n";
echo "COUNT=" . count(array_unique($issues)) . "\n";
