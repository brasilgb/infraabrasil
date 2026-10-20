const {chromium}=require('/node_modules/playwright');
const assert=require('node:assert/strict');
const fs=require('node:fs');
(async()=>{
 const browser=await chromium.launch({headless:true,args:['--no-sandbox','--disable-dev-shm-usage']});
 const page=await browser.newPage({viewport:{width:1440,height:1000}});
 const errors=[];page.on('pageerror',e=>errors.push(e.message));
 const out='/evidence/interface';fs.mkdirSync(out,{recursive:true});
 await page.goto('http://127.0.0.1:8000/login');await page.locator('#email').fill('ux@example.test');await page.locator('#password').fill('Local-ux02-only!');await page.locator('button[type=submit]').click();await page.waitForURL('**/dashboard');
 await page.goto('http://127.0.0.1:8000/prospecting');await page.getByRole('heading',{name:'Capturar empresas',exact:true}).waitFor();await page.screenshot({path:`${out}/1440-capture.png`,fullPage:true});
 const credential=JSON.parse(fs.readFileSync('/work/credential.json','utf8'));
 const payload={source:'AB Prospect - Google Maps',prospects:[{name:'Oficina PROSPECT10 Final',phone:'(51) 3333-0808',category:'Oficina mecânica',city:'Porto Alegre',state:'RS',address:'Rua sintética, 7',hasWebsite:false,canImprove:true,placeId:'ChIJPROSPECT10FinalSynthetic'}]};
 const capture=await page.request.post('http://127.0.0.1:8000/api/prospects/import',{headers:{Authorization:`Bearer ${credential.token}`,Accept:'application/json'},data:payload});assert.ok([200,201].includes(capture.status()),`Capture ${capture.status()}`);
 const recapture=await page.request.post('http://127.0.0.1:8000/api/prospects/import',{headers:{Authorization:`Bearer ${credential.token}`,Accept:'application/json'},data:payload});assert.equal(recapture.status(),200);
 await page.goto('http://127.0.0.1:8000/settings/integrations/ab-prospect');await page.screenshot({path:`${out}/1440-settings.png`,fullPage:true});
 await page.goto('http://127.0.0.1:8000/prospecting/prospects?category=Oficina&city=Porto%20Alegre&state=RS&website=without');await page.getByText('Oficina PROSPECT10 Final',{exact:true}).first().waitFor();await page.screenshot({path:`${out}/1440-filters.png`,fullPage:true});
 await page.goto('http://127.0.0.1:8000/prospecting/prospects?q=PROSPECT10&website=with');assert.equal(await page.locator('tbody').getByText('Oficina PROSPECT10 Final',{exact:true}).count(),0);
 await page.goto('http://127.0.0.1:8000/prospect-products');
 if(await page.locator('p').filter({hasText:'Gestão PROSPECT10'}).first().count()===0){
 await page.getByRole('button',{name:'Novo produto',exact:true}).click();await page.locator('#product-name').fill('Gestão PROSPECT10');await page.locator('#product-segments').fill('Oficina, assistência técnica');await page.locator('#product-excluded').fill('Atacado');await page.locator('#product-website').selectOption('without');await page.screenshot({path:`${out}/1440-commercial-criteria.png`});await page.getByRole('button',{name:'Salvar',exact:true}).click();await page.getByRole('dialog').waitFor({state:'hidden'});}
 await page.locator('p').filter({hasText:'Gestão PROSPECT10'}).first().waitFor();
 await page.goto('http://127.0.0.1:8000/prospecting/prospects?q=PROSPECT10%20Final');await page.getByLabel('Produto de qualificação',{exact:true}).selectOption({label:'Gestão PROSPECT10'});await page.waitForURL('**product=*');
 await page.getByRole('button',{name:'Detalhes',exact:true}).first().click();await page.getByRole('button',{name:'Prévia de qualificação',exact:true}).click();await page.getByRole('region',{name:'Prévia de qualificação',exact:true}).waitFor();await page.screenshot({path:`${out}/1440-preview.png`});await page.getByRole('button',{name:'Confirmar qualificação',exact:true}).click();await page.getByRole('dialog').waitFor({state:'hidden'});await page.locator('tbody').getByText('Prioridade alta',{exact:true}).waitFor();
 await page.waitForTimeout(5000);
 const checks=[];
 for(const width of [1440,390,320]){await page.setViewportSize({width,height:1000});await page.waitForTimeout(200);assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),'Overflow '+width);const box=await page.getByLabel('Origem da captura',{exact:true}).boundingBox();assert.ok(box.x+box.width<=width-16,'Origin filter overflow '+width);await page.screenshot({path:`${out}/${width}-leads.png`,fullPage:true});await page.getByRole('button',{name:'Detalhes',exact:true}).first().click();await page.getByRole('button',{name:'Prévia de qualificação',exact:true}).click();await page.getByRole('region',{name:'Prévia de qualificação',exact:true}).waitFor();await page.getByRole('region',{name:'Prévia de qualificação',exact:true}).scrollIntoViewIfNeeded();await page.screenshot({path:`${out}/${width}-preview.png`});await page.keyboard.press('Escape');checks.push({width,result:'PASS'});}
 await page.setViewportSize({width:1440,height:1000});await page.getByRole('button',{name:'Detalhes',exact:true}).first().click();
 if(await page.getByRole('button',{name:'Selecionar como prospect',exact:true}).count()){await page.getByRole('button',{name:'Selecionar como prospect',exact:true}).click();await page.getByRole('dialog').waitFor({state:'hidden'});await page.getByRole('button',{name:'Detalhes',exact:true}).first().click();}
 await page.getByRole('button',{name:'Adicionar ao CRM',exact:true}).click();await page.locator('#convert-product').selectOption({label:'Gestão PROSPECT10'});await page.screenshot({path:`${out}/1440-conversion.png`});await page.getByRole('button',{name:'Criar leads',exact:true}).click();await page.getByRole('dialog').waitFor({state:'hidden'});
 await page.goto('http://127.0.0.1:8000/leads?search=PROSPECT10');await page.getByText('Oficina PROSPECT10 Final',{exact:true}).first().waitFor();await page.screenshot({path:`${out}/1440-crm.png`,fullPage:true});
 const xsrf=(await page.context().cookies()).find(c=>c.name==='XSRF-TOKEN');assert.ok(xsrf);
 const leadProps=JSON.parse(await page.locator('script[data-page="app"]').textContent()).props;const lead=leadProps.leads.data.find(l=>l.company_name==='Oficina PROSPECT10 Final');assert.ok(lead);
 const headers={'X-XSRF-TOKEN':decodeURIComponent(xsrf.value),Accept:'application/json',Referer:'http://127.0.0.1:8000/leads?search=PROSPECT10'};
 const move=await page.request.patch(`http://127.0.0.1:8000/leads/${lead.id}/status`,{headers,maxRedirects:0,data:{status:'meeting'}});assert.ok([302,303].includes(move.status()),`Move ${move.status()}`);
 const note=await page.request.post(`http://127.0.0.1:8000/leads/${lead.id}/activities`,{headers,maxRedirects:0,data:{type:'note',description:'Homologação PROSPECT10 sem contato',follow_up_action:'keep'}});assert.ok([302,303].includes(note.status()),`Note ${note.status()}`);
 await page.goto('http://127.0.0.1:8000/leads?search=PROSPECT10');await page.getByText('Oficina PROSPECT10 Final',{exact:true}).first().waitFor();assert.equal(JSON.parse(await page.locator('script[data-page="app"]').textContent()).props.leads.data.find(l=>l.id===lead.id).status,'meeting');await page.screenshot({path:`${out}/1440-pipeline.png`,fullPage:true});
 await page.goto('http://127.0.0.1:8000/dashboard');await page.getByText('Prospects vinculados ao CRM',{exact:true}).waitFor();await page.screenshot({path:`${out}/1440-dashboard.png`,fullPage:true});
 assert.deepEqual(errors,[]);fs.writeFileSync(`${out}/results.json`,JSON.stringify({captureStatus:capture.status(),recaptureStatus:recapture.status(),catalogue:'PASS',preview:'PASS',qualification:'PASS',conversion:'PASS',crm:'PASS',dashboard:'PASS',pipelineMovement:'PASS',note:'PASS',filters:'PASS',settings:'PASS',checks,errors},null,2));console.log('PROSPECT10 browser full flow PASS');await browser.close();
})().catch(e=>{console.error(e);process.exit(1)});
