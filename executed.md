# Execução de `correio.md`: VETOR-MAIL-01 (auditoria e centralização dos e-mails). Resultado: **AUDITORIA SOMENTE LEITURA CONCLUÍDA, ARQUITETURA PROPOSTA, AGUARDANDO APROVAÇÃO**

- **Data:** 2026-10-09 (America/Sao_Paulo).
- **SHA-256 do `correio.md` executado:** `ff3d02a2a1a4269bb12f11320613209173467edc62d35b6e39f9f7c0eb161ebd`.
- **SHA-256 do último correio registrado:** `340281df6ed3f1520d3adfb7f05c160ed0b234bc419fb824b6d4cd703cac6788` (VETOR-DEPLOY-01). O conteúdo mudou e o VETOR-MAIL-01 não tinha execução anterior, então foi executado.
- **Código auditado:** VetorOS `439edbf0` (`main`). Laravel 12.69.2.
- **Dados:** dump de produção `vetoros-new.sql` (phpMyAdmin, MySQL 8.4.11, gerado em 20/09/2026 21:38), lido só de forma agregada. Nenhum payload, e-mail, host ou usuário SMTP foi exibido ou copiado.
- **Nada foi alterado:** nenhuma migration, commit, push, deploy, reenvio ou exclusão de jobs. Produção não foi acessada.

## 1. Mapa dos envios atuais

| E-mail | Classe | SMTP usado hoje | Como é enviado |
|---|---|---|---|
| OS criada | `OrderCreatedMail` | do tenant | síncrono (`OrderNotificationService::deliverCreated`) |
| Status da OS / orçamento gerado | `OrderStatusUpdatedMail` | do tenant | síncrono |
| Lembrete de cobrança | `OrderPaymentReminderMail` | do tenant | síncrono |
| Acompanhamento de orçamento | `OrderBudgetFollowUpMail` | do tenant | síncrono |
| Lembrete de avaliação | `OrderFeedbackReminderMail` | do tenant | síncrono |
| Teste SMTP do tenant | `Mail::raw` (`OtherController`) | do tenant | síncrono |
| Boas-vindas no cadastro | `UserRegisteredMail` | do sistema | síncrono, falha só registrada em log |
| Avisos de assinatura | `SubscriptionStatusMail` | do sistema | comando agendado e RootAdmin |
| Fatura paga | `SubscriptionInvoicePaidMail` | do sistema | síncrono |
| NFS-e do SaaS | `SaasFiscalDocumentMail` | do sistema | síncrono |
| Pedido de avaliação da plataforma | `TenantFeedbackRequestMail` | do sistema | comando agendado |
| Ajustes e melhorias (criado/atualizado) | `TenantImprovementRequest*Mail` | do sistema | síncrono |
| Recuperação de senha | notificação padrão do Laravel | `mail.default` do processo | síncrono |
| Confirmação de cadastro | **não existe**: `User` não implementa `MustVerifyEmail` | — | — |
| Comunicados administrativos | **não existe** | — | — |

- O "SMTP do sistema" é `config('mail.system')`, lido de `MAIL_*` do ambiente. **Não existe tela de SMTP no RootAdmin.**
- O SMTP do tenant fica em `others.mail_*`. A senha é gravada com `Crypt::encryptString` e não volta para a tela (`mail_password_set`). As duas linhas configuradas no dump estão nesse formato.

## 2. As falhas históricas de `SendOrderCreatedNotification` e `SendOrderStatusUpdatedNotification`

**Contagem no dump de 20/09:** 957 linhas em `failed_jobs`, e não 860. Todas estão na conexão `database`, fila `default`.

