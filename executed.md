# Execução de `correio.md`: FISCAL-SPEDY-04 + ADENDO — ADMINISTRAÇÃO FISCAL CENTRAL NO ROOTADMIN E NOTAS DO SAAS (COMMIT LOCAL, SEM PUSH, SEM DEPLOY)

- Data: 2026-10-06/07 (America/Sao_Paulo).
- SHA-256 do `correio.md` executado: `73c77f4dfa42ec0469369ea806bcf31ff5e88c7ef1691eaa8922702b028a2e14`.
- SHA-256 da execução anterior: `d2ddac1750f451d239bfba5111fbd3cd432b04d3e18f035cb23750ec66623c87` (VETOROS-STABLE-01). O arquivo mudou, então foi executado.
- Implementação reaproveita os serviços, models e migrations fiscais existentes; registros manuais, NF-e/NFC-e/NFS-e, webhooks, sincronização, cancelamentos e PDF/XML preservados.

## 1. Estrutura implementada

### RootAdmin → Fiscal (`/admin/fiscal`)

Novo grupo de rotas (`routes/admin-fiscal.php`) com o middleware `RootAdminOnly`: só passa usuário **sem tenant e com papel root**. Os demais recebem **403 direto** (inclusive em requisição web, sem o redirect do handler global); visitante vai para o login. Menu do admin: "Notas SaaS" virou **Fiscal**. A tela antiga de registros (`/admin/fiscal-documents`) continua acessível como "Registros anteriores".

| Aba | Conteúdo |
|---|---|
| **Integração Spedy** | ambiente sandbox/produção; chave titular **somente escrita** (mostra só o final ••••1234 e a origem: painel ou variável de ambiente); diagnóstico de conexão (`GET /companies`, sem emissão) só por botão; configuração/verificação do webhook `invoice.status_changed` (reaproveita o existente ou cria) e gravação do segredo; histórico das alterações críticas |
| **Empresas emissoras** | lista com CNPJ, nome, situação do cadastro, certificado, ambiente e pendências; habilitar/bloquear emissão; liberar NF-e, NFC-e e NFS-e por empresa; aprovar/revogar produção (exibe quantas notas foram autorizadas em homologação); cadastrar/sincronizar na Spedy; consultar cadastro e certificado na Spedy (ação explícita) |
| **Monitoramento** | filtros por período, empresa e modelo; totais por status; documentos por empresa/modelo; falhas com motivo; pendentes de reconciliação (inclusive envios sem confirmação); webhooks recebidos/não processados; histórico de operações; **utilização mensal** de notas autorizadas como base para política comercial futura (sem cobrança). Só lê o banco |
| **Notas do SaaS** | cadastro do emitente da plataforma, certificado A1, confirmação tributária e aprovação de produção; seleção do cliente contratante (plano, período, situação e vencimento da assinatura); pagamentos aprovados com o **valor efetivamente cobrado** e período de referência sugerido (editável); emissão manual da NFS-e; situação, PDF/XML, envio por e-mail com histórico de entregas e cancelamento |

### Segurança e persistência

- **Fonte única da chave** (`SpedyPlatformConfig`), com precedência por campo: valor gravado pelo RootAdmin em `spedy_platform_settings` (criptografado) > variável de ambiente (`services.spedy.*`, usada como bootstrap). `SpedyClient` e webhook usam só esse resolvedor. Sem duplicação de fonte.
- **Rotação:** gravar uma nova chave substitui a anterior, registra a data e o final da chave e invalida o webhook e o segredo, que precisam ser reconfigurados.
- **Troca de ambiente:**
  - sandbox → produção exige confirmação explícita e **descarta os cadastros de sandbox** (empresas e emitente da plataforma), que não valem em outra conta;
  - produção → sandbox é **bloqueado** se houver empresas cadastradas, porque as chaves de produção não podem ser recuperadas.
- **Confirmação adicional (senha do RootAdmin):** credencial e ambiente, webhook, aprovação de produção de empresa ou do emitente e cancelamento de nota do SaaS.
- **Auditoria** em `fiscal_admin_audits` (usuário, empresa, ação, alvo, IP; nunca segredos): credenciais, ambiente, diagnóstico, webhook, habilitação/bloqueio, cadastro remoto, consulta, emitente, emissão, cancelamento e envio das notas do SaaS.
- **Habilitação por padrão desligada.** A emissão do cliente exige, nesta ordem:
  1. plataforma configurada;
  2. empresa habilitada;
  3. **modelo liberado pelo RootAdmin**;
  4. **produção aprovada**, se o ambiente for produção;
  5. cadastro;
  6. certificado;
  7. confirmação tributária;
  8. CSC (NFC-e).
- Nenhuma chamada à API ao abrir as telas; segredos fora de HTML, JSON, props Inertia e logs. Chaves em `$hidden`; o teste verifica a ausência na resposta, no banco cru e no log.
- **On-premise:** não existe modo on-premise neste repositório (ONPREM-03 ausente). A chave titular nunca vai ao frontend; impedir sua presença numa futura instalação on-premise depende desse modo existir.

### Responsabilidades do cliente (`Sistema → Configurações fiscais`)

- Mantém: dados fiscais, certificado A1, regime, IE/IM, CSC/ID da NFC-e, preferências, status de habilitação, emissão e gestão dos próprios documentos.
- **Removido:** botão e rota de cadastro do emitente na Spedy. O cadastro remoto agora é comandado pelo RootAdmin.
- **Bloqueado:** trocar para produção sem aprovação. A opção aparece desabilitada e a validação no backend recusa.
- Ao salvar, sincroniza **só as configurações de emissão** com a chave da própria empresa (`SpedyCompanyService::syncSettings`). Os dados cadastrais vão à Spedy apenas pelo RootAdmin (chave titular).

### Notas do SaaS (adendo)

