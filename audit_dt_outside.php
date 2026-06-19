<?php
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
foreach ($files as $path) {
  $c = file_get_contents($path);
  if (!str_contains($c,'.DataTable(')) continue;
  // remove @push scripts blocks
  $outside = preg_replace('/@push\s*\(\s*[\'"]scripts[\'"]\s*\).*?@endpush/s','',$c);
  if (!preg_match('/\.DataTable\s*\(/',$outside)) continue;
  if (preg_match('/await\s+window\.lazyDT\s*\(/',$outside)) continue;
  echo "$path has .DataTable outside @push(scripts) without lazyDT nearby\n";
}
