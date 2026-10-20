<?php
require '/opt/infra-abrasil/backups/chat12/app/vendor/autoload.php';
$app=require '/opt/infra-abrasil/backups/chat12/app/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Http::preventStrayRequests();
$tenant=App\Models\Tenant::create(['name'=>'CHAT12 homologação sintética','active'=>true]);
$user=App\Models\User::factory()->reader()->create(['name'=>'Operador sintético CHAT12','email'=>'chat12@example.test','password'=>Illuminate\Support\Facades\Hash::make('Local-Chat12-Test!')]);
App\Models\TenantMembership::create(['tenant_id'=>$tenant->id,'user_id'=>$user->id,'role'=>'owner']);
$connection=App\Models\TenantWhatsappConnection::create(['tenant_id'=>$tenant->id,'session'=>'ui-synthetic-session','webhook_secret'=>'synthetic-webhook-secret','provisioned_at'=>now(),'state'=>'connected']);
app(App\Services\TenantContext::class)->run($tenant,function(){
    $product=App\Models\ProspectProduct::create(['name'=>'VetorOS sintético','active'=>true]);
    App\Models\SupportAutomationRule::create(['name'=>'Planos compartilhados','channel'=>'both','trigger'=>'keyword','keywords'=>['planos','demonstracao'],'response'=>'Vamos conhecer sua empresa.','action'=>'qualify','subject'=>'comercial','priority'=>1,'active'=>true,'interest_product_id'=>$product->id]);
    App\Models\SupportAutomationRule::create(['name'=>'Suporte humano','channel'=>'both','trigger'=>'keyword','keywords'=>['suporte'],'response'=>'A equipe de suporte dará continuidade ao atendimento.','action'=>'handoff','subject'=>'suporte','priority'=>2,'active'=>true]);
    App\Models\SupportAutomationRule::create(['name'=>'Boas-vindas','channel'=>'both','trigger'=>'welcome','keywords'=>[],'response'=>'Bem-vindo ao atendimento. Como podemos ajudar?','action'=>'reply','priority'=>100,'active'=>true]);
});
file_put_contents('/opt/infra-abrasil/backups/chat12/ui-fixture.json',json_encode(['tenant_id'=>$tenant->id,'session'=>$connection->session]));
echo "Fixture sintético CHAT12 criado.\n";