| Item | Resultado |
|---|---|
| Jobs | 748 `SendOrderStatusUpdatedNotification` e 209 `SendOrderCreatedNotification` |
| `failed_at` | todas entre **2026-09-18 18:57:42 e 18:58:14 UTC** (32 segundos), cerca de uma hora depois do primeiro commit da stack consolidada (`f11d50c`, 18/09 17:00 UTC) |
| Despacho original (`createdAt`) | maio/2026: 257, junho: 594, julho: 106 |
| Exceção | **as 957** são `RuntimeException: Unsupported cipher or incorrect key length` |
| Ponto da falha | `TenantMailConfig.php:89` (`Crypt::decryptString` da senha SMTP do tenant), chamado por `OrderNotificationService::deliverCreated`/`deliverStatusUpdated` |
| OS afetadas | 111 OS distintas (até 20 jobs por OS), todas ainda existentes, de **2 tenants**: os únicos com SMTP completo no dump |
| Fila pendente | `jobs` vazia no dump |

**Diagnóstico:**
1. Até 06/07/2026 (`4b4fd6f7`), o envio era enfileirado (`::dispatch()->afterCommit()`). No ambiente antigo nenhum worker processava a fila, então os jobs foram se acumulando entre maio e julho.
2. Em 18/09 o `vetoros-worker` da stack nova subiu e processou o acúmulo de uma vez, com uma `APP_KEY` em formato inválido. O compose só exige que a variável não esteja vazia, não que tenha o formato `base64:` de 32 bytes.
3. O erro de chave inválida é `RuntimeException`, mas o código só captura `DecryptException`. Por isso cada job abortou, em vez de seguir sem senha.
4. Desde 06/07 o código envia de forma síncrona e não despacha mais esses jobs. As duas classes continuam existindo só para que as linhas de `failed_jobs` possam ser lidas.

**Recomendação:** **não reenviar**. São avisos de OS de 3 a 5 meses atrás, e reenviá-los mandaria até 20 e-mails para o mesmo cliente. Também **não excluir agora**. Depois da aprovação, arquivar ou remover com backup (§6, fase 4).

**Diferença 860 × 957:** confirmar em produção, somente leitura:
```sql
SELECT DATE(failed_at) dia, COUNT(*) FROM failed_jobs GROUP BY dia;
```

## 3. Problemas encontrados

| # | Gravidade | Problema | Evidência |
|---|---|---|---|
| A1 | **Alta** | Em produção, o SMTP do sistema vem do `.env.example`: `MAIL_HOST=127.0.0.1`, `MAIL_PORT=1025`, remetente `hello@example.com`, `APP_NAME=TechOs` | `docker-compose.yml` usa `env_file: ./gateway/vetoros/.env.example` e não sobrescreve `MAIL_*`; o `.dockerignore` exclui `.env`. Bate com a pendência "`artisan about` mostra `TechOs` e `local`". Se não houver outra fonte, todos os e-mails institucionais (cadastro, senha, assinatura, NFS-e do SaaS, ajustes) falham na conexão. **Confirmar em produção (§5).** |
| A2 | **Alta** | Senha SMTP do tenant que não decifra (chave trocada) vira `null` sem aviso, e o envio tenta autenticar sem senha. Chave em formato inválido derruba o envio inteiro (`RuntimeException` não tratada) | `TenantMailConfig::applyForTenantId`, linhas 86–91; as 957 falhas. A verificação `security:audit-app-key --verify-hash` em produção continua pendente |
| A3 | **Média** | A troca de SMTP é global ao processo (`Config::set` + `purge`) e o resultado depende da ordem das chamadas. Os mailables chamam `applyTenantMailConfig`/`applySystemDefault` dentro de `envelope()`, mas nessa hora o `Mailer` já foi resolvido em `Mail::to()`. O transporte e o remetente usados são os que estavam ativos antes, e a troca só vale para o envio seguinte | Laravel 12.69.2: `Mailer::sendMailable()` passa a própria instância para `Mailable::send()`, que hidrata o envelope depois. Os envios de OS funcionam porque `OrderNotificationService` aplica a configuração **antes** de `Mail::to()`. Um e-mail do SaaS enviado no mesmo processo logo depois de um e-mail de tenant sairia pelo SMTP e com o remetente daquele tenant, e um tenant sem SMTP deixa `mail.default = log` (envio descartado em silêncio) |
| A4 | Média | `AppServiceProvider::boot` aplica a configuração de e-mail a cada boot. Nessa hora ainda não há usuário nem sessão, então sempre cai no padrão do sistema: estado global sem efeito útil | `AppServiceProvider.php:80-81` |
| A5 | Média | O cadastro registra no log o e-mail do usuário e a mensagem bruta do SMTP, que pode conter host e usuário | `RegisteredUserController.php:98-104` |
| A6 | Média | Não há confirmação de cadastro, comunicados administrativos nem configuração de SMTP no RootAdmin | §1 |
| A7 | Baixa | Envios manuais (cobrança, acompanhamento) não têm chave de idempotência; dois cliques enviam dois e-mails. Não há timeout SMTP explícito. Os jobs antigos usavam `--tries=3` com `retry_after` igual ao `--timeout` (90 s) | `docker-compose.yml:121`, `config/queue.php:42` |
| A8 | Baixa | OS sem tenant cai no SMTP do sistema com a marca do tenant | `TenantMailConfig::hasConfiguredForTenantId(null)` |

