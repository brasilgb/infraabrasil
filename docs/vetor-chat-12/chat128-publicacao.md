# CHAT-12.8 — Publicação coordenada (preparada, não executada)

## Identificação

Bases atuais: infraestrutura `f0ca7df364cb37d39ef005a3098aa89668a7396e`, VetorLeads `f072ba01e1206ac876b806ddfeb9aecfabe890c8`, VetorOS `953b90a825c76239055c29f90d77530770e6c2a5`. A entrega inclui mudanças locais ainda sem commit; estes hashes identificam apenas as bases, não uma versão publicável da CHAT-12.8. Nenhum commit, push, imagem Docker de release ou deploy foi criado nesta execução. Os builds Vite de validação estão nas cópias ignoradas `backups/chat128/{vetorleads,vetoros}/public/build`. Os hashes dos manifests constam no arquivo `chat128-artifacts.json`.

Antes da publicação, revisar e identificar os commits definitivos dos dois produtos e da infraestrutura; construir imagens imutáveis e registrar seus IDs/digests e os manifests correspondentes. Não usar os hashes acima como identificação da entrega ainda não commitada. O compose usa builds locais dos serviços `vetorleads` e `vetoros`; esta etapa não substitui imagens em execução.

## Ordem

1. Revisar os diffs acumulados das etapas anteriores e fazer backup do banco operacional, imagens e assets existentes. Ensaiar as migrations em cópia do banco real. Não executar `migrate:fresh`, seed de teste ou rollback destrutivo na operação.
2. Preservar as chaves `APP_KEY`, cookies, configuração de sessão, credencial de integração servidor-servidor e armazenamento de sessões existentes. A ponte usa credenciais e tabelas próprias; não exige limpar sessões administrativas nem conversas públicas.
3. Se ainda pendentes, aplicar em ordem as migrations de automação `180000`, multicanal `190000`, laboratório `200000` e credenciais `210000`, todas de 2026-10-19. A migration `2026_10_19_200000_create_support_sandbox_tables.php` continua criando apenas conversas/mensagens; a nova `2026_10_19_210000_create_support_sandbox_accesses_table.php` cria credenciais mesmo quando a migration do laboratório já foi aplicada. Verificar também as migrations comerciais anteriores do workspace.
4. Publicar primeiro a API/administração VetorLeads com schema compatível e os endpoints `/api/web-chat/sandbox/*`. Preservar `services.web_chat` e `WEB_CHAT_SERVICE_TOKEN` do ambiente, sem expô-los ao navegador. A emissão exige administrador autenticado, verificado e autorizado no tenant. Credenciais têm 256 bits de aleatoriedade, armazenamento SHA-256, uso único para resgate e expiração absoluta em 30 minutos.
5. Publicar em seguida o VetorOS, contendo proxy `/chat/sandbox/*`, componente atualizado e lockfile. Preparar dependências de desenvolvimento e builds no ambiente de build; nunca instalar dependências de desenvolvimento nos containers produtivos. Usar `npm ci`, `npm run types`, `npm run test:js`, `npx eslint .` e `npm run build`. O carregador `tsx` permite executar os testes TypeScript em Node 20.19.2. No VetorLeads, usar `npm run types:check`, `npm run lint:check` e `npm run build`, além das verificações PHP.
6. Coordenar as duas versões: sem API nova o widget de teste deve falhar fechado, sem fallback operacional. Em uma janela de publicação parcial, não distribuir códigos até que ambos os lados estejam disponíveis. Conferir URLs, credencial de serviço e limite de requisições. Em ambientes com domínios distintos, manter cookies independentes; ensaio local com portas diferentes deve usar hosts distintos para impedir colisão do cookie `XSRF-TOKEN`.
7. Manter todas as regras produtivas inativas e `SUPPORT_AUTOMATION_WHATSAPP_DELIVERY_ENABLED=false`. Não mudar ativação por tenant, agenda ou sessões WAHA. As 14 respostas revisadas são modelos locais; esta entrega não atualiza regras já gravadas. Formas de pagamento dependem de confirmação comercial e nenhum horário foi inventado.
8. Conferir integração somente com sessão sandbox emitida pelo administrador: intenção, contexto, qualificação, prévia de oportunidade, operador, retorno ao automático, encerramento, expiração, revogação e recarga do navegador. Conferir que CRM, mensagens operacionais, jobs e outbox não aumentam. Conferir separadamente a preservação do atendimento público. A validação local não equivale a homologação pública ou WhatsApp real.

## Reversão

Revogar primeiro as credenciais de homologação e suspender novas emissões. Restaurar o widget/proxy VetorOS anterior e depois a aplicação VetorLeads anterior compatível com o schema. Preservar tabelas, conversas públicas, sessões, decisões e dados comerciais: não é necessário remover as tabelas sandbox para reverter o código. Manter a chave de criptografia e os dados de sessão. Credenciais do navegador ficam em memória; o indicador de modo de teste permanece na sessão do navegador e impede fallback mesmo após recarga. Sair do teste é ação explícita e restaura a conversa pública previamente salva.

Se for realmente necessário desfazer schema, planejar retenção/exportação do histórico sandbox e executar uma mudança própria aprovada; não usar `migrate:rollback` sobre o lote acumulado. A reversão não deve apagar histórico comercial, outbox ou sessões WAHA.

## Limitação conhecida

ESLint global do VetorOS retorna 442 erros e 11 avisos em arquivos fora do diff desta entrega; o resumo está em `chat128-os-eslint-summary.json`, sem problemas nos arquivos alterados. A correção desse passivo amplo exige uma etapa própria. Não registrar o lint global como aprovado.