- **Emitente próprio:** `admin_fiscal_settings` (ABrasil Sistemas), independente dos dados dos clientes, com cadastro próprio na Spedy, chave de empresa criptografada, certificado, regime, IM, município, item LC 116, ISS e tipo de tributação. Não usa o tenant do cliente como emitente.
- **Vínculo financeiro:** a NFS-e nasce de um `Payment` com status `approved`. O valor é o `amount` cobrado, não o preço de tabela (teste: plano a R$ 89,90 e pagamento de R$ 59,90 → nota de R$ 59,90). O plano vem do `external_reference` do pagamento; o período de referência é sugerido pela data de aprovação e os meses do plano (mensal, semestral e anual preservados) e pode ser ajustado.
- **Nunca automático:** nada é emitido por existir assinatura ou por vencimento/aprovação de cobrança; só por ação do RootAdmin.
- **Duplicidade:** trava no pagamento + uma nota ativa (processando/contingência/autorizada) por pagamento. Rejeitada/falha reenvia com o **mesmo** `integrationId`; substituir uma autorizada exige cancelá-la antes.
- **Separação:** documentos em `admin_fiscal_documents`, arquivos em `fiscal/saas/...`, telas próprias. O webhook só aplica evento do SaaS se o CNPJ do emissor for o da plataforma.
- **E-mail:** envio do PDF com registro em `admin_fiscal_document_deliveries` (data, destinatário, resultado, usuário). Falha de envio fica registrada e **não reemite** a nota.
- `SpedyInvoiceState` passou a concentrar mapeamento de status, regra de regressão, datas e resumo, usados pelas notas dos clientes e pelas do SaaS. `fiscal:sync-spedy` e o job `StoreFiscalDocumentFiles` cobrem os dois tipos.

### Migration

`2026_10_07_100000_create_fiscal_central_administration`:
- tabelas `spedy_platform_settings`, `fiscal_admin_audits` e `admin_fiscal_document_deliveries`;
- liberações por modelo e aprovação de produção em `fiscal_settings`;
- emitente na Spedy em `admin_fiscal_settings`;
- ciclo de vida, período de referência e guarda de arquivos em `admin_fiscal_documents`.

**Defeito encontrado só no MySQL:** o nome padrão da FK de entregas passava de 64 caracteres (erro 1059); foi definido nome explícito.

## 2. Fluxos de permissão

| Ator | Pode | Não pode |
|---|---|---|
| RootAdmin (sem tenant, papel root) | tudo em `/admin/fiscal`; operações sensíveis com senha | — |
| Administrador/operador de tenant | configurar dados fiscais próprios, enviar certificado, emitir e gerir as próprias notas | acessar `/admin/fiscal` (403), cadastrar emitente na Spedy, liberar modelos, aprovar ou ir para produção, ver notas de outra empresa |
| Visitante | — | `/admin/fiscal` redireciona ao login |

## 3. Resultados dos testes

- Novo `tests/Feature/Admin/FiscalCentralAdministrationTest.php`: **14 testes / 130 asserções**, todos com `Http::fake` + `preventStrayRequests`:
  - RootAdmin × admin/operador de tenant (403) × visitante;
  - chave somente escrita, precedência banco > env, senha, nada vazado;
  - regras de troca de ambiente;
  - diagnóstico e webhook com segredo guardado e não exibido;
  - falha da Spedy sem vazar chave em resposta ou log;
  - liberação/bloqueio efetivos da emissão (modelo, produção com senha, bloqueio);
  - cliente sem rota de cadastro e sem produção;
  - cadastro remoto pelo RootAdmin com a chave titular;
  - monitoramento só pelo banco e com filtro;
  - NFS-e do SaaS com valor cobrado, emitente próprio, sem misturar com notas de cliente e sem duplicidade;
  - pagamento não aprovado e usuário de tenant barrados;
  - falha de comunicação mantém "processando"; cancelamento exige senha;
  - entrega por e-mail registrada e falha sem reemissão;
  - webhook do SaaS só com o CNPJ da plataforma.
- Testes fiscais anteriores atualizados para as liberações do RootAdmin: 28/28.
- **Suíte completa: 353/353 em SQLite e 353/353 em MySQL 8.4.11** (instância descartável, encerrada ao final; banco do Compose não usado).
- **MySQL 8.4:** `migrate` (166), **rollback total e reaplicação** OK.
- Achado durante a validação, fora da tarefa: `test_technician_dashboard_returns_summary_and_next_schedule` falhava todo dia entre 22h e 0h UTC ("agora + 2h" caía no dia seguinte). Relógio do teste fixado ao meio-dia, em commit separado.
- `tsc --noEmit`: 0 erros. ESLint nas telas novas e na tela fiscal do cliente: 0 problemas (`any` substituídos por tipos). Pint: arquivos tocados ok. `npm run build`: ok.
- **Correção de registro anterior:** a comparação de ESLint relatada na FISCAL-SPEDY-03.3 usou o formatador `unix`, que não existe nesta versão do ESLint (saída vazia nos dois lados). Refeita agora com o formatador padrão contra `52ad0f3c`: as contagens são idênticas antes e depois em todos os arquivos tocados (InvoiceModal 1/1, SaleInvoiceModal 1/1, edit-tenant 9/9, fiscal-documents 0/0, others 3/3, sales 13/13). A conclusão "nenhum problema novo" se confirma.

## 4. Arquivos

- **Novos:**
  - `app/Http/Middleware/RootAdminOnly.php`
  - `app/Http/Controllers/Admin/Fiscal/{FiscalIntegrationController,FiscalCompanyController,FiscalMonitoringController,SaasInvoiceController}.php`
  - `app/Models/Admin/{SpedyPlatformSetting,FiscalAdminAudit,AdminFiscalDocumentDelivery}.php`
  - `app/Services/Fiscal/SaasInvoiceService.php`
  - `app/Services/Fiscal/Spedy/{SpedyPlatformConfig,SpedyInvoiceState}.php`
  - `app/Mail/SaasFiscalDocumentMail.php`, `resources/views/emails/saas-fiscal-document.blade.php`
  - `routes/admin-fiscal.php`
  - migration `2026_10_07_100000_…`
  - `resources/js/pages/admin/fiscal/{fiscal-tabs,integration,companies,monitoring,saas}.tsx`
  - `tests/Feature/Admin/FiscalCentralAdministrationTest.php`
