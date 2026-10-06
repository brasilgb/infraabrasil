# AB PROSPECT — Fase D3: pré-cutover e validação ponta a ponta

## Objetivo

Executar uma etapa de **pré-cutover técnico** do novo AB Prospect SaaS multi-tenant.

A Fase D3 deve validar, em ambiente isolado e controlado, que o fluxo completo do novo domínio funciona de ponta a ponta:

**Tenant → usuário → produto → campanha → token de integração → importação de empresa/prospect → operação comercial → WhatsApp → ACK → inbound → automação → trial → registration check**

Ao final desta fase devemos ter:

1. contratos das integrações confirmados;
2. simulação ponta a ponta aprovada;
3. riscos de migração do legado documentados;
4. estratégia clara sobre dados legados;
5. checklist exato de cutover;
6. checklist exato de rollback;
7. nenhuma alteração em produção.

---

# 1. Regras de execução

Trabalhar somente no repositório:

`/opt/infra-ab-prospect`

Branch atual do projeto SaaS.

Antes de alterar qualquer arquivo:

1. ler integralmente o `correio.md`;
2. ler o `executed.md` atual;
3. conferir `git status`;
4. conferir os commits das Fases D1 e D2;
5. registrar no relatório o HEAD inicial;
6. auditar o estado atual antes de implementar.

Não assumir nomes de classes, rotas ou estruturas. Localizar no código real.

---

# 2. PROIBIDO nesta fase

Não executar:

- deploy;
- push;
- migration no banco real `ab_prospect`;
- alteração no banco `abrasilsistemas` de produção;
- alteração nos workflows n8n reais;
- alteração no WAHA real;
- alteração de webhook;
- alteração em Nginx;
- alteração em SSL;
- alteração em DNS;
- restart de container compartilhado;
- envio de WhatsApp real;
- disparo de automação real;
- publicação/distribuição da extensão;
- inserção de secrets reais;
- exclusão do domínio legado;
- remoção de `Lead`;
- remoção de `LeadActivity`;
- remoção de `LeadAutomationExecution`;
- remoção definitiva de `prospect.token`;
- importação de dados de produção.

Toda validação deve acontecer em cópia/scratchpad, banco descartável, mocks ou serviços falsos.

---

# 3. Primeiro passo: auditoria completa da D2

Antes da D3 propriamente dita, confirmar objetivamente o estado deixado pela D2.

Auditar:

- gestão de `ProspectProduct`;
- gestão de `TenantMembership`;
- proteção do último owner;
- configurações WhatsApp por tenant;
- horário comercial;
- configuração da automação `trial_expired`;
- `TenantApiToken`;
- emissão;
- rotação;
- revogação;
- exibição única do token puro;
- telas de integração;
- permissões owner/admin/member;
- RootAdmin em acesso explícito;
- isolamento cross-tenant;
- secrets mascarados/criptografados.

Registrar no relatório os commits reais da D2.

Se algo previsto na D2 não estiver implementado, não ocultar. Corrigir somente se for um defeito necessário para executar a D3.

Não ampliar a D2 com funcionalidades não relacionadas ao pré-cutover.

---

# 4. Criar uma matriz de readiness

Antes dos testes E2E, criar no relatório uma matriz:

| Componente | Estado | Bloqueia cutover? | Evidência |
|---|---|---|---|
| Tenant | | | |
| Membership | | | |
| Produto | | | |
| Campanha | | | |
| TenantApiToken | | | |
| Extensão 1.5 | | | |
| Import API | | | |
| Company | | | |
| CampaignProspect | | | |
| WhatsApp outbound | | | |
| ACK | | | |
| Inbound | | | |
| AutomationExecution | | | |
| TrialExpiredAutomation | | | |
| Registration check | | | |
| TenantSettings | | | |
| n8n contract | | | |
| WAHA contract | | | |
| Legacy coexistence | | | |
| Rollback | | | |

Estados:

- READY
- PARTIAL
- BLOCKED
- NOT APPLICABLE

Não marcar READY apenas porque existe código. Deve haver evidência.

---

# 5. Contrato oficial da extensão 1.5

Auditar o código atual da extensão.

Confirmar:

- versão;
- armazenamento do token;
- armazenamento do `campaign_id`;
- endpoint usado;
- headers;
- payload;
- tratamento de 401;
- tratamento de 403;
- tratamento de 404;
- tratamento de 422;
- retry;
- duplicação;
- comportamento quando campanha não existe;
- comportamento quando token pertence a outro tenant.

