# Execução de `correio.md`: VETOR-CONSOLIDATE-01 — Commit e continuidade (COMMITS LOCAIS, SEM PUSH, SEM DEPLOY)

- **Data:** 2026-10-08 (America/Sao_Paulo).
- **SHA-256 do `correio.md` executado:** `286204dcdf44d9ae1358caf368aea74fddb37e05b3cb0157a63cf909a83ba78f`.
- **SHA-256 da execução anterior:** `1ab17c8378cb0772a7be404f3fc5c50138d1ce661051f5635c15569db704ab36` (VETOR-PROD-IMPORT-01). O arquivo mudou, então foi executado.
- **Restrições:** commits **locais**, conforme autorizado. **Nenhum push e nenhum deploy.**

---

## 1. Estado antes dos commits

| Repositório | Branch | HEAD | Situação |
|---|---|---|---|
| `infra-abrasil` (principal) | `main` | `20d8833` | `correio.md`, `executed.md` e o ponteiro `gateway/vetoros` modificados |
| `gateway/vetoros` (submódulo) | `main` | `65ca5009` | 84 entradas: 42 modificados e 42 novos, todos código, migrations, testes e telas das etapas INTEL e PROD-IMPORT |

**Conferência do conteúdo:**
- nenhum `.env`, credencial, backup, dump, temporário ou artefato entre os arquivos;
- `.env`, `storage/logs` e `public/build` são ignorados pelo `.gitignore`;
- a worktree temporária usada na validação foi removida.

**Observação:** o `git submodule status` do repositório principal acusa `gateway/abrasilsistemas` sem mapeamento no `.gitmodules`. É preexistente, não foi alterado e não afeta o `gateway/vetoros`.

## 2. Commits realizados

**Submódulo `gateway/vetoros` (branch `main`):**

| Hash | Mensagem | Conteúdo |
|---|---|---|
| `9c69101a` | Implanta o núcleo VETOR-INTEL: trilha da OS, integridade financeira, orçamento versionado e comunicação | VETOR-INTEL-01, 02, 02.1, 03 e homologação: 79 arquivos, +7520/−332 |
| `4fda449a` | Adiciona modelo e importação CSV de produtos (VETOR-PROD-IMPORT-01) | 6 arquivos, +961/−1 |

**Repositório principal `infra-abrasil`:** commit do ponteiro do submódulo (→ `4fda449a`) + `correio.md` + `executed.md`, feito após este relatório. O hash está no fechamento da execução, fora deste arquivo, porque um commit não consegue conter o próprio hash.

**Separação dos commits.** As etapas INTEL-01, 02, 02.1, 03 e a homologação alteraram, em sequência, os **mesmos** arquivos centrais (`OrderController`, `OrderStatusService`, `Order`, `OrderItemSyncService`, `OrderPaymentService`, `edit-order.tsx`…). Um commit por etapa exigiria fabricar versões intermediárias desses arquivos que nunca existiram nem foram testadas. Fiz, portanto, **um commit coerente para o núcleo INTEL** e **um para a importação CSV**, que é independente. `routes/app.php`, que tinha linhas das duas frentes, foi separado no índice sem tocar no arquivo de trabalho: o commit INTEL tem só a rota de taxas e o commit de importação adiciona as rotas de importação.

**Validação de cada commit:**
- `9c69101a` testado **isolado** numa worktree temporária: **492 aprovados, 2710 asserções**, exatamente o total anterior à importação, e `tsc` sem erros;
- `4fda449a` (árvore completa): **503 aprovados, 2805 asserções**, `tsc` sem erros.

**Estado final do submódulo:** HEAD `4fda449a`. As únicas pendências são o **INTEL-04** (seção 4), deixado **sem commit** de propósito porque ainda não foi aprovado.

## 3. Importação CSV

| Item | Situação |
|---|---|
| Download do modelo | ok: testado (BOM, `;`, cabeçalho, exemplo ignorado) |
| Seleção e prévia | ok: testado (contagens, erros por linha, nada gravado) |
| Importação de válidos | ok: testado (peça e produto, valores brasileiros, NCM, tenant) |
| Duplicados | ok: testado (já cadastrados, sem diferenciar maiúsculas, e repetidos no arquivo, ignorados sem alterar o existente) |
| Entrada no estoque | ok: testado (movimento `entrada` com custo e usuário; estoque 0 sem movimento) |
| Exibição de erros | ok: testado no servidor (mensagens por linha); o modal as lista |
| Verificação visual | **não realizada**: não há navegador disponível nesta sessão; recomendo conferir o modal manualmente |

**11 testes** (`PartImportTest`), todos aprovados em cada execução da suíte.

## 4. VETOR-INTEL-04 — Indicadores Operacionais e Comerciais (primeira entrega)

### Auditoria das fontes

| Fonte | Uso no indicador |
|---|---|
| `order_events` (INTEL-01) | OS paradas, produtividade, entregas, renegociações |
| `order_status_history` | referência de OS paradas legadas (sem eventos) |
| `order_technician_assignments` (INTEL-01) | técnico responsável **no instante** do evento |
| `order_budgets` (INTEL-03) | aguardando, vencendo, conversão e tempo de aprovação |
| `original_delivery_forecast`, `delivery_date` (INTEL-02) | cumprimento do prazo prometido |
| `OrderMarginService` (INTEL-02.1/03) | rentabilidade com `known`/`unknown`/`pending` |
| permissão `reports.view` | acesso (a mesma dos relatórios existentes) |

