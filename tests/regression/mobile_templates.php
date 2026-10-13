<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$count = 0;
$failures = [];
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views'))) as $file) {
    if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) continue;
    try {
        token_get_all(app('blade.compiler')->compileString(file_get_contents($file->getPathname())), TOKEN_PARSE);
        $count++;
    } catch (Throwable $e) {
        $failures[] = ['file' => $file->getPathname(), 'error' => $e->getMessage()];
    }
}
echo json_encode(['compiled' => $count, 'failures' => $failures], JSON_PRETTY_PRINT).PHP_EOL;
if ($failures) exit(1);
