# VETOR-CHAT-12.1 — Publicação e homologação operacional

Executado em **19/10/2026 UTC**.

**Estado: publicado; infraestrutura saudável; homologação operacional parcial.** VetorLeads, seu scheduler/worker e a aplicação/widget público do VetorOS foram atualizados. As três migrations pendentes foram aplicadas depois de backup e ensaio em cópia MySQL 8.4. O envio automático de WhatsApp continua globalmente desativado. O teste sintético de webhook passou, mas não há regras ativas em nenhum canal; não foi possível homologar respostas automáticas, transferência humana ou outbox. Não houve envio real, campanha, push ou commit.

## Comparação

O correio mudou de **VETORLEADS-PROSPECT-08** para **VETOR-CHAT-12.1**.

- Hash anterior: `00c7f00a20abb3be555f249a18bc801834e378ab00327a62d4e2d16f9b3c19af`.
- Hash atual: `7625b2d9927b6a37fbcb0d14e3ea94b24c3852c8d0e3ffecf4396a9cd5ea4678`.

A instrução nova pede publicação coordenada e testes operacionais dos canais. O relatório CHAT-12 e o roteiro anterior foram consultados. As mudanças locais do VetorLeads e do widget VetorOS foram preservadas e incluídas nas imagens publicadas.

## Auditoria, backup e ensaio

- MySQL operacional: **8.4.11**; banco VetorLeads com aproximadamente **2,63 MB** de tabelas.
- Backup pré-publicação do banco: [vetorleads_pre_chat121_20261019.sql](backups/vetorleads_pre_chat121_20261019.sql), **475 KB**, SHA-256 `afda4842726528b84dde5a82f2586e938342380f24b1cfc8ced7f45b920a5193`, permissão `600`.
- Configurações salvas em [chat121-prepublish-20261019](backups/chat121-prepublish-20261019), diretório `700`; arquivos `600`. Inclui o `.env` operacional e configuração do Compose/Nginx.
- Restaurado backup em schema temporário e ensaiadas, em ordem, as migrations comercial, CHAT-11 e CHAT-12. Todas passaram no MySQL 8.4. O schema temporário e a permissão temporária foram removidos após o ensaio.
- Imagens de produção de VetorLeads e VetorOS construídas com sucesso. A geração do bundle VetorOS emitiu um aviso de chunk PDF grande; build concluído.

## Publicação

Migrations aplicadas na base operacional, lote 6:

1. `2026_10_19_130000_add_commercial_proposals_and_closure`
2. `2026_10_19_180000_create_support_automation_tables`
3. `2026_10_19_190000_extend_support_automation_multichannel`

VetorLeads, worker e scheduler foram recriados. A aplicação VetorOS foi recriada para publicar o widget; seus worker e scheduler, sem alterações de backend nesta entrega, permaneceram ativos. Os serviços verificados ficaram saudáveis. Nginx passou em `nginx -t`. O scheduler VetorLeads registra `support:deliver --limit=20` a cada minuto.

A configuração efetiva `support_automation.whatsapp_delivery_enabled` foi confirmada como **false**. Nenhuma sessão foi alterada. O bundle servido pelo Nginx contém a mudança visual do widget que identifica respostas do assistente.

## Homologação feita

**WebChat público do VetorOS:** `https://vetoros.com.br/` respondeu HTTP 200. Chromium carregou o widget, abriu o painel “Atendimento VetorOS” e não registrou erros JavaScript. Nenhuma mensagem foi enviada durante esse teste visual.

**WhatsApp/WAHA:** existe uma sessão conectada e provisionada. Um evento sintético marcado como teste foi enviado à API operacional, sem destinatário real e sem envio de resposta:

- HMAC inválido: **401**.
- HMAC válido, tenant ativo e evento aceito: **201**; receipt registrado.
- Replay do mesmo evento: **200**, idempotente; permaneceu um único receipt.
- Trava global: **false**.
- Regras ativas: **zero** no WebChat e **zero** no WhatsApp.
- Outbox: **zero** itens.

O evento sintético e seu histórico técnico foram mantidos no banco para auditoria; não foram apagados. Nenhuma mensagem real foi enviada ou recebida nesse teste.

## Pendências para homologação integral

Não foi criada regra temporária em produção: isso poderia responder a visitantes reais durante o teste. Sem regras revisadas e ativas, não há resposta automática para validar no WebChat ou WhatsApp, nem transferência humana, retorno à automação, conteúdo de outbox, ACK/echo real, opt-out ou recuperação de entrega. O fluxo de qualificação/CRM também não foi exercitado no ambiente operacional.

O correio exige autorização explícita para enviar a um destinatário real de teste. Essa autorização não foi dada, então a sessão permaneceu conectada, mas sem ativação de automação. Não ativar tenants adicionais nem campanhas. Para concluir a homologação, revisar e aprovar as regras e o tenant de teste; depois, autorizar uma sessão e destinatário de teste para a etapa de envio real. Até lá, **não declarar homologação integral dos dois canais**.

## Estado do código e da operação

As alterações dos repositórios continuam locais e sem commit; não houve push. A publicação usou as árvores locais existentes, incluindo alterações acumuladas anteriores. Não foram descartadas alterações preexistentes. O backup e as configurações pré-publicação estão preservados nos caminhos acima. A trava final de envio WhatsApp permanece **desligada**.
