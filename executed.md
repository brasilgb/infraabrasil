# Execução de `correio.md`: VETOR-INTEL-04.2 — Aprovação da segunda entrega e preparação para homologação (COMMITS LOCAIS; SEM PUSH, SEM DEPLOY, SEM MIGRATIONS EM PRODUÇÃO)

- **Data:** 2026-10-08 (America/Sao_Paulo).
- **SHA-256 do `correio.md` executado:** `7e85fb3db5f0305c5d66d01724de8cbe33b3636a5b96f4651a7bf7829330bf54`.
- **SHA-256 da execução anterior:** `592ab25e5561d43105bec7b1f76c220fa32429a24756fe26e0327cc5c61aa1b5` (VETOR-INTEL-04.1). O arquivo mudou, então foi executado.
- **Restrições:** nenhum push, deploy ou migration. Nenhuma regra aprovada foi alterada (seção 4 do correio). Nenhum recurso novo implementado. O VETOR-INTEL-05 não foi iniciado.

Caminhos relativos a `gateway/vetoros/`.

---

## 1. Commit da segunda entrega

| Hash | Mensagem |
|---|---|
| **`8f46d37d`** | Finaliza painel de indicadores do VETOR-INTEL-04 |

Arquivos commitados: **exatamente os 9 da lista do correio** (`git diff --cached --name-only` conferido antes do commit):

- `app/Http/Controllers/App/DashboardController.php`
- `app/Http/Controllers/App/IntelIndicatorController.php`
- `app/Services/Intel/OperationalIndicatorsService.php`
- `resources/js/pages/app/intel/indicators.tsx`
- `resources/js/pages/app/dashboard/ope-order/index.tsx`
- `resources/js/Utils/navLinks.ts`
- `tests/Feature/App/IntelIndicatorsPageTest.php`
- `tests/Feature/App/OperationalIndicatorsTest.php`
- `docs/architecture/vetor-intel-04-indicadores.md`

**Ficaram fora do commit**, por não pertencerem à entrega: a alteração do **rodapé** pedida em conversa, ainda **sem commit** e aguardando instrução:
- `resources/js/components/app-footer.tsx`: "abrasilsistemas.com.br - VetorOs" + versão;
- `.env.example`: `VITE_APP_VERSION="v2.0.0"`.

O `.env` local também foi ajustado para `v2.0.0`; ele é ignorado pelo Git.

## 2. Validação após o commit

| Verificação | Resultado |
|---|---|
| Suíte PHP completa (`php artisan test --parallel`) | **521 aprovados, 0 falhas, 2918 asserções** (~13 s) |
| `npx tsc --noEmit` | **sem erros** |
| Pint (5 PHP da entrega) | **aprovado** em todos |
| ESLint | `indicators.tsx` e `navLinks.ts` limpos; `dashboard/ope-order/index.tsx` com 3 problemas **preexistentes** (iguais ao HEAD anterior à entrega) |
| Prettier | `indicators.tsx` e `dashboard/ope-order/index.tsx` ok; `navLinks.ts` com a diferença **preexistente** do import `BadgePercent` (documentada no INTEL-04.1; não corrigida, conforme o correio) |

## 3. Estado do Git

**Submódulo `gateway/vetoros` (main):**

```
8f46d37d Finaliza painel de indicadores do VETOR-INTEL-04
84355362 Implementa indicadores operacionais e comerciais do VETOR-INTEL-04
4fda449a Adiciona modelo e importação CSV de produtos (VETOR-PROD-IMPORT-01)
9c69101a Implanta o núcleo VETOR-INTEL: trilha da OS, integridade financeira, orçamento versionado e comunicação
65ca5009 Centraliza a administração fiscal no RootAdmin e emite notas do SaaS (FISCAL-SPEDY-04)
```

Pendentes, fora do escopo: `.env.example` e `resources/js/components/app-footer.tsx` (rodapé).

**Repositório principal `infra-abrasil`:** commit local com o ponteiro de `gateway/vetoros` → `8f46d37d`, mais `correio.md` e `executed.md`. Nada além disso entra. O hash é informado no fechamento da execução, porque um commit não pode conter o próprio hash.

## 4. Regras aprovadas preservadas

Conferidas no código commitado e cobertas pelos testes:
- OS atrasada avaliada contra o **prazo original** (`past_original`), com a renegociação como contexto;
- técnico histórico **somente com atribuição registrada** (`order_technician_assignments`), nunca o técnico atual retroativo;
- produtividade sem ranking;
- rentabilidade só de OS completas;
- valores `null` exibidos como "—", nunca como zero;
- isolamento por tenant;
- acesso por `reports.view`;
- dashboard no escopo individual do usuário;
- WAHA e Spedy não tocados.

