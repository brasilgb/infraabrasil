# ABRASIL-SAAS-PADRAO-01.2 — execução parcial

**Data:** 02/11/2026
**Instrução vigente:** `correio.md` da raiz, diferente da `ABRASIL-SAAS-PADRAO-01.1` anteriormente registrada.
**VetorSales:** branch `abrasil-auth-ux-03`, base `35857c9`, HEAD `7b8dd964a883947a5d71e93aa9ecfa2b8cb92078`.

## Implementado

- Checkout Efí do tenant reunido em uma janela com Pix, boleto/Bolix e cartão. Os meios aparecem apenas quando estão habilitados, homologados e com a configuração necessária.
- Cartão tokenizado no navegador com a biblioteca JavaScript oficial Efí. O endpoint recebe apenas `payment_token`, máscara, titular e endereço de cobrança; número, validade e CVV não são enviados ao VetorSales nem persistidos. O cartão exige credenciais de Cobranças, identificador público de conta e homologação. Trocar o identificador invalida a homologação anterior.
- Corrigido o filtro de visitas atrasadas para incluir compromissos abertos de meses anteriores. Totais monetários retornados pela API foram normalizados com duas casas. O teste de desempenho comercial foi fixado em uma data estável. Três testes que verificam SQL `FOR UPDATE` passam a ser executados apenas em bancos que suportam esse comando; continuam ativos no MySQL.
- O fluxo de renovação mantém o dia contratual fixo e a carência existente. Nenhuma cobrança real foi criada nesta execução.

## Validação

- Suíte completa em SQLite de teste isolado `/tmp/vetorsales012_testing`: **353 aprovados, 3 ignorados, 3033 asserções**. Os três ignorados verificam `FOR UPDATE`, que o SQLite não emite. Antes dos ajustes, a suíte reproduziu **7 falhas e 348 aprovações**: três dependiam de `FOR UPDATE`; quatro envolviam data/valor e filtro de visitas.
- Testes focados de Efí, configurações e checkout: **17 aprovados, 107 asserções** após os ajustes, incluindo a exposição do cartão somente com configuração e homologação.
- `npm run types`, `npm run build -- --outDir /tmp/vetorsales-012-build`, `php -l` dos controladores e serviço alterados e `git diff --check`: aprovados. O build avisou sobre imagem resolvida em runtime e tamanho de chunk.
- Teste MySQL isolado com banco `vetorsales_reconcile_testing`: **impedido antes da execução** por `SQLSTATE[HY000] [2002] Operation not permitted` ao conectar em `localhost:3306`. A aprovação em SQLite não equivale à homologação em MySQL.

## Git e publicação

- Commits no VetorSales: `ed72403` (relatórios comerciais e testes dependentes de MySQL) e `7b8dd96` (checkout unificado e tokenização Efí).
- `git push origin HEAD:abrasil-auth-ux-03` retornou sucesso: `25e8f7c..7b8dd96`. Uma consulta `git ls-remote` antes e depois do push falhou por DNS (`Could not resolve host: github.com`); portanto, há confirmação do push pelo retorno do Git, mas não uma verificação independente do SHA remoto.
- A branch local contém a `origin/main` em cache, com 8 commits à frente. A `main` remota atual não foi confirmada. Não houve integração à main, backup novo, migrations, deploy ou verificação de serviços na VPS.
- O gitlink do VetorSales foi atualizado no superprojeto para `7b8dd96`. O gitlink do VetorLeads não foi alterado; suas mudanças locais preexistentes permanecem.

## Pendências para o critério de conclusão

- Implementar seleção e alteração de plano pelo tenant, contratação de usuários adicionais, cotação proporcional e confirmação antes de cobrar. O backend atual ainda não suporta essas alterações de modo seguro.
- Homologar em navegador desktop e celular as telas RootAdmin, Meu Plano, os três meios de pagamento e renovação de conta suspensa. Não houve acesso a uma instância com credenciais Efí de homologação nem transação ponta a ponta.
- Executar os testes MySQL, confirmar estado remoto e, depois da homologação, integrar à main, realizar backup verificável antes de migrations, publicar e verificar os serviços. Não há evidência de que a versão da VPS tenha mudado.

**Estado:** parcial. O critério de conclusão de `correio.md` ainda não foi atingido.