- **Alterados:**
  - `bootstrap/app.php` (grupo e alias `root.admin`), `routes/app.php` (rota de cadastro do cliente removida)
  - `app/Models/App/FiscalSetting.php` (liberações/produção no bloqueio), `app/Models/Admin/{AdminFiscalSetting,AdminFiscalDocument}.php`
  - `app/Services/Fiscal/NativeFiscalService.php` (usa `SpedyInvoiceState`), `app/Services/Fiscal/Spedy/{SpedyClient,SpedyCompanyService}.php`
  - `app/Http/Controllers/App/FiscalSettingController.php`, `app/Http/Controllers/Integration/SpedyWebhookController.php`
  - `app/Console/Commands/SyncSpedyFiscalDocuments.php`, `app/Jobs/StoreFiscalDocumentFiles.php`
  - `resources/js/Utils/navLinks.ts`, `resources/js/pages/app/fiscal-settings/index.tsx`
  - `tests/Feature/App/{NativeFiscalEmissionTest,TechnicianScheduleApiTest}.php`

## 5. Commits locais (VetorOS, sem push)

| Commit | Conteúdo |
|---|---|
| `1b394b65` | Fixa o relógio do teste do painel do técnico |
| `65ca5009` | FISCAL-SPEDY-04 + adendo (administração central e notas do SaaS) |

O VetorOS está 7 commits à frente de `origin/main`. O gitlink da infra continua em `73955455` (não pedido nesta tarefa).

## 6. Pendências de homologação

1. Gravar a chave titular **sandbox** no painel (ou manter a variável), testar a conexão e configurar o webhook pela aba Integração.
2. Empresa de teste:
   - liberar modelos e cadastrar na Spedy pelo RootAdmin;
   - cliente envia o certificado A1 e confirma os dados tributários;
   - emitir em homologação, conferir no Monitoramento e só então aprovar produção.
3. **Emitente da plataforma:**
   - dados reais da ABrasil Sistemas, certificado A1 e validação contábil (item LC 116, ISS, tipo de tributação);
   - definir se o emitente será a própria empresa titular da conta Spedy ou uma empresa criada (o código cria uma empresa nova);
   - emitir uma NFS-e em simulação a partir de um pagamento aprovado e testar envio por e-mail e cancelamento.
4. **Período de referência:** os pagamentos não guardam início/fim do período. O sistema sugere pela data de aprovação + meses do plano; conferir antes de emitir.
5. Volume persistente para `FISCAL_STORAGE_ROOT` antes de qualquer emissão real (XML/PDF dos clientes e do SaaS).
6. Pendências da STABLE-01 seguem válidas: CC-e e inutilização não implementadas; Regime Normal com imposto destacado bloqueado; débito/crédito no PDV.

Nenhuma emissão real, chamada à Spedy fora dos testes simulados, deploy, push ou alteração em produção.

---

# Execução de `correio.md`: VETOROS-STABLE-01 — SUÍTE ESTÁVEL (339/339 EM SQLITE E MYSQL 8.4), FISCAL REVISADO (COMMITS LOCAIS, SEM PUSH)

- Data: 2026-10-06, 16:00–17:40 (America/Sao_Paulo).
- SHA-256 do `correio.md` executado: `d2ddac1750f451d239bfba5111fbd3cd432b04d3e18f035cb23750ec66623c87` (VETOROS-STABLE-01).
- SHA-256 da execução anterior: `db18dc6ad32a9b92496a5e67e660d8166afd2023fc5598dd8b703ba10ee94d78` (VETOROS-COMERCIAL-02). O arquivo mudou, então foi executado.
- **Atenção:** durante a execução o `correio.md` foi substituído por **FISCAL-SPEDY-04 + ADENDO (Notas do SaaS)** (SHA-256 `73c77f4dfa42ec0469369ea806bcf31ff5e88c7ef1691eaa8922702b028a2e14`). **Não iniciada**; aguarda nova execução.
- `origin/main` do VetorOS já estava em `73955455`: os commits das tarefas anteriores foram publicados fora desta sessão. Os commits desta tarefa existem só localmente.

## 1. Causa de cada grupo de falhas (Fase 1)

Nenhuma das 36 falhas exigiu remover teste. Duas eram defeitos reais (D); as demais, testes desatualizados em relação a mudanças deliberadas do código (T).

| Grupo | Qtde | Tipo | Causa | Correção |
|---|---|---|---|---|
| TechnicianScheduleApiTest | 8 | **D** | `TechnicianScheduleController` usava `OrderStatus::label()` sem `use App\Support\OrderStatus` → **erro 500** em listagem, painel, status, check-in e relatório do app do técnico | import adicionado |
| TechnicianScheduleApiTest | 1 | T (fixture) | a asserção esperava a OS "aberta", mas a factory sorteia `service_status` | status fixado no fixture; a asserção de que a OS não muda foi mantida |
| OrderControllerTest | 6 | T | `order_logs` de criação/status/pagamento/remoção/confirmações foram removidos de propósito em `35a33547` (20/09, junto com a tela de histórico); a auditoria operacional ficou no lugar | asserções passam para `operational_audits` (mesmo usuário/entidade); onde já havia essa asserção, só o bloco obsoleto saiu |
| OsControllerTest | 3 | T | idem (`customer_*_acknowledged`, `feedback_submitted`) | bloco obsoleto removido; `operational_audits` já verificado logo abaixo |
| FollowUpControllerTest | 5 | T | logs de pausa/retomada/resposta/atribuição/adiamento removidos em `35a33547`, **sem substituto** | mantidas as asserções do estado persistido (`*_paused_by`, `*_assigned_to`, `*_snoozed_until` etc.); log obsoleto removido |
| FollowUpControllerTest | 1 | T | `bootstrap/app.php` converte 403 web em redirect com `authorization_error` | verifica redirect, mensagem e que a OS **não** foi pausada |
| PermissionsTest | 2 | T | idem (403 → redirect) | `assertRedirect` + `authorization_error` |
| PaymentControllerTest | 3 | T | o payer do PIX usa `pix_{tenant}@vetoros.com.br` desde `ed638c14` (21/05); o teste esperava o e-mail do tenant. A falha do Mockery no `tearDown` deixava a transação aberta e **derrubava 135 testes seguintes** na suíte completa | asserção atualizada |
| WhatsAppSendControllerTest | 2 | T (mock) | o envio passou a consultar `/api/contacts/check-exists` (nono dígito) antes do `sendText`; só o `sendText` era simulado → chamada real escapava (`ConnectionException`) | fake do check-exists, `Http::preventStrayRequests()` e asserção da consulta; log `whatsapp_sent` (removido em `35a33547`) retirado |
| ProcessCustomerFeedbackRequestsCommandTest | 2 | T | logs removidos em `35a33547` | estado (`*_sent_at`, `*_expired_at`) e e-mail continuam verificados |
| QualityIndicatorControllerTest | 1 | T | idem | `operational_audits` já verificado |
| PartControllerTest | 2 | T | `where('parts.data.1', null)` falha em chave inexistente na versão atual do Inertia | `has('parts.data', 1)` (contagem exata) |

