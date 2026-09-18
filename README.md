# infra-abrasil

Stack Docker local consolidada para VetorOS, VetorPet e ABrasil Sistemas.

## Subida local

1. Copie `.env.example` para `.env` e preencha os segredos.
2. Execute `./scripts/up.sh`.
3. Acesse `http://vetoros.localhost`, `http://vetorpet.localhost` e `http://abrasilsistema.localhost`.

O host `waha.localhost` encaminha para a API compartilhada. WAHA também fica publicado localmente em `WAHA_PORT`; em VPS, remova essa publicação e exponha somente Nginx/HTTPS.

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

## Arquitetura

Nginx faz o reverse proxy para três pools PHP-FPM. MySQL usa databases e usuários separados; Redis fica disponível na rede compartilhada, embora os três projetos atualmente usem fila/cache em database. WAHA é único, persistido em `volumes/waha` e acessível internamente como `http://waha:3000`.

Os Dockerfiles executam `npm ci`/Yarn e `npm run build` no estágio Node, depois `composer install --no-dev --optimize-autoloader` no estágio PHP. O runtime não executa Vite nem `php artisan serve`.

HTTPS futuro: terminar TLS no serviço Nginx, montando certificados em um diretório dedicado e adicionando listeners 443 por host. Certbot não é iniciado nesta etapa.

## Pendências conhecidas

- A pasta do terceiro projeto existente é `gateway/abrasilsistemas`; o serviço exposto pela stack é `abrasilsistema`, conforme o nome funcional solicitado.
- A validação completa de build/subida depende de Docker Engine disponível e dos segredos preenchidos em `.env`.
