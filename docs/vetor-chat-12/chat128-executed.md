# VETOR-CHAT-12.8 — Laboratório e widget integrados em ambiente isolado

## Resultado e limites

A ponte temporária entre o Laboratório VetorLeads e o widget real VetorOS foi concluída e validada localmente. A jornada cobre abertura, mensagem, intenção, resposta, continuidade, qualificação, prévia de oportunidade, transferência humana, resposta de operador, retorno ao automático, encerramento, expiração e revogação. Os builds e testes dos dois produtos passaram. O lint global do VetorOS permanece reprovado por **442 erros e 11 avisos em arquivos fora do diff**; os arquivos alterados passaram na checagem específica. Não registrar o lint global como aprovado.

Não houve deploy, commit, push, migration operacional, ativação de regras produtivas, alteração de sessão WAHA ou mensagem real. Esta é validação local com dados sintéticos, não homologação pública nem WhatsApp real.

## Auditoria e preservação

Foram lidos `correio.md`, o relatório CHAT-12.7 e os diffs acumulados dos dois repositórios. O relatório anterior está preservado em `docs/vetor-chat-12/chat128-previous-executed.md`, além do arquivo histórico CHAT-12.7. A solicitação CHAT-12.8 está arquivada em `docs/vetor-chat-12/chat128-correio.md`. Alterações anteriores de atendimento, automação, CRM e testes foram preservadas. O laboratório existente foi estendido; não foi reconstruído.

## Implementação

- Emissão/revogação exige administrador autenticado e verificado, com papel owner/admin no tenant. Os endpoints administrativos usam as mesmas travas comerciais do laboratório.
- Código e sessão usam 32 bytes aleatórios cada (256 bits), armazenados somente como SHA-256. O acesso vincula tenant, administrador emissor e conversa sandbox; expira em 30 minutos. O resgate do código é de uso único, com transação e bloqueio da linha. Nova emissão revoga os acessos anteriores da conversa.
- Middleware valida token, expiração, revogação e conversa antes de executar o laboratório. O contexto de tenant vem da credencial validada; uma sessão não pode consultar outra conversa. Os hashes ficam ocultos na serialização do modelo, e respostas que transportam a credencial usam `Cache-Control: no-store`.
- Encerrar ou reiniciar uma conversa revoga as credenciais anteriores. A revogação explícita também invalida a sessão já resgatada. Os limites de consulta, mensagens e emissão possuem contadores separados; foi corrigida uma colisão em que polling consumia o limite de emissão.
- VetorOS usa proxy e endpoints exclusivos `/chat/sandbox/*` → `/api/web-chat/sandbox/*`. O navegador mantém o token somente em memória e envia-o em cabeçalho. O indicador de modo de teste permanece em sessionStorage; recarregar, expirar, revogar ou rejeitar a credencial mantém o widget bloqueado em homologação, sem fallback operacional. Não há parâmetro público de URL que libere rascunhos.
- O widget exige código válido para enviar mensagem de teste. Ao entrar no modo de teste, limpa a exibição da conversa anterior. O atalho para WhatsApp é ocultado durante a homologação. Sair do teste exige ação explícita e restaura a sessão pública anteriormente salva, inclusive após recarregar o navegador.
- O laboratório acompanha as mensagens do widget por consulta autenticada e permite operador, retomada do automático, qualificação e prévia de CRM. Erros de ações administrativas são exibidos na tela. Trocar o canal limpa a seleção incompatível de regras.
- A migration existente `2026_10_19_200000_create_support_sandbox_tables.php` cria apenas conversas/mensagens. A nova `2026_10_19_210000_create_support_sandbox_accesses_table.php` cria credenciais separadamente, permitindo atualizar instalações que já aplicaram o laboratório.

Arquivos principais: `SupportSandboxController`, `Api/SupportSandboxWidgetController`, `ResolveSupportSandboxSession`, `SupportSandboxAccess`, migrations do laboratório/credenciais, rotas administrativas/API, `support-automation/lab.tsx`, proxy `Site/WebChatProxyController`, rotas e componente `site/components/web-chat.tsx`, e testes correspondentes nos dois produtos.

## Testes e builds

