# infra-abrasil

Stack Docker local consolidada para VetorOS, VetorPet e ABrasil Sistemas.

## Subida local

1. Copie `.env.example` para `.env` e preencha os segredos.
2. Execute `./scripts/up.sh`.
3. Acesse `http://vetoros.localhost`, `http://vetorpet.localhost` e `http://abrasilsistema.localhost`.

O host `waha.localhost` encaminha para a API compartilhada. WAHA também fica publicado localmente em `WAHA_PORT`; em VPS, remova essa publicação e exponha somente Nginx/HTTPS.

O host `phpmyadmin.localhost` dá acesso ao phpMyAdmin, que gerencia o MySQL compartilhado. phpMyAdmin também fica publicado localmente em `PHPMYADMIN_PORT` (padrão `8084`); o login é feito com usuário/senha do MySQL (ex.: `root` e `MYSQL_ROOT_PASSWORD`, ou as credenciais de cada app). Em VPS, restrinja o acesso (firewall/VPN) ou remova essa publicação, já que o phpMyAdmin não tem autenticação própria além da do MySQL.

O host `n8n.localhost` dá acesso ao n8n (automações de prospecção do CRM ABrasil Sistemas, com integração futura ao WAHA/WhatsApp). O n8n usa SQLite interno com dados persistidos em `volumes/n8n` e **não publica porta no host**; acesso só via Nginx (`n8n.localhost` local, `https://n8n.abrasilsistemas.com.br` em produção). n8n consegue alcançar o WAHA internamente em `http://waha:3000`, na mesma rede Docker.

## Subida em VPS com domínio real e HTTPS

Com o DNS de `vetoros.com.br`, `vetorpet.com.br`, `abrasilsistemas.com.br` (+ `www.`, redirecionado para o apex) e `n8n.abrasilsistemas.com.br` (sem `www.`) apontando (registro A) para o IP da VPS, o Nginx atende cada domínio com certificado Let's Encrypt:

1. Copie `.env.example` para `.env`, gere segredos reais (`MYSQL_ROOT_PASSWORD`, `*_DB_PASSWORD`, `*_APP_KEY` no formato `base64:...`), confirme `*_DOMAIN`/`*_APP_URL` com os domínios reais e defina `CERTBOT_EMAIL` com um e-mail válido (usado pelo Let's Encrypt para avisos).
2. Libere as portas 80 e 443 no firewall da VPS.
3. Execute `./scripts/up.sh` (ou `docker compose up -d --build`). Nesse primeiro boot o Nginx ainda não tem certificado real; rode em seguida `./scripts/init-letsencrypt.sh`, que gera um certificado autoassinado temporário (para o Nginx conseguir subir), sobe o Nginx e então solicita o certificado real via Certbot (webroot) para cada domínio. Domínios cujo DNS ainda não propagou ficam com o certificado autoassinado até serem executados novamente.
4. Rode as migrations (`docker compose exec vetoros php artisan migrate --force`, idem para `vetorpet` e `abrasilsistema`).
5. Acesse `https://vetoros.com.br`, `https://vetorpet.com.br` e `https://abrasilsistemas.com.br`.
6. Configure a renovação periódica de certificados chamando `./scripts/renew-certs.sh` (ex.: cron diário/semanal do host — `crontab -e`: `0 3 * * * cd /root/infra-abrasil && ./scripts/renew-certs.sh >> /var/log/certbot-renew.log 2>&1`). Certificados Let's Encrypt duram 90 dias.

Os vhosts `*.localhost` continuam disponíveis em paralelo (HTTP, sem TLS) para uso em desenvolvimento local via `./scripts/up.sh` sem domínio configurado.

## Operação

```sh
docker compose config
docker compose ps
docker compose logs --tail=100 nginx vetoros vetorpet abrasilsistema waha
docker compose exec vetoros php artisan migrate
docker compose exec vetorpet php artisan migrate
docker compose exec abrasilsistema php artisan migrate
```

Migrations e seeders não são executados automaticamente. O init do MySQL só roda na primeira criação de `volumes/mysql`; não remova esse volume sem backup.

### n8n: atualizar e fazer backup

Atualizar (não afeta vetoros/vetorpet/abrasilsistema, pois não usam `--build` nem dependem do n8n):

```sh
docker compose pull n8n
docker compose up -d --no-deps n8n
```

Backup: pare a escrita (opcional) e copie o volume de dados (workflows, credenciais criptografadas com `N8N_ENCRYPTION_KEY`, configurações):

```sh
tar -czf n8n-backup-$(date +%Y%m%d).tar.gz -C volumes n8n
```

Guarde `N8N_ENCRYPTION_KEY` (em `.env`) junto do backup — sem ela as credenciais salvas no n8n não podem ser descriptografadas na restauração.

## Arquitetura

Nginx faz o reverse proxy para três pools PHP-FPM. MySQL usa databases e usuários separados; Redis fica disponível na rede compartilhada, embora os três projetos atualmente usem fila/cache em database. WAHA é único, persistido em `volumes/waha` e acessível internamente como `http://waha:3000`. phpMyAdmin gerencia o MySQL compartilhado, acessível internamente como `http://phpmyadmin:80`. n8n roda com SQLite interno (sem Postgres), persistido em `volumes/n8n`, sem porta publicada no host, e alcança o WAHA pela rede Docker interna (`http://waha:3000`) para a futura automação CRM → n8n → WAHA → WhatsApp.

Os Dockerfiles executam `npm ci`/Yarn e `npm run build` no estágio Node, depois `composer install --no-dev --optimize-autoloader` no estágio PHP. O runtime não executa Vite nem `php artisan serve`.

HTTPS: TLS é terminado no serviço Nginx com certificados Let's Encrypt persistidos em `volumes/certbot/conf` (montados em `/etc/letsencrypt`). O serviço `certbot` (perfil `certbot`, não sobe com `docker compose up -d` normal) emite/renova os certificados via desafio `webroot`, servido em `volumes/certbot/www`. Veja `scripts/init-letsencrypt.sh` e `scripts/renew-certs.sh`.

## Pendências conhecidas

- A pasta do terceiro projeto existente é `gateway/abrasilsistemas`; o serviço exposto pela stack é `abrasilsistema`, conforme o nome funcional solicitado.
- A validação completa de build/subida depende de Docker Engine disponível e dos segredos preenchidos em `.env`.
- `abrasilsistemas.com.br` ainda não resolvia (sem registro DNS/NS) no momento da configuração; está com certificado autoassinado temporário até o DNS propagar — rode `./scripts/init-letsencrypt.sh` novamente depois.
- Renovação automática dos certificados depende de um agendador no host (cron ou equivalente); nenhum estava instalado nesta VPS — configure manualmente conforme a seção acima.
- `n8n.abrasilsistemas.com.br` ainda não tem registro DNS (A) apontando para o IP da VPS; o n8n está no ar apenas com certificado autoassinado temporário. Assim que o DNS for criado, rode `./scripts/init-letsencrypt.sh` novamente para emitir o certificado real (a rotina só reprocessa domínios sem certificado válido, sem afetar `vetoros`/`vetorpet`/`abrasilsistema` já emitidos).
- Diversas senhas em `.env` de produção ainda estão com valores placeholder (`change-me-*`) ou fracas (ex.: `MYSQL_ROOT_PASSWORD`) — troque por segredos fortes reais assim que possível; isso é anterior a esta mudança e não foi alterado aqui.
