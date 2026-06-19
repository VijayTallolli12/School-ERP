<?php
// Per-file: any .DataTable( in @push scripts where file lacks await window.lazyDT in that block
$files = [];
foreach (['resources/views','app/Modules'] as $root) {
  $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
  foreach ($it as $f) {
    if (!$f->isFile() || !str_ends_with($f->getFilename(), '.blade.php')) continue;
    $p = str_replace('\\','/',$f->getPathname());
    if (str_contains($p,'/Modules/') && !preg_match('#/Modules/[^/]+/Views/#',$p)) continue;
    $files[] = $p;
  }
}
$bad = [];
foreach ($files as $path) {
  $c = file_get_contents($path);
  if (!str_contains($c,'.DataTable(')) continue;
  if (!preg_match_all('/@push\s*\(\s*[\'"]scripts[\'"]\s*\)(.*?)@endpush/s',$c,$m)) {
    if (str_contains($c,'.DataTable(')) $bad[] = "$path (DataTable outside @push scripts?)";
    continue;
  }
  foreach ($m[1] as $block) {
    if (!preg_match('/\.DataTable\s*\(/',$block)) continue;
    if (!preg_match('/await\s+window\.lazyDT\s*\(/',$block)) {
      $bad[] = $path;
      break;
    }
  }
}
echo implode("\n", array_unique($bad))."\n";
