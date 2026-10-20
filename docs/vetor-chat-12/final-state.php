<?php
require '/opt/infra-abrasil/backups/chat12/app/vendor/autoload.php';
$app=require '/opt/infra-abrasil/backups/chat12/app/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$counts=[];foreach(['tenants','users','web_conversations','web_chat_messages','leads','lead_activities','support_automation_rules','support_automation_decisions','support_automation_outbox','support_whatsapp_receipts','support_automation_audits','commercial_proposals','prospecting_results','jobs','failed_jobs'] as $table){$counts[$table]=Illuminate\Support\Facades\DB::table($table)->count();}
$decisions=Illuminate\Support\Facades\DB::table('support_automation_decisions')->selectRaw('channel,outcome,count(*) as total')->groupBy('channel','outcome')->get();
$outbox=Illuminate\Support\Facades\DB::table('support_automation_outbox')->selectRaw('status,count(*) as total')->groupBy('status')->get();
echo json_encode(['database'=>'chat12_ui sintético','version'=>Illuminate\Support\Facades\DB::selectOne('select version() as version')->version,'counts'=>$counts,'decisions'=>$decisions,'outbox'=>$outbox],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),"\n";
