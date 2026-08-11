<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Modules\Notifications\Models\Notification;
use App\Models\User;

echo "=== All Notifications ===\n";
$notifications = Notification::withTrashed()->get();
echo "Total: " . $notifications->count() . "\n";
foreach($notifications as $n) {
    echo "ID: ".$n->id.", Title: ".$n->title.", Type: ".$n->type.", Target: ".$n->target_type.", Status: ".$n->status.", School: ".$n->school_id.", Sent: ".($n->sent_at ?? 'No').", Users: ".$n->users()->count()."\n";
}

echo "\n=== Parent Users ===\n";
$parents = User::whereHas('roles', fn($q) => $q->where('name', 'Parent'))->get();
foreach($parents as $p) {
    echo "User ID: ".$p->id.", Name: ".$p->name.", Email: ".$p->email.", School: ".$p->current_school_id."\n";
    $guardian = $p->guardian;
    if ($guardian) {
        echo "  Guardian ID: ".$guardian->id.", UUID: ".$guardian->uuid.", School: ".$guardian->school_id."\n";
    }
}

echo "\n=== Notification_User Pivot ===\n";
$pivots = \DB::table('notification_user')->get();
foreach($pivots as $p) {
    echo "Notification: ".$p->notification_id.", User: ".$p->user_id.", Read: ".$p->is_read.", Status: ".$p->delivery_status."\n";
}