**Decisão a confirmar com o produto:** em `35a33547` o follow-up perdeu a trilha de "quem pausou/atribuiu/adiou" (sobra só `paused_by`). Os testes foram alinhados ao comportamento atual; se a trilha for desejada, o caminho é registrar essas ações em `operational_audits`.

### Antes/depois

| Execução | Antes | Depois |
|---|---|---|
| Suíte completa (modo normal) | 171 falhas (cascata de transação aberta) | **339 passando / 0 falhas** |
| Por arquivo (linha de base anterior) | 294 passando / 36 falhas | 339 passando / 0 falhas |

## 2. Validação MySQL 8.4 (Fase 2)

- Instância **MySQL Community 8.4.11** (binário oficial `linux-glibc2.28-x86_64-minimal` do CDN da Oracle, via HTTPS; o MD5 publicado não pôde ser conferido automaticamente) no scratchpad, porta 33084, `datadir` próprio. Banco real do Compose não utilizado. Instância encerrada ao final.
- `migrate:fresh`: 164 (depois 165) migrations OK.
- **Rollback total falhava** em migrations antigas (só o caminho `down`):
  - `2026_06_10_130000_ensure_technician_schedule_columns_exist`: o `down` apagava colunas de `2026_06_01_120000`, cujo `down` falhava em seguida → `down` vazio, com comentário.
  - `2026_04_06_130000_add_cash_session…`: removia o índice composto antes da FK que o usa (erro 1553) → FK primeiro.
  - `2026_04_02_112214_add_performance_indexes…`: FKs criadas depois passam a usar os índices compostos (erro 1553) → antes de remover cada índice, cria índice simples na coluna de FK quando necessário (só MySQL).
- Depois das correções: rollback total (165 → 0), reaplicação (0 → 165) e `rollback --step=2` + reaplicação das migrations fiscais, tudo OK.
- Duas asserções dependiam da ordem das chaves de colunas JSON (o MySQL normaliza a ordem): passam a comparar com as chaves ordenadas, valores e tipos estritos.
- **Suíte completa no MySQL 8.4: 339 passando / 0 falhas**, incluindo fiscais, comerciais e isolamento entre tenants.
- **Envios simultâneos (MySQL 8.4 real):** 5 rodadas × 4 processos PHP independentes emitindo a mesma venda, com a Spedy simulada respondendo em 2 s. Em todas as rodadas: **1 emissão, 3 bloqueios ("Já existe uma nota fiscal em processamento"), 1 chamada à Spedy e 1 documento**.

## 3. Revisão fiscal (Fase 3)

| Item | Resultado |
|---|---|
| Idempotência em envios simultâneos | OK: trava no registro de origem + `integration_id` único, comprovado no MySQL acima |
| Segurança dos webhooks | OK (assinatura Standard Webhooks, 5 min, deduplicação, só notas próprias). **Reforço:** evento com CNPJ do emissor diferente do tenant da nota é ignorado |
| Erros e reconciliação | **Defeito corrigido:** o `touch()` de cada rodada do `fiscal:sync-spedy` renovava o `updated_at`, e a regra "30 min sem confirmação → liberar reenvio" nunca disparava. Novo `submitted_at` |
| Acesso entre tenants | OK (route binding com tenant, PDF público pela chave da OS, webhook global só por referência própria) |
| Certificados e segredos | OK: `.pfx` e senha não são guardados; chave da empresa e CSC criptografados e em `$hidden`; destinatário redigido no payload |
| **Regras tributárias sem valores presumidos** | **Defeito corrigido.** Antes caía em CFOP 5102, CSOSN 102/CST 0, PIS/COFINS 99, unidade "UN" e `taxationInMunicipality`, e a tela pré-preenchia 0/102/49/UN. As colunas legadas já têm padrões `NOT NULL` (UN/0/102/99) desde abril. Agora: CFOP/NCM obrigatórios na peça; unidade, origem, CSOSN/CST e PIS/COFINS obrigatórios; tipo de tributação da NFS-e configurável; **emissão bloqueada até o tenant confirmar que os dados tributários foram validados pela contabilidade**, e qualquer mudança tributária exige reconfirmação; Regime Normal com ICMS/PIS/COFINS destacado bloqueado (não há base/alíquota no VetorOS) |
| Cancelamentos | OK (só autorizadas, 15–255 caracteres) |
| Carta de correção (CC-e) e inutilização | **Não implementadas**: a Spedy oferece CC-e só para NF-e e inutilização para NF-e/NFC-e. Registradas como pendência |
| Persistência de XML/PDF e rastreabilidade | **Defeito corrigido:** antes só havia download sob demanda. Agora o job `StoreFiscalDocumentFiles` (fila `database` com o worker existente) guarda XML e PDF no disco privado `fiscal` com SHA-256 ao autorizar/cancelar, e o download usa a cópia local |
| Compatibilidade com vendas/OS/PDV | **Defeitos corrigidos:** (1) a venda podia ser cancelada no VetorOS com nota autorizada → bloqueado até cancelar a nota; (2) venda/OS com histórico de nota podia ser excluída → bloqueado; (3) total da venda diferente da soma dos itens → desconto rateado nos itens (`discountAmount`); acréscimo bloqueado |

Nenhuma emissão real nem chamada à Spedy: todos os testes usam `Http::fake` + `preventStrayRequests`.

Testes fiscais: 19 → **28** (novos: confirmação tributária e reconfirmação, nenhum CFOP/CST presumido, Regime Normal bloqueado, rateio de desconto e acréscimo, guarda de XML/PDF com hash e download local, reconciliação após 30 min apesar da rotação, webhook com CNPJ divergente, venda/OS protegidas).

