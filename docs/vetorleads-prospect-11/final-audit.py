import subprocess as s, pathlib as p, json, hashlib, datetime
root=p.Path('/opt/infra-abrasil');out=root/'docs/vetorleads-prospect-11';private=root/'backups/prospect11'
def run(args,data=None):
 r=s.run(args,input=data,capture_output=True)
 if r.returncode:raise RuntimeError(str(args[:4])+r.stderr.decode()[-800:])
 return r.stdout
def save(name,obj):(out/name).write_text(json.dumps(obj,indent=2)+'\n')
php='''$r=[];foreach(['app','config','routes','resources','database/migrations'] as $dir){foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS)) as $f){if($f->isFile())$r[$f->getPathname()]=hash_file('sha256',$f->getPathname());}}foreach(['composer.lock','package-lock.json'] as $f)$r[$f]=hash_file('sha256',$f);echo json_encode($r);'''
deployed=json.loads(run(['docker','exec','infra-abrasil-vetorleads-1','php','-r',php]))
diff=[f for f,h in deployed.items() if hashlib.sha256((root/'staging/prospect10/app'/f).read_bytes()).hexdigest()!=h]
assert not diff,diff
save('source-parity.json',{'files_compared':len(deployed),'directories':['app','config','routes','resources','database/migrations'],'lockfiles':['composer.lock','package-lock.json'],'different_files':diff,'scope':'imagem publicada versus fontes usadas no teste isolado'})
before=json.loads((out/'services-before.json').read_text()); current=json.loads(run(['docker','inspect']+run(['docker','ps','-q']).decode().split()))
final=[{'name':x['Name'],'id':x['Id'],'image':x['Image'],'restart':x['RestartCount'],'started':x['State']['StartedAt'],'health':x['State'].get('Health',{}).get('Status')} for x in current]
targets=['/infra-abrasil-'+t+'-1' for t in ['vetorleads','vetorleads-worker','vetorleads-scheduler']]
for old in before:
 cur=next(x for x in final if x['name']==old['name'])
 if old['name'] not in targets:assert cur['id']==old['id'] and cur['restart']==old['restart'] and cur['started']==old['state']['StartedAt']
 else:assert cur['restart']==0 and cur['health']=='healthy' and cur['image']=='sha256:4d9deab99bb7ab4d2ffbe660983cb668ca34a931ae34cd4e982d957d8181a733'
save('services-final.json',{'observed_at':datetime.datetime.now(datetime.timezone.utc).isoformat(),'services':final,'other_containers_unchanged':True})
oom=s.run(['journalctl','-k','-b','--no-pager','-g','oom|Out of memory|Killed process'],capture_output=True);assert oom.returncode in [0,1] and 'Killed process' not in oom.stdout.decode()
save('memory-final.json',{'observed_at':datetime.datetime.now(datetime.timezone.utc).isoformat(),'memory':run(['free','-b']).decode(),'uptime':run(['uptime']).decode(),'oom_current_boot':oom.stdout.decode(),'disk':run(['df','-h','/','/tmp','/run']).decode()})
for name in ['vetorleads','vetorleads-worker','vetorleads-scheduler','mysql']:
 r=s.run(['docker','logs','--since','2026-10-19T12:39:00Z','infra-abrasil-'+name+'-1'],capture_output=True);(private/(name+'-final.log')).write_bytes(r.stdout+r.stderr)
php="foreach(glob('storage/logs/*.log') as $f){foreach(file($f) as $l){if(preg_match('/^\\[2026-10-19 (1[3-9]|12:([4-5][0-9]|39)):/',$l)&&preg_match('/ERROR|CRITICAL|EMERGENCY/',$l))echo $l;}}"
errors=run(['docker','exec','infra-abrasil-vetorleads-1','php','-r',php]);(private/'laravel-errors-after-publication.log').write_bytes(errors);assert not errors
save('application-log-check.json',{'errors_since_publication':0,'private_log_path':str(private/'laravel-errors-after-publication.log')})
print('Paridade de fontes, serviços compartilhados, memória e logs finais aprovados.')