Pontos que já estão corretos: a configuração do tenant é lida por `tenant_id` explícito; a senha nunca volta para a interface; a falha de envio de OS é gravada em `order_messages` sem detalhes do servidor ("Falha no envio do e-mail."); o teste SMTP envia só para o e-mail da própria empresa e exige configuração completa.

## 4. Arquitetura proposta

**Princípio:** nenhum envio depende da configuração global de e-mail. Cada envio constrói o próprio mailer a partir de uma fonte explícita: plataforma ou tenant.

1. **`PlatformMailConfig`** (mesmo padrão de `SpedyPlatformConfig`): tabela singleton `platform_mail_settings` com host, porta, criptografia, usuário, senha (cast `encrypted`, somente escrita), remetente e reply-to. Precedência: banco > variáveis de ambiente.
   - **RootAdmin → Configurações → E-mail:** formulário com senha "Configurado — preencha para trocar", envio de teste para o e-mail do root autenticado e auditoria da alteração (sem valores).
   - Middleware `RootAdminOnly`, como no fiscal.
   - No boot, registrar o mailer nomeado `platform` e torná-lo o `mail.default`, para que recuperação de senha e futuras notificações usem a plataforma.
2. **`TenantMailerFactory::for(int $tenantId): ?Mailer`:** lê `others` por `tenant_id` e devolve `Mail::build([...])` com remetente fixo (`alwaysFrom`). Nunca altera `config()`.
   - Devolve `null` com motivo tipado (`not_configured`, `credentials_unreadable`) quando falta configuração ou a senha não decifra, capturando **`DecryptException` e `RuntimeException`**.
   - O motivo vai para `order_messages` e para um aviso no painel do tenant ("Revise a senha SMTP").
3. **`MailDispatcher`:** ponto único de envio, `platform()->send(...)` e `tenant($id)->send(...)`.
   - Remover `AppliesTenantMailConfig`, as chamadas em `envelope()` e o `applyForTenantId` do `AppServiceProvider`.
   - Logs só com classe da exceção, código e ids, nunca host, usuário, senha ou endereço.
4. **Confiabilidade:**
   - Envio de OS volta para a fila, mas **idempotente**: a linha de `order_messages` é criada `pending` com chave única (OS + modelo + versão do orçamento ou evento de status) antes do despacho. O job sai sem fazer nada se ela já estiver `sent`.
   - `tries=3`, `backoff=[60, 300, 900]`, `timeout` menor que `retry_after`, timeout SMTP de 15 s.
   - Limite por tenant (`RateLimiter::for('tenant-mail')`) e trava de reenvio manual para o mesmo modelo em poucos minutos.
