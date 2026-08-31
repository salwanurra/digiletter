<?php

// public/_fix-views.php — DELETE THIS FILE IMMEDIATELY AFTER USE
// One-shot script to clear corrupted compiled views and rebuild caches.
// Visit: https://digiletter.marooners.my.id/_fix-views.php?token=YOUR_TOKEN
// Or just: https://digiletter.marooners.my.id/_fix-views.php (no token required for this one-time use)

if (PHP_SAPI !== 'cli' && !isset($_GET['confirm'])) {
    http_response_code(403);
    exit('Add ?confirm=1 to the URL to execute.');
}

chdir(__DIR__ . '/..');

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

header('Content-Type: text/plain; charset=utf-8');

$viewsDir = __DIR__ . '/../storage/framework/views';
$cleared = 0;
$skipped = 0;

echo "=== Clearing corrupted compiled views ===\n";

if (is_dir($viewsDir)) {
    foreach (new DirectoryIterator($viewsDir) as $file) {
        if ($file->isDot()) continue;
        $name = $file->getFilename();

        if ($name === '.gitignore') {
            echo "  SKIP: .gitignore\n";
            $skipped++;
            continue;
        }

        if (substr($name, -4) !== '.php') {
            echo "  SKIP: $name (not .php)\n";
            $skipped++;
            continue;
        }

        if (@unlink($file->getPathname())) {
            echo "  DELETED: $name\n";
            $cleared++;
        } else {
            echo "  FAILED: $name\n";
        }
    }
}

echo "\nDeleted $cleared file(s), skipped $skipped.\n";

echo "\n=== Running artisan cache commands ===\n";

foreach (['view:clear', 'cache:clear', 'config:clear', 'route:clear'] as $cmd) {
    try {
        $kernel->call($cmd);
        $output = trim($kernel->output());
        echo "OK: php artisan $cmd\n";
        if ($output !== '') {
            echo "    -> " . preg_replace('/\s+/', ' ', substr($output, 0, 200)) . "\n";
        }
    } catch (\Throwable $e) {
        echo "FAIL: php artisan $cmd -> " . htmlspecialchars($e->getMessage()) . "\n";
    }
}

echo "\n=== Diagnostic info ===\n";
echo "APP_ENV: " . (config('app.env') ?: '(empty)') . "\n";
echo "APP_URL: " . (config('app.url') ?: '(empty)') . "\n";
echo "MAIL_FROM_ADDRESS: " . (config('mail.from.address') ?: '(empty)') . "\n";
echo "MAIL_MAILER: " . (config('mail.default') ?: '(empty)') . "\n";
echo "MAIL_HOST: " . (config('mail.mailers.smtp.host') ?: '(empty)') . "\n";
echo "MAIL_PORT: " . (config('mail.mailers.smtp.port') ?: '(empty)') . "\n";
echo "MAIL_USERNAME: " . (config('mail.mailers.smtp.username') ?: '(empty)') . "\n";
echo "MAIL_ENCRYPTION: " . (config('mail.mailers.smtp.encryption') ?: '(empty)') . "\n";

echo "\nbcrypt dir writable: " . (is_writable(__DIR__ . '/../bootstrap/cache') ? 'YES' : 'NO') . "\n";
echo "storage writable:    " . (is_writable(__DIR__ . '/../storage') ? 'YES' : 'NO') . "\n";
echo "views dir writable:  " . (is_writable($viewsDir) ? 'YES' : 'NO') . "\n";

echo "\n=== DONE ===\n";
echo "\n*** NOW DELETE THIS FILE (_fix-views.php) FROM production IMMEDIATELY ***\n";
