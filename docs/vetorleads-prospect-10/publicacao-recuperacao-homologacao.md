# Publicação controlada — preparado, não aplicado

## Bloqueio de capacidade

O kernel confirmou três OOM globais encerrando o mysqld em 19/10, às 10:33:04, 10:35:05 e 10:42:50 UTC. Esses eventos explicam as desconexões e os sete reinícios registrados do worker. A observação de processos recuperados não demonstra que a causa de capacidade foi resolvida.

Antes da janela, é necessário autorizar e validar uma correção de capacidade do host compartilhado. O host tem aproximadamente 7,8 GiB de RAM, nenhum swap e /tmp em tmpfs com aproximadamente 3,2 GiB ocupados na leitura. Isso consome memória do host; não atribui, sozinho, a causa a um processo específico. Não remover dados de outros trabalhos para liberar memória. Cópias novas desta etapa estão em disco, e testes foram limitados em memória e executados sem duplicar os serviços operacionais.

Proposta para revisão: inventariar consumidores/picos, migrar artefatos de desenvolvimento do tmpfs para disco preservando caminhos e conteúdo após análise de dependências, avaliar capacidade adicional/swap e validar folga sob a carga esperada. Alterações de host e limites operacionais exigem autorização. Limitar apenas o MySQL sem dimensionamento pode causar OOM dentro do container e não constitui solução demonstrada. Evitar rebuild durante a publicação: a imagem já está pronta. Nenhuma dessas mudanças de host foi aplicada.

## Artefatos e pré-requisitos

- Candidato: commit VetorLeads `00282e4ab046b4fc0f7bd0a5f33766255104df3e`; imagem `vetorleads:prospect09-00282e4`; ID `sha256:4d9deab99bb7ab4d2ffbe660983cb668ca34a931ae34cd4e982d957d8181a733`. Mesma imagem para app, worker e scheduler. Não recompilar código comercial que não mudou.
- Novo probe local: `scripts/vetorleads-process-health.php`. Confere PID 1 e conectividade MySQL com transação READ ONLY/SELECT 1, sem bootstrap do Laravel, disparo ou consumo de jobs. Não mede progresso de tarefas nem corrige OOM automaticamente. O override monta o probe como arquivo somente leitura nos dois consumidores; o arquivo precisa acompanhar a infraestrutura na publicação.
- Arquivo preparado: `docs/vetorleads-prospect-10/publication.override.yml`. Validar com Compose base como primeiro arquivo para resolver `./scripts` a partir da raiz da infraestrutura. As configurações de healthcheck não foram aplicadas em produção.
- Snapshot atualizado e assets realmente servidos estão em `staging/prospect10/`, fora do Git, com hashes em `backup-metadata.json`. O snapshot inclui dados privados; não publicar seu conteúdo. Criar novamente na janela se houver novas escritas.
- Tags locais de recuperação: `vetorleads:prospect10-recovery-vetorleads`, `vetorleads:prospect10-recovery-vetorleads-worker` e `vetorleads:prospect10-recovery-vetorleads-scheduler`. IDs originais em `operational-services.json`. As três imagens originais são distintas: usar a tag correspondente a cada processo na reversão.
- A infraestrutura e o VetorLeads têm pendências locais; o ponteiro VetorOS modificado é preexistente. Não usar `git add .`, nem publicar alterações do VetorOS. Preparar commit de infraestrutura apenas com os arquivos aprovados e o ponteiro VetorLeads; preservar separadamente a alteração local do VetorOS.
- As 48 migrations já estão aplicadas. Nenhuma migration adicional é necessária nem autorizada por este correio.

## Aplicação, somente após autorização expressa

1. Aprovar a mitigação de capacidade, janela, responsável e estado de recuperação. Confirmar que os reinícios não continuam aumentando e que o MySQL permanece acessível.
2. Reconferir remotos, ponteiros, imagem/manifest e hash do probe. Não atualizar dependências nem mudar VetorOS nesta janela. Push somente dos commits revisados e explicitamente aprovados.
3. Atualizar backup/assets se necessário; preservar as tags e hashes de recuperação. Verificar permissões do probe no mount somente leitura.
4. Validar o override:

