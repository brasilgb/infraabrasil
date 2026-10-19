# Próxima publicação — somente preparação

Nada deste documento foi executado em produção nesta etapa. O correio proíbe deploy, push e migrations operacionais.

1. Revisar o commit local, a migration aditiva e os relatórios. Publicar os commits somente em etapa autorizada.
2. Ensaiar o upgrade em cópia isolada do MySQL 8.4 operacional antes da janela. Nesta etapa, o banco de desenvolvimento e testes foi MariaDB nativo 11.8.6, sem Docker.
3. Preparar imagem imutável da nova versão, registrar digest e testar os assets; manter a mesma imagem para app/worker/scheduler. O override operacional continua apontando para a versão PROSPECT-09/11 já publicada: não foi alterado para esta evolução.
4. Gerar novo backup consistente e preservar imagens/configurações/assets da versão operacional. Validar restauração em banco separado e memória disponível no host.
5. Com autorização específica, aplicar apenas `2026_10_19_130000_add_commercial_proposals_and_closure.php` ao VetorLeads. Ela adiciona a tabela `commercial_proposals` e colunas nullable `negotiated_value`, `closed_at`, `closed_by_user_id` a `leads`. Não reescreve registros existentes. Coordenar a aplicação antes de iniciar código novo que consulta essas colunas/tabela.
6. Somente então atualizar a imagem dos três serviços e validar healthchecks, assets, filas, isolamento e o ciclo sintético. Não reiniciar serviços compartilhados como parte genérica do procedimento.
7. Vendas antigas sem valor negociado ficam sinalizadas, sem usar estimativas como venda fechada. Qualquer preenchimento de dados históricos exige uma decisão explícita e autorização própria; não há backfill automático.
8. Em falha, reverter somente código/configuração/assets. Preservar tabela, colunas e registros comerciais. Não executar o `down` destrutivo em produção nem sobrepor o banco com um snapshot antigo.

## Reprodução local

Artefatos privados ficam em `backups/commercial01/`, ignorado pelo Git. O MariaDB usa datadir próprio, buffer pool de 128 MiB e `--skip-networking`; somente o socket privado é utilizado. Os schemas são `commercial01_test` e `commercial01_ui`, sem conexão com o MySQL operacional. A cópia de teste usa mailer array e fixtures sintéticas.

O teste de aceitação está em `tests/Feature/CommercialCycleTest.php`; o harness de navegador está em `browser.cjs` nesta pasta. O banco de testes pode ser recriado, mas nunca se deve apontar esse harness para credenciais ou schemas operacionais. Os serviços locais de homologação serão encerrados ao terminar, preservando os arquivos e evidências.