## 4. Validação geral (Fase 4)

| Verificação | Resultado |
|---|---|
| Testes PHP | 339/339 em SQLite e em MySQL 8.4 |
| TypeScript (`tsc --noEmit`) | 0 erros |
| Build (`npm run build`) | OK |
| Pint | todos os arquivos tocados sem problemas novos (só pendências pré-existentes de estilo em arquivos antigos) |
| Migrations | 165; fresh, rollback total e reaplicação no MySQL 8.4 |
| Integridade do Git | `git fsck` limpo nos dois repositórios (um *dangling commit* local de um `--amend`, inofensivo); árvore do VetorOS limpa |

**ONPREM-03:** continua ausente. Nenhuma ocorrência de "ONPREM", "on-premise", "onpremise" ou "on_premise" em mensagens ou conteúdo de todo o histórico (`git log --all`), em branches locais ou remotas (só `main`) ou em stash. Não reconstruída e nenhum outro projeto alterado.

## 5. Commits locais (VetorOS, sem push)

| Commit | Responsabilidade |
|---|---|
| `fd8d4638` | Corrige erro 500 na API do técnico (import de `OrderStatus`) |
| `470db9bb` | Estabiliza a suíte alinhando os testes ao comportamento atual |
| `b9cb04e0` | Corrige o rollback completo das migrations no MySQL 8.4 (+ testes de JSON independentes da ordem das chaves) |
| `1b4e33c6` | Remove linhas em branco deixadas pela estabilização (formatação) |
| `34cd1c13` | Endurece a emissão fiscal nativa (itens da Fase 3; migration `2026_10_06_160000_add_traceability_and_tax_confirmation_to_fiscal_tables`; job `StoreFiscalDocumentFiles`; disco `fiscal`) |

Infra: o gitlink continua em `73955455` (o `git submodule status` mostra `+34cd1c13`). Não foi atualizado porque a tarefa não pediu.

## 6. Riscos fiscais restantes

1. Regime Normal com ICMS/PIS/COFINS destacado não é emitido (bloqueado): falta modelar base, alíquota e redução por item.
2. Forma de pagamento "cartão" do VetorOS é enviada como crédito (`creditCard`); o PDV não distingue débito/crédito nem envia dados do cartão (bandeira, autorização), o que algumas SEFAZ exigem.
3. `isFinalCustomer` é derivado do documento do cliente (CPF = consumidor final). Uma empresa compradora como consumidora final exigiria opção explícita.
4. CC-e (NF-e) e inutilização de numeração não implementadas.
5. NFC-e: credenciamento estadual (SC, PR, ES, PA) e regras de contingência offline não exercitados.
6. NFS-e: `cityServiceCode`/`cnaeCode` e regras por município não modelados; retenções (ISS retido etc.) não suportadas.
7. **Persistência do XML em produção:** o `docker-compose` só monta `storage/app/public` como volume. O disco `fiscal` (padrão `storage/app/private/fiscal`) **se perde ao recriar o container** até que `FISCAL_STORAGE_ROOT` aponte para um volume persistente — alteração de infraestrutura pendente de autorização.
8. Follow-up sem trilha de auditoria das ações (ver decisão na seção 1).

## 7. Pendências de homologação

1. Conta Spedy sandbox (`SPEDY_OWNER_API_KEY`), segredo e webhook `invoice.status_changed`.
2. Tenant de teste liberado no admin, certificado A1 válido e confirmação tributária com dados reais da contabilidade.
3. Emissões em homologação/simulação: NF-e (com e sem desconto), NFC-e (CSC de homologação), NFS-e (simulação), cancelamento, guarda de XML/PDF, webhook e reconciliação.
4. Volume persistente para `FISCAL_STORAGE_ROOT` antes de qualquer emissão real.
5. Migrations novas (`2026_10_06_120000` e `2026_10_06_160000`) ainda não aplicadas em nenhum ambiente real.

Nenhum push, deploy, alteração em produção ou emissão fiscal real.

---

# Execução de `correio.md`: VETOROS-COMERCIAL-02 — CONSOLIDADO EM COMMITS LOCAIS, GITLINK ATUALIZADO (SEM PUSH, SEM DEPLOY)

- Data: 2026-10-06, 15:20–16:00 (America/Sao_Paulo).
- SHA-256 do `correio.md` executado: `db18dc6ad32a9b92496a5e67e660d8166afd2023fc5598dd8b703ba10ee94d78`.
- SHA-256 da execução anterior: `641d7841cc59d10f120f0f52a250442a38e4012c393278f132dfc9954f3c6ba5` (VETOROS-COMERCIAL-01.1). O arquivo mudou, então foi executado.
- Também corrigido o erro relatado pelo usuário na tela **Sistema e módulos**: `Uncaught TypeError: Cannot read properties of undefined (reading 'name')`.

## 1. Correção: tela Sistema e módulos

- Causa: `resources/js/pages/app/others/index.tsx` lia `auth.user.tenant.plan.name`. Desde `d41f3e85` ("Torna lazy as props compartilhadas do Inertia"), a relação `tenant.plan` só é carregada pelo closure `subscription` do `HandleInertiaRequests`, que é pulado quando `roles === 1` (administrador). Para administradores, `plan` chegava indefinido e a tela quebrava. Defeito anterior às tarefas fiscal/comercial.
- Correção: `OtherController@index` envia `licensePlanName` (`$tenant?->plan?->name`) e a tela usa `licensePlanName ?? auth?.user?.tenant?.plan?.name ?? 'Sem plano'`.
- Teste novo `tests/Feature/App/OtherSettingsLicenseTest.php` (2 testes: administrador com plano e tenant sem plano). Falhava antes da correção e passa depois.

## 2. Consolidação comercial e fonte única dos preços