| Verificação | Resultado |
|---|---|
| Regressão PHP VetorLeads, SQLite em memória com proteção CHAT-12.5 | **1.318 testes / 13.963 asserções aprovados** |
| Laboratório, jornada widget, autorização, isolamento, expiração, revogação, uso único, ausência de escrita operacional e separação dos limites | **8 testes / 102 asserções aprovados**; incluídos na suíte completa |
| Regressão PHP VetorOS, SQLite em memória | **820 testes / 5.734 asserções aprovados** |
| Proxy público/sandbox, credencial inválida e rejeições 401/403/404 sem fallback | **9 testes / 29 asserções aprovados**; incluídos na suíte completa |
| PHPStan VetorLeads | **Zero erros** |
| TypeScript dos dois produtos | **Aprovado** |
| Testes JavaScript VetorOS em Node 20.19.2 | **4 aprovados**, usando `node --import tsx --test` |
| ESLint global VetorLeads | **Aprovado** |
| ESLint dos arquivos alterados VetorOS | **Aprovado** |
| ESLint global VetorOS | **442 erros / 11 avisos**, todos fora do diff; passivo separado |
| Builds de produção dos dois produtos | **Aprovados**, em cópias isoladas com dependências dos lockfiles |
| Chromium 1440/390 px | **18 verificações responsivas aprovadas**, sem overflow horizontal ou erro JavaScript |
| `git diff --check` nos dois repositórios | **Aprovado** |

As instalações reproduzíveis `npm ci --offline` foram executadas nas cópias locais ignoradas, sem atualizar indiscriminadamente os pacotes nem instalar dependências de desenvolvimento em containers produtivos. `tsx` e o comando de teste corrigem a execução TypeScript no Node 20. O VetorOS emite aviso de chunks maiores que 500 kB; o build conclui. As instruções e os cuidados dos ambientes estão em `docs/vetor-chat-12/chat128-environment.md`.

## Evidências visuais e isolamento

`docs/vetor-chat-12/chat128-interface/results.json` registra a jornada aprovada, a restauração explícita da sessão pública, zero erros JavaScript e zero chamadas operacionais durante o sandbox. As capturas de desktop/celular incluem lista de regras, seleção WhatsApp simulado, resposta no widget, qualificação, operador nos dois lados, encerramento/revogação, erro administrativo e expiração real da credencial no servidor.

A consulta final ao SQLite isolado está em `docs/vetor-chat-12/chat128-isolation.json`: zero leads, atividades comerciais, conversas/mensagens/eventos operacionais, estados de atendimento operacional, decisões de produção, receipts WhatsApp, jobs, failed_jobs e outbox. Todas as regras sintéticas ficaram inativas. As mensagens do ensaio ficaram somente nas tabelas sandbox. Ao concluir, todas as credenciais sintéticas remanescentes foram revogadas e os dois servidores temporários foram encerrados (`chat128-shutdown.json`). O teste de restauração da conversa pública intercepta a resposta no navegador, sem criar registro operacional no servidor.

Capturas principais:

- `docs/vetor-chat-12/chat128-interface/1440-lab-rules.png`
- `docs/vetor-chat-12/chat128-interface/390-qualification.png`
- `docs/vetor-chat-12/chat128-interface/1440-widget-operator.png`
- `docs/vetor-chat-12/chat128-interface/390-widget-expired.png`

## Revisão das 14 regras

A revisão individual está em `docs/vetor-chat-12/chat128-rule-review.md`. Foram conferidos conteúdo, reconhecimento, prioridade, conflitos, informações comerciais/funcionais, transferência e agenda. As formas de pagamento continuam condicionadas à confirmação comercial; nenhum horário foi inventado e a regra fora do expediente permanece inativa.

Duas precedências merecem atenção no ensaio de frases com múltiplas intenções: os termos gerais “sistema/software” podem selecionar a apresentação antes de um recurso específico; “financeiro” prevalece sobre “nota fiscal” quando os dois aparecem juntos. A ordem de seleção na interface não muda a prioridade numérica. Essas ressalvas foram documentadas, sem alterar ou ativar regras operacionais.

## Git e publicação

As mudanças permanecem locais e sem commit. O inventário dos dois repositórios está em `docs/vetor-chat-12/chat128-git-state.json`. Foram preservadas as alterações acumuladas e pastas de evidências anteriores. Builds geraram assets somente nas cópias ignoradas em `backups/chat128`, mantendo os assets rastreados do workspace.

O procedimento completo está em `docs/vetor-chat-12/chat128-publicacao.md`: backup, ensaio de upgrade, migrations 180000 → 190000 → 200000 → 210000 conforme pendências reais, API/administração VetorLeads antes do widget/proxy VetorOS, atualização coordenada, manutenção das chaves e sessões, validação sandbox e reversão por código sem apagar histórico. Bases Git e hashes dos manifests locais estão em `docs/vetor-chat-12/chat128-artifacts.json`. Nenhuma imagem Docker de release foi criada; os commits definitivos e digests deverão ser registrados quando a publicação for autorizada.

## Pendências reais

1. Resolver ou tratar formalmente o passivo de ESLint global do VetorOS em uma etapa própria; não foi escondido nem classificado como aprovado.
2. Publicação não executada: revisar o diff acumulado, identificar commits/imagens finais e ensaiar migrations sobre cópia do banco operacional antes de qualquer liberação.
3. Homologação pública e WhatsApp real continuam fora desta etapa. Regras produtivas e WhatsApp automático não foram ativados.