O contrato esperado é conceitualmente:

- Bearer token = `TenantApiToken`;
- tenant vem exclusivamente do token;
- `campaign_id` identifica campanha dentro do tenant;
- produto não é enviado pela extensão;
- dados da empresa alimentam `Company`;
- inclusão comercial gera/reutiliza `CampaignProspect`.

Mas validar contra o código real e documentar o formato exato.

Não inventar payload.

---

# 6. Harness isolado da extensão

Criar ou ampliar testes da extensão para simular:

1. importação normal;
2. retry da mesma empresa;
3. mesmo Google Place ID;
4. mesma empresa em outra campanha;
5. mesmo Place ID em outro tenant;
6. token inválido;
7. token revogado;
8. token expirado;
9. campanha inexistente;
10. campanha de outro tenant;
11. `tenant_id` forjado;
12. campos opcionais;
13. telefone fixo;
14. telefone celular;
15. WhatsApp conhecido;
16. empresa sem site;
17. empresa com site;
18. dados parciais.

Não acessar Google real.

---

# 7. Simulador do n8n

Não alterar n8n real.

Criar um harness/mock local que represente o contrato que o n8n deverá cumprir no cutover.

Auditar os endpoints atualmente usados pelo novo domínio.

Simular pelo menos:

### CRM → n8n → WhatsApp

1. CRM envia mensagem;
2. mock recebe:
   - tenant;
   - session;
   - provider;
   - remetente;
   - destino;
   - mensagem;
   - CampaignProspect;
   - dados de automação quando aplicável;
3. mock devolve `provider_message_id`;
4. outbound é persistido em `Activity`.

### n8n/WAHA → CRM ACK

Simular:

- PENDING;
- SERVER;
- DEVICE;
- READ;
- PLAYED;
- ERROR;
- regressões fora de ordem;
- retries;
- mesmo id em tenants diferentes.

Confirmar regra monotônica já existente.

---

# 8. Simular inbound completo

Em ambiente isolado:

1. criar tenant A;
2. criar tenant B;
3. criar Company A e Company B;
4. usar números iguais nos dois tenants;
5. criar CampaignProspect;
6. registrar outbound;
7. gerar inbound simulado;
8. validar atribuição.

Cobrir:

- empresa única;
- mesma empresa em várias campanhas;
- apenas um prospect elegível;
- dois prospects com outbound;
- outbound mais recente vence;
- empate no mesmo segundo;
- prospect encerrado;
- nenhuma campanha elegível;
- telefone existente apenas em outro tenant;
- mesmo telefone em dois tenants;
- mesma mensagem repetida;
- `provider_message_id` repetido em outro tenant.

A regra conservadora existente deve ser preservada.

---

# 9. Simulação ponta a ponta principal

Criar um cenário E2E isolado completo.

## Tenant A

Criar:

- tenant;
- owner;
- admin;
- member;
- produto;
- campanha;
- configurações WhatsApp;
- horário comercial;
- configuração de automação;
- TenantApiToken.

Guardar token somente no processo de teste.

Não persistir token puro em fixture, snapshot ou log.

---

# 10. Fluxo E2E obrigatório

Executar no ambiente descartável:

### Etapa 1 — integração

Usar o TenantApiToken recém-emitido.

### Etapa 2 — importação

Simular extensão 1.5 importando uma empresa.

Esperado:

- Company criada;
- CampaignProspect criado;
- tenant correto;
- campaign correta;
- produto herdado da campanha.

### Etapa 3 — reimportação

Enviar a mesma empresa novamente.

Esperado:

- sem duplicação indevida da Company;
- sem duplicação do CampaignProspect;
- comportamento idempotente documentado.

### Etapa 4 — operação comercial

Alterar:

- status;
- score;
- qualificação;
- responsável;
- follow-up;
- notas.

Confirmar tenant e permissions.

### Etapa 5 — WhatsApp outbound

Enviar por mock.

Confirmar:

- configuração do tenant usada;
- nenhuma configuração global usada;
- session correta;
- remetente correto;
- Activity outbound criada;
- CampaignProspect correto.

### Etapa 6 — ACK

Aplicar:

PENDING → SERVER → DEVICE → READ.

Confirmar:

- Activity única;
- progressão correta;
- retry idempotente;
- regressão ignorada.

### Etapa 7 — inbound

Simular resposta do cliente.

Confirmar:

- Company correta;
- prospect correto quando atribuível;
- histórico correto;
- unread;
- read.

### Etapa 8 — trial