5. **Funções novas da plataforma:** confirmação de cadastro (`MustVerifyEmail`, decisão de produto: bloquear ou só lembrar) e comunicado administrativo do RootAdmin para os responsáveis dos tenants, com fila, limite e registro de entregas (reaproveitando o padrão de `AdminFiscalDocumentDelivery`).
6. **Compatibilidade:** o formato de `others.mail_*` não muda e as configurações dos tenants são preservadas. Os templates continuam iguais.

## 5. Verificações em produção (somente leitura, quando autorizadas)

```bash
cd /opt/infra-abrasil
docker compose exec vetoros php artisan tinker --execute="echo config('mail.system.host').':'.config('mail.system.port').' '.config('mail.system.from_address');"
docker compose exec vetoros php artisan security:audit-app-key --verify-hash
docker compose logs --since 720h vetoros | grep -c "Connection could not be established"
```
Também a contagem de `failed_jobs` da §2. Nenhum desses comandos imprime senhas.

## 6. Plano de execução (cada fase só depois de aprovação)

| Fase | Escopo | Migrations | Risco |
|---|---|---|---|
| MAIL-01.1 | Infra: passar `VETOROS_MAIL_*`, `APP_NAME=VetorOS` e `APP_ENV=production` pelo compose; validar o formato da `APP_KEY`; teste de envio | não | baixo, só recria os 3 containers do VetorOS |
| MAIL-01.2 | `PlatformMailConfig`, tela do RootAdmin, mailer `platform`, recuperação de senha pela plataforma, logs sem dados sensíveis | sim (aditiva) | baixo |
| MAIL-01.3 | `TenantMailerFactory` + `MailDispatcher`, fim do estado global, tratamento de chave ilegível, fila idempotente com limites | sim (índice único em `order_messages`) | médio |
| MAIL-01.4 | Confirmação de cadastro e comunicados; arquivamento dos 957 `failed_jobs` com backup e remoção das duas classes de job antigas | opcional | baixo |

**Testes previstos:**
- dois tenants enviando em sequência no mesmo processo, conferindo host e remetente de cada transporte;
- e-mail do SaaS enviado depois de um e-mail de tenant usando a plataforma;
- senha ilegível e chave inválida sem exceção, com motivo registrado;
- recuperação de senha pela plataforma;
- logs sem senha, usuário ou endereço;
- idempotência do envio (mesma chave, um só e-mail);
- teste SMTP autorizado só para o e-mail da empresa ou do root.

## Pendências e decisões

1. Aprovar o plano (ou ajustar fases) antes de qualquer migration, commit ou deploy.
2. Rodar as verificações da §5 em produção, principalmente o host SMTP do sistema (A1) e o `--verify-hash` (A2).
3. Decidir se a confirmação de cadastro bloqueia o acesso ou só lembra.
4. Decidir o destino dos 957 `failed_jobs`: arquivar em tabela/arquivo ou apenas remover após backup.

---

# Execução manual: pull, migrations e build do VetorOS + correção de permissões. Resultado: **IMPLANTADO E VALIDADO**

**Data:** 2026-10-08
**Host:** `srv1990862` (`/opt/infra-abrasil`)
**Código em produção:** VetorOS `4c4ce74c`, infra `96db499`

> Solicitado diretamente pelo operador, fora de um `correio.md`. Durante a validação, foi encontrado e corrigido um erro que deixava o VetorOS fora do ar (`Permission denied` em `bootstrap/app.php`).

## 1. Pull

- O `infra-abrasil` estava 2 commits atrás e foi atualizado com `--ff-only` até `96db499`.
- O `correio.md` local (VETOR-DEPLOY-01.1) era diferente do remoto. Ele foi preservado no stash `correio.md local antes do pull 2026-10-08`.
- O submódulo `gateway/vetoros` já estava em `4c4ce74c`, igual ao `origin/main`.
- O merge `96db499` trouxe para o `executed.md` marcadores de conflito já commitados (`<<<<<<<`/`>>>>>>>`). Os marcadores foram removidos e os dois relatórios foram mantidos, que era a intenção do merge.

## 2. Backup

