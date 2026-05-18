<?php

require __DIR__.'/railway-env.php';

$tables = array_slice($argv, 1);

if ($tables === []) {
    fwrite(STDERR, "[railway-start] No tables were provided for verification.\n");
    exit(1);
}

$pdo = new PDO(
    'mysql:host='.railway_env('DB_HOST').';port='.railway_env('DB_PORT', '3306').';dbname='.railway_env('DB_DATABASE').';charset=utf8mb4',
    railway_env('DB_USERNAME'),
    railway_env('DB_PASSWORD'),
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ],
);

foreach ($tables as $table) {
    $statement = $pdo->prepare('SHOW TABLES LIKE ?');
    $statement->execute([$table]);

    if ($statement->fetchColumn() === false) {
        fwrite(STDERR, "[railway-start] Required table missing after migration: {$table}\n");
        exit(1);
    }

    fwrite(STDOUT, "[railway-start] Verified table exists: {$table}\n");
}
