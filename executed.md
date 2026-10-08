# Execução de `correio.md`: VETOR-INTEL-04.3 — Encerramento e preparação para homologação (COMMITS LOCAIS; SEM PUSH, SEM DEPLOY, SEM MIGRATIONS EM PRODUÇÃO)

- **Data:** 2026-10-08 (America/Sao_Paulo).
- **SHA-256 do `correio.md` executado:** `f1dea701665941a1091fd717328f9a2a2b6ea1c86be3674242ee9b5b2799caa3`.
- **SHA-256 da execução anterior:** `7e85fb3db5f0305c5d66d01724de8cbe33b3636a5b96f4651a7bf7829330bf54` (VETOR-INTEL-04.2). O arquivo mudou, então foi executado.
- **Restrições respeitadas:**
  - o VETOR-INTEL-05 não foi iniciado;
  - nenhum push, deploy ou migration;
  - WAHA e Spedy não foram tocados;
  - nenhuma regra comercial, fiscal ou operacional foi alterada.

Caminhos relativos a `gateway/vetoros/`.

---

## 1. Encerramento do Git

**Situação encontrada antes desta etapa:**

| Verificação | Resultado |
|---|---|
| Commit `8f46d37d` no VetorOS | ✔ presente: "Finaliza painel de indicadores do VETOR-INTEL-04" |
| Commit do repositório principal | ✔ `3b07265`: "Atualiza ponteiro do VetorOS (painel de indicadores do VETOR-INTEL-04) e registra a execução" |
| Ponteiro do submódulo no principal | ✔ `git ls-tree HEAD gateway/vetoros` = `8f46d37daa0a8bc3d2d5c24b8ed2053678b3f820` |

**Commits desta etapa:**

| Repositório | Hash | Mensagem |
|---|---|---|
| `gateway/vetoros` | **`7e9e005e`** | Atualiza rodapé para ABrasil Sistemas — VetorOS e centraliza a versão em v2.0.0 |
| `infra-abrasil` | (informado no fechamento) | ponteiro de `gateway/vetoros` → `7e9e005e`, mais `correio.md` e `executed.md` |

O hash do commit do repositório principal não está aqui porque um commit não consegue conter o próprio hash.

## 2. Rodapé e versão

**Rodapé (todas as telas internas):** **ABrasil Sistemas — VetorOS | v2.0.0**. "ABrasil Sistemas" é o link para `https://abrasilsistemas.com.br` (nova aba, `rel="noreferrer"`).

**Centralização da versão** (verificada e implementada):

| Antes | Depois |
|---|---|
| A versão vinha do `VITE_APP_VERSION` de **cada** `.env`; produção e staging precisavam ser atualizados à mão | **Fonte única:** `"version"` do `package.json` (agora `2.0.0`), injetado pelo Vite na compilação como `__APP_VERSION__` (com prefixo `v`) |

- **Compatibilidade:**
  - ambientes que ainda definem `VITE_APP_VERSION` continuam funcionando; a variável só deixa de ser usada;
  - o `.env.example` explica a mudança;
  - o build SSR usa a mesma configuração.
- **Próximas versões:** basta alterar `"version"` no `package.json` (ou usar `npm version`) e recompilar.

**Comprovação:**
- `npx vite build` concluído;
- o bundle compilado contém `children:"ABrasil Sistemas"`, `" — VetorOS | "` e `children:"v2.0.0"`;
- `tsc` sem erros, com o tipo de `__APP_VERSION__` declarado em `global.d.ts`.

### Arquivos do commit `7e9e005e`

| Arquivo | Mudança |
|---|---|
| `resources/js/components/app-footer.tsx` | Novo texto e link do rodapé; usa `__APP_VERSION__` |
| `.env.example` | `VITE_APP_VERSION` substituído por um comentário explicando a fonte única |
| `package.json` | `"version": "2.0.0"` |
| `package-lock.json` | Somente a versão do próprio projeto (2 linhas); nenhuma dependência alterada |
| `vite.config.ts` | Lê o `package.json` e define `__APP_VERSION__` |
| `resources/js/types/global.d.ts` | Declaração de tipo de `__APP_VERSION__` |

