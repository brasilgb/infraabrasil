# Reversão da CHAT-12.9

Procedimento preparado e não executado. Não usar rollback de migrations nem apagar tabelas/históricos.

1. Interromper novas emissões de códigos e revogar os acessos sandbox ainda válidos pela administração. Encerrar as janelas de homologação antes de restaurar o widget anterior, que não conhece o modo sandbox. A identidade sintética desta execução já foi removida e seus acessos revogados.
2. Manter as travas global e por tenant desligadas, sem alterar regras, horários, WAHA, chaves de criptografia ou credenciais de integração.
3. Usar os IDs anteriores, separados por serviço, preservados em `images-before.json`. O arquivo `rollback-images.json` é um overlay Compose pronto, sem credenciais. Ele contempla também os IDs diferentes que existiam entre API e workers do VetorOS.
4. A partir de `/opt/infra-abrasil`, restaurar primeiro os três serviços VetorOS e depois os três VetorLeads usando o overlay:

```bash
docker compose -f docker-compose.yml -f docker-compose.override.yml -f docs/vetor-chat-12/chat129/rollback-images.json up -d --no-build --no-deps vetoros vetoros-worker vetoros-scheduler
docker compose -f docker-compose.yml -f docker-compose.override.yml -f docs/vetor-chat-12/chat129/rollback-images.json up -d --no-build --no-deps vetorleads vetorleads-worker vetorleads-scheduler
docker compose exec -T nginx nginx -t
docker compose exec -T nginx nginx -s reload
```

5. Verificar saúde, sessões, atendimento público e ausência de automação. Os entrypoints das imagens anteriores restauram os respectivos manifests no volume de assets; os arquivos de assets existentes não foram limpos durante esta publicação.
6. Restaurar também a configuração de imagens para evitar que um próximo `up` sem overlay recoloque a release nova. A cópia exata anterior do override está em `backups/chat129/docker-compose.override.before.yml`; preservar as travas desligadas e revisar o diff antes de substituir a configuração. Registrar essa reversão em commit local próprio, sem push automático.
7. Preservar as três tabelas sandbox e todas as migrations aplicadas. O schema é aditivo e não exige remover dados para restaurar o código anterior. Manter o backup SQL protegido em `backups/chat129/vetorleads-before.sql`; restauração integral do banco não é o procedimento normal de reversão de código e exigiria reconciliar qualquer atividade posterior.

As imagens anteriores não foram removidas. Nenhum serviço não relacionado precisa ser recriado. A sessão WAHA `vetoros1-9` já estava FAILED antes desta publicação; não reiniciá-la como parte desta reversão.
