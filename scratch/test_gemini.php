<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Gemini\Laravel\Facades\Gemini;

try {
    $res = Gemini::generativeModel('gemini-2.5-flash')->generateContent('Say hello in 3 words');
    echo "SUCCESS: " . ($res->candidates[0]->content->parts[0]->text ?? 'empty') . "\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
