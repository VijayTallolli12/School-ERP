<?php

require __DIR__.'/railway-env.php';

$required = [
    'APP_KEY',
    'APP_URL',
    'DB_DATABASE',
    'DB_USERNAME',
    'DB_PASSWORD',
];

foreach ($required as $key) {
    $value = railway_env($key);

    if ($value === null || $value === '') {
        fwrite(STDERR, "[railway-start] {$key} is missing.\n");
        exit(1);
    }
}

$expected = [
    'DB_CONNECTION' => 'mysql',
    'DB_HOST' => 'mysql.railway.internal',
    'DB_PORT' => '3306',
];

foreach ($expected as $key => $expectedValue) {
    $actual = railway_env($key);

    if ($actual !== $expectedValue) {
        fwrite(STDERR, "[railway-start] {$key} must be {$expectedValue}; current value is ".($actual ?: '[empty]').".\n");
        exit(1);
    }
}

fwrite(STDOUT, "[railway-start] APP_KEY present and Railway MySQL variables look correct.\n");
