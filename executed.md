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
