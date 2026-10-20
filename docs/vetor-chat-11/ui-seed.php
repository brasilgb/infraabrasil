<?php
require __DIR__.'/app/vendor/autoload.php';
$app = require __DIR__.'/app/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Http::preventStrayRequests();
$t = App\Models\Tenant::create(['name' => 'Empresa sintética CHAT11', 'active' => true]);
$u = App\Models\User::factory()->reader()->create(['name' => 'Operador sintético', 'email' => 'chat11@example.test', 'password' => Illuminate\Support\Facades\Hash::make('Local-Chat11-Test!')]);
App\Models\TenantMembership::create(['tenant_id' => $t->id, 'user_id' => $u->id, 'role' => 'owner']);
app(App\Services\TenantContext::class)->run($t, function () use ($t) {
    foreach(config('support_automation.knowledge') as $i => $template) {
        App\Models\SupportAutomationRule::create([...$template, 'channel' => 'web_chat', 'trigger' => 'keyword', 'action' => $i === 9 ? 'qualify' : ($i === 10 ? 'handoff' : 'reply'), 'subject' => $i === 10 ? 'suporte' : 'comercial', 'priority' => $i, 'active' => true]);
    }
    file_put_contents(__DIR__.'/fixture.json', json_encode(['tenant_id' => $t->id]));
});
echo "Fixture sintético criado.\n";