- Revisei as alterações comerciais: nenhuma mistura com arquivos fiscais, de infraestrutura ou de terceiros.
- **Fonte única:** os valores ficam só na tabela `plans`, editada em `/admin/plans` (middleware `admin`: apenas usuários sem tenant, isto é, RootAdmin). O teste confirma que visitante e administrador de tenant não alteram valores.
- **Sem preço hardcoded no frontend:** `pricing-data.ts` não tem mais nomes, periodicidades nem valores de planos; só textos comuns e a mensagem do orçamento. A lista vem do backend. Os valores que antes estavam no frontend (89,90/479,40/838,80) divergiam da tabela `plans` semeada pelas migrations (mensal 49,90; semestral 0; anual 419,16), o que reforça a fonte única.
- **Arquitetura para preços públicos futuros:**
  - `app/Services/Commercial/PublicPlanCatalog.php` monta Mensal (1 mês), Semestral (6) e Anual (12, "Mais popular") lendo `plans` (ignora trial/cortesia).
  - `config/commercial.php` → `public_prices_enabled` (`PUBLIC_PRICES_ENABLED`, padrão **false**, documentado no `.env.example`).
  - Desligado (padrão): o catálogo não consulta valores e não envia `price_label`.
  - Ligado: envia `price_label` formatado no backend a partir do banco (plano com valor 0 fica sem preço). O frontend só exibe `plan.price_label ?? 'Solicite um orçamento'`, sem regra de preço duplicada.
  - `site/PlanController` passa `publicPlans` para a página.
- Mantidos: "Solicite um orçamento", "Consultar pelo WhatsApp" com o número comercial existente, telas de cobrança/renovação dos assinantes e Mercado Pago. Nenhuma regra de cobrança nova.

## 3. Testes e validações

| Validação | Resultado |
|---|---|
| Comerciais `PublicPricingTest` | 7 passando: sem preço em HTML/metadados/JSON-LD/Inertia; fontes sem preço; três periodicidades vindas do backend; valores do banco ocultos com a flag desligada e expostos (do banco) com ela ligada; só RootAdmin altera; assinantes veem os valores |
| `OtherSettingsLicenseTest` | 2 passando |
| Fiscais (`NativeFiscalEmissionTest` + `FiscalDocumentServiceTest`) | 23 passando (verificados na suíte completa) |
| Regressão completa por arquivo (SQLite) | **294 passando / 36 falhas**. Comparada com a linha de base da FISCAL-SPEDY-03.3 (36 falhas): **nenhuma falha nova** |
| `tsc --noEmit` | 0 erros |
| ESLint (arquivos do site alterados) | sem problemas |
| Pint (PHP novo/alterado) | ok |
| `npm run build` | ok; chunks públicos (`hero`, `pricing`) sem nenhum valor; só a chave `price_label` |

## 4. Commits locais

| Repositório | Commit | Conteúdo |
|---|---|---|
| `gateway/vetoros` | `65e5956c` | Emissão fiscal nativa via Spedy (FISCAL-SPEDY-03.3), da execução anterior |
| `gateway/vetoros` | `c4b5c510` | Comercial: preços públicos removidos, `PublicPlanCatalog`, `config/commercial.php`, site e testes. Arquivos: `.env.example`, `config/commercial.php`, `app/Services/Commercial/PublicPlanCatalog.php`, `app/Http/Controllers/Site/PlanController.php`, `resources/js/pages/site/**` (site-contact, pricing, pricing-data, hero, cta, footer, whatsapp-float, plans/index), `resources/views/app.blade.php`, `tests/Feature/Site/PublicPricingTest.php` |
| `gateway/vetoros` | `73955455` | Correção da tela Sistema e módulos: `OtherController.php`, `others/index.tsx`, `OtherSettingsLicenseTest.php`. Commit separado, por não ser fiscal nem comercial |
| `infra-abrasil` | `a8b4564` | `.gitmodules` só com `gateway/vetoros` (URL e branch verificadas) e gitlink de `52ad0f3c` para `73955455`. Outros submódulos não alterados |

Árvore do VetorOS limpa após os commits. Na infra ficam pendentes apenas `correio.md` e `executed.md`, como antes. **Nenhum push**: os três commits do VetorOS existem só neste clone; até o push, quem clonar a infra não encontrará o commit apontado pelo gitlink.

## 5. Pendências técnicas

1. **ONPREM-03 ausente neste clone.** Não há modo on-premise no código, nos commits nem na documentação de `brasilgb/vetoros` (main). As instruções sobre separação cloud/on-premise não puderam ser verificadas. Nenhum modo on-premise foi implementado, como determinado.
2. **Homologação real da Spedy.** Pendente: conta sandbox (`SPEDY_OWNER_API_KEY`), segredo e webhook `invoice.status_changed`, certificado A1 válido, emissões de teste NF-e/NFC-e/NFS-e, cancelamento, PDF/XML, reconciliação e validação contábil dos códigos tributários. Nada foi transmitido.
3. **MySQL 8.4.** As migrations e 41 testes foram validados só em MariaDB 11.8 descartável. Falta rodar `migrate` e os testes fiscais/comerciais em MySQL 8.4 (versão da produção no `docker-compose`).
4. **36 falhas pré-existentes** (sem relação com fiscal/comercial; não corrigidas):
   - FollowUpControllerTest 6
   - OrderControllerTest 6 (`order_logs` esperados)
   - TechnicianScheduleApiTest 9
   - PaymentControllerTest 3 (e-mail do pagador divergente; a falha do Mockery no `tearDown` vaza transação)
   - OsControllerTest 3
   - PartControllerTest 2
   - PermissionsTest 2
   - WhatsAppSendControllerTest 2
   - ProcessCustomerFeedbackRequestsCommandTest 2
   - QualityIndicatorControllerTest 1
5. Publicação dos commits (push do VetorOS e da infra) e deploy: aguardam autorização.

## 6. Confirmações

Nenhum deploy, push, alteração em produção, transmissão de nota fiscal real ou mudança em outros submódulos.

---

# Execução de `correio.md`: VETOROS-COMERCIAL-01.1 — FISCAL COMMITADO LOCALMENTE, COMERCIAL CONCLUÍDO (SEM PUSH, SEM DEPLOY)

- Data: 2026-10-06, 14:50–15:15 (America/Sao_Paulo).
- SHA-256 do `correio.md` executado: `641d7841cc59d10f120f0f52a250442a38e4012c393278f132dfc9954f3c6ba5`.
- SHA-256 da execução anterior: `a5552076e8bf7320a5d2de0ab873b7faf1c6489cfa207b64b1f08600501c3d9c` (VETOROS-COMERCIAL-01). O arquivo mudou, então foi executado.

