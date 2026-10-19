<?php

/** Read-only probe for queue/scheduler containers; no Laravel bootstrap or job execution. */
function vetorleadsProcessHealthy(array $arguments, string $command, callable $databaseCheck): bool
{
    if (! in_array($command, ['queue:work', 'schedule:work'], true)) {
        return false;
    }
    if (count($arguments) < 3 || basename($arguments[0]) !== 'php'
        || basename($arguments[1]) !== 'artisan' || $arguments[2] !== $command) {
        return false;
    }

    try {
        return $databaseCheck() === true;
    } catch (Throwable) {
        return false;
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $arguments = explode("\0", rtrim((string) @file_get_contents('/proc/1/cmdline'), "\0"));
    $healthy = vetorleadsProcessHealthy($arguments, $argv[1] ?? '', static function (): bool {
        $host = getenv('DB_HOST');
        $port = getenv('DB_PORT') ?: '3306';
        $database = getenv('DB_DATABASE');
        if (! $host || ! $database || ! ctype_digit($port)) {
            return false;
        }
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", getenv('DB_USERNAME'), getenv('DB_PASSWORD'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 2,
        ]);
        $pdo->exec('SET SESSION TRANSACTION READ ONLY');
        $pdo->beginTransaction();
        try {
            return (int) $pdo->query('SELECT 1')->fetchColumn() === 1;
        } finally {
            $pdo->rollBack();
        }
    });
    exit($healthy ? 0 : 1);
}
