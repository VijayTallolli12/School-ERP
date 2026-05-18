<?php

require __DIR__.'/railway-env.php';

$host = railway_env('DB_HOST');
$port = railway_env('DB_PORT', '3306');
$database = railway_env('DB_DATABASE');
$username = railway_env('DB_USERNAME');
$password = railway_env('DB_PASSWORD');
$deadline = time() + 60;
$attempt = 1;
$lastError = null;

while (time() <= $deadline) {
    try {
        new PDO(
            "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ],
        );

        fwrite(STDOUT, "[railway-start] MySQL connection ready.\n");
        exit(0);
    } catch (Throwable $exception) {
        $lastError = $exception->getMessage();
        fwrite(STDOUT, "[railway-start] MySQL not ready yet; attempt {$attempt}.\n");
        $attempt++;
        sleep(2);
    }
}

fwrite(STDERR, "[railway-start] MySQL did not become ready: {$lastError}\n");
exit(1);
