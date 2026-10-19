import subprocess as s, pathlib as p, json, time, hashlib
root=p.Path('/opt/infra-abrasil'); out=root/'docs/vetorleads-prospect-11'; backup=root/'backups/prospect11'
def run(args,data=None,check=True):
 r=s.run(args,input=data,stdout=s.PIPE,stderr=s.PIPE)
 if check and r.returncode: raise RuntimeError(str(args[:5])+': '+r.stderr.decode()[-800:])
 return r.stdout
def save(name,obj): (out/name).write_text(json.dumps(obj,indent=2)+'\n')
def states():
 return [{'name':x['Name'],'id':x['Id'],'image':x['Image'],'restart':x['RestartCount'],'state':x['State']} for x in json.loads(run(['docker','inspect']+run(['docker','ps','-q']).decode().split()))]
def mem():
 oom=s.run(['journalctl','-k','-b','--no-pager','-g','oom|Out of memory|Killed process'],capture_output=True)
 assert oom.returncode in [0,1]
 assert 'Killed process' not in oom.stdout.decode() and 'Out of memory' not in oom.stdout.decode()
 available=int(next(x.split()[1] for x in p.Path('/proc/meminfo').read_text().splitlines() if x.startswith('MemAvailable:')))
 assert available>2*1024*1024
 return {'available_kib':available,'free':run(['free','-b']).decode(),'uptime':run(['uptime']).decode(),'oom':oom.stdout.decode(),'disk':run(['df','-h','/','/tmp','/run']).decode()}
compose=['docker','compose','-f','docker-compose.yml','-f','docker-compose.override.yml']
targets=['vetorleads','vetorleads-worker','vetorleads-scheduler']
before=json.loads((out/'services-before.json').read_text()); now=states()
for old in before:
 cur=next(x for x in now if x['name']==old['name'])
 assert cur['restart']==old['restart'] and cur['state']['StartedAt']==old['state']['StartedAt']
assert json.loads((out/'restore.json').read_text())['all_counts_match']
assert hashlib.sha256((backup/'snapshot.sql').read_bytes()).hexdigest()==json.loads((out/'backup.json').read_text())['sha256']
save('memory-before-publication.json',mem()); run(compose+['config','--quiet'])
run(['docker','run','--rm','--network','none','--memory','128m','--entrypoint','php','-v',str(root/'scripts/vetorleads-process-health.php')+':/health.php:ro','-v',str(root/'docs/vetorleads-prospect-10/health-probe-test.php')+':/test.php:ro','vetorleads:prospect09-00282e4','/test.php'])
recovery={'services':{name:{'image':'vetorleads:prospect11-recovery-'+name,'pull_policy':'never'} for name in targets}}
(backup/'recovery.json').write_text(json.dumps(recovery,indent=2))
try:
 run(compose+['stop','vetorleads-worker','vetorleads-scheduler'])
 run(compose+['up','-d','--no-deps','--no-build','--pull','never']+targets)
 print('Três serviços publicados; aguardando healthchecks.',flush=True)
 for i in range(18):
  time.sleep(5); current=states(); selected=[x for x in current if x['name'] in ['/infra-abrasil-'+t+'-1' for t in targets]]
  assert all(x['image']=='sha256:4d9deab99bb7ab4d2ffbe660983cb668ca34a931ae34cd4e982d957d8181a733' for x in selected)
  mem()
  if len(selected)==3 and all(x['state'].get('Health',{}).get('Status')=='healthy' and x['restart']==0 for x in selected): break
 else: raise RuntimeError('Healthchecks não aprovados')
 for command,container in [('queue:work','infra-abrasil-vetorleads-worker-1'),('schedule:work','infra-abrasil-vetorleads-scheduler-1')]:
  run(['docker','exec',container,'php','/opt/infra/vetorleads-process-health.php',command])
 save('services-after.json',states()); save('memory-after-publication.json',mem())
 print('Healthchecks dos três serviços aprovados.',flush=True)
except Exception as error:
 save('publication-error.json',{'error':str(error)})
 run(['docker','compose','-f','docker-compose.yml','-f',str(backup/'recovery.json'),'up','-d','--no-deps','--no-build','--pull','never']+targets)
 run(['docker','cp',str(backup/'assets')+'/.','infra-abrasil-vetorleads-1:/var/www/html/public/build'])
 save('services-recovered.json',states())
 raise
