import os,json,subprocess,time
from pathlib import Path
base=Path('/opt/infra-abrasil');app=base/'backups/chat12/app';docs=base/'docs/vetor-chat-12'
env=dict(os.environ,DB_CONNECTION='mysql',DB_DATABASE='chat12_concurrency')
def run(*args):
 r=subprocess.run(args,cwd=app,env=env,text=True,capture_output=True);assert r.returncode==0,r.stdout+r.stderr;return r.stdout
# Dedicated schema only, never a production/default database.
run('php','artisan','migrate:fresh','--force')
run('php',str(docs/'concurrency-seed.php'))
ledger=base/'backups/chat12/delivery-ledger.log';ledger.write_text('')
worker=str(docs/'concurrency-worker.php')
def parallel(mode,args,count=4):
 ps=[subprocess.Popen(['php',worker,mode,*args],cwd=app,env=env,stdout=subprocess.PIPE,stderr=subprocess.PIPE,text=True) for _ in range(count)]
 out=[]
 for proc in ps:
  stdout,stderr=proc.communicate(timeout=30);assert proc.returncode==0,stdout+stderr;out.append(json.loads(stdout))
 return out
def sql(query):
 return run('mariadb','--socket=/opt/infra-abrasil/backups/commercial01/mysql.sock','-u','root','-N','chat12_concurrency','-e',query).strip()
receive=parallel('receive',['5511999991234','same-inbound-event','planos'])
assert sum(r['result']=='created' for r in receive)==1
assert sql('select count(*) from support_whatsapp_receipts')=='1'
assert sql('select count(*) from support_automation_decisions')=='1'
outbox=sql('select id from support_automation_outbox limit 1')
deliver=parallel('deliver',[outbox]);assert len(ledger.read_text().splitlines())==1
assert sql('select status from support_automation_outbox limit 1')=='delivered'
# Operator owns the row before the delivery starts: no outbound call can pass the takeover.
run('php',worker,'receive','5511999991235','operator-inbound','planos')
chat=sql("select id from web_conversations where whatsapp_number='5511999991235'")
item=sql(f"select id from support_automation_outbox where conversation_id='{chat}'")
lock=base/'backups/chat12/operator-lock'
if lock.exists():lock.unlink()
operator=subprocess.Popen(['php',worker,'assume',chat],cwd=app,env=env,stdout=subprocess.PIPE,stderr=subprocess.PIPE,text=True)
for _ in range(100):
 if lock.exists():break
 time.sleep(.01)
assert lock.exists()
blocked=parallel('deliver',[item],2);stdout,stderr=operator.communicate(timeout=15);assert operator.returncode==0,stderr
assert sql(f'select status from support_automation_outbox where id={item}')=='cancelled'
assert len(ledger.read_text().splitlines())==1
# Qualification completion replayed concurrently creates exactly one commercial opportunity.
for event,body in [('qual-start','qualificar'),('qual-name','Ana sintética'),('qual-company','Oficina sintética')]:run('php',worker,'receive','5511999991236',event,body)
qualified=parallel('receive',['5511999991236','same-interest','Organizar estoque'])
assert sql('select count(*) from leads')=='1'
assert sql('select count(*) from web_conversations where qualified_at is not null')=='1'
result={'database':sql('select version()'),'workers':4,'inbound_replay':receive,'delivery_race':deliver,'operator_race':blocked,'qualification_replay':qualified,'simulated_external_sends':1,'leads_created':1,'assertions':'PASS'}
(docs/'concurrency-results.json').write_text(json.dumps(result,indent=2));print('MariaDB: replay, entrega única, tomada humana e qualificação concorrente aprovados.')