- `backups/vetoros-deploy-01/vetoros_pre_migrate_20261008_211911.sql` (15 MB, `mysqldump --single-transaction --routines --triggers`, termina em `Dump completed`).

## 3. Migrations

- Executadas com a imagem nova: `migrate:status --pending` resultou em *No pending migrations*, e `migrate --force` respondeu *Nothing to migrate*.
- O banco não foi alterado.

## 4. Erro corrigido: `Permission denied` em `bootstrap/app.php`

- **Sintoma:** `require_once(/var/www/html/bootstrap/app.php): Failed to open stream: Permission denied`, com erro fatal em todas as requisições ao VetorOS.
- **Causa:** no checkout das 16:55, feito com umask restritivo, 189 arquivos versionados e 10 diretórios do `gateway/vetoros` ficaram com permissão `600`/`700`. O `COPY` do Dockerfile copiou essas permissões para a imagem, e o php-fpm, que roda como `www-data`, não conseguia ler os arquivos. O defeito já existia no deploy anterior (o container de 4 horas antes tinha a mesma imagem).
- **Correção:**
  - Permissões normalizadas no host: arquivos `644`, executáveis `755`, diretórios `755`. Nenhuma alteração no git.
  - Imagens `vetoros`, `vetoros-worker` e `vetoros-scheduler` reconstruídas.
  - Só esses três serviços foram recriados, e o nginx foi recarregado. Os serviços compartilhados não foram tocados.
- **Rollback:** a imagem anterior foi marcada como `infra-abrasil-vetoros:rollback-20261008`. Ela contém o mesmo defeito de permissão e só serve para uma emergência que não tenha a ver com isso.

## 5. Validação

- `https://vetoros.com.br`:
  - `/`, `/login` e `/up` responderam **200**, e `/admin` respondeu **302**;
  - nenhuma página mostrou "Fatal error".
- `vetoros`, `vetoros-worker`, `vetoros-scheduler` e `nginx` estão **healthy**.
- O scheduler está executando as tarefas (ex.: `fiscal:sync-spedy`).
- Não houve nenhum erro ou exceção nos logs depois do redeploy.
- Os assets do volume `vetoros-build` são sincronizados pelo entrypoint a partir de `/opt/public-build` e conferem com a imagem.

## 6. Testes

- Suíte Pest executada numa cópia isolada do código (`git archive`), num container sem rede, com SQLite em memória e dependências de dev. Produção não foi tocada.
- Resultado: **532 testes passando (2966 asserções)**, 0 falhas.
- As falhas das rodadas iniciais vinham só do ambiente de teste: faltavam as pastas `storage/framework` e o `public/build/manifest.json`. Não foi preciso alterar o código.

## Pendências

1. Configurar `umask 022` no usuário e no processo que fazem checkout em `/opt/infra-abrasil`. Sem isso, um próximo pull pode recriar arquivos com `600` e derrubar o VetorOS de novo.
2. O `artisan about` mostra `Environment: local` e o nome `TechOs` em produção. O modo debug está desligado, mas o ideal é `APP_ENV=production`.
3. `php artisan security:audit-app-key --verify-hash` (VETOR-DEPLOY-01.1, Etapa 4) ainda não foi executado.
4. Homologação funcional dos módulos (VETOR-DEPLOY-01.1, Etapa 7) não realizada.

---

# Execução de `correio.md`: VETOR-DEPLOY-01.1 (retomada da publicação na VPS). Resultado: **INTERROMPIDA NA ETAPA 1, IMPLANTAÇÃO NÃO REALIZADA**

**Data:** 2026-10-08
**Host:** `srv1990862`. O executor roda na própria VPS de produção, em `/opt/infra-abrasil`.

> **Classificação: BLOQUEADA.** O commit `4c4ce74c` do VetorOS não existe localmente e não pode ser baixado, porque o GitHub recusa a deploy key do VetorOS (`Permission denied (publickey)`). Sem esse código, a Etapa 4 (comando `security:audit-app-key`), a Etapa 5 (migrations INTEL) e a Etapa 6 (build) não podem ser executadas. Nada foi alterado: nenhum container, imagem, banco, volume ou arquivo de código. Também não foi feito pull nem push.

