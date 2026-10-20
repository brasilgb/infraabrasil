<?php

use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Models\User;
use App\Support\TestDatabaseSafety;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../../../gateway/vetorleads/vendor/autoload.php';
$app = require __DIR__.'/../../../gateway/vetorleads/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$connectionName = (string) config('database.default');
$connection = (array) config("database.connections.{$connectionName}", []);
TestDatabaseSafety::assertSafe([
    'APP_ENV' => $app->environment(),
    'DB_CONNECTION' => $connection['driver'] ?? null,
    'DB_DATABASE' => $connection['database'] ?? null,
    'DB_HOST' => $connection['host'] ?? null,
    'DB_USERNAME' => $connection['username'] ?? null,
    'DB_URL' => $connection['url'] ?? null,
]);

if (($connection['driver'] ?? null) !== 'sqlite') {
    throw new RuntimeException('The demo fixture requires isolated SQLite.');
}

$password = getenv('CHAT125_TEST_PASSWORD');
if (! is_string($password) || $password === '') {
    throw new RuntimeException('Set CHAT125_TEST_PASSWORD for the synthetic accounts.');
}

DB::transaction(function () use ($password): void {
    $root = User::query()->firstOrCreate(['email' => 'root-chat125@example.test'], [
        'name' => 'CHAT-12.5 Root Demo',
        'password' => $password,
        'role' => 'admin',
    ]);
    $root->forceFill(['is_root_admin' => true, 'active' => true, 'role' => 'admin', 'password' => $password])->save();

    $tenant = Tenant::query()->firstOrCreate(['name' => 'CHAT-12.5 Synthetic Tenant'], ['active' => true]);
    $owner = User::query()->firstOrCreate(['email' => 'owner-chat125@example.test'], [
        'name' => 'CHAT-12.5 Tenant Demo',
        'password' => $password,
        'role' => 'admin',
    ]);
    $owner->forceFill(['active' => true, 'role' => 'admin', 'password' => $password])->save();
    TenantMembership::query()->firstOrCreate(['tenant_id' => $tenant->id, 'user_id' => $owner->id], ['role' => 'owner']);

    $hasRule = DB::table('support_automation_rules')->where('tenant_id', $tenant->id)->where('name', 'CHAT-12.5 Demo Draft')->exists();
    if (! $hasRule) {
        DB::table('support_automation_rules')->insert([
            'tenant_id' => $tenant->id,
            'channel' => 'web_chat',
            'name' => 'CHAT-12.5 Demo Draft',
            'trigger' => 'keywords',
            'keywords' => json_encode(['teste'], JSON_THROW_ON_ERROR),
            'response' => 'Resposta sintética de homologação.',
            'action' => 'reply',
            'subject' => 'comercial',
            'priority' => 100,
            'active' => false,
            'schedule' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    echo json_encode(['root_id' => $root->id, 'tenant_id' => $tenant->id, 'owner_id' => $owner->id]), PHP_EOL;
});