```sh
docker compose -f docker-compose.yml -f docs/vetorleads-prospect-10/publication.override.yml config --quiet
```

5. Coordenar a pausa dos consumidores VetorLeads para evitar versões concorrentes durante a troca. Sem reiniciar MySQL, Redis, WAHA, n8n, nginx ou outros produtos. Aplicar somente os três serviços:

```sh
docker compose -f docker-compose.yml -f docs/vetorleads-prospect-10/publication.override.yml up -d --no-deps --no-build vetorleads vetorleads-worker vetorleads-scheduler
```

6. Conferir o mesmo ID de imagem nos três containers, PID/health, logs, filas e scheduler. Verificar assets por HTTP e compatibilidade do WebChat. Não disparar job comercial ou mensagem como smoke test. Comparar contagens de tenant e dados anteriores.

## Homologação real — autorização própria

Preencher antes de qualquer escrita: tenant, operador e papel, produto, até três empresas/identidades Maps, campos que podem ser alterados e janela. A execução atual usa apenas uma empresa sintética em tenant local; nenhuma empresa real foi selecionada implicitamente.

Verificar automações n8n/WAHA, gatilhos de lead e filas do tenant. A ausência de fila não garante que uma ação futura não dispare um evento. Exigir bloqueio comprovado de mensagens/eventos externos para o tenant; não alterar automações de outros produtos. Se não houver como garantir isso sem mudança operacional, pausar e solicitar autorização específica para essa mudança.

1. Capturar as empresas aprovadas via extensão; guardar lote/identidades de forma privada.
2. Recapturar uma empresa; conferir deduplicação, lacunas preenchidas e score/versão/vínculo/oposição preservados.
3. Validar filtros de segmento, cidade, UF, site e contato e a listagem dos capturados.
4. Associar produto e critérios de segmento; gerar prévia e confirmar uma qualificação individual, com isolamento de tenant.
5. Converter individualmente para CRM. Repetir a ação e comprovar ausência de lead/nota duplicados.
6. Movimentar a oportunidade no funil dentro do tenant e registrar somente uma nota de homologação; não marcar ligação ou WhatsApp como realizado.
7. Conferir histórico e dashboard contra o banco: inclusão no CRM e reunião não são vendas ganhas. Preservar os registros e encerrar a amostra sem exclusões implícitas.

Nenhum WhatsApp, e-mail, consulta externa de site/CNPJ/IA ou automação comercial faz parte desta autorização. O teste real depende da publicação autorizada e do bloqueio de envios demonstrado.

## Recuperação

Interromper em perda de isolamento, duplicação, corrupção, mensagem inesperada, falha persistente de assets/consumidor ou novo OOM. Guardar logs privados e métricas antes de agir.

1. Reverter apenas os três serviços para as tags individuais de recuperação, com override revisado, `--no-deps --no-build`. Voltar também a configuração anterior de healthcheck/mount se necessário. Não reconstruir nem alterar produtos vizinhos.
2. Restaurar a cópia dos assets realmente servidos em `vetorleads-build` e conferir o hash do manifest. Manter arquivos antigos durante a transição para evitar 404 em abas abertas. Toda escrita no volume servido pertence à janela autorizada.
3. Manter as colunas aditivas. Nunca usar `migrate:rollback` para desfazer artefatos; os métodos down apagam campos e dados.
4. Se houver corrupção comercial, restaurar o snapshot em banco isolado e propor reparação seletiva por tenant/ID. Não sobrepor produção inteira com snapshot antigo nem apagar escritas posteriores. Qualquer reparação exige autorização separada.
5. A reversão de aplicação não resolve falta de memória do host. Tratar o incidente de capacidade sem reiniciar o banco compartilhado como procedimento genérico de deploy.