Iniciar trial pelo fluxo atualmente previsto.

Confirmar:

- produto da campanha;
- `trial_started_at`;
- `trial_ends_at`;
- timezone;
- concorrência/idempotência.

### Etapa 9 — registration check

Mockar endpoint do produto.

Cobrir:

- não cadastrado;
- cadastrado;
- erro HTTP;
- timeout;
- resposta inválida;
- segredo ausente;
- campanha sem produto;
- produto em modo manual.

### Etapa 10 — trial_expired

Avançar relógio no teste.

Confirmar:

- candidato correto;
- horário comercial;
- cutoff;
- reserva de AutomationExecution;
- segunda tentativa não executa novamente;
- Activity vinculada quando mock de envio retorna;
- outro tenant não interfere.

---

# 11. Fail-closed

Validar explicitamente:

Sem configuração WhatsApp:

- não envia.

Com configuração incompleta:

- não envia.

Sem token:

- 401.

Token inválido/revogado/expirado:

- rejeitado.

Tenant inativo:

- rejeitado.

Campanha de outro tenant:

- invisível/404.

Produto de outro tenant:

- invisível/recusado.

Sem cutoff de automação:

- automação desligada.

Fora do horário comercial:

- automação não dispara.

Sem registration endpoint válido:

- não alterar estado comercial incorretamente.

---

# 12. Segurança de secrets

Auditar novamente:

- token de TenantApiToken;
- token da API de registration check;
- eventuais secrets do WhatsApp;
- configurações de integração.

Confirmar:

- token puro de TenantApiToken somente na criação/rotação;
- `token_hash` nunca sai da API/UI;
- token de registration check não retorna puro;
- logs não contêm secrets;
- exceptions não imprimem secrets;
- `executed.md` não contém secrets;
- fixtures não contêm credenciais reais.

Adicionar teste se necessário.

---

# 13. Auditoria dos workflows atuais — somente código/config exportada local

Se houver workflow n8n versionado/exportado no repositório ou arquivos locais já disponíveis, auditar.

Não acessar/modificar o n8n de produção.

Mapear para cada workflow atual:

| Workflow | Contrato legado | Contrato novo | Mudança necessária |
|---|---|---|---|

Especialmente:

- envio de WhatsApp;
- ACK;
- inbound;
- outbound externo;
- trial expired.

Precisamos saber exatamente o que será modificado no cutover.

---

# 14. Identificar a quebra de contrato do cutover

Documentar claramente a diferença:

## Antes

- token global `AB_PROSPECT_API_TOKEN`;
- ids de `Lead`;
- extensão 1.4;
- configurações globais;
- Lead/LeadActivity.

## Depois

- `TenantApiToken`;
- ids de `CampaignProspect`;
- extensão 1.5;
- `campaign_id`;
- TenantSettings;
- Company/CampaignProspect/Activity.

Criar tabela campo a campo.

---

# 15. Dados legados — tomar decisão explícita

Não migrar nada nesta fase.

Mas precisamos decidir a estratégia.

Auditar o legado existente e propor opções tecnicamente seguras.

## Opção A — novo SaaS começa vazio

- legado permanece no painel RootAdmin;
- novas captações entram no SaaS;
- sem migração imediata;
- limpeza só mais tarde.

## Opção B — migrar cadastro comercial

- Lead → Company;
- Lead → CampaignProspect;
- produtos/campanhas precisam ser definidos;
- atividades podem ou não ser migradas.

## Opção C — migração integral

- Leads;
- activities;
- automations;
- histórico WhatsApp.

Essa opção exige auditorias adicionais.

No relatório, recomendar tecnicamente a estratégia de menor risco com base no estado real encontrado.

Não executar a migração.

---

# 16. Auditoria de `provider_message_id`

Este item continua pendente e é obrigatório antes de qualquer migração de histórico WhatsApp.

Como a leitura de produção pode ser bloqueada, fazer o seguinte:

1. preparar as consultas exatas;
2. testar as consultas em banco descartável;
3. registrar o comando que deverá ser executado em leitura na produção posteriormente.

Precisamos obter:

- quantidade total não nula;
- `COUNT(DISTINCT provider_message_id)`;
- `COUNT(DISTINCT BINARY provider_message_id)`;
- `MAX(CHAR_LENGTH(provider_message_id))`;
- presença de caracteres fora de ASCII;
- ids que só diferem por caixa;
- duplicidades reais;
- vazios.

Não acessar a produção se a proteção bloquear.

Não contornar a proteção.