`app-footer.tsx` e `.env.example` eram os arquivos autorizados. Os outros quatro são **necessários para a centralização**, que o mesmo item do correio pede para verificar; todos estão no mesmo commit independente. O `.env` local (ignorado pelo Git) ainda tem `VITE_APP_VERSION="v2.0.0"`, o que é inofensivo.

## 3. Testes executados

| Verificação | Resultado |
|---|---|
| Suíte PHP completa (antes do commit) | **521 aprovados, 0 falhas, 2918 asserções** |
| Testes de indicadores e dashboard (`IntelIndicatorsPageTest`, `OperationalIndicatorsTest`, `DashboardControllerTest`) | **36 aprovados, 203 asserções** |
| `npx tsc --noEmit` (antes e depois do commit) | sem erros |
| `npx vite build` | concluído; versão injetada comprovada no bundle |
| ESLint (`app-footer.tsx`, `vite.config.ts`) | sem problemas |
| Prettier | `app-footer.tsx` e `global.d.ts` ok. `vite.config.ts` tem diferenças **preexistentes** (vírgulas finais no bloco `build`/`server`, já presentes no HEAD), não corrigidas por estarem fora do escopo |

## 4. Homologação

O **roteiro do INTEL-04.2 foi preservado** e segue abaixo, ampliado com tablet, rodapé e dashboard.

**O que já está comprovado por testes automatizados:**
- permissão (`reports.view`; técnico bloqueado na tela e na API);
- filtros (período, limites, validação de 366 dias);
- dataset vazio e parcial (não calculável ≠ zero);
- isolamento por tenant;
- contexto das OS paradas sem técnico retroativo;
- concordância entre o dashboard e os indicadores ("Aguardando aprovação" e "Orçamento vencido").

**Não comprovado:** a parte **visual e de uso real** (desktop, tablet e celular). Não há navegador nesta sessão.

**Divergências encontradas:** **nenhuma** nas verificações automatizadas desta etapa. Qualquer divergência da homologação manual deve ser registrada antes de qualquer correção, como pede o correio.

### Roteiro de homologação manual — `/app/intel/indicators`

Pré-requisitos:
- usuário **administrador** (ou com a permissão `reports`) e um **técnico**, ambos do mesmo tenant;
- frontend recompilado (`npm run build`).

**Desktop**
1. **Menu:** "Geral → Indicadores" visível para o administrador. Para o técnico, invisível, e a URL direta é negada.
2. **Filtros:**
   - abre com os últimos 30 dias;
   - mudar campos **não** recarrega até clicar em "Aplicar";
   - cada atalho (Hoje, 7, 30 e 90 dias, Este mês, Mês anterior) aplica na hora;
   - período acima de 366 dias ou invertido bloqueia o botão com aviso.
3. **Resumo:** 7 cards com valor grande e contexto; avisos em âmbar quando houver dado fora do cálculo; sem dados aparece "—" (nunca "0"); o card leva à seção.
4. **Qualidade dos dados:** coerente com os avisos; sem pendências, mensagem verde.
5. **Tabelas:**
   - OS paradas: das mais antigas para as mais recentes, com referência "trilha de eventos" ou "histórico legado";
   - produtividade: com a linha "Sem técnico atribuído no momento" quando houver.
6. **Links:** o número da OS e os orçamentos vencendo abrem a OS correta.
7. **Estados:** carregando (spinner; opacidade ao reaplicar), erro (mensagem em vermelho) e vazio por seção.
8. **Legibilidade:** R$ e percentuais no formato brasileiro, datas DD/MM/AAAA, textos sem truncar.
9. **Rodapé:** "ABrasil Sistemas — VetorOS | v2.0.0"; o link abre `abrasilsistemas.com.br` em nova aba.