## Etapa 1: Verificação no remoto

| Item | Esperado | Encontrado | Status |
|---|---|---|---|
| Infra-abrasil `origin/main` | `b7c7782` | `b7c7782` (o `git fetch` funcionou) | OK no remoto |
| Ponteiro do submódulo em `b7c7782` | `4c4ce74c` | `4c4ce74c81d643b97811412c5abb5a85b91c73ec` | OK |
| VetorOS `4c4ce74c` no remoto | presente | **não verificável**: `git fetch` em `gateway/vetoros` falha com `Permission denied (publickey)` (host `github-vetoros`, chave `vetoros_deploy`) | **BLOQUEIO** |
| VetorOS `4c4ce74c` local | — | `fatal: Not a valid object name` | ausente |

Estado local da infra (working tree da VPS):

- HEAD local: `8b3b821`, **atrás** de `origin/main` por 6 commits (`a8b4564` … `b7c7782`).
- `correio.md` modificado localmente. O conteúdo local difere do `correio.md` de `origin/main` (73+/50−).
- `gateway/vetoros` aponta localmente para `237c4d18` (2026-10-07, "Remove valores da landing page…"), e não para `52ad0f3` (HEAD) nem para `4c4ce74c` (remoto).
- O `executed.md` de `origin/main` também difere do local. Um pull futuro vai entrar em conflito com `correio.md` e `executed.md`.
- O projeto Desgarrados não foi tocado.

## Etapa 2: Estado da VPS (somente leitura)

- **Containers:** os 18 estão `Up (healthy)`. Os do VetorOS (`vetoros`, `vetoros-worker`, `vetoros-scheduler`) estão no ar há cerca de 23h. Os serviços compartilhados (mysql 8.4, redis, nginx, waha, n8n, phpmyadmin) e os demais projetos estão no ar há cerca de 2 dias.
- **Imagens VetorOS atuais:** `infra-abrasil-vetoros:latest` `7deb8d85869d`, `-worker:latest` `4004b42fffd8`, `-scheduler:latest` `1ed9203971c9` (criadas há cerca de 23h, 2.04 GB cada). Estão marcadas apenas como `latest`, sem tag de rollback.
- **Código em execução:** Laravel 12.69.2. O container ativo **não** tem o comando `security:audit-app-key`.
- **Banco:** MySQL com bind mount `/opt/infra-abrasil/volumes/mysql`. Migrations do VetorOS no container ativo: 163 `Ran`, 0 pendentes (no código atual).
- **Volumes:** `infra-abrasil_vetoros-build` e outros `*-build`, além de 5 volumes anônimos.
- **Disco:** `/` com 99G, 33G usados e **63G livres** (35%). Há espaço suficiente para backup e build.
- **Rollback:** é viável, desde que as imagens atuais recebam uma tag antes do build (ver comandos abaixo).

## Etapas 3 a 8: não executadas

- **3 (Backup):** não foi feito. Como a implantação não pode prosseguir, não havia motivo para gerar um backup agora. Ele deve ser feito imediatamente antes da atualização.
- **4 (APP_KEY):** não foi possível, porque o comando só existe no código `4c4ce74c`, que não está disponível.
  - **Verificação manual parcial (2026-10-08, feita pelo operador):** no container ativo, `docker compose exec vetoros php artisan tinker --execute="echo config('app.key') ? 'APP_KEY CONFIGURADA' : 'APP_KEY AUSENTE';"` retornou **`APP_KEY CONFIGURADA`**, sem imprimir o valor. Isso confirma apenas que a chave existe. A compatibilidade com os dados já gravados (`--verify-hash`) continua pendente até o código `4c4ce74c` estar disponível.
- **5 (Migrations INTEL):** não foi possível levantá-las sem o código novo.
- **6 a 7:** não executadas por dependerem das etapas anteriores.
- VETOR-INTEL-05 não foi iniciado.

