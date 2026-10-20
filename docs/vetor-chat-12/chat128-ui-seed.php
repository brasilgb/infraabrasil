<?php
require '/opt/infra-abrasil/backups/chat128/vetorleads/vendor/autoload.php';
$app = require '/opt/infra-abrasil/backups/chat128/vetorleads/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Http::preventStrayRequests();
$tenant=App\Models\Tenant::create(['name'=>'CHAT128 laboratório sintético','active'=>true]);
$user=App\Models\User::factory()->create(['name'=>'Administrador CHAT128','email'=>'chat128@example.test','password'=>Illuminate\Support\Facades\Hash::make('Local-Chat128-Test!'),'email_verified_at'=>now()]);
App\Models\TenantMembership::create(['tenant_id'=>$tenant->id,'user_id'=>$user->id,'role'=>'owner']);
app(App\Services\TenantContext::class)->run($tenant,function(){
 App\Models\SupportAutomationRule::create(['name'=>'Planos do laboratório','channel'=>'both','trigger'=>'keyword','keywords'=>['planos'],'response'=>'Vamos conhecer sua empresa.','action'=>'qualify','subject'=>'comercial','priority'=>10,'active'=>false]);
 App\Models\SupportAutomationRule::create(['name'=>'Atendimento humano de teste','channel'=>'both','trigger'=>'keyword','keywords'=>['humano'],'response'=>'Transferência simulada.','action'=>'handoff','priority'=>1,'active'=>false]);
 App\Models\SupportAutomationRule::create(['name'=>'Resposta de teste','channel'=>'both','trigger'=>'keyword','keywords'=>['recursos'],'response'=>'Ordens e equipamentos no laboratório.','action'=>'reply','priority'=>20,'active'=>false]);
});
echo "Fixture sintético criado; regras inativas.\n";