---

# 17. Preparar script de auditoria legado somente leitura

Criar, se fizer sentido, script/command seguro que faça somente SELECT e produza um relatório de compatibilidade para migração.

Deve:

- não gravar;
- não alterar;
- não bloquear por longos períodos;
- não exibir dados pessoais desnecessários;
- não exibir conteúdo das mensagens;
- produzir apenas agregações e exemplos mascarados quando indispensáveis.

Não executar contra produção nesta fase.

---

# 18. Preparar runbook do cutover

Criar no `executed.md` um runbook detalhado, mas **não executar**.

Separar por fases.

## CUTOVER-0 — pré-condições

Confirmar:

- commit aprovado;
- backup;
- tenant;
- owner/admin;
- produto;
- campanha;
- token;
- WhatsApp settings;
- business hours;
- automation settings;
- extensão 1.5 pronta;
- workflows n8n novos preparados;
- rollback preparado.

## CUTOVER-1 — aplicação

Passos necessários para:

- build;
- migrations;
- containers;
- healthcheck.

Não executar.

## CUTOVER-2 — configuração

- provisionar tenant;
- memberships;
- produto;
- campanha;
- settings;
- token.

## CUTOVER-3 — integração

- mudar n8n;
- instalar token;
- trocar ids de Lead por CampaignProspect;
- configurar campaign_id;
- habilitar workflows novos.

## CUTOVER-4 — extensão

- distribuir/configurar 1.5;
- tenant token;
- campaign id.

## CUTOVER-5 — teste controlado

Usar um único lead de teste.

Validar:

- import;
- outbound;
- ACK;
- inbound;
- trial;
- registration check;
- automation-state.

## CUTOVER-6 — observação

Definir consultas/logs para verificar:

- 401;
- 403;
- 404;
- 409;
- 422;
- 500;
- mensagens órfãs;
- ACK sem Activity;
- inbound sem Company;
- automation reservation sem Activity.

---

# 19. Rollback

Preparar rollback correspondente a cada etapa.

O rollback deve considerar:

- código;
- imagem;
- banco;
- n8n;
- extensão;
- token;
- settings.

Não presumir que migration destrutiva pode simplesmente ser revertida.

Nesta fase ainda mantemos as tabelas legadas justamente para facilitar rollback.

Definir claramente:

### rollback antes de nova captação

simples.

### rollback depois de nova captação

mais complexo, pois dados podem ter entrado no domínio novo.

Não perder Company/Prospect/Activity gerados durante o período.

---

# 20. Compatibilidade temporária

Avaliar se o cutover precisa de janela de compatibilidade.

Exemplo:

- extensão antiga ainda circulando;
- workflow antigo ainda ativo;
- novo SaaS já implantado.

Não criar automaticamente uma camada dual-write.

Primeiro avaliar.

Se dual compatibility for necessária, documentar impacto e risco.

Preferência: corte claro e curto.

---

# 21. Testar multi-tenant com três tenants

Além do E2E principal:

- Tenant A configurado;
- Tenant B configurado com outra sessão/token;
- Tenant C incompleto.

Validar:

A nunca usa:

- token de B;
- sessão de B;
- produto de B;
- campanha de B;
- Company de B;
- Activity de B.

C deve falhar fechado.

---

# 22. Testar RootAdmin

RootAdmin deve:

- entrar explicitamente;
- ficar visualmente identificado;
- ter acesso auditado;
- não virar membership;
- não virar responsável comercial;
- poder administrar configuração conforme regra da D2;
- não conseguir quebrar isolation fornecendo IDs externos ao tenant selecionado.

---

# 23. Testar owner/admin/member

### Owner

Pode administrar tenant.

### Admin

Conforme decisão implementada na D2.

### Member

Pode operar comercialmente, mas não:

- gerenciar tokens;
- alterar configurações críticas;
- administrar membros;
- gerenciar produto/configuração quando protegido.

Confirmar contra o código real.

---

# 24. Último owner

Revalidar em MySQL real descartável:

- remover último owner;
- desativar último owner;
- rebaixar último owner;
- concorrência em duas operações simultâneas;
- owner A remove B enquanto B tenta remover A.

O tenant nunca deve ficar sem owner ativo por corrida.

Se a D2 já tiver este teste, apenas repetir e documentar.

---

# 25. Banco MySQL 8.4 descartável

Executar a suíte completa em:

- SQLite;
- MySQL 8.4 real descartável.

No MySQL:

