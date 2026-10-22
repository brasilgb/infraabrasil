# Execução — VETORCHAT-SAAS-05

Data: 22/10/2026. **Classificação: BLOQUEADO para ativação.** A revisão local encontrou arquitetura e testes relevantes implementados, mas o conflito preexistente do gitlink VetorPet não pôde ser finalizado porque `.git/modules/gateway/vetorpet` está somente para leitura. Não houve deploy nem homologação na VPS; não declarar operação real homologada.

## Comparação da instrução

`correio.md` mudou de VETORLEADS-DEPLOY-01 para VETORCHAT-SAAS-05. O texto anterior está no `HEAD` e o novo no working tree; o SHA-256 da instrução atual consta em `docs/vetorchat-saas-05/instruction-hashes.txt`. A instrução e o relatório anterior foram arquivados em `docs/vetorchat-saas-05/`.

## Auditoria e achados

- O VetorLeads mantém a gestão central. O widget resolve o tenant pela chave pública no servidor; produtos são identificados separadamente. Os quatro templates apresentam `data-product` distintos (`abrasil`, `vetoros`, `vetorleads`, `vetorpet`). A chave pública de 48 caracteres é configuração de cada instalação; valores reais e sua correspondência com o tenant ABrasil/VPS não foram lidos nem validados nesta execução.
- Migrations e código inspecionados definem escopo de tenant para produtos, regras, conversas e conexões WAHA. A conexão de WhatsApp tem sessão e segredo HMAC próprios por tenant. O webhook confere HMAC-SHA512, tenant ativo e sessão provisionada; eventos de mensagem são idempotentes, ACKs reconhecem transições de entrega e falhas de concorrência retornam 409 para retry. A cobertura correspondente passou nos testes do VetorLeads abaixo.
- A inbox central reúne WebChat e WhatsApp, com filtros de produto/canal/status, atribuição, mensagens e estado. Resposta no widget é armazenada na conversa original. A ponte para WhatsApp é ação explícita do operador, exige consentimento e sessão provisionada, bloqueia contato duplicado/ambíguo entre conversas e pausa automação durante transferência.
- O código inclui limites de requisição, validação exata de origem, token do visitante separado, deduplicação, opt-out e bloqueios de contato. A revisão de código não substitui avaliação jurídica de LGPD nem prova o comportamento em produção.
- A automação de atendimento existente é determinística, configurável e opcional; `SupportAutomation` declara não usar IA nem entrega externa para a decisão. VetorLeads tem serviço de IA para análise/qualificação de prospects, mas não há evidência de que ele esteja integrado à inbox para responder conversas. IA de atendimento não está operacional. Próxima etapa: propor e implementar um adaptador de resposta assistida com escopo por tenant/produto, trilha de auditoria, proteção de dados, revisão humana e envio desligado por padrão, reutilizando provedor/configuração existente somente após verificar disponibilidade e autorização.

## Git e VetorPet

- O índice raiz tinha conflito `UU gateway/vetorpet`, com estágios 1/2/3; as pontas identificadas eram `be43aee`, `0321426` e `5ab9ab0`. Os branches `main` e `origin/main` do submódulo divergiam. `git merge-tree` não indicou conflitos textuais e mostrou alterações distintas em ambas as linhas.
- O checkout atual do submódulo está em `5ab9ab0` e contém alterações locais relevantes para o widget: `.env.example`, `config/services.php`, `resources/views/app.blade.php` e o teste novo `tests/Feature/VetorChatInstallationTest.php`. Foram preservadas.
- Tentei iniciar o merge local para resolver o gitlink, mas o Git não conseguiu gravar `ORIG_HEAD.lock`: o diretório de metadados `.git/modules/gateway/vetorpet` é somente leitura. Não alterei o índice, não removi os estágios do conflito e não escolhi uma ponta como vencedora. A resolução permanece pendente até o metadado Git aceitar escrita; então deve-se mesclar `origin/main` com `main`, revisar a árvore resultante, preservar as alterações acima e atualizar o gitlink raiz.

## Testes locais

| Produto/escopo | Resultado |
|---|---|
| VetorLeads — widget, produtos, inbox, WhatsApp por tenant e instalação | **45 passaram; 434 assertions.** A primeira execução recebeu 404 por cache de rotas antigo; repetição com `APP_ROUTES_CACHE=/tmp/vetorchat-sa05-no-routes.php` passou. |
| ABrasil Sistemas — instalação institucional | **10 passaram; 40 assertions.** |
| VetorOS — instalação e proxy de WebChat | **11 avisos; 34 assertions; sem falha de processo.** Inicialmente faltava APP_KEY; com chave sintética temporária, os testes ficaram como avisos por limitações/fixtures ambientais locais. Não contam como homologação do proxy. |
| VetorPet — instalação | **Não executado:** o submódulo não tem `vendor/autoload.php`. Dependências não foram instaladas. |

São testes locais, com configuração sintética. Não foi feita navegação em produção, recebimento de mensagem de cliente, envio real pelo WAHA nem verificação do HMAC contra uma sessão operacional. Evidência de navegador da etapa anterior não cobre os novos quatro produtos nem o estado atual da VPS.

## Pendências e checklist de ativação

- [ ] Tornar gravável o metadado Git do submódulo e concluir/revisar a mesclagem do VetorPet sem perder alterações locais; confirmar o gitlink no índice raiz.
- [ ] Em ambiente de homologação isolado, instalar dependências já previstas do VetorPet e executar seus testes; corrigir as limitações ambientais do conjunto VetorOS e repetir os testes.
- [ ] Confirmar no painel qual tenant é ABrasil, configurar/verificar chave pública compartilhada e quatro produtos ativos com origens HTTPS corretas. Não colocar chave no repositório.
- [ ] Confirmar que cada tenant externo tem sua própria chave, sessão WAHA, segredo HMAC, autorização e domínio; validar que falhas nunca cruzam tenants/produtos.
- [ ] Preparar plano de backup, migrations compatíveis, build e rollback da VPS; este passo foi apenas solicitado, não executado.
- [ ] Homologar em ambiente autorizado: widget → inbox do produto correto, resposta WebChat, ponte WAHA com consentimento, webhook assinado, retry/ACK/idempotência, opt-out e isolamento.
- [ ] Verificar controles de privacidade e retenção com responsáveis; decidir separadamente se/como habilitar IA de atendimento.
- [ ] Só após os itens anteriores, classificar como PRONTO PARA ATIVAÇÃO ou PRONTO COM RESSALVAS. Ativação em VPS depende de autorização explícita.

Nenhum código de aplicação, configuração da VPS, migration ou container foi alterado. Nenhuma mensagem real foi enviada. O relatório anterior permanece arquivado em `docs/vetorchat-saas-05/executed-anterior.md`.
