# Execução — VETORLEADS-QUALIFY-01

Implementação concluída localmente em 20/10/2026, sem publicação em produção.

## Comparação do correio

O `correio.md` da raiz mudou de **VETORLEADS-VALIDATE-01** para **VETORLEADS-QUALIFY-01**. SHA-256 atual: `769389c8de686451b90fa5c90d7e1913e4878dda8b66f88c9b00b4f18793c390`. A instrução atual está preservada em [correio.md](docs/vetorleads-qualify-01/correio.md). O relatório anterior foi arquivado integralmente em [previous-executed.md](docs/vetorleads-qualify-01/previous-executed.md), SHA-256 `613df666d7f5aee4e8e8d1b3c4b18ab3c76e04f5fe46bce31e767245f903f88f`.

## Implementação

- Reaproveitei a qualificação por produto, a validação empresarial, as evidências das fontes, o CRM e o fluxo existente de captura. O score comercial agora está na versão de regras `v4`; identidade e atividade continuam como dimensões independentes.
- Criei perfil ICP por tenant com segmento incluído/excluído, estado, município, porte cadastral, preferência por presença de site, produto/serviço e proposta de valor. Configurações são armazenadas pelo model tenant-scoped `Setting`, com o cast criptografado já usado pelo sistema. Perfil desligado mantém o comportamento anterior.
- O score explica os critérios do ICP e a aderência do produto. Porte só recebe evidência de validação cadastral atual associada ao mesmo CNPJ. Ausência de dados fica pendente, e segmento explicitamente excluído zera a dimensão de aderência.
- Cada qualificação guarda contexto comercial, hash do perfil e hash dos dados usados. Mudança de perfil, evidências, dados, status empresarial ou oposição invalida uma revisão anterior.
- Identifiquei canais disponíveis junto à origem e confiança. Fontes públicas, popularidade, avaliações e situação cadastral não são tratadas como prova de intenção de compra nem como confirmação de disponibilidade do canal.
- A interface existente permite configurar ICP (owner/admin), revisar score e canais, ler justificativas e usar rascunho e perguntas específicos para o segmento. O rascunho fica explícito como conteúdo para revisão humana; nenhum WhatsApp, e-mail ou mensagem de rede social é enviado.
- Incluí indicadores de qualificados, revisados, pendentes e bloqueados, além de filtro por estado de revisão. Conversão para CRM com ICP ativo exige qualificação atual e revisão humana ligada ao mesmo hash; oposição, ausência de contato ou situação empresarial que exige análise permanecem bloqueios.
- Registrei a revisão com ator, data, tenant e hash. A nova migration adiciona campos à qualificação existente; não cria catálogo nem motor de score paralelo.

## Verificação

- Suíte completa de testes Pest em SQLite de teste: **1.300 testes aprovados, 13.590 assertions**.
- Foram adicionados testes de ICP tenant-scoped e criptografado, explicação do score, origem dos canais e bloqueio/liberação da conversão ao CRM pela revisão humana.
- `npm run build`: aprovado. Os assets compilados durante a verificação foram restaurados e não fazem parte das alterações.
- Pint (`--test`), `git diff --check` e `php artisan route:list --path=prospecting`: aprovados; as rotas de configuração ICP e revisão comercial aparecem sob o middleware de tenant.
- Os testes usam SQLite isolado e fontes HTTP bloqueadas/simuladas. Não houve consulta de leads reais nem chamada de API comercial.

## Arquivos centrais

Novos: `IdealCustomerProfile`, `ProspectCommercialContext`, controllers de ICP e revisão comercial, migration `2026_10_20_150000_add_commercial_review_to_prospecting_qualifications.php`, editor `ideal-customer-profile.tsx` e testes de fluxo.

Integrados: `ProspectQualifier`, `ProspectQualificationPreview`, `ProspectConverter`, `ProspectingController`, painel `prospecting-results.tsx`, página `prospecting/prospects.tsx`, `DashboardController` e rotas web. A validação empresarial e os demais arquivos previamente alterados foram preservados.

Não foi feito commit, push, deploy, migração em banco operacional nem habilitação de API paga. Para ativar em ambiente aprovado, aplicar as migrations após backup e publicar o build da aplicação; nenhum envio de mensagem faz parte deste fluxo.
