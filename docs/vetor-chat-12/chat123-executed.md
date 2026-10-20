# VETOR-CHAT-12.3 — Relatório de execução e homologação

Executado em **19/10/2026 UTC**.

**Resultado: correio.md é novo e foi analisado. A homologação encontrou falhas e permanece incompleta; não há aprovação para ativação.** O relatório anterior foi preservado em [chat122-executed.md](docs/vetor-chat-12/chat122-executed.md), e a solicitação atual em [chat123-correio.md](docs/vetor-chat-12/chat123-correio.md).

## Comparação

- `correio.md` atual: **VETOR-CHAT-12.3 — Homologação Completa e Preparação para Ativação**, SHA-256 `8e89987990043b8af12f4db75dbf5baa245c25f408f268498215d3fbe7366184`.
- O relatório 12.2 registra o correio anterior com SHA-256 `2829878064158eee8e8c35bb76f19193101e50eabb0933afb8689ebb564b3107`.
- A 12.3 pede regressão isolada completa, análise das 14 regras, jornadas multicanal, configuração de horários, revisão visual e preparação operacional. Portanto, a solicitação mudou materialmente.

## Segurança do ambiente — incidente durante a preparação

Uma tentativa inicial de iniciar `php artisan test` herdou a conexão padrão do Compose (`DB_CONNECTION=mysql`, `DB_DATABASE=vetorleads`) antes da configuração de testes do PHPUnit. **Essa tentativa alcançou o banco operacional.** Uma consulta somente de leitura observou 1 tenant, 1 lead, 0 propostas, 61 resultados de prospecção, 3 conversas WebChat e 14 regras de automação.

Não houve escrita deliberada, ativação ou envio real nessa tentativa. Porém, não foi possível comprovar que todos os efeitos de teste foram revertidos; por isso, este relatório não declara impacto zero nem certifica o estado dos dados operacionais após aquela tentativa. As execuções seguintes usaram o banco privado `vetorleads_chat123_test`, acessível por socket local e sem rede. A configuração versionada do PHPUnit foi restaurada para SQLite em memória e passou a forçar todas as variáveis de ambiente (`force="true"`), impedindo que a conexão padrão do Compose a substitua.

## Resultados

- **Serviços:** Docker reportou VetorLeads, worker e scheduler como `Up (healthy)`. VetorOS, demais aplicações, MySQL e WAHA também estavam `Up (healthy)`. Nenhum serviço foi publicado ou reiniciado nesta execução.
- **Regras:** leitura estática da configuração confirmou 14 rascunhos, todos inativos, no canal `both`, com prioridades distintas. A regra de fora do expediente exige configuração de agenda. O teste direcionado confirmou criação idempotente, estado inativo e prévia sem efeitos laterais.
- **Testes direcionados:** `SupportAutomationTest.php` e `SupportMultichannelTest.php` passaram antes das últimas correções de análise estática: **25 testes, 222 assertions**. Uma repetição após essas correções não iniciou porque o executável Pest do volume temporário perdeu a referência ao autoloader; não houve nova execução dos dois arquivos depois das correções.
- **Regressão completa no banco isolado:** **1.278 passaram e 27 falharam (13.613 assertions, 106,27 s)**. As falhas ocorreram em CustomerOnboarding (3), Prospecting (21), TenantCommercialIsolation (1) e TrialExpiredAutomation (2). Elas precisam ser investigadas antes de declarar a regressão aprovada; não foram atribuídas automaticamente a esta mudança.
- **PHPStan:** passou após correção de 9 apontamentos no controller e serviço da automação.
- **TypeScript:** `npm run types:check` passou após geração dos tipos Wayfinder.
- **ESLint:** a verificação global encontrou 5 problemas de formatação já existentes em `extension/ab-prospect-firefox/tests/extension.test.mjs` (linhas 241, 244 e 248). A verificação seletiva dos arquivos da interface de automação e navegação passou.
- **Build Vite:** passou; transformou 2.413 módulos.
- **PHP:** `php -l` passou no controller e serviço alterados; `git diff --check` passou.
- **Revisão visual:** não executada. Não há Chromium disponível no ambiente para inspecionar desktop e celular.
- **Jornadas externas:** não foi disparado atendimento real nem mensagem real. A homologação multicanal coberta pelos testes direcionados passou na execução inicial; não foi realizada nova inspeção visual nem ensaio manual ao vivo.

## Horários e prontidão

A tela existente oferece fuso, dias, início/fim e modo dentro/fora do horário; a ação e a resposta da regra permitem configurar encaminhamento e mensagem. Nenhum expediente foi presumido ou gravado. A regra dependente de horário continua como rascunho inativo até configuração do administrador.

A execução 12.2 registrou 14 regras no tenant Anderson Brasil (ID 1), nenhuma ativa, e trava global de envio WhatsApp desligada. Como houve a tentativa inicial com a conexão operacional, esses dados não foram consultados novamente nesta rodada. Não ativar regras nem liberar o WhatsApp com base neste relatório.

## Próximas pendências

1. Auditar o banco operacional e os registros de teste em torno da tentativa inicial para determinar se houve qualquer efeito persistente.
2. Investigar as 27 falhas da regressão e repetir a suíte completa.
3. Reexecutar os dois arquivos direcionados após corrigir o ambiente temporário Pest.
4. Corrigir os 5 erros globais de ESLint ou demonstrar que são anteriores e não relacionados; revisar a interface com Chromium em desktop e celular.
5. Confirmar tenant, regras, operadores, sessão WAHA, backup e mecanismo de desativação com evidência atual antes de qualquer ativação.

Não houve commit, push, publicação nem ativação. As alterações locais anteriores foram preservadas.
