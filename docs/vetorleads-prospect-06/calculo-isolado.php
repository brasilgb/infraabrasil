<?php
// Isolated side: raw rows exist only in this pipe and in memory; stdout contains aggregates only.
ini_set('display_errors','0');
require '/work/leads/vendor/autoload.php';
$app=require '/work/leads/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_error_handler(function(){throw new RuntimeException('Audit PHP warning');});
try{
    $report=['mode'=>'READ_ONLY_OPERATIONAL_DATA','context'=>'general_product_key_0','rules_version'=>App\Services\Prospecting\ProspectQualifier::RULES_VERSION,'qualifier_sha256'=>hash_file('sha256','/work/leads/app/Services/Prospecting/ProspectQualifier.php'),'analyzed'=>0,'failed'=>0,'priorities'=>array_fill_keys(['high','medium','low','insufficient'],0),'inventory'=>[],'previous_versions'=>[],'score_changes'=>['higher'=>0,'equal'=>0,'lower'=>0,'no_previous'=>0],'samples'=>[]];
    $salt=random_bytes(32);$finished=false;
    $hydrate=function(string $class,array $attrs){$model=new $class();$model->setRawAttributes($attrs,true);return $model;};
    while(($line=fgets(STDIN))!==false){
        $item=json_decode($line,true,512,JSON_THROW_ON_ERROR);
        if($item['type']==='start'){
            if($item['read_only']!==true)throw new RuntimeException('No read-only evidence');
            $tenant=$hydrate(App\Models\Tenant::class,$item['tenant']);app(App\Services\TenantContext::class)->set($tenant);
            $report['scope']=['tenant_id'=>$tenant->id,'product_key'=>0,'captured_at'=>$item['captured_at'],'limit'=>$item['limit'],'chunk'=>$item['chunk'],'before'=>$item['before']];
        }elseif($item['type']==='row'){
            $result=$hydrate(App\Models\ProspectingResult::class,$item['result']);
            $sources=array_map(fn($row)=>$hydrate(App\Models\ProspectingResultSource::class,$row),$item['sources']);
            $result->setRelation('sources',new Illuminate\Database\Eloquent\Collection($sources));
            $previous=$item['previous'] ? $hydrate(App\Models\ProspectingQualification::class,$item['previous']) : null;
            $search=$item['search'] ? $hydrate(App\Models\ProspectingSearch::class,$item['search']) : null;
            try{
                $preview=app(App\Services\Prospecting\ProspectQualificationPreview::class)->one($result,search:$search,previous:$previous);
                $report['analyzed']++;$report['priorities'][$preview['proposed']['priority']]++;
                foreach($preview['inventory'] as $key=>$value){$report['inventory'][$key]=($report['inventory'][$key]??0)+(int)$value;}
                $version=$preview['previous']['version']??'none';$report['previous_versions'][$version]=($report['previous_versions'][$version]??0)+1;
                $change=$preview['previous']===null ? 'no_previous' : ($preview['proposed']['score']>$preview['previous']['score'] ? 'higher' : ($preview['proposed']['score']<$preview['previous']['score'] ? 'lower' : 'equal'));$report['score_changes'][$change]++;
                if(count($report['samples'])<6){$report['samples'][]=['reference'=>substr(hash_hmac('sha256',(string)$result->id,$salt),0,12),'previous'=>$preview['previous'],'proposed'=>$preview['proposed']];}
            }catch(Throwable){$report['failed']++;}
        }elseif($item['type']==='end'){$finished=true;$report['evidence']=$item;}
    }
    if(!$finished || !$report['evidence']['unchanged'] || $report['failed']>0)throw new RuntimeException('Incomplete or inconsistent audit');
    $report['percentages']=array_map(fn($n)=>$report['analyzed'] ? round($n*100/$report['analyzed'],2) : 0,$report['priorities']);
    $report['complete']=$report['analyzed']===$report['scope']['before']['counts']['prospecting_results'];
    $report['peak_memory_bytes']=memory_get_peak_usage(true);
    echo json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),PHP_EOL;
}catch(Throwable){fwrite(STDERR,"Preview failed; no original records emitted or persisted.\n");exit(1);}
