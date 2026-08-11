<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== model_has_roles ===\n";
$roles = \DB::table('model_has_roles')->get();
foreach($roles as $r) {
    echo "Role ID: ".$r->role_id.", Model ID: ".$r->model_id.", Model Type: ".$r->model_type.", School: ".$r->school_id."\n";
}

echo "\n=== roles table ===\n";
$roles = \DB::table('roles')->get();
foreach($roles as $r) {
    echo "Role ID: ".$r->id.", Name: ".$r->name.", Guard: ".$r->guard_name.", School: ".$r->school_id."\n";
}