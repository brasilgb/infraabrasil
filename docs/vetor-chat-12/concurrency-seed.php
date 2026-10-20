<?php
require '/opt/infra-abrasil/backups/chat12/app/vendor/autoload.php';
$app=require '/opt/infra-abrasil/backups/chat12/app/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$tenant=App\Models\Tenant::create(['name'=>'CHAT12 concorrência sintética','active'=>true]);$tenant->forceFill(['whatsapp_automation_enabled'=>true,'whatsapp_automation_homologated_at'=>now()])->save();
$connection=App\Models\TenantWhatsappConnection::create(['tenant_id'=>$tenant->id,'session'=>'concurrency-synthetic','webhook_secret'=>'synthetic-secret','provisioned_at'=>now()]);
app(App\Services\TenantContext::class)->run($tenant,function(){
    foreach(['planos'=>'reply','qualificar'=>'qualify'] as $keyword=>$action) {
        App\Models\SupportAutomationRule::create(['name'=>$keyword,'channel'=>'both','trigger'=>'keyword','keywords'=>[$keyword],'response'=>'Resposta sintética','action'=>$action,'subject'=>'comercial','active'=>true,'priority'=>1]);
    }
});
file_put_contents('/opt/infra-abrasil/backups/chat12/concurrency-fixture.json',json_encode(['tenant_id'=>$tenant->id,'connection_id'=>$connection->id]));
