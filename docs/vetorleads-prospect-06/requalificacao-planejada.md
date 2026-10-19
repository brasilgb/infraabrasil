# Plano de requalificação posterior — NÃO EXECUTADO

A autorização atual abrange auditoria e preparação. Este documento não autoriza atualização, migrations, deploy, envio de mensagens ou movimento para CRM. Não foi criado comando de aplicação em massa.

## Prévia preparada

Comando local, para operador administrativo com acesso controlado ao host:

```bash
php artisan prospecting:preview TENANT_ID --actor=ADMIN_USER_ID --limit=100 --sample=5
php artisan prospecting:preview TENANT_ID --actor=ADMIN_USER_ID --product=PRODUCT_ID --limit=100 --after-id=LAST_ID --sample=5
```

Omitir `--product` para contexto geral (`product_key=0`). Nunca comparar essa saída com score de outro produto. O comando não tem `--apply`, `--all` nem execução para todos os tenants. Valida administrador ativo, associação owner/admin ao tenant (ou o papel root existente), produto no mesmo tenant e limite entre 1 e 500. `--actor` identifica o administrador validado; não autentica quem tem acesso ao shell. Não expor esse comando como endpoint que aceite parâmetros de usuários comuns.

O comando e os serviços são preparação local. Não foram instalados no container operacional. A auditoria desta etapa usou PDO por stdin, transação READ ONLY e cálculo em container isolado sem rede; nenhum arquivo de aplicação foi copiado para o container operacional.

## Condições para uma execução futura

1. **Aprovação separada:** registrar autorização administrativa de implantação e de atualização dos dados, indicando tenant, contexto de produto e IDs. Proibir fallback para “todos”. Se o catálogo estiver vazio, manter contexto geral ou pedir definição comercial antes de qualquer qualificação específica; não transformar rótulo textual de captura em produto automaticamente.
2. **Backup verificável:** guardar backup consistente das tabelas do tenant em local restrito, com checksum e retenção. Não colocar dump com empresas/contatos no Git ou nos relatórios. Restaurar em ambiente isolado e conferir vínculos, oposição, qualificações, lotes e CRM antes de aprovar execução.
3. **Manifesto explícito:** tenant, produto ou contexto geral, lista fechada de IDs elegíveis, total esperado, versão v3, hash do qualificador, data da prévia, contagens por prioridade, hashes/versões/updated_at anteriores e referência de aprovação. Lista vazia deve resultar em zero alterações, nunca em seleção global.
4. **Revalidação imediata:** gerar nova prévia com transação somente leitura. Verificar mudanças de dados, produto, código e instante de avaliação. Rejeitar inconsistências com o manifesto; o componente de atualidade pode mudar com o tempo.
5. **Confirmação administrativa:** mostrar quantidade exata, tenant, contexto e mudanças previstas. Exigir confirmação explícita antes da primeira escrita. Reutilizar as políticas owner/admin/root existentes; usuários comuns não escolhem tenants alheios.
6. **Pequenos lotes:** iniciar com até 10 IDs e revisão do resultado; aumentar somente após confirmação do comportamento. Transação e lock curtos por registro. Revalidar tenant, produto e versão/updated_at antes de cada escrita; conflito deve ser ignorado e relatado, sem sobrescrever trabalho recente.
7. **Preservação:** manter a qualificação anterior por produto, snapshot dos campos recuperados e backup externo. Preservar IDs, fontes, lotes, avaliações, observações, favoritos, oposição e vínculos com CRM. Não executar enriquecimento externo, IA, conversão ao CRM ou mensagens como efeito colateral.
8. **Auditoria:** registrar executor, autorização, tenant, produto, IDs, versão/hash v3, antes/depois, sucesso, conflito e falha. Relatório compartilhável deve trazer só contagens e amostras anonimizadas; dados completos ficam em armazenamento restrito.
9. **Interrupção e retomada:** checkpoint com IDs concluídos, pendentes e com falha. Terminar o registro/transação corrente e parar antes do seguinte. Retomar só o conjunto restante, após nova verificação. Mesmos dados e contexto devem produzir o mesmo hash e evitar regravação desnecessária.
10. **Recuperação assistida:** interromper se a validação divergir. Restaurar somente os IDs/contextos atingidos, com nova confirmação e comparação de versão, evitando apagar alterações posteriores legítimas. Os snapshots existentes guardam a última versão anterior por produto; não substituem backup testado nem constituem histórico ilimitado.
11. **Conferência final:** contagens e integridade antes/depois, mudanças previstas versus aplicadas, falhas/conflitos, evidência de que CRM/históricos/oposição foram preservados e encerramento administrativo.

## Situação encontrada nesta auditoria

Tenant 1: 61 prospects, 2 lotes, nenhum produto cadastrado, 61 qualificações v2. A prévia geral v3 resulta em 61 prioridades médias por disponibilidade de contato com aderência neutra. Não há localização completa recuperável do payload/endereço existente pelo mecanismo conservador. A ausência de produto e localização não deve ser resolvida por suposição ou descoberta por telefone/coordenadas.

Existe migration pendente de identificação do WebChat. Ela está fora desta preparação de requalificação; não aplicá-la incidentalmente durante uma execução comercial.
