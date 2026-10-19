# VETORLEADS-COMERCIAL-01 — Ciclo comercial B2B

Executado em **19/10/2026 UTC**.

**Estado final: IMPLEMENTADO E VALIDADO LOCALMENTE. PUBLICAÇÃO NÃO EXECUTADA.** Um operador sintético concluiu Prospect → Qualificação → CRM → Contato → Oportunidade → Proposta → Negociação → Fechamento → Indicadores. As melhorias foram implementadas no VetorLeads, reutilizando os módulos existentes. Não houve push, deploy, migration operacional, alteração de registros comerciais reais ou envio de mensagens reais.

## Comparação do correio

O arquivo mudou de PROSPECT-11 para COMERCIAL-01:

- SHA-256 anterior: `3a69a95cc535bfbb25e7f310471426fb7790cda78596eefbaff83e2483230283`.
- SHA-256 executado: `d9bd19925dfc575f9850453d48ac6e124517b786fd187b9db0b338f7793e545f`.
- [Relatório anterior preservado](docs/vetorleads-commercial-01/prospect11-executed.md) e [metadados desta execução](docs/vetorleads-commercial-01/execution.json).

A nova autorização foi respeitada: desenvolvimento e testes locais, com MariaDB nativo, sem introduzir Docker no desenvolvimento desta etapa. A publicação autorizada no correio anterior não foi reutilizada como autorização para publicar estas mudanças.

## Inventário funcional e reaproveitamento

O [inventário detalhado](docs/vetorleads-commercial-01/inventario.md) classifica as funcionalidades anteriores e relaciona arquivos concretos de evidência.

| Área | Estado encontrado | Entrega desta etapa |
| --- | --- | --- |
| Captura, deduplicação e prospects | Implementada | Reutilizada e validada com entrada sintética. |
| Qualificação, score e produtos | Implementada | Prévia e confirmação por produto preservadas e exercitadas. |
| Conversão para CRM | Implementada | Repetição comprovadamente idempotente, sem nova oportunidade. |
| Funil e oportunidades por tenant | Implementada | Preservadas as seis chaves existentes, rótulos/ordem configuráveis e quadro do CRM. |
| Atividades e abordagem comercial | Implementada | Reutilizados chamada, WhatsApp manual, e-mail, reunião, observação, status, vendedor e próximo contato. Registro agora atômico com alterações do lead. |
| WhatsApp, WebChat e histórico técnico | Implementada | Preservados. Atividade manual continua distinta de mensagem técnica enviada/recebida. |
| Follow-ups | Implementada | Reutilizados pendentes, vencidos, hoje/futuros, tarefas e histórico. |
| Propostas comerciais B2B | Ausente | Implementadas propostas, itens, descontos, snapshots, versões, estados e PDF. Recursos de e-books não foram confundidos com propostas B2B. |
| Fechamento / reabertura | Parcial | Implementados valor negociado, data/autor do fechamento, idempotência e reabertura com motivo e auditoria. |
| Dashboard e relatórios | Parcial | Corrigida a soma indevida de estimativas como valor vendido; acrescentados indicadores de propostas e resultados por vendedor/produto/segmento/origem. |
| Recebimentos financeiros | Ausente no B2B | Não foi criado recebimento fictício; a interface informa que não é registrado, e a métrica retorna `null`. |

As oito etapas do funil eram **sugeridas** no correio. Foram preservadas as seis chaves compatíveis, a personalização por tenant e os status existentes; o acompanhamento de proposta enviada/aceita pertence ao novo módulo de propostas, sem reescrever funis configurados.

## Implementação

**Propostas:** associação à oportunidade; seleção de produtos/serviços do catálogo do tenant; quantidade, valor unitário e desconto total; condições, validade e observações; totais calculados no servidor em centavos inteiros. Valores de totais e tenant enviados pelo cliente não definem os registros persistidos. Nome de produto, itens, empresa e valores ficam preservados na versão. Revisão cria nova versão sem sobrescrever conteúdo anterior. Há estados rascunho, enviada, aceita, recusada e expirada; expiração é derivada da validade, sem jobs ou gravação ao consultar. Revisões antigas não podem ser enviadas/aceitas como se fossem a versão atual. Aceite não fecha automaticamente a oportunidade.

