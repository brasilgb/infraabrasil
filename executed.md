# Relatório de execução — consolidação Docker

## Auditoria

| Aplicação | Laravel | PHP | Node/package manager | Banco | Queue/scheduler | Persistência |
|---|---|---|---|---|---|---|
| VetorOS | 12.x | `^8.3` (imagem 8.4 FPM) | Vite 6 / npm lock | MySQL | database queue + scheduler diário | `storage/app/public`, PDF via dompdf |
| VetorPet | 12.x | `^8.3` (imagem 8.4 FPM) | Vite 6 / Yarn lock | MySQL | database queue; worker/scheduler preparados | `storage/app/public` |
| ABrasil Sistemas | 13.x | `^8.3` (imagem 8.4 FPM) | Vite / npm lock | MySQL | database queue; worker/scheduler preparados | `storage/app/public` |

Os três Dockerfiles instalam `pdo_mysql`, `mbstring`, `exif`, `pcntl`, `bcmath`, `gd`, `zip` e `opcache`. Não foi encontrado uso que justifique Imagick. Redis foi auditado, mas os `.env.example` atuais usam database para cache/fila; o serviço fica disponível sem ser ativado funcionalmente.

## Arquitetura final

`nginx → vetoros/vetorpet/abrasilsistema (PHP-FPM)`; cada aplicação conecta ao seu database MySQL e ao Redis compartilhado. As três aplicações apontam para o único `waha` em `http://waha:3000`. Nginx também disponibiliza `waha.localhost` apenas para teste local.

## Serviços

`mysql`, `redis`, `waha`, `nginx`, três runtimes PHP-FPM, três workers e três schedulers. Todos os serviços de runtime usam `restart: unless-stopped`; logs têm rotação de 10 MB/3 arquivos.

## MySQL e volumes

Databases separados: `vetoros`, `vetorpet`, `abrasilsistema`. Usuários separados: `vetoros_user`, `vetorpet_user`, `abrasilsistema_user`, cada um com acesso somente ao próprio database. Dados ficam em `volumes/mysql`; sessões WAHA em `volumes/waha`; uploads em `volumes/vetoros`, `volumes/vetorpet` e `volumes/abrasilsistema`.

## Nginx e frontend

Hosts locais: `vetoros.localhost`, `vetorpet.localhost`, `abrasilsistema.localhost`. O build acontece no estágio Node (`npm ci` para VetorOS/ABrasil e Yarn para VetorPet) e os assets são copiados para volumes compartilhados com Nginx. Não há Vite dev server nem `php artisan serve` no runtime.

## WAHA

O Compose interno antigo do VetorOS foi removido. O serviço agora é controlado somente pelo Compose raiz, usa `devlikeapro/waha:latest`, API key por `.env`, rede `infra-abrasil-network`, restart automático e persistência em `volumes/waha`. As sessões não são apagadas automaticamente; nenhuma sessão antiga foi encontrada em `gateway/vetoros` para migração.

## Variáveis e segurança

`.env.example` raiz contém apenas placeholders. `.env` local foi preenchido com credenciais de desenvolvimento e deve permanecer fora do controle de versão. Credenciais reais encontradas nos exemplos das aplicações foram removidas. HTTPS futuro deve ser terminado no Nginx, com certificados montados em diretório próprio e listeners 443.

## Validação

- `sh -n` passou para todos os scripts criados.
- Dockerfile, Compose e arquivos de configuração foram revisados.
- `docker compose config`, `build`, `up` e `ps` não puderam ser executados: o binário Docker disponível é a integração Docker Desktop do Windows e retornou `The command 'docker' could not be found in this WSL 2 distro`.

## Comandos manuais

```sh
cp .env.example .env
docker compose config
docker compose up -d --build
docker compose ps
docker compose exec vetoros php artisan migrate
docker compose exec vetorpet php artisan migrate
docker compose exec abrasilsistema php artisan migrate
```

Não executar `down -v` sem backup: isso remove dados persistentes. Em uma máquina com Docker Engine funcional, validar também healthchecks, conectividade MySQL/WAHA e os três hosts locais.

## Pendências reais

1. Habilitar Docker Engine/integração WSL para executar a validação obrigatória.
2. Confirmar as credenciais definitivas e substituir os valores locais do `.env` antes de qualquer VPS.
3. O diretório existente do terceiro projeto é `gateway/abrasilsistemas` (plural); o serviço foi nomeado `abrasilsistema` para seguir o nome funcional solicitado sem mover o repositório interno.