- `migrate:fresh`;
- suíte;
- rollback/migrate quando aplicável;
- constraints;
- collations;
- concorrência;
- transações.

Não usar apenas SQLite como evidência de comportamento concorrente.

---

# 26. Testes frontend

Executar:

- `tsc --noEmit`;
- ESLint nos arquivos alterados;
- Prettier;
- build Vite.

Comparar erros globais com baseline quando houver dívida técnica preexistente.

Não atribuir erro antigo à D3.

---

# 27. PHP

Executar:

- suíte completa;
- Pint;
- PHPStan direcionado aos arquivos tocados;
- `git diff --check`.

Se PHPStan global tiver erros anteriores, comparar baseline × HEAD.

---

# 28. Performance

Não otimizar prematuramente.

Mas validar que os novos fluxos não introduziram consultas globais.

Nos principais endpoints E2E, capturar queries e verificar:

- todas as consultas ao domínio tenant-owned levam tenant explícito ou vêm de relação tenant-owned;
- nenhuma consulta nova às tabelas legadas;
- nenhum full scan obviamente introduzido pela D3.

Usar `EXPLAIN` somente se aparecer consulta nova relevante.

---

# 29. Observabilidade para cutover

Preparar uma lista de sinais que serão monitorados na futura virada:

- TenantApiToken auth failures;
- import failures;
- duplicate Company;
- duplicate CampaignProspect;
- cross-tenant denial;
- WhatsApp send failure;
- ACK without matching Activity;
- inbound unmatched;
- automation reservation conflict;
- registration check error;
- trial inconsistencies.

Não instalar stack nova de observabilidade.

Usar logs e consultas existentes.

---

# 30. Não fazer limpeza do legado

Mesmo que os testes da D3 estejam 100% verdes:

não remover ainda:

- `Lead`;
- `LeadActivity`;
- `LeadAutomationExecution`;
- painel legado;
- `prospect.token`;
- comandos legados;
- migrations legadas;
- settings globais ainda consumidas pelo legado.

A remoção será posterior ao cutover estável.

---

# 31. Commits

Dividir em commits pequenos se houver código necessário.

Sugestão:

### D3.1
Harness e contratos de integração.

### D3.2
E2E isolado.

### D3.3
Auditoria de migração/readiness e ferramentas somente leitura.

### D3.4
Runbook de cutover/rollback e ajustes finais encontrados pelos testes.

Não forçar quatro commits se não houver mudança real correspondente.

Nenhum push.

---

# 32. Critérios para considerar D3 aprovada

A D3 só pode terminar como READY FOR CUTOVER se:

- suíte SQLite passar;
- suíte MySQL passar;
- E2E completo passar;
- cross-tenant passar;
- extensão 1.5 contratualmente validada;
- mock n8n/WAHA passar;
- WhatsApp fail-closed confirmado;
- token lifecycle confirmado;
- last-owner concurrency confirmado;
- trial confirmado;
- registration check confirmado;
- automation idempotency confirmada;
- nenhum secret vazado;
- runbook pronto;
- rollback pronto;
- estratégia do legado documentada;
- nenhuma produção alterada.

Se um item obrigatório falhar, reportar:

**D3 PARTIAL — CUTOVER BLOQUEADO**

e explicar exatamente o bloqueador.

---

# 33. `executed.md`

Atualizar o `executed.md` de forma exaustiva com:

1. HEAD inicial;
2. commits encontrados da D2;
3. auditoria inicial;
4. matriz de readiness;
5. arquitetura do harness;
6. contratos exatos;
7. testes da extensão;
8. testes n8n mock;
9. testes WAHA mock;
10. fluxo E2E;
11. resultados cross-tenant;
12. resultados RootAdmin/roles;
13. last-owner concurrency;
14. SQLite;
15. MySQL;
16. frontend;
17. Pint;
18. PHPStan;
19. performance/query audit;
20. dados legados;
21. `provider_message_id`;
22. estratégia de migração;
23. riscos;
24. runbook de cutover;
25. runbook de rollback;
26. commits;
27. confirmação de nenhum push;
28. confirmação de nenhuma alteração em produção.

---

# 34. Parar ao concluir a D3

Não iniciar automaticamente:

- deploy;
- cutover;
- Fase E;
- remoção do legado;
- migração real;
- alteração de n8n real;
- alteração de WAHA real;
- publicação da extensão.

Ao final, parar e aguardar autorização.

A próxima etapa, se a D3 estiver aprovada, será uma fase separada de **CUTOVER CONTROLADO EM PRODUÇÃO**.
