<?php
// Operational side: PDO only, no Laravel boot, no files, no business events.
ini_set('display_errors', '0');
set_error_handler(function () { throw new RuntimeException('Audit PHP warning'); });
try {
    $tenantId = $argv[1] ?? '';
    if (!ctype_digit($tenantId) || (int)$tenantId < 1 || getenv('DB_DATABASE') !== 'vetorleads') throw new RuntimeException('Invalid explicit scope');
    $tenantId=(int)$tenantId;
    $pdo=new PDO('mysql:host='.getenv('DB_HOST').';port='.(getenv('DB_PORT')?:'3306').';dbname='.getenv('DB_DATABASE').';charset=utf8mb4',getenv('DB_USERNAME'),getenv('DB_PASSWORD'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false]);
    $pdo->exec('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
    $pdo->exec('SET SESSION TRANSACTION READ ONLY');
    $pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT');
    if ((int)$pdo->query('SELECT @@transaction_read_only')->fetchColumn() !== 1) throw new RuntimeException('No read-only guarantee');
    $select=function(string $sql,array $params=[])use($pdo){$q=$pdo->prepare($sql);$q->execute($params);return $q->fetchAll(PDO::FETCH_ASSOC);};
    $tenant=$select('SELECT id,active FROM tenants WHERE id=?',[$tenantId])[0]??null;
    if(!$tenant)throw new RuntimeException('Scope missing');
    $tables=['prospecting_results','prospecting_result_sources','prospecting_qualifications','prospecting_searches','prospecting_search_results','prospect_products','leads'];
    $fingerprint=function()use($select,$tables,$tenantId){$digest=hash_init('sha256');$counts=[];foreach($tables as $table){$after=0;$count=0;do{$rows=$select('SELECT * FROM '.$table.' WHERE tenant_id=? AND id>? ORDER BY id LIMIT 100',[$tenantId,$after]);foreach($rows as $row){hash_update($digest,$table.json_encode($row,JSON_THROW_ON_ERROR));$after=(int)$row['id'];$count++;}}while(count($rows)===100);$counts[$table]=$count;}return ['sha256'=>hash_final($digest),'counts'=>$counts];};
    $before=$fingerprint();
    echo json_encode(['type'=>'start','tenant'=>$tenant,'database'=>'vetorleads','read_only'=>true,'before'=>$before,'limit'=>500,'chunk'=>100,'context'=>'general_product_key_0','captured_at'=>gmdate('c')],JSON_THROW_ON_ERROR),"\n";
    $after=0;$count=0;
    do {
        $rows=$select('SELECT * FROM prospecting_results WHERE tenant_id=? AND id>? ORDER BY id LIMIT 100',[$tenantId,$after]);
        foreach($rows as $row){
            if($count>=500)break 2;
            $id=(int)$row['id'];
            $sources=$select('SELECT * FROM prospecting_result_sources WHERE tenant_id=? AND prospecting_result_id=? ORDER BY id',[$tenantId,$id]);
            $previous=$select('SELECT * FROM prospecting_qualifications WHERE tenant_id=? AND prospecting_result_id=? AND product_key=0 LIMIT 1',[$tenantId,$id])[0]??null;
            $search=$previous && $previous['prospecting_search_id'] ? ($select('SELECT * FROM prospecting_searches WHERE tenant_id=? AND id=?',[$tenantId,$previous['prospecting_search_id']])[0]??null) : null;
            echo json_encode(['type'=>'row','result'=>$row,'sources'=>$sources,'previous'=>$previous,'search'=>$search],JSON_THROW_ON_ERROR),"\n";
            $after=$id;$count++;
        }
    }while(count($rows)===100);
    $final=$fingerprint();
    $pdo->exec('COMMIT');
    echo json_encode(['type'=>'end','after'=>$final,'exported'=>$count,'unchanged'=>hash_equals($before['sha256'],$final['sha256'])],JSON_THROW_ON_ERROR),"\n";
} catch(Throwable $e){fwrite(STDERR,"Operational read-only export failed; no data details emitted.\n");exit(1);}