## 1. Código fiscal preservado

- Revisei `git diff` e todos os arquivos novos do `gateway/vetoros`: todas as alterações vieram das execuções FISCAL-SPEDY-03.3 e VETOROS-COMERCIAL-01. Nenhuma alteração de terceiros. Nenhum segredo no diff (`.env` está no `.gitignore`); `.env.example` só tem variáveis vazias.
- Nenhum reset, checkout destrutivo ou limpeza.
- Antes do commit: `NativeFiscalEmissionTest` + `FiscalDocumentServiceTest` → 23 passando.
- **Commit local `65e5956c`** — "Implementa emissão fiscal nativa via Spedy (FISCAL-SPEDY-03.3)": 37 arquivos, só a parte fiscal:
  - serviços `app/Services/Fiscal/**`, controllers `FiscalEmission`, `FiscalSetting`, `SpedyWebhook`, comando `fiscal:sync-spedy`;
  - models `FiscalSetting`, `FiscalDocument`, `Tenant`; `TenantRequest`; `OsController` (PDF público da NFS-e); `FiscalDocumentController`; `HandleInertiaRequests` (`fiscalSetting.native`);
  - migration `2026_10_06_120000_add_native_emission_fields_to_fiscal_tables.php`, rotas (`api`, `app`, `web`, `console`), `config/services.php`, `.env.example`;
  - telas: configurações fiscais, notas fiscais, modais de venda/OS, admin do tenant e link em Sistema e módulos;
  - testes `NativeFiscalEmissionTest` e os ajustes de ambiente de testes da mesma execução (duas migrations compatíveis com SQLite e `tearDown` protegidos de `CompanyControllerTest`/`AuxiliaryAppControllerTest`).
- Os arquivos comerciais ficaram fora desse commit.

## 2. Alterações comerciais (VETOROS-COMERCIAL-01)

Implementadas na execução anterior e conferidas agora contra os itens da §2:

| Decisão | Situação |
|---|---|
| Preservar planos mensal, semestral e anual | mantidos em `pricing-data.ts` e nos cards de `/planos`/home |
| Preservar todos os preços no banco | nenhuma migration nem escrita em `plans`; teste confirma o valor gravado |
| Preços administrados só pelo RootAdmin | `/admin/plans` fica atrás do middleware `admin` (só usuário sem tenant). **Teste novo**: visitante é mandado ao login, admin de tenant não altera o valor, root altera |
| Remover preços das superfícies públicas | cards, hero e JSON-LD (`offers/price/priceCurrency`) sem valores; HTML, metadados, props Inertia e chunks do build conferidos |
| "Solicite um orçamento" no lugar do preço inicial | hero e cards |
| Botões "Consultar pelo WhatsApp" | cards de planos, com mensagem contendo plano e periodicidade |
| WhatsApp comercial já configurado | `5551998931325`, centralizado em `site-contact.ts`; nenhum número novo |
| Preservar assinaturas e cobranças | telas de renovação/bloqueio para assinantes continuam mostrando os valores; Mercado Pago intocado |
| Sem franquias ou cobrança de notas | nada implementado |

Arquivos comerciais (não commitados): `resources/js/pages/site/components/{site-contact.ts (novo), pricing-data.ts, pricing.tsx, hero.tsx, cta.tsx, footer.tsx, whatsapp-float.tsx}`, `resources/js/pages/site/plans/index.tsx`, `resources/views/app.blade.php`, `tests/Feature/Site/PublicPricingTest.php` (novo).

## 3. Testes

- Comerciais — `PublicPricingTest`: **5 passando / 251 asserções**: páginas públicas sem preço (HTML, metadados, JSON-LD, Inertia), fontes do site sem preço, três periodicidades + WhatsApp contextualizado, preços só pelo RootAdmin, assinantes vendo os valores.
- Fiscais: `NativeFiscalEmissionTest` 19 + `FiscalDocumentServiceTest` 4 → 23 passando.
- Regressão completa por arquivo (SQLite): **291 passando / 36 falhas**. Comparada com a linha de base da FISCAL-SPEDY-03.3 (286 passando / 36 falhas): **nenhuma falha nova**; as 36 são as mesmas pré-existentes (FollowUp, Order, TechnicianScheduleApi, Payment, OsController, Part, Permissions, WhatsAppSend, ProcessCustomerFeedbackRequests, QualityIndicator).
- `tsc --noEmit`: 0 erros. Pint no teste novo: ok. O build de frontend da execução anterior continua válido para os arquivos do site.
- `public/`, `public-old/`, `composer.lock` e `yarn.lock` sem alterações.

## 4. Pendências

1. **Commit comercial:** não criado. O `correio.md` autorizou explicitamente só o commit fiscal; as alterações comerciais estão revisadas, testadas e prontas para um commit separado quando autorizado.
2. **Infra (`infra-abrasil`):** o gitlink `gateway/vetoros` ainda aponta para `52ad0f3c`; o clone está em `65e5956c`. O `.gitmodules` da execução FISCAL-SPEDY-03.3 também não está commitado. Nenhuma mudança na infra sem autorização.
3. Homologação Spedy (credenciais sandbox, webhook, certificado A1 de teste, validação contábil): sem alteração em relação ao relatório FISCAL-SPEDY-03.3.
4. Isolamento cloud/on-premise: continua não verificável — não existe modo on-premise neste repositório.

## 5. Commits locais

| Repositório | Commit | Conteúdo |
|---|---|---|
| `gateway/vetoros` | `65e5956c` | Emissão fiscal nativa via Spedy (FISCAL-SPEDY-03.3) |

Nenhum push e nenhum deploy. Produção e infraestrutura compartilhada intactas.

---

# Execução de `correio.md`: VETOROS-COMERCIAL-01 — PREÇOS REMOVIDOS DO SITE PÚBLICO (SEM COMMIT, SEM DEPLOY)

