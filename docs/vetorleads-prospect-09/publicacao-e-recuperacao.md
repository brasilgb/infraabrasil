# Publicação e recuperação — PROSPECT-09

Procedimento preparado em 19/10/2026; nenhuma das ações operacionais abaixo foi executada nesta etapa.

## Checklist antes da autorização

- Conferir HEAD, árvore de trabalho, remotos e divergências antes de push. VetorLeads candidato: `adcc6e40f8025161882c95631f29cb69eab0bdba`. O ponteiro VetorOS mudou previamente para `953b90a825c76239055c29f90d77530770e6c2a5`; preservar essa mudança, que não pertence ao escopo comercial. Não fazer commit/push cego do ponteiro antigo.
- Verificar `runtime-read-only.json`: as duas migrations já estão aplicadas no operacional. Não reaplicar nem executar `migrate:rollback`. Se o estado mudar, interromper e investigar. Não invocar `migrate` genérico.
- Conferir o ID da imagem candidata em `image-id.txt` e o manifest extraído. Usar a mesma imagem em app/worker/scheduler. Repetir testes se fontes ou dependências mudarem.
- Registrar os IDs das três imagens operacionais, manter tags locais de recuperação e guardar cópia dos assets realmente servidos no volume `vetorleads-build`. Guardar configurações efetivas e segredos fora do Git. Verificar espaço e retenção dos backups.
- Criar novo snapshot consistente na janela aprovada e restaurá-lo num clone. O snapshot desta etapa foi restaurado com sucesso; não garante RPO para uma janela futura.
- Definir janela, operador, tenant e até três empresas autorizadas. Revisar automações, jobs pendentes, gatilhos de lead, webhook n8n e integrações WAHA. Só liberar escritas de homologação após comprovar que não haverá mensagens/eventos externos; se não houver bloqueio por tenant comprovável, interromper a homologação real.

## Publicação delimitada, após autorização específica

1. Publicar somente os commits revisados e aprovar explicitamente o ponteiro de infraestrutura. Não publicar VetorOS implicitamente.
2. Fixar `image: vetorleads:prospect09-00282e4` para `vetorleads`, `vetorleads-worker` e `vetorleads-scheduler` num override revisado, com `pull_policy: never`; validar com `docker compose -f docker-compose.yml -f <override-aprovado> config --quiet`. O override preparado está em `publication.override.yml`; conferir seu ID antes da janela. Não incluir segredos no relatório.
3. Coordenar processamento de filas para não misturar consumidores antigos/novos. Aplicar somente os três serviços, com `up -d --no-deps --no-build vetorleads vetorleads-worker vetorleads-scheduler`. Não reiniciar MySQL, Redis, WAHA, n8n, nginx ou outros produtos.
4. Verificar versão/health dos três processos, manifest/HTTP dos assets e erros. O healthcheck PHP-FPM sozinho não comprova schema nem fluxo funcional.
5. Testar proxy/widget VetorOS e API com token de conversa/tenant autorizado. Não usar um visitante operacional existente como cobaia. Parar em erro de tenant, duplicação, perda de histórico ou envio externo.

## Homologação comercial reproduzível, após autorização específica

Registrar privadamente tenant, operador, produto, lote e identidades Maps de até três empresas aprovadas. Preservar oposição e dados já existentes. Nenhum WhatsApp, e-mail, campanha, follow-up ou contato comercial é autorizado.

1. Capturar as empresas pela extensão com token do tenant; conferir origem/lote e deduplicação. Captura não deve criar lead.
2. Recapturar uma empresa; comparar score/versão antes e depois e comprovar preservação da qualificação e preenchimento conservador de lacunas.
3. Listar e filtrar por lote, produto, prioridade, contato, origem e localização. Confirmar contagens e ausência de acesso por outro tenant.
4. Cadastrar produto/segmentos do tenant e demonstrar os critérios desejados/excluídos e preferência de site; não sobrescrever produto comercial existente sem autorização.
5. Gerar prévia, registrar justificativas, confirmar individualmente somente um prospect aprovado. Contexto alterado ou prévia expirada deve impedir confirmação. Nunca requalificar em massa.
6. Converter em lead ou vincular ao existente; repetir a operação e comprovar ausência de duplicata e preservação dos dados anteriores.
7. Conferir histórico, vínculo CRM e dashboard: conversão para CRM não é venda ganha. Encerrar com evidências privadas e registros preservados.

Consultas externas de site/CNPJ/IA não fazem parte do roteiro. O teste real continua pendente; a suíte usa mocks e registros sintéticos.

## Recuperação sem rollback destrutivo

- Interromper a liberação/homologação em falha de isolamento, mensagem externa inesperada, erro persistente, duplicação ou perda de dados. Preservar logs sanitizados e IDs privados.
- Reverter os mesmos três serviços para os IDs/tags anteriores registrados antes da janela, sem rebuild e sem recriar dependências. Restaurar os assets preservados no volume servido e verificar manifest e hashes. Não substituir imagens de outros produtos.
- Manter as seis colunas aditivas e dados: a aplicação anterior deve tolerá-las. Não executar os métodos `down()` porque removem informações.
- Se houver corrupção de dados, restaurar backup em outro banco isolado, comparar por tenant/ID e preparar reparação seletiva auditada. Qualquer reparação operacional requer autorização própria; não restaurar snapshot inteiro sobre produção, pois descartaria escritas posteriores.
- O clone local reconstruído é ensaio de compatibilidade e recuperação da cópia, não restauração operacional nem medição de carga concorrente real.