**PDF:** documento A4 real, com texto selecionável, acentos, escape de conteúdo e paginação automática, produzido localmente sem nova dependência PHP ou serviço externo. Visualização da proposta e download são manuais. Registrar como enviada apenas grava o estado e o histórico; não transmite mensagem.

**Fechamento:** ganho/perda, motivo da perda, valor negociado explícito, data e autor do fechamento; vendedor e produto permanecem na oportunidade e no registro de histórico. O fechamento exige responsável/produto e impede substituição silenciosa de um fechamento existente. Repetição equivalente não duplica o histórico. Reabertura exige motivo, preserva os dados anteriores no histórico e retira a oportunidade das vendas atualmente fechadas. Vendedor/produto de uma venda com valor negociado só mudam após reabertura. Caminhos antigos de edição, status, atividade e lote foram protegidos para não contornar a reabertura; transações e locks serializam alterações concorrentes.

**Indicadores:** prospects capturados/qualificados, conversão para CRM, oportunidades abertas, ganhos/perdas, contatos e follow-ups existentes foram mantidos. Novos indicadores separam pipeline estimado, valor negociado fechado, propostas emitidas/aceitas e recebimentos não registrados. Propostas emitidas são contadas por série, evitando inflar o indicador por versões. Relatórios agrupam oportunidades, ganhos, perdas, valores e conversão por vendedor, produto, segmento e origem. Vendas antigas sem valor negociado ficam sinalizadas; não houve backfill nem conversão de estimativa em receita real.

**Navegação:** Dashboard, Capturar empresas, Prospects, CRM / Oportunidades, Propostas, Atividades e follow-ups, Produtos e serviços e Relatórios. O quadro permanece na aba do CRM, e configurações ficam no rodapé. A edição do lead reúne histórico, propostas e fechamento.

## Arquivos e migration

Commit local da aplicação: **`f072ba01e1206ac876b806ddfeb9aecfabe890c8`** — `Completa ciclo comercial B2B com propostas versionadas e fechamento auditado`.

Foram registrados **27 arquivos**, com lista e hashes em [changed-files.json](docs/vetorleads-commercial-01/changed-files.json). Principais alterações:

- `app/Models/CommercialProposal.php`, `app/Services/CommercialMoney.php`, `ProposalPdf.php`, `CommercialMetrics.php`.
- `CommercialProposalController`, `CommercialClosureController`, `CommercialReportController`; ajustes em `LeadController`, `LeadActivityController`, `DashboardController` e `Lead`.
- Novas telas `resources/js/pages/proposals/*`, relatório comercial, componentes de indicadores e fechamento; integração ao dashboard, edição do lead e menu.
- Rotas comerciais e `tests/Feature/CommercialCycleTest.php`; testes anteriores de dashboard passaram a distinguir estimativa de valor negociado.
- Migration **`2026_10_19_130000_add_commercial_proposals_and_closure.php`**: cria `commercial_proposals` e acrescenta `negotiated_value`, `closed_at`, `closed_by_user_id` nullable em `leads`. Sem backfill automático.

A migration foi aplicada **somente no MariaDB sintético**. O schema local ficou com 49 migrations; o schema operacional não foi alterado. A configuração/imagem operacional continuou na versão anteriormente publicada. Nenhum serviço pago, API nova ou dependência de produto foi acrescentado.

## Testes e isolamento

