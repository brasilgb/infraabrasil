const {chromium}=require('/opt/infra-abrasil/backups/prospect11/browser/node_modules/playwright');
const fs=require('node:fs'),assert=require('node:assert/strict');
const L='http://127.0.0.1:8016',O='http://localhost:8017',out='/opt/infra-abrasil/docs/vetor-chat-12/chat128-interface';
(async()=>{
 const browser=await chromium.launch({headless:true,args:['--no-sandbox','--disable-dev-shm-usage']});
 const context=await browser.newContext({viewport:{width:1440,height:1000}}),page=await context.newPage(),widget=await context.newPage(),errors=[],operational=[],checks=[];
 for(const p of [page,widget]) { p.setDefaultTimeout(15000); p.on('pageerror',e=>errors.push(e.message)); }
 await context.route('**/*',r=>[L,O].some(b=>r.request().url().startsWith(b+'/'))?r.continue():r.abort());
 widget.on('request',r=>{if(r.url().includes('/chat/conversations'))operational.push(r.url())});
 await page.goto(L+'/login');await page.locator('#email').fill('chat128@example.test');await page.locator('#password').fill('Local-Chat128-Test!');await page.locator('button[type=submit]').click();await page.waitForURL('**/dashboard');
 async function api(method,path,data){const token=(await context.cookies(L)).find(c=>c.name==='XSRF-TOKEN');const r=await page.request.fetch(L+path,{method,data,headers:{Accept:'application/json','X-XSRF-TOKEN':decodeURIComponent(token?.value||'')}});assert.ok(r.ok(),path+' '+r.status()+' '+(await r.text()).slice(0,400));return r.json()}
 async function shot(p,name,width){await p.setViewportSize({width,height:1000});await p.screenshot({path:`${out}/${width}-${name}.png`,fullPage:p===page});const overflow=await p.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1);assert.equal(overflow,false,name+' '+width+' overflow');checks.push({name,width,overflow})}
 await page.goto(L+'/settings/support-automation/lab');await page.getByText('Planos do laboratório',{exact:false}).waitFor();
 for(const width of [1440,390]) { await shot(page,'lab-rules',width); await page.locator('select').selectOption('whatsapp'); await shot(page,'lab-whatsapp',width); await page.locator('select').selectOption('web_chat'); }
 for(const box of await page.locator('input[type=checkbox]').all()) await box.check();await page.getByRole('button',{name:'Iniciar conversa',exact:true}).click();
 await page.getByRole('button',{name:'Emitir código temporário',exact:true}).click();const code=await page.locator('code').textContent();assert.equal(code.length,64);
 const id=(await page.evaluate(()=>document.body.innerText)).match(/[0-9a-f]{8}-[0-9a-f-]{27,}/)?.[0];
 await widget.goto(O);await widget.getByRole('button',{name:/Abrir.*atendimento|Abrir.*chat/i}).click();await widget.getByRole('button',{name:'Tenho código de homologação'}).click();await widget.getByLabel('Código temporário').fill(code);await widget.getByRole('button',{name:'Validar código',exact:true}).click();await widget.getByText('Sandbox isolado',{exact:false}).waitFor();assert.equal(await widget.getByRole('link',{name:'Continuar no WhatsApp',exact:true}).count(),0);
 async function send(body,answer){await widget.getByPlaceholder('Mensagem para o laboratório...').fill(body);await widget.getByRole('button',{name:'Enviar mensagem',exact:true}).click();if(answer)await widget.getByText(answer,{exact:false}).last().waitFor()}
 await send('recursos','Ordens e equipamentos no laboratório.');
 for(const width of [1440,390])await shot(widget,'widget-rule',width);
 await send('planos','Como você se chama?');await send('Ana','Qual é o nome da sua empresa?');await send('Oficina','Qual telefone com DDD?');await send('51995179173','Qual é o seu interesse?');await send('Organizar ordens','Simulação concluída.');
 await page.getByText('Organizar ordens',{exact:false}).first().waitFor();for(const width of [1440,390])await shot(page,'qualification',width);
 await page.getByRole('button',{name:'Retomar robô',exact:true}).click();await send('humano','Transferência simulada.');await page.getByRole('button',{name:'Assumir como operador',exact:true}).click();await page.getByRole('button',{name:'Resposta humana',exact:true}).click();await widget.getByText('Resposta humana de teste',{exact:true}).waitFor();
 for(const width of [1440,390]){await shot(page,'operator',width);await shot(widget,'widget-operator',width)}
 await page.getByRole('button',{name:'Retomar robô',exact:true}).click();await send('recursos','Ordens e equipamentos no laboratório.');await page.getByRole('button',{name:'Encerrar',exact:true}).click();await widget.getByText('A sessão de homologação expirou ou foi revogada.',{exact:false}).waitFor();
 for(const width of [1440,390])await shot(widget,'widget-revoked',width);
 await widget.getByLabel('Código temporário').fill('a'.repeat(64));await widget.getByRole('button',{name:'Validar código',exact:true}).click();await widget.getByText('Código inválido, utilizado ou expirado.',{exact:false}).waitFor();
 await page.getByRole('button',{name:'Retomar robô',exact:true}).click();await page.getByRole('alert').waitFor();
 for(const width of [1440,390])await shot(page,'lab-error',width);
 // A fresh sandbox credential expires on the server; the widget remains locked in test mode.
 const start=await api('POST','/settings/support-automation/lab',{channel:'web_chat',rule_ids:[1,2,3]});
 const issued=await api('POST',`/settings/support-automation/lab/${start.conversation.id}/widget-code`,{});
 await widget.getByLabel('Código temporário').fill(issued.code);await widget.getByRole('button',{name:'Validar código',exact:true}).click();await widget.getByText('Sandbox isolado',{exact:false}).waitFor();
 require('node:child_process').execFileSync('python3',['-c',"import sqlite3,sys; c=sqlite3.connect('/opt/infra-abrasil/backups/chat128/vetorleads/database/database.sqlite'); c.execute(\"update support_sandbox_accesses set expires_at='2000-01-01 00:00:00' where id=?\",(int(sys.argv[1]),)); c.commit()",String(issued.access_id)]);
 await widget.getByText('A sessão de homologação expirou ou foi revogada.',{exact:false}).waitFor();
 for(const width of [1440,390])await shot(widget,'widget-expired',width);
 const next=await api('POST',`/settings/support-automation/lab/${start.conversation.id}/widget-code`,{});
 await widget.getByLabel('Código temporário').fill(next.code);await widget.getByRole('button',{name:'Validar código',exact:true}).click();await widget.getByText('Sandbox isolado',{exact:false}).waitFor();
 await api('DELETE',`/settings/support-automation/lab/widget-code/${next.access_id}`);
 await widget.getByText('A sessão de homologação expirou ou foi revogada.',{exact:false}).waitFor();
 const previousId='e5bb3a70-f718-46c8-a100-8c9d85a6f69d';
 await widget.evaluate(id=>localStorage.setItem('vetoros-web-chat-v1',JSON.stringify({id,expires:Date.now()+86400000,identified:true})),previousId);
 await widget.reload();await widget.getByRole('button',{name:'Abrir atendimento',exact:true}).click();await widget.getByLabel('Código temporário').waitFor();
 assert.deepEqual(operational,[]);assert.deepEqual(errors,[]);
 const sandboxOperational=[...operational];
 await widget.route(`**/chat/conversations/${previousId}`,r=>r.fulfill({status:200,contentType:'application/json',body:JSON.stringify({messages:[],identified:true})}));
 const restored=widget.waitForRequest(r=>r.url().endsWith('/chat/conversations/'+previousId));
 await widget.getByRole('button',{name:'Sair do teste',exact:true}).click();await restored;
 assert.equal(await widget.evaluate(()=>JSON.parse(localStorage.getItem('vetoros-web-chat-v1')).id),previousId);
 assert.deepEqual(errors,[]);
 fs.writeFileSync(out+'/results.json',JSON.stringify({checks,errors,operational:sandboxOperational,restoredPublicSession:true,journey:'passed',id},null,2));await browser.close();console.log('Widget journey and responsive checks passed.');
})().catch(e=>{console.error(e);process.exit(1)});