- Data: 2026-10-06, 14:20–14:45 (America/Sao_Paulo).
- SHA-256 do `correio.md` executado: `a5552076e8bf7320a5d2de0ab873b7faf1c6489cfa207b64b1f08600501c3d9c`.
- SHA-256 da execução anterior: `0d0fafd439847974e5a1bbdf62b3caae84fe5659a1191637bf66418f24a2c3dc` (FISCAL-SPEDY-03.3). O arquivo mudou, então foi executado.
- Projeto: `gateway/vetoros` (HEAD `52ad0f3c`, árvore com as alterações da FISCAL-SPEDY-03.3 ainda não commitadas, preservadas).

## 1. Levantamento

Os valores públicos vinham de um único módulo, `resources/js/pages/site/components/pricing-data.ts` (`MONTHLY_BASE_PRICE = 89.9` e totais 89,90 / 479,40 / 838,80), consumido por:

- `pricing.tsx` — cards de `/planos` e da home (preço, "equivale a R$ …/mês", selo de desconto "-x%");
- `hero.tsx` — "A partir de R$ 89,90/mês, sem taxa por usuário" (home, link para `/planos`).

Também verificados: `app.blade.php` (meta description, Open Graph, Twitter e JSON-LD), FAQ, CTA, rodapé, banner, `welcome.tsx`, termos/privacidade, `public/sitemap.xml`, `robots.txt`, documentação pública e as props Inertia das páginas públicas. O único outro ponto com preço era o JSON-LD `SoftwareApplication.offers` (`price: "0"`, `priceCurrency: BRL`). `/planos` e `/` não recebem planos do backend; o prop compartilhado `plans` só é preenchido para usuário autenticado com tenant.

O WhatsApp comercial já configurado no site é `5551998931325` (usado em CTA, rodapé, botão flutuante, `/planos` e cards). Nenhum número novo foi criado.

## 2. Alterações

| Arquivo | Mudança |
|---|---|
| `resources/js/pages/site/components/site-contact.ts` (novo) | `COMMERCIAL_WHATSAPP` (número existente) e `whatsappLink(mensagem)` — fonte única do link |
| `resources/js/pages/site/components/pricing-data.ts` | sem nenhum valor; mantém Mensal, Semestral e Anual (Anual como "Mais popular"), recursos e `quoteMessage(plano, periodicidade)` |
| `resources/js/pages/site/components/pricing.tsx` | removidos preço, valor equivalente mensal e selo de desconto; no lugar, "Solicite um orçamento"; botão **Consultar pelo WhatsApp** com a mensagem "Olá! Gostaria de solicitar um orçamento do plano {Mensal/Semestral/Anual} (periodicidade {mensal/semestral/anual}) do VetorOS." |
| `resources/js/pages/site/components/hero.tsx` | "A partir de R$ 89,90/mês, sem taxa por usuário" → **"Solicite um orçamento"** (mantém o link para `/planos`) |
| `resources/views/app.blade.php` | removido `offers`/`price`/`priceCurrency` do JSON-LD; descrições SEO/OG mantidas (não tinham preço) |
| `cta.tsx`, `footer.tsx`, `whatsapp-float.tsx`, `site/plans/index.tsx` | links `wa.me` passam a usar `whatsappLink()`; mesma mensagem e número |
| `tests/Feature/Site/PublicPricingTest.php` (novo) | cobertura descrita abaixo |

Não alterado (conforme §4): periodicidades, tabela `plans` e valores no banco, assinaturas, Mercado Pago, histórico financeiro, renovação/vencimento, e a emissão fiscal Spedy (§5 — sem preço, franquia ou diferenciação por plano).

Contratação direta sem consulta: o site público não tem fluxo de compra. O cadastro público cria apenas o trial e não exibe planos nem valores. Os valores aparecem só depois do login, nas telas de renovação/bloqueio (`Subscription/Blocked`, `auth/ExpiredSubscription`) usadas por quem já é assinante — preservadas.

## 3. Testes

- `PublicPricingTest`, 4 testes / 244 asserções:
  1. `/` e `/planos` respondem 200, e HTML, metadados, JSON-LD e o JSON Inertia hidratado não contêm `R$`, valores no formato `00,00`, `/mês`, "por mês", `price`/`priceCurrency` nem `offers`; prop `plans` vazio para visitante.
  2. Nenhum arquivo de `resources/js/pages/site` contém preço, `formatCurrency`/`MONTHLY_BASE_PRICE` ou "A partir de"; hero tem "Solicite um orçamento".
  3. Mensal, Semestral e Anual presentes com periodicidade; mensagem contextualizada com plano e periodicidade; botão "Consultar pelo WhatsApp"; número comercial existente; nenhum `wa.me/` fora de `site-contact.ts`.
  4. Assinante bloqueado continua vendo o plano e o valor (838,80) em `subscription.blocked`; valor no banco inalterado.
- Bundles do build: os chunks públicos (`hero`, `pricing`) não contêm valores.
- Suíte completa por arquivo (SQLite): **290 passando / 36 falhas**, exatamente as 36 pré-existentes da linha de base (diff vazio). Antes desta tarefa: 286.
- `tsc --noEmit`: 0 erros. ESLint nos arquivos do site alterados: sem problemas. Prettier aplicado só aos arquivos alterados. Pint no teste novo: ok. `npm run build`: ok.

## 4. Impedimentos e observações

- **Isolamento cloud/on-premise (§6 e §7): não verificável neste repositório.** Não há modo on-premise no código do VetorOS (nenhuma flag, config, middleware ou rota; ONPREM-03 continua ausente, como registrado na execução anterior). As alterações não reativam nem criam superfícies SaaS; se o modo on-premise existir em outro branch/repositório, precisa ser integrado antes que o teste de isolamento possa ser escrito.
- O site não tem testes de componente JS (sem Vitest/Jest no projeto); por isso a cobertura de componentes é feita por teste PHP sobre as fontes, as respostas HTTP e o build.
- Quando os preços voltarem a ser publicados, os valores precisam ser reintroduzidos em `pricing-data.ts` (não são lidos do banco).

## 5. Confirmações

- Nenhum deploy, migration de produção, alteração no banco, no Mercado Pago ou em serviços compartilhados.
- Nenhum commit e nenhum push. As alterações da FISCAL-SPEDY-03.3 continuam na árvore de trabalho, intactas.
- `public/`, `public-old/`, `composer.lock` e `yarn.lock` sem alterações.

---

