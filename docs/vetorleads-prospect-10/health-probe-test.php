<?php
require '/health.php';
$checks = [
    'worker and database available' => vetorleadsProcessHealthy(['php', 'artisan', 'queue:work', 'database'], 'queue:work', fn () => true),
    'scheduler and database available' => vetorleadsProcessHealthy(['/usr/local/bin/php', '/var/www/html/artisan', 'schedule:work'], 'schedule:work', fn () => true),
    'worker rejects database failure' => ! vetorleadsProcessHealthy(['php', 'artisan', 'queue:work'], 'queue:work', fn () => throw new RuntimeException('unavailable')),
    'scheduler rejects database failure' => ! vetorleadsProcessHealthy(['php', 'artisan', 'schedule:work'], 'schedule:work', fn () => false),
    'php-fpm is not a worker' => ! vetorleadsProcessHealthy(['php-fpm', '-F'], 'queue:work', fn () => true),
    'scheduler is not a worker' => ! vetorleadsProcessHealthy(['php', 'artisan', 'schedule:work'], 'queue:work', fn () => true),
    'rejects substring matches' => ! vetorleadsProcessHealthy(['php', 'artisan', 'queue:work-foreign'], 'queue:work', fn () => true),
    'rejects arbitrary commands' => ! vetorleadsProcessHealthy(['php', 'artisan', 'migrate'], 'migrate', fn () => true),
];
foreach ($checks as $name => $ok) {
    echo ($ok ? 'PASS ' : 'FAIL ').$name.PHP_EOL;
}
exit(in_array(false, $checks, true) ? 1 : 0);