**Tablet (≈ 768 px)**
1. Cards em duas colunas, filtros em grade, tabelas com rolagem própria.
2. Menu lateral recolhível sem sobrepor o conteúdo.

**Celular (≈ 375 px)**
1. Cards em uma coluna, sem cortar valores longos (ex.: R$ 1.234.567,89).
2. Tabelas com rolagem horizontal dentro da caixa, sem rolagem horizontal da página.
3. Filtros empilhados; atalhos quebram linha; botão "Aplicar" na largura total.
4. Textos e botões sem sobreposição; área de toque adequada.
5. Rodapé centralizado, em até duas linhas, sem corte.

**Dashboard (comportamento)**
1. "Aguardando aprovação" **não** conta OS cujo orçamento corrente venceu.
2. "Orçamento vencido" mostra essas OS e leva à lista de OS em "Orçamento Gerado".
3. "Prazo vencido" igual ao total no prazo vigente dos Indicadores.
4. Como técnico: os números refletem **apenas as OS dele** (escopo individual preservado).

**Dados reais — conferir com OS conhecidas**

| Caso | Como provocar ou localizar | Esperado |
|---|---|---|
| OS parada | OS ativa sem mudança de status há mais de 7 dias | em "OS paradas" com os dias corretos |
| OS atrasada | OS ativa com prazo original vencido | em "OS atrasadas (prazo original)" |
| Prazo renegociado | adiar a previsão de uma OS atrasada | **continua atrasada** pelo original; conta como renegociada |
| Orçamento aguardando | "Orçamento Gerado" com validade futura | em "Orçamentos aguardando" e no card do dashboard |
| Orçamento vencido | validade no passado | sai de "aguardando"; dashboard mostra "Orçamento vencido" |
| Técnico atribuído | técnico definido depois da implantação do INTEL-01 | nome em "OS paradas" |
| OS sem trilha histórica | OS anterior à trilha de eventos | "histórico legado" e "sem atribuição registrada" |
| Rentabilidade completa | entregue, paga com taxa conhecida, custos conhecidos | entra na margem conhecida |
| Rentabilidade incompleta | custo avulso em branco ou taxa não informada | fora da soma; listada em "custos incompletos" |
| Conversão | um orçamento aprovado e um recusado no período | 50%; pendentes fora da taxa |

Para cada divergência, registrar o número da OS, o valor esperado, o valor exibido, o dispositivo e um print.

## 5. Estado do Git

**Submódulo `gateway/vetoros` (main):** árvore **limpa**.

```
7e9e005e Atualiza rodapé para ABrasil Sistemas — VetorOS e centraliza a versão em v2.0.0
8f46d37d Finaliza painel de indicadores do VETOR-INTEL-04
84355362 Implementa indicadores operacionais e comerciais do VETOR-INTEL-04
4fda449a Adiciona modelo e importação CSV de produtos (VETOR-PROD-IMPORT-01)
9c69101a Implanta o núcleo VETOR-INTEL: trilha da OS, integridade financeira, orçamento versionado e comunicação
```

**Repositório principal `infra-abrasil`:** commit local com o ponteiro → `7e9e005e`, `correio.md` e `executed.md`.

**Nada foi enviado ao remoto.**

## 6. Pendências de homologação

1. **Homologação visual e de uso** da tela Indicadores, do dashboard e do rodapé em desktop, tablet e celular, conforme o roteiro.
2. **Deploy:** quando autorizado, rodar as migrations INTEL e recompilar o frontend; a versão v2.0.0 vem do build, sem ajuste de `.env`. Antes, conferir a `APP_KEY` do ambiente.
3. **Pendências anteriores:**
   - homologação WAHA (webhook e segredo) e Spedy sandbox;
   - conferência visual das telas INTEL-02.1, INTEL-03 e da importação CSV.
4. **VETOR-INTEL-05:** não iniciado; aguarda a avaliação desta entrega e da homologação.
