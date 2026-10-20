# VETOR-CHAT-12.6 — Relatório de execução

**Resultado: PILOTO OPERACIONAL NÃO APROVADO; execução segura parcial concluída.** `correio.md` mudou de CHAT-12.5 para o novo escopo CHAT-12.6. O pedido anterior foi preservado em `docs/vetor-chat-12/chat125-correio.md` e o relatório anterior em `docs/vetor-chat-12/chat125-executed.md`; esta solicitação foi arquivada em `docs/vetor-chat-12/chat126-correio.md`. A revisão operacional foi somente leitura. O site e o widget carregaram, mas não enviei mensagem: o widget não oferece isolamento de identidade de teste, e uma mensagem criaria registros reais no VetorLeads. Não recomendo liberação pública neste estado.

## 1. Versionamento, checkout e imagens publicadas

- Checkout raiz: branch `main`, HEAD `f0ca7df`. VetorLeads: HEAD `f072ba01e1206ac876b806ddfeb9aecfabe890c8`; VetorOS: HEAD `953b90a825c76239055c29f90d77530770e6c2a5`.
- Os dois submódulos estão modificados: VetorLeads tem 36 entradas no status Git e VetorOS uma. São alterações acumuladas de fases anteriores, não um pacote CHAT-12.6 recém-homologado. Não descartei, agrupei ou stageei nada.
- Em produção, VetorLeads app/worker/scheduler usam `vetorleads:prospect09-00282e4`, imagem `sha256:2469df8808be3a946b2fa3641f8bad7aa030f138a33314d63f0fdc00550a4d37`. VetorOS app usa `infra-abrasil-vetoros:latest`, imagem `sha256:5f17c3fef2fee54c5bfe9fbf337560c7e3c31e636682f20ff7fdd9077502af16`. Serviços listados estavam saudáveis na consulta.
- As imagens não têm label de revisão Git, portanto não é possível provar a árvore completa que as gerou. O SHA-256 do widget fonte VetorOS local coincide com o arquivo dentro da imagem ativa. O `WebChatController.php` VetorLeads também coincide, mas `SupportAutomation.php` difere em dois trechos de código entre local e imagem; os trechos têm comportamento equivalente para os casos observados, mas isso impede declarar identidade integral checkout/imagem.
- `.env` dos projetos, `vendor`, `node_modules`, banco de dados e backup consultados são ignorados pelo Git. Não encontrei esses itens staged ou no status versionável. Artefatos de relatórios anteriores permanecem não rastreados na raiz e precisam ser separados conscientemente antes de preparar uma versão.
- Não criei commits, push ou imagens. Não preparei commits de release porque o piloto público e a identidade isolada ainda não foram homologados e o checkout mistura alterações acumuladas.

A evidência redigida de imagens e versão está em `docs/vetor-chat-12/chat126-evidence.json`.

## 2. Revisão das 14 regras

Consultei as 14 regras do tenant Anderson Brasil em transação `READ ONLY`, encerrada com `ROLLBACK`. Todas estão inativas, no canal `both`; nenhuma tem horário/fuso configurado. O estado observado foi 0 ativas e 14 inativas.

As regras cobrem boas-vindas; apresentação do VetorOS; assistência técnica; clientes/equipamentos; ordens de serviço; orçamentos; peças/estoque; vendas/financeiro; fiscal; planos/contratação; demonstração; suporte; fora do expediente; transferência humana. O conteúdo de produto é específico, evita preços e, em geral, não promete confirmação ou prazo. A regra de demonstração deixa claro que não reserva horário. As respostas de suporte e transferência informam que o retorno depende da disponibilidade.

Dois pontos impedem aprovação do conteúdo para ativação:

1. A regra de planos afirma que, após o teste, o pagamento é somente Pix. O site público `/planos` confirma teste de 14 dias sem cartão, mas o cadastro de cobrança está em homologação, sem métodos habilitados nem homologação concluída. A página pública de FAQ também diz Pix; não consegui confirmar que essa forma de pagamento está pronta para clientes. Confirmar a condição real com a área responsável ou remover a afirmação tanto da regra quanto da FAQ antes de ativar.
2. A regra “Atendimento fora do expediente” diz que expediente precisa ser configurado, e sua agenda está vazia. Sem dias, fuso e horário aprovados, não há como validar encaminhamento fora de expediente.

Não alterei regra ou conteúdo operacional. As 14 continuam inativas.

