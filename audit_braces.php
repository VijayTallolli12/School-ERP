<?php
$list = [
'resources/views/modules/attendance/index.blade.php',
'resources/views/modules/fees/index.blade.php',
'resources/views/modules/parents/index.blade.php',
'resources/views/modules/reports/absent_students/index.blade.php',
'resources/views/modules/reports/attendance/index.blade.php',
'resources/views/modules/reports/students/gender_wise.blade.php',
'resources/views/modules/reports/teachers/workload.blade.php',
'resources/views/modules/dashboard/index.blade.php',
'app/Modules/Reports/Views/exams/pass_fail_analysis.blade.php',
];
foreach ($list as $f) {
  $c = file_get_contents($f);
  if (!preg_match("/@push\s*\(\s*['\"]scripts['\"]\s*\)(.*?)@endpush/s", $c, $m)) {
    echo "$f: no scripts push\n";
    continue;
  }
  $js = $m[1];
  $js = preg_replace('/<script[^>]*>|<\/script>/i', '', $js);
  $js = preg_replace('/\{\{[\s\S]*?\}\}/', '0', $js);
  $js = preg_replace('/\{!![\s\S]*?!!\}/', '0', $js);
  $js = preg_replace('/@can\([\s\S]*?@endcan/', '', $js);
  $js = preg_replace('/@json\([^)]*\)/', '0', $js);
  $open=substr_count($js,'{'); $close=substr_count($js,'}');
  $op=substr_count($js,'('); $cp=substr_count($js,')');
  $ok = ($open==$close && $op==$cp) ? 'balanced' : 'MISMATCH';
  echo basename(dirname($f)).'/'.basename($f)." $ok braces $open/$close parens $op/$cp\n";
}
