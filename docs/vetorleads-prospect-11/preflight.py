import subprocess as s, pathlib as p, json, hashlib, time, secrets
root=p.Path('/opt/infra-abrasil'); out=root/'docs/vetorleads-prospect-11'; private=root/'backups/prospect11'; out.mkdir(exist_ok=True); private.mkdir(parents=True,exist_ok=True); private.chmod(0o700)
def run(args, data=None):
 r=s.run(args,input=data,stdout=s.PIPE,stderr=s.PIPE);
 if r.returncode: raise RuntimeError(str(args[:4])+': '+r.stderr.decode()[-1000:])
 return r.stdout
def save(name,obj): (out/name).write_text(json.dumps(obj,indent=2)+'\n')
def db(sql,container='infra-abrasil-mysql-1',database='vetorleads'):
 return run(['docker','exec','-i',container,'sh','-c','MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N -B '+database],sql.encode()).decode()
services=json.loads(run(['docker','inspect']+run(['docker','ps','-q']).decode().split()))
save('services-before.json',[{'name':x['Name'],'id':x['Id'],'image':x['Image'],'restart':x['RestartCount'],'state':x['State']} for x in services])
image=json.loads(run(['docker','image','inspect','vetorleads:prospect09-00282e4']))[0]
assert image['Id']=='sha256:4d9deab99bb7ab4d2ffbe660983cb668ca34a931ae34cd4e982d957d8181a733'
save('candidate.json',{'id':image['Id'],'digests':image['RepoDigests']})
for x in services:
 name=x['Name'].lstrip('/').removeprefix('infra-abrasil-').removesuffix('-1')
 if name in ['vetorleads','vetorleads-worker','vetorleads-scheduler']: run(['docker','tag',x['Image'],'vetorleads:prospect11-recovery-'+name])
run(['docker','compose','-f','docker-compose.yml','-f','docs/vetorleads-prospect-10/publication.override.yml','config','--quiet'])
assert not db("SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_SCHEMA=DATABASE() AND TABLE_TYPE='BASE TABLE' AND ENGINE<>'InnoDB';").strip()
tables=db('SHOW TABLES;').split()
counts={t:int(db('SELECT COUNT(*) FROM `'+t+'`;')) for t in tables}; save('counts-before.json',counts)
assert counts['migrations']==48
save('migrations-before.json',db('SELECT migration FROM migrations ORDER BY migration;').split())
dump=run(['docker','exec','infra-abrasil-mysql-1','sh','-c','MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysqldump -uroot --single-transaction --quick --no-tablespaces --set-gtid-purged=OFF --hex-blob --routines --events --triggers vetorleads'])
(private/'snapshot.sql').write_bytes(dump)
run(['docker','cp','infra-abrasil-vetorleads-1:/var/www/html/public/build',str(private/'assets')])
save('backup.json',{'path':str(private),'bytes':len(dump),'sha256':hashlib.sha256(dump).hexdigest(),'manifest_sha256':hashlib.sha256((private/'assets/manifest.json').read_bytes()).hexdigest()})
password=secrets.token_hex(24); env=private/'restore.env'; env.write_text('MYSQL_ROOT_PASSWORD='+password+'\nMYSQL_DATABASE=restorecheck\n'); env.chmod(0o600)
run(['docker','run','-d','--name','vetorleads-prospect11-restore','--network','none','--memory','768m','--memory-swap','768m','--env-file',str(env),'mysql:8.4','--innodb-buffer-pool-size=128M','--mysqlx=0'])
try:
 for i in range(60):
  try: db('SELECT 1;','vetorleads-prospect11-restore','restorecheck'); break
  except RuntimeError: time.sleep(2)
 else: raise RuntimeError('clone not ready')
 run(['docker','exec','-i','vetorleads-prospect11-restore','sh','-c','MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot restorecheck'],dump)
 restored={t:int(db('SELECT COUNT(*) FROM `'+t+'`;','vetorleads-prospect11-restore','restorecheck')) for t in tables}
 assert restored==counts
 checks=db('CHECK TABLE '+','.join('`'+t+'`' for t in tables)+' QUICK;','vetorleads-prospect11-restore','restorecheck')
 assert all(line.endswith('\tOK') for line in checks.strip().splitlines())
 save('restore.json',{'counts':restored,'all_counts_match':True,'checks':checks.splitlines()})
finally: run(['docker','stop','vetorleads-prospect11-restore'])
save('memory.json',{'free':run(['free','-b']).decode(),'uptime':run(['uptime']).decode(),'kernel_oom':run(['journalctl','-k','-b','--no-pager','-g','oom|Out of memory|Killed process']).decode(),'disk':run(['df','-h','/','/tmp','/run']).decode()})
print('Preflight, backup e restauração aprovados.')