## Para destravar (ação manual)

1. Corrigir o acesso ao repositório VetorOS. A chave pública `/root/.ssh/vetoros_deploy.pub` precisa estar cadastrada como deploy key em `brasilgb/vetoros`, ou outra chave válida deve ser configurada em `Host github-vetoros`. Teste com:
   ```bash
   ssh -T git@github-vetoros
   ```
2. Decidir o destino das alterações locais antes de sincronizar a infra. O `correio.md` local e o ponteiro `237c4d18` diferem do remoto. Uma forma de preservar tudo:
   ```bash
   cd /opt/infra-abrasil
   git stash push -m "pre-deploy-01.1" correio.md executed.md gateway/vetoros
   git pull --ff-only origin main
   git submodule update --init gateway/vetoros   # deve resultar em 4c4ce74c
   git -C gateway/vetoros log -1 --format='%H'
   ```
3. Marcar as imagens atuais para rollback:
   ```bash
   for s in vetoros vetoros-worker vetoros-scheduler; do
     docker tag infra-abrasil-$s:latest infra-abrasil-$s:rollback-20261008
   done
   ```
4. Fazer o backup verificável do banco (sem imprimir credenciais e usando as variáveis do próprio container):
   ```bash
   mkdir -p /opt/infra-abrasil/backups
   docker exec infra-abrasil-mysql-1 sh -c 'exec mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --routines --triggers --databases <db_vetoros>' \
     | gzip > /opt/infra-abrasil/backups/vetoros-20261008.sql.gz
   gunzip -t /opt/infra-abrasil/backups/vetoros-20261008.sql.gz && zcat /opt/infra-abrasil/backups/vetoros-20261008.sql.gz | tail -1   # deve mostrar "Dump completed"
   ```
5. Rodar a auditoria da APP_KEY sem substituir o container ativo, usando um container temporário da imagem nova:
   ```bash
   docker compose build vetoros
   docker compose run --rm --no-deps vetoros php artisan security:audit-app-key --verify-hash
   docker compose run --rm --no-deps vetoros php artisan migrate:status   # listar pendentes INTEL
   docker compose run --rm --no-deps vetoros php artisan migrate --pretend # revisar SQL, sem aplicar
   ```
6. Com tudo aprovado, seguir para `docker compose up -d --no-deps vetoros vetoros-worker vetoros-scheduler` e para a homologação da Etapa 7.

Depois do passo 1, basta reenviar o mesmo `correio.md` para a execução continuar a partir da Etapa 1.

---

# Execução de `correio.md`: VETOR-DEPLOY-01 — Push, build e validação na VPS — **BLOQUEADO**

- **Data:** 2026-10-08 (America/Sao_Paulo).
- **SHA-256 do `correio.md` executado:** `340281df6ed3f1520d3adfb7f05c160ed0b234bc419fb824b6d4cd703cac6788`.
- **SHA-256 da execução anterior:** `7a9e51278b89152316eaf6257621a3210ca1d85cafb795396c7ba0e85a214b98` (VETOR-HML-01.2). O arquivo mudou, então foi executado.

## Classificação: **BLOQUEADO**

O **push** (item 1) foi **negado pela política de permissões do ambiente** do Claude Code, classificado como publicação externa. As etapas seguintes dependem dele, porque a VPS precisa obter os commits aprovados do remoto. A tentativa seguinte, só de localizar o acesso SSH à VPS, foi negada pelo mesmo motivo.

Conforme o correio e a própria política, **nada foi contornado**:
- nenhum código enviado por outro meio;
- nenhuma conexão com a VPS;
- nenhum backup, build, migration ou troca de containers;
- **produção não foi tocada**.

## 1. Verificação de sincronização (feita, somente leitura)

`git fetch` nos dois repositórios: os dois estão **apenas à frente** do remoto, **nada atrás**. O push seria um *fast-forward* simples, sem force e sem sobrescrever nada.