## 5. Roteiro de homologação manual — `/app/intel/indicators`

Pré-requisitos:
- usuário **administrador** (ou com a permissão `reports`) e um **técnico**, ambos do mesmo tenant;
- frontend recompilado (`npm run build`).

### Desktop

1. **Menu:** "Geral → Indicadores" aparece para o administrador. **Não** aparece para o técnico, e o acesso direto pela URL é negado.
2. **Filtros:**
   - abrir a página (período padrão de 30 dias);
   - alterar as datas e os limites e conferir que **nada recarrega até clicar em "Aplicar"**;
   - testar cada atalho (Hoje, 7 dias, 30 dias, 90 dias, Este mês, Mês anterior), que aplica na hora;
   - testar um período acima de 366 dias ou com a data final antes da inicial: o botão fica bloqueado com aviso.
3. **Resumo:**
   - 7 cards legíveis, com valor grande e contexto abaixo;
   - avisos em âmbar quando houver registros fora do cálculo;
   - um card sem dados mostra "—", não "0";
   - clicar num card rola até a seção correspondente.
4. **Qualidade dos dados:** a lista bate com os avisos dos cards; sem pendências, aparece a mensagem verde.
5. **Tabelas:** OS paradas (ordem decrescente de dias, referência "trilha de eventos" ou "histórico legado") e produtividade (com a linha "Sem técnico atribuído no momento" quando houver).
6. **Links:** o número da OS e os orçamentos vencendo abrem a OS correta.
7. **Estados:**
   - **carregando:** spinner e "Carregando indicadores..." na primeira carga; opacidade reduzida ao reaplicar;
   - **erro:** com o servidor indisponível ou filtro inválido via URL, aparece a mensagem em vermelho;
   - **vazio:** num tenant sem dados, as seções mostram textos de vazio e não quebram.
8. **Legibilidade:** valores em R$ no formato brasileiro, percentuais com vírgula, datas em DD/MM/AAAA e textos sem truncar.

### Mobile (≈ 375 px, e também tablet)

1. **Cards:** uma coluna, largura total, sem corte de valores longos (ex.: R$ 1.234.567,89).
2. **Tabelas:** rolagem horizontal dentro da caixa, sem empurrar a página para os lados.
3. **Filtros:** campos empilhados, atalhos quebrando linha e botão "Aplicar" na largura total.
4. **Textos e botões:** nada sobreposto ou cortado; área de toque adequada.
5. **Overflow:** a página inteira sem rolagem horizontal.

### Dados reais (conferir manualmente algumas OS conhecidas)

| Caso | Como provocar ou localizar | Esperado na tela |
|---|---|---|
| OS parada | OS ativa sem mudança de status há mais de 7 dias | aparece em "OS paradas" com os dias corretos |
| OS atrasada | OS ativa com prazo original vencido | conta em "OS atrasadas (prazo original)" |
| Prazo renegociado | alterar a previsão de uma OS atrasada para o futuro | **continua atrasada** pelo original; conta em "renegociado" |
| Orçamento aguardando | OS em "Orçamento Gerado" com validade futura | entra em "Orçamentos aguardando" e no card do dashboard |
| Orçamento vencido | validade no passado | sai de "aguardando"; dashboard mostra "Orçamento vencido" |
| Técnico atribuído | OS com técnico definido depois da implantação do INTEL-01 | nome do técnico em "OS paradas" |
| OS sem trilha histórica | OS antiga, anterior à trilha de eventos | referência "histórico legado" e técnico "sem atribuição registrada" |
| Rentabilidade completa | OS entregue, paga com taxa conhecida e custos conhecidos | entra na margem conhecida |
| Rentabilidade incompleta | OS entregue com custo avulso em branco ou taxa não informada | fica fora da soma e aparece no quadro "custos incompletos" |
| Conversão | um orçamento aprovado e um recusado no período | taxa de 50%; pendentes fora da taxa |

Para cada divergência, registrar o número da OS, o valor esperado, o valor exibido e um print.

## 6. Pendências

1. **Homologação manual** da tela, conforme o roteiro acima. Não feita nesta sessão: sem navegador disponível.
2. **Rodapé** (`app-footer.tsx` e `.env.example`): aguarda decisão de commit. Os outros ambientes precisam de `VITE_APP_VERSION="v2.0.0"` no `.env` e de um novo build.
3. **Anteriores, inalteradas:**
   - homologação WAHA (webhook e segredo) e Spedy sandbox;
   - conferência visual das telas INTEL-02.1, INTEL-03 e importação CSV;
   - deploy assistido das migrations INTEL, quando autorizado.
4. **Próxima fase:** **não** iniciada. Aguardo a revisão deste `executed.md` e o resultado da homologação para abrir o VETOR-INTEL-05.
