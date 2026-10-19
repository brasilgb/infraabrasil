import subprocess as s, pathlib as p, json, hashlib, time
root=p.Path('/opt/infra-abrasil'); out=root/'docs/vetorleads-prospect-11'; private=root/'backups/prospect11'
def run(args,data=None):
 r=s.run(args,input=data,stdout=s.PIPE,stderr=s.PIPE)
 if r.returncode: raise RuntimeError(str(args[:5])+': '+r.stderr.decode()[-1000:])
 return r.stdout
def save(name,obj): (out/name).write_text(json.dumps(obj,indent=2)+'\n')
def db(sql):return run(['docker','exec','-i','infra-abrasil-mysql-1','sh','-c','MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot -N -B vetorleads'],sql.encode()).decode()
checks=[]
manifest=json.loads((private/'assets/manifest.json').read_text())
paths=['/','/login','/dashboard','/prospecting','/settings/integrations/ab-prospect','/prospecting/prospects','/prospect-products','/leads']
paths+=['/build/'+x['file'] for x in manifest.values() if x.get('isEntry')]
for path in paths:
 status=run(['curl','--silent','--show-error','--max-time','20','--noproxy','*','--resolve','vetorleads.com.br:443:127.0.0.1','-o',str(private/'http-body'),'-w','%{http_code}','https://vetorleads.com.br'+path]).decode()
 assert status==('302' if path in paths[2:8] else '200'),(path,status)
 checks.append({'path':path,'status':int(status)})
save('http.json',checks)
before=json.loads((out/'counts-before.json').read_text()); after={t:int(db('SELECT COUNT(*) FROM `'+t+'`;')) for t in before}; save('counts-after.json',after)
assert all(before[t]==after[t] for t in before if t not in ['cache','cache_locks','sessions'])
migrations=db('SELECT migration FROM migrations ORDER BY migration;').split(); assert migrations==json.loads((out/'migrations-before.json').read_text())
save('migrations-after.json',migrations)
logs={}
for name in ['vetorleads','vetorleads-worker','vetorleads-scheduler','mysql']:
 r=s.run(['docker','logs','--since','2026-10-19T12:39:00Z','infra-abrasil-'+name+'-1'],capture_output=True); data=r.stdout+r.stderr; (private/(name+'.log')).write_bytes(data)
 logs[name]={'bytes':len(data),'error_lines':sum(any(word in l.lower() for word in ['sqlstate','fatal','production.error','exception']) for l in data.decode().splitlines())}
save('logs-summary.json',logs)
assert all(x['error_lines']==0 for x in logs.values())
run(['docker','start','vetorleads-prospect11-ui-fixed'])
try:
 time.sleep(2)
 ui=out/'interface'; ui.mkdir(exist_ok=True)
 run(['docker','run','--rm','--network','container:vetorleads-prospect11-ui-fixed','--memory','512m','--memory-swap','512m','-e','PLAYWRIGHT_BROWSERS_PATH=/tool/browsers','-v',str(private/'browser')+':/tool:ro','-v',str(private/'browser/node_modules')+':/node_modules:ro','-v','/tmp/prospect11-browser.cjs:/check.cjs:ro','-v',str(ui)+':/evidence','--entrypoint','node','prospect10-browser','/check.cjs'])
finally:run(['docker','stop','vetorleads-prospect11-ui-fixed'])
print('HTTP, assets, dados, migrations, logs e navegador isolado aprovados.')
