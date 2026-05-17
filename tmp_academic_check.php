<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use App\Models\AcademicYear;
use App\Models\User;
$user = User::find(1);
if ($user) {
    echo 'user_id=' . $user->id . ' school_id=' . $user->school_id . PHP_EOL;
    foreach (AcademicYear::where('school_id', $user->school_id)->get() as $year) {
        echo $year->id . ' ' . $year->name . ' ' . $year->school_id . PHP_EOL;
    }
} else {
    echo 'no user found' . PHP_EOL;
}