- **Regressão completa: 1.280 testes aprovados, 13.630 assertions, 75,731 s**, em PHP **8.4.26** e MariaDB nativo **11.8.6**. [Resultado](docs/vetorleads-commercial-01/all-tests.log).
- Após os locks finais dos caminhos antigos, **62 testes / 479 assertions** aprovados, cobrindo ciclo novo, gerenciamento de leads, atribuição e atividades/follow-ups. [Resultado](docs/vetorleads-commercial-01/final-lock-tests.log).
- **PHPStan nível 7 sem erros**, TypeScript, ESLint dos arquivos alterados e build Vite aprovados; PHP formatado com Pint e interfaces com Prettier. [PHPStan](docs/vetorleads-commercial-01/phpstan.log), [TypeScript](docs/vetorleads-commercial-01/typescript.log), [ESLint](docs/vetorleads-commercial-01/eslint.log) e [build](docs/vetorleads-commercial-01/build.log).
- **Isolamento:** testes negam leitura/alteração/PDF de proposta de outro tenant; recusam lead, produto e revisão estrangeiros; negam fechamento/reabertura de lead de outro tenant; listagem e relatório permanecem no escopo correto. Novos endpoints reutilizam `EnsureCommercialAccess`, `TenantContext` e `BelongsToTenant`. Também foram testados valores inválidos, desconto excessivo, expiração, transições inválidas, revisão preservada, idempotência e rollback de atividade que tenta reabrir indevidamente.
- **Navegador Chromium nativo:** jornada comercial integral e 12 verificações de telas em **1440 e 390 px**, com HTTP 200, sem erros JavaScript e sem transbordamento horizontal do documento. Tabelas usam rolagem interna. Amostras de proposta desktop e relatório celular foram inspecionadas visualmente. [Resultado](docs/vetorleads-commercial-01/interface/results.json), [proposta desktop](docs/vetorleads-commercial-01/interface/1440-_proposals_3.png) e [relatório celular](docs/vetorleads-commercial-01/interface/390-_commercial_reports.png).
- **PDF:** validado com `pdfinfo` e `pdftotext`: A4, PDF 1.4, texto extraível com valores/acentos, sem JavaScript. [PDF sintético](docs/vetorleads-commercial-01/interface/proposta-sintetica.pdf), [validação](docs/vetorleads-commercial-01/pdfinfo.txt) e [texto extraído](docs/vetorleads-commercial-01/pdf-text.txt).
- Os **27 arquivos** alterados coincidiram entre a árvore entregue e a cópia nativa utilizada nas verificações finais. [Paridade](docs/vetorleads-commercial-01/source-parity.json).

O ambiente local foi criado em `backups/commercial01/`, ignorado pelo Git: MariaDB com datadir/socket próprios, `--skip-networking`, buffer pool de 128 MiB, schemas `commercial01_test` e `commercial01_ui`. A aplicação de homologação usou apenas `127.0.0.1:8012`, mailer de teste e identidades sintéticas. PHP CLI, binários MariaDB/Node e bibliotecas de navegador/leitura PDF foram instalados como ferramentas de desenvolvimento, sem criar serviço global MariaDB. Não foi introduzido Docker para esta etapa. Os processos locais foram encerrados ao concluir; arquivos e evidências foram preservados.

## Resultado da jornada sintética

Um tenant, um operador, um produto e uma empresa sintéticos. O prospect foi qualificado, convertido para um único lead, recebeu registro manual de chamada/interesse/objeção e follow-up; teve proposta emitida, revista, aceita e negociada; a oportunidade foi ganha com **R$ 345,55 negociados**, refletidos nos indicadores sem informar recebimento financeiro.

Estado final da homologação: **1 tenant, 1 usuário, 1 prospect, 1 lead, 3 snapshots de propostas, 12 atividades, 0 jobs e 0 jobs falhos**. Há um rascunho preservado de uma passagem inicial do harness e versões 1/2 da série concluída; o indicador corretamente mostra **1 proposta emitida e 1 aceita**, sem multiplicar versões. Ajustes do harness trataram a navegação Inertia; não foram correções de dados operacionais. Todos os valores deste parágrafo são sintéticos. [Contagens e indicadores](docs/vetorleads-commercial-01/synthetic-final-state.json).

## Preparação e limites da próxima publicação

O [roteiro de preparação](docs/vetorleads-commercial-01/preparacao-publicacao.md) identifica o commit, a migration aditiva, a ordem de upgrade, backup, imagem única e recuperação sem remover os dados novos.

Antes da publicação será necessário ensaiar o upgrade em MySQL 8.4 isolado, gerar/verificar a nova imagem, atualizar backup e validar capacidade do host. Nesta etapa, os testes de banco foram feitos no **MariaDB nativo**, conforme solicitado; não se declara ensaio desta migration no MySQL operacional. A versão nova depende da migration e não deve ser publicada isoladamente sobre o schema antigo.

A receita recebida continua fora do módulo: não há integração de pagamentos nem baixa financeira automática. A amostra real em produção, qualquer preenchimento histórico, mensagens, push, deploy e migrations operacionais dependem de uma próxima autorização. O ponteiro VetorOS preexistente e as evidências anteriores foram preservados.

**Critério de aceite local atendido com dados sintéticos. A publicação desta evolução permanece pendente e não foi executada.**
