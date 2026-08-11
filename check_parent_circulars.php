<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Modules\Notifications\Models\Notification;
use App\Core\Tenant\SchoolContext;

app(SchoolContext::class)->set(1);

$user = User::find(9);
echo "User: ".$user->name." (ID: ".$user->id.")";
echo " Roles: ".$user->getRoleNames()->implode(', ');
echo " School: ".$user->current_school_id;
echo PHP_EOL;

$notifications = Notification::query()
    ->whereIn('target_type', ['parents', 'students', 'all'])
    ->where('type', 'announcement')
    ->where('status', 'sent')
    ->orderByDesc('id')
    ->get();

echo "Found ".$notifications->count()." notifications".PHP_EOL;
foreach($notifications as $n) {
    echo "ID: ".$n->id.", Title: ".$n->title.", Target: ".$n->target_type.", Users: ".$n->users()->count();
    echo " Has User 9: ".($n->users()->where('user_id', 9)->exists() ? 'YES' : 'NO');
    echo PHP_EOL;
}