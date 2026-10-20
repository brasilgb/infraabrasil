# CHAT-12.8 — Ambiente local reproduzível

Validação realizada em Node 20.19.2, npm 9.2.0 e PHP 8.4.26. As cópias ignoradas em `backups/chat128/vetorleads` e `backups/chat128/vetoros` contêm o código do workspace, `vendor` local compartilhado e `node_modules` próprios instalados com `npm ci --offline`, respeitando os lockfiles. Nenhuma dependência foi instalada em container produtivo. O esbuild do VetorOS precisou executar fora do sandbox devido a EPERM na verificação do binário. O PHPStan também precisou de socket local fora da restrição do sandbox.

As cópias foram preparadas excluindo `.git`, arquivos `.env*`, `storage`, `vendor` e `node_modules`; o storage foi criado novamente, o vendor local foi vinculado e os caches de configuração/rotas foram removidos apenas nas cópias. Cada cópia recebeu `.env` com chave sintética, SQLite próprio em `database/database.sqlite`, cache/sessão em arquivo, fila síncrona, mail em log, SSR desligado e credencial servidor-servidor sintética. Não copiar `.env` operacional para o ensaio.

Migrar somente esses bancos isolados. Criar o fixture inicial com `php docs/vetor-chat-12/chat128-ui-seed.php` (usa a cópia VetorLeads por caminho absoluto; deve ser usado num banco novo). Não repetir a criação do usuário sobre o mesmo fixture já preenchido. Todas as regras sintéticas nascem inativas; WhatsApp não recebe conexão, credencial nem ativação.

Servidores locais de ensaio: VetorLeads `http://127.0.0.1:8016`, VetorOS `http://localhost:8017`, ambos vinculados a loopback via `php -S 127.0.0.1:<porta> -t public`. Hosts distintos evitam colisão do cookie CSRF. O proxy do VetorOS aponta exclusivamente para `http://127.0.0.1:8016`. No navegador, requisições a outros destinos são bloqueadas.

A suíte PHP do VetorLeads usa o bootstrap de segurança da CHAT-12.5 e SQLite em memória. A suíte VetorOS força SQLite em memória no `phpunit.xml`; foi criado somente um `.env.testing` ignorado, com chave sintética, cache e mail em array e nenhuma credencial real. Sem essa configuração inicial, o ambiente não tinha APP_KEY e a execução falhava; após a preparação, as suítes foram repetidas.

O ensaio pode ser repetido, com os servidores locais ativos, por:

```bash
PLAYWRIGHT_BROWSERS_PATH=/opt/infra-abrasil/backups/prospect11/browser/browsers node docs/vetor-chat-12/chat128-browser.cjs
```

O roteiro usa Chromium headless, o fixture sintético e sessões isoladas. Mede larguras 1440/390, captura interface, registra erros JavaScript e monitora chamadas operacionais. No fim, verifica a restauração de uma sessão pública previamente salva usando uma resposta interceptada pelo navegador; essa checagem não cria conversa operacional no servidor. O ensaio de expiração modifica somente `support_sandbox_accesses` no SQLite isolado.

As evidências `chat128-interface/results.json`, `chat128-isolation.json` e logs `chat128-*` acompanham esta execução. Códigos eventualmente visíveis nas capturas são credenciais exclusivamente sintéticas, expiradas/revogadas; não são credenciais de produção.
