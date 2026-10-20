# Como reproduzir o ensaio isolado

Use nomes novos para container/rede/volume e diretório privado com permissão 0700. Não conecte o clone à rede operacional e não publique sua porta. Preserve o dump fora do Git (0600). Credenciais devem ser geradas novamente e guardadas em arquivo privado.

1. Registrar a leitura operacional com PDO: `SET SESSION TRANSACTION READ ONLY`, `SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ`, `START TRANSACTION WITH CONSISTENT SNAPSHOT`, consultar versão/migrations/colunas/contagens e finalizar com `ROLLBACK`, sem bootstrap Laravel operacional.
2. No container MySQL operacional, extrair somente `vetorleads` com `mysqldump --single-transaction --skip-lock-tables --no-tablespaces --set-gtid-purged=OFF --skip-add-locks --hex-blob`. Usar credenciais já presentes no ambiente sem imprimi-las. Isso pressupõe ausência de DDL concorrente durante o snapshot.
3. Criar rede `docker network create --internal <rede-ensaio>` e container `mysql:8.4`, conferindo versão 8.4.11 neste ensaio. Usar volume exclusivo, sem bind no datadir operacional. Aguardar conexão autenticada após o fim da inicialização temporária.
4. Criar bancos `rehearsal` e `functional`. Criar usuário distinto com privilégios somente nesses dois bancos. Restaurar o dump em `rehearsal`; nunca usar root na aplicação de testes.
5. O snapshot atual já contém as seis colunas novas. Para repetir o estado anterior, **somente no clone**, remover as seis colunas e as duas entradas correspondentes de `migrations`. Isso reconstrói o schema anterior; não constitui snapshot histórico completo pré-migration. Se houver novos dados nessas seis colunas, preservá-los em cópia privada antes da reconstrução.
6. Gerar, para cada tabela exceto `migrations`, um hash de cada linha usando somente colunas antigas; ordenar hashes das linhas, agregar com SHA-256 e registrar quantidade. Excluir as três colunas novas de cada tabela afetada dos dois lados da comparação. Comparar também esquema e índices; nenhum índice é criado/removido por essas migrations.
7. Com cópia limpa do código e dependências de teste PHP 8.4, apontar `.env` exclusivamente ao clone e `DB_DATABASE=rehearsal`. Usar cache/sessão array, mail array e nenhuma integração operacional. Rodar separadamente:

```sh
php artisan migrate --force --path=database/migrations/2026_10_18_210000_add_identification_to_web_conversations.php
php artisan migrate --force --path=database/migrations/2026_10_19_000000_add_commercial_criteria_to_prospect_products.php
```

8. Repetir os hashes e contagens. Conferir `SHOW COLUMNS` e `SHOW INDEX` das duas tabelas. Confirmar que `subject` (varchar 24), `identified_at` e `whatsapp_consent_at` (timestamp) são nulos inicialmente; segmentos são text nullable; `website_preference` é varchar 16, NOT NULL e default `any`. Registrar tempos reportados pelo Laravel e tempo total de processo separadamente.
9. Medir contenção em **tabela de prova descartável** do clone: manter uma transação leitora aberta, executar `ALTER TABLE` em outra sessão com `lock_wait_timeout=1`, esperar erro 1205; após liberar a leitora, repetir e esperar sucesso. Não interpretar o tempo sem concorrência como garantia de duração em produção.
10. Preparar `phpunit.mysql.xml` a partir de `phpunit.xml`, substituindo SQLite por MySQL e banco por `functional`. Rodar `vendor/bin/pest --configuration=phpunit.mysql.xml --compact`. Esse banco é sintético e recebe `migrate:fresh`/limpeza dos testes; **não usar rehearsal ou banco operacional**. Rodar também a suíte SQLite original e os testes WebChat do VetorOS isolados e extensão Node sem rede.
11. Construir imagem candidata pelo Dockerfile, sem executar Compose up. Extrair o manifest de `/opt/public-build/manifest.json`, registrar ID da imagem e validar os comandos app/worker/scheduler sem processar filas.
12. Parar o container exclusivo de ensaio após coleta de evidências. Manter backup/cópia em área privada para recuperação; eliminar posteriormente conforme retenção aprovada, sem operações sobre volumes de outros serviços.

A restauração das 41 tabelas foi executada nesta etapa. A comparação agregada das colunas antigas preservou também os identificadores de tenant e conversas existentes. Testes funcionais usam dados sintéticos, não escrevem na cópia dos dados comerciais.