### Regras de cálculo documentadas

Em `gateway/vetoros/docs/architecture/vetor-intel-04-indicadores.md`. Princípios:
- tenant explícito em toda consulta;
- **nada presumido**: o que não tem fonte é contado como desconhecido, nunca vira zero;
- `orders.updated_at` nunca é data de evento;
- período padrão de 30 dias, máximo de 366.

### Implementado

- **`App\Services\Intel\OperationalIndicatorsService`**: dez blocos.

  | Bloco | O que mede |
  |---|---|
  | OS paradas | a partir do último evento de status; legado pelo histórico; sem referência conta como desconhecido |
  | OS atrasadas | inclui quantas tiveram o prazo renegociado |
  | Orçamentos aguardando | quantidade, valor e idade média; legado sem data fica fora da média |
  | Orçamentos vencendo | dentro de N dias |
  | Conversão | por **ciclo de orçamento da OS**, não por versão; legado excluído |
  | Tempo de aprovação | média e mediana, desde o **primeiro envio** do ciclo |
  | Produtividade dos técnicos | concluídas, entregues e retornos em garantia, atribuídos ao técnico **do momento do evento**; sem atribuição registrada vai para `unattributed`, sem imputar ao técnico atual; sem ranking |
  | Cumprimento de prazo | contra o **prazo original**; sem prazo original fica fora e é contada |
  | Rentabilidade real | soma margem **só de OS completas**; incompletas contadas por componente e status |
  | Qualidade dos dados | resumo dos desconhecidos e pendentes |

- **`GET /app/intel/indicators`** (`app.intel.indicators`, `IntelIndicatorController`, `reports.view`, throttle 60/min): JSON, parâmetros `from`, `to`, `stalled_days` e `expiring_days`, com validação.
- **Interface:** não criada nesta entrega (incremental). A API está pronta para a tela do próximo passo.

### Testes

**`OperationalIndicatorsTest`** (9 testes, 49 asserções), com cenários datados (relógio fixo):

| Teste | Verifica |
|---|---|
| OS paradas | ordem por dias, origem da referência (eventos × histórico) e desconhecidas |
| OS atrasadas | renegociação |
| Orçamentos | aguardando (valor, idade média, idade desconhecida) e vencendo |
| Conversão | ciclos × versões (aprovada após renegociação = 54 h; taxa 33,3%; 1,25 versão por ciclo; legado e fora do período excluídos) |
| Produtividade | técnico do momento (Ana concluiu, Bruno entregou), retorno em garantia e evento sem atribuição |
| Prazo | no prazo, atrasada (4 dias), sem prazo original, renegociada |
| Rentabilidade | soma só da OS completa (margem R$ 215,00); incompleta por componente |
| Endpoint | estrutura, período e **isolamento por tenant** |
| Endpoint | permissão (técnico: 403) e validação do período |

## 5. Testes executados

| Execução | Resultado | Tempo |
|---|---|---|
| Antes dos commits (árvore completa) | 503 aprovados, 2805 asserções | ~15 s |
| Commit `9c69101a` isolado (worktree) | 492 aprovados, 2710 asserções (×2) | — |
| Final com INTEL-04 — 1 | **512 aprovados, 0 falhas, 2854 asserções** | 15 s |
| Final com INTEL-04 — 2 | **512 aprovados, 0 falhas, 2854 asserções** | 13 s |
| Final com INTEL-04 — 3 | **512 aprovados, 0 falhas, 2854 asserções** | 17 s |

**Qualidade:**
- `npx tsc --noEmit`: sem erros;
- Pint: aprovado no serviço, no controller e no teste do INTEL-04; nenhuma diferença nas linhas novas de `routes/app.php`.

## 6. Pendências e riscos

1. **INTEL-04 sem commit:** aguardando sua aprovação (serviço, controller, rota, documento e teste).
2. **Sem push nem deploy:** os commits existem só localmente. As **quatro migrations INTEL** (`2026_10_07` a `2026_10_10`) precisarão rodar em produção no deploy, e o ponto da `APP_KEY` (seção 0 do INTEL-03) deve ser conferido antes.
3. **Homologações externas pendentes** (do INTEL-03): WAHA (envio, ACK e READ reais; webhook e segredo vazios no `.env` local) e NFS-e no sandbox da Spedy.
4. **Telas sem conferência visual:** importação CSV, abas Orçamento e Comunicação, configuração de taxas e custo avulso.
5. **Indicadores dependem de histórico novo:** OS anteriores à trilha de eventos e às versões de orçamento aparecem como desconhecidas, por definição. Os números ficam mais completos com o tempo.
6. **Desempenho:** a produtividade consulta a atribuição por evento e a rentabilidade calcula a margem por OS. Adequado para períodos de até um ano em volumes de assistência técnica; para volumes grandes, avaliar cache ou consulta agregada.
7. **`gateway/abrasilsistemas`** sem mapeamento no `.gitmodules` do repositório principal (preexistente).

## 7. Próxima etapa recomendada

1. **Revisar e aprovar** a primeira entrega do INTEL-04 e o documento de regras. Depois, commitar.
2. **INTEL-04, segunda entrega:** tela "Indicadores" consumindo `GET /app/intel/indicators`, com destaque visual dos números incompletos (qualidade dos dados), e substituição dos cards antigos do dashboard que ainda contam status por regras simples.
3. Em paralelo, a **homologação externa** (WAHA e Spedy sandbox) e o **deploy assistido** das migrations, quando autorizados.
