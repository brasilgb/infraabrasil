# ABRASIL-AUTH-UX-01 — Padronização de Login e Cadastro (VetorLeads, VetorSales e VetorOS). Resultado: **IMPLEMENTADO E VALIDADO EM BUILD; NÃO PUBLICADO**

**Data:** 2026-10-25
**Origem:** `correio.md` (raiz do `infra-abrasil`), diferente do correio anterior (DESGARRADOS-NEXT-29), que foi substituído.

## 1. Alterações por projeto

Todas as alterações estão na árvore de trabalho. Nada foi commitado, enviado ou publicado.

### VetorLeads (`gateway/vetorleads`)

- `resources/js/layouts/auth/auth-simple-layout.tsx`: layout dividido (desktop: painel de marca com `public/images/vetorleads-hero-green-1280.webp` + formulário; mobile: formulário com imagem oculta).
- `docs/ABRASIL-AUTH-UX-01.md`: registro da entrega.

### VetorSales (`gateway/vetorsales`)

- `resources/js/layouts/auth/auth-simple-layout.tsx`: mesmo padrão, com `public/images/vetorsales-commercial-hero.webp` e a mensagem "Sua operação comercial, sempre em movimento."
- `resources/js/pages/auth/register.tsx`: removido o wrapper com `max-h-[72svh]` e rolagem interna. Havia alterações não commitadas nesse arquivo antes desta tarefa (`commercial_segment: 'other_b2b'`); foram mantidas.
- `docs/ABRASIL-AUTH-UX-01.md`: registro da entrega.

### VetorOS (`gateway/vetoros`)

- `resources/js/layouts/auth/auth-simple-layout.tsx`: mesmo padrão, com `public/images/vetoros-hero-maintenance.png` e a mensagem "Sua assistência técnica mais organizada e produtiva.". `FlashToastMessages` e `Toaster` mantidos.
- `resources/js/pages/auth/register.tsx`: removido o wrapper com `max-h-[72svh]` e rolagem interna. Largura de referência `max-w-4xl` mantida.
- `docs/ABRASIL-AUTH-UX-01.md`: registro da entrega.

Não foram alterados: autenticação, sessões, validações, recuperação de senha, multitenant, banco, pagamentos, integrações, landing pages e logos.

## 2. Validação

| Projeto    | `tsc --noEmit` | `vite build` | `eslint` (arquivos alterados)                       |
|------------|----------------|--------------|-----------------------------------------------------|
| VetorLeads | 0 erros        | OK           | OK (após `--fix` de ordem de imports)               |
| VetorSales | 0 erros        | OK           | Layout OK; erro pré-existente `as any` em `login.tsx` (fora do escopo) |
| VetorOS    | 0 erros        | OK           | OK                                                  |

Não executado: testes de navegador, screenshots antes/depois e testes de responsividade em 360–1440 px. Também não foram executados testes PHP, porque nenhum arquivo de backend foi alterado.

## 3. Efeitos colaterais tratados

- O `vite build` apagou arquivos versionados em `public/build` nos projetos VetorLeads e VetorSales. Os arquivos foram restaurados com `git checkout -- public/build`, e os artefatos novos foram removidos com `git clean -f -- public/build`. Depois disso, `public/build` está igual ao commit. O VetorOS não teve alteração em `public/build`.
- Uma tentativa de remoção com variável de shell foi bloqueada pela verificação de segurança e não foi executada. A limpeza acima foi feita com comandos de caminho literal.

## 4. Pendências

- Evidências visuais (antes/depois) e testes de responsividade e de fluxo de login/cadastro/recuperação no navegador.
- Commit, push e deploy: não executados. O correio pede que nada seja publicado sem validação da interface e dos fluxos.
- Os repositórios VetorLeads e VetorSales têm alterações não commitadas de outras frentes (por exemplo, `EfiSettingsController` e `efi.tsx` no VetorOS). Elas não foram tocadas.