| Repositório | Remoto | Branch | À frente | Atrás |
|---|---|---|---|---|
| `gateway/vetoros` | `github.com/brasilgb/vetoros` | `main` @ `4c4ce74c` | 9 | 0 |
| `infra-abrasil` | `github.com/brasilgb/infraabrasil` | `main` @ `b7c7782` | 4 | 0 |

**Atenção:** o push do `vetoros` publica **9 commits**, não só os 4 citados no correio. Os outros 5 são ancestrais de `4c4ce74c`, aprovados em etapas anteriores, mas nunca enviados:

```
4c4ce74c Ajusta layout das prioridades e permissões do técnico na OS (VETOR-HML-01.2)
0f8ce438 Adiciona auditoria somente leitura da APP_KEY (security:audit-app-key)
6ccff9af Corrige divergências da homologação VETOR-HML-01.1
7b54fd70 Adiciona atalhos de Caixa, PDV e Calendário junto às prioridades do dashboard (VETOR-UX-DASH-01)
7e9e005e Atualiza rodapé para ABrasil Sistemas — VetorOS e centraliza a versão em v2.0.0
8f46d37d Finaliza painel de indicadores do VETOR-INTEL-04
84355362 Implementa indicadores operacionais e comerciais do VETOR-INTEL-04
4fda449a Adiciona modelo e importação CSV de produtos (VETOR-PROD-IMPORT-01)
9c69101a Implanta o núcleo VETOR-INTEL: trilha da OS, integridade financeira, orçamento versionado e comunicação
```

Ou seja, o deploy leva para produção **todo o VETOR-INTEL** (trilha da OS, integridade financeira, orçamento versionado, comunicação, indicadores e importação CSV), com as **migrations** correspondentes.

**CI:** o push dispara apenas `gateway/vetoros/.github/workflows/ci.yml` (instalação, tipos e testes). **Não há deploy automático**, então o push sozinho não altera produção.

## 2–7. Não executados

| Etapa | Situação |
|---|---|
| 2. Preparação da VPS (estado, containers, backup, rollback) | ⛔ não executada (bloqueio) |
| 3. Auditoria da `APP_KEY` em produção | ⛔ não executada |
| 4. Build | ⛔ não executado |
| 5. Migrations | ⛔ não levantadas em produção |
| 6. Atualização de `vetoros`, `vetoros-worker` e `vetoros-scheduler` | ⛔ não executada |
| 7. Homologação (inclusive OS de serviço externo editada por técnico) | ⛔ não executada |

## Como prosseguir

**Opção A — você executa o push** (fast-forward, na ordem pedida):

```bash
cd ~/projects/laravel/infra-abrasil/gateway/vetoros && git push origin main
git branch -r --contains 4c4ce74c          # deve listar origin/main
cd .. && git push origin main               # infra-abrasil (ponteiro → 4c4ce74c)
```

Depois, para as etapas na VPS, é preciso **autorização explícita do acesso** (host e usuário SSH), ou que você as execute seguindo o correio.

**Opção B — liberar a permissão** de push e de SSH à VPS para o Claude Code nesta sessão (regra nas configurações) e pedir a reexecução.

**Ordem obrigatória na VPS, quando houver acesso**, conforme o correio:
1. estado atual e containers;
2. **backup verificável** do banco e dos dados persistentes;
3. `security:audit-app-key --verify-hash` sem substituir a aplicação ativa. **Parar se houver ilegíveis.** No banco local, 664 de 664 chaves públicas e 4 de 4 senhas SMTP não decifram com a chave local;
4. revisar as migrations INTEL pendentes **antes** de trocar containers;
5. build;
6. troca só dos 3 serviços VetorOS;
7. homologação.

## Pendências

1. Push dos dois repositórios: por você ou com permissão liberada.
2. Acesso autorizado à VPS para as etapas 2 a 7.
3. **`gateway/desgarrados`** aparece com alterações locais no repositório principal. Não são desta execução e não foram tocadas.
4. **VETOR-INTEL-05:** não iniciado.
