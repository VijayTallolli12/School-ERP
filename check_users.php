<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;

echo "=== All Users ===\n";
$users = User::with('roles')->get();
foreach($users as $u) {
    $roles = $u->roles->pluck('name')->implode(', ');
    echo "User ID: ".$u->id.", Name: ".$u->name.", Email: ".$u->email.", Roles: ".$roles.", School: ".$u->current_school_id."\n";
}

echo "\n=== Guardians ===\n";
$guardians = \App\Modules\Parents\Models\Guardian::with('user')->get();
foreach($guardians as $g) {
    echo "Guardian ID: ".$g->id.", UUID: ".$g->uuid.", Name: ".$g->first_name." ".$g->last_name.", User: ".($g->user_id ?? 'none').", School: ".$g->school_id.", Students: ".$g->students_count."\n";
}

echo "\n=== Students ===\n";
$students = \App\Modules\Students\Models\Student::with('currentSession.classSection.schoolClass', 'currentSession.classSection.section')->get();
foreach($students as $s) {
    echo "Student ID: ".$s->id.", Name: ".$s->name.", UUID: ".$s->uuid.", Class: ".($s->currentSession?->classSection?->schoolClass?->name ?? 'none')." ".($s->currentSession?->classSection?->section?->name ?? 'none').", Guardian: ".($s->guardians->first()?->first_name ?? 'none')."\n";
}