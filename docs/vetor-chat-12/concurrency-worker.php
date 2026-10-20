<?php
require '/opt/infra-abrasil/backups/chat12/app/vendor/autoload.php';
$app=require '/opt/infra-abrasil/backups/chat12/app/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['support_automation.whatsapp_delivery_enabled'=>true,'services.waha.tenant_enabled'=>true,'services.waha.base_url'=>'http://waha-simulated.test']);
Illuminate\Support\Facades\Http::preventStrayRequests();
$fixture=json_decode(file_get_contents('/opt/infra-abrasil/backups/chat12/concurrency-fixture.json'),true);
$tenant=App\Models\Tenant::findOrFail($fixture['tenant_id']);
app(App\Services\TenantContext::class)->run($tenant,function()use($argv,$fixture){
    $mode=$argv[1];$start=microtime(true);$result=null;
    if($mode==='receive') {
        $connection=App\Models\TenantWhatsappConnection::findOrFail($fixture['connection_id']);
        $result=Illuminate\Support\Facades\DB::transaction(fn()=>app(App\Services\SupportWhatsappConversation::class)->receive($connection,$argv[2],$argv[3],$argv[4],false));
    } elseif($mode==='deliver') {
        Illuminate\Support\Facades\Http::fake(function($request) {
            if(str_ends_with($request->url(),'/sendText')) {
                $file=fopen('/opt/infra-abrasil/backups/chat12/delivery-ledger.log','a');flock($file,LOCK_EX);fwrite($file,"simulated-send\n");flock($file,LOCK_UN);fclose($file);usleep(300000);
                return Illuminate\Support\Facades\Http::response(['id'=>'concurrency-provider-id']);
            }
            return Illuminate\Support\Facades\Http::response(['status'=>'WORKING']);
        });
        app(App\Services\SupportAutomationDelivery::class)->deliver((int)$argv[2]);
    } elseif($mode==='assume') {
        Illuminate\Support\Facades\DB::transaction(function()use($argv){
            $chat=App\Models\WebConversation::lockForUpdate()->findOrFail($argv[2]);
            file_put_contents('/opt/infra-abrasil/backups/chat12/operator-lock', 'locked');usleep(500000);
            $chat->forceFill(['automation_paused'=>true,'status'=>'in_progress'])->save();app(App\Services\SupportAutomation::class)->cancelPending($chat);
        });
    }
    echo json_encode(['mode'=>$mode,'result'=>$result,'start'=>$start,'end'=>microtime(true)]),"\n";
});