## 3. WebChat público e identidade de teste

- `https://vetoros.com.br/` respondeu HTTP 200. Chromium abriu o widget: painel visível, nenhum erro JavaScript e nenhuma requisição não-GET/HEAD. Não enviei mensagem nem formulário.
- O widget usa uma sessão aleatória guardada no navegador, mas não possui identidade/marcador de homologação. O endpoint `/chat/conversations` sempre cria conversa, mensagem e eventos operacionais e chama o motor de automação. Identificação com assunto comercial pode criar um lead no CRM. Não há isolamento para que o teste receba respostas sem expor outros visitantes a uma regra temporariamente ativa nem para separar os registros sintéticos dos indicadores comerciais.
- Na produção, as regras estão todas inativas, então uma conversa pública também não validaria a resposta automática. Ativar temporariamente uma regra sem isolamento poderia responder a qualquer visitante.
- Por isso a jornada solicitada — resposta automática, qualificação, contato/lead CRM, transferência, resposta do operador e encerramento — não foi executada no site público. O único perfil encontrado no tenant é uma associação `owner`, com e-mail não verificado; isso não confirma operador disponível para tomar e responder à conversa.
- O WebChat foi apenas verificado visualmente e por GET. A validação ponta a ponta realizada em CHAT-12.5 foi sintética/local e não substitui esta homologação operacional.

**Pendência técnica:** preparar um caminho de teste autorizado e isolado no servidor/widget, com marcador persistente de identidade sintética que não entre nos indicadores comerciais nem permita que regras temporárias atinjam visitantes comuns. Homologar essa alteração em ambiente isolado antes de qualquer publicação. Nenhuma publicação foi feita nesta execução.

## 4. WhatsApp/WAHA

- Registro do tenant 1 aponta sessão `connected`, provisionada, última conexão em 18/10 às 17:55 UTC e última atividade observada em 19/10 às 16:02 UTC. Não foi feita chamada de envio ou teste de webhook nesta execução.
- Trava de automação WhatsApp do tenant está desligada; `whatsapp_automation_homologated_at` é nulo. Flag global efetiva de entrega também é `false`; outbox está vazia; nenhuma das 14 regras está ativa.
- A conexão parece provisionada, mas recebimento real, HMAC em runtime, reconciliação, deduplicação e resposta com WAHA não foram revalidados ao vivo. Não enviei mensagens. O correio condiciona o envio real a autorização específica de sessão, destino, conteúdo e escopo; essa autorização não consta nesta solicitação. A trava permanece desligada.

## 5. Atendimento humano e indicadores

A central e os fluxos humanos têm cobertura sintética registrada em CHAT-12.5. Nesta rodada não criei conversa operacional, não identifiquei contato e não gerei lead sintético, então não há interação artificial adicionada aos indicadores. Produção mostra apenas uma associação owner não verificada; disponibilidade, fila e tempo de resposta não foram confirmados. Não é seguro prometer handoff funcional ou horário de atendimento até haver operador confirmado.

## 6. Liberação recomendada e pendências

**Não liberar o atendimento automatizado publicamente.** Manter as 14 regras inativas e as duas travas WhatsApp desligadas. Para concluir um piloto controlado, ainda é necessário:

1. Implementar e validar isolamento de teste no WebChat, sem contaminar CRM/indicadores nem responder a visitantes comuns; publicar essa mudança somente após autorização própria.
2. Confirmar operador verificado e disponível, fila/plantão e dias/fuso/horário de atendimento.
3. Confirmar meios de pagamento homologados e alinhar regra de planos com o site público.
4. Executar a jornada WebChat completa com identidade de teste autorizada; depois, reconciliar e limpar apenas dados de teste conforme procedimento aprovado.
5. Para WhatsApp real, obter autorização específica com sessão, número de destino, conteúdo e escopo. Até lá, não enviar nem habilitar.
6. Fechar proveniência de Git/imagens e separar os arquivos acumulados por versão antes de preparar commits reproduzíveis.

Esta execução não alterou código, banco ou regras; não ativou automação; não enviou mensagens; não criou commits, push ou deploy. A consulta operacional e a verificação visual pública estão resumidas em `docs/vetor-chat-12/chat126-evidence.json`.

**Distinção de estado:** widget publicado e carregando: confirmado; teste WebChat público: somente leitura; homologação ponta a ponta controlada: não realizada; WhatsApp: preparado mas sem teste real; atendimento público ativado: não.
