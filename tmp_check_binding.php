<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
try {
    $result = $app->make(App\Modules\Exams\Repositories\ExamRepositoryInterface::class);
    var_dump($result instanceof App\Modules\Exams\Repositories\ExamRepositoryInterface);
} catch (Throwable $e) {
    echo get_class($e) . ': ' . $e->getMessage();
}
