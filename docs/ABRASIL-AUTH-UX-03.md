# ABRASIL-AUTH-UX-03 — Login e cadastro do ecossistema

**Data:** 25/10/2026  
**Resultado:** implementado, verificado visualmente e publicado em branches Git.

## Diagnóstico

As três aplicações usavam `auth-simple-layout`, mas apresentavam uma composição dividida: imagem em um painel lateral no desktop e formulário em outra área. No mobile, a imagem desaparecia. O login do VetorSales ainda tinha estilos verdes próprios em campos e botão; VetorLeads e VetorOS herdavam tamanhos e raios diferentes dos componentes globais. O VetorLeads usava `app.tsx` para aplicar o layout de autenticação e precisava receber a largura larga do cadastro por `Register.layout`.

## Implementação

Foi adotada a mesma estrutura nas três aplicações: imagem cobrindo a viewport, overlay suave, card claro centralizado, logo no topo, mensagem de marca, título, descrição e formulário. O CSS de composição e os tokens da superfície de autenticação são idênticos nos três projetos. Os logins têm card de 448 px no desktop e 358 px em viewport de 390 px. O cadastro usa card de 896 px no desktop e largura fluida no mobile. Os formulários, consentimentos e ações próprios de cada produto foram preservados.

| Projeto | Imagem de fundo | Arquivos alterados | Commit |
| --- | --- | --- | --- |
| VetorSales | `public/images/vetorsales-commercial-hero.webp` (equipe comercial) | `resources/css/app.css`, `resources/js/layouts/auth/auth-simple-layout.tsx`, `resources/js/pages/auth/login.tsx` | `25e8f7c` |
| VetorLeads | `public/images/vetorleads-hero-green-1280.webp` (profissional em prospecção) | `resources/css/app.css`, `resources/js/layouts/auth-layout.tsx`, `resources/js/layouts/auth/auth-simple-layout.tsx`, `resources/js/pages/auth/register.tsx`, `resources/js/components/password-input.tsx` | `41b30e1` |
| VetorOS | `public/images/vetoros-hero-maintenance.png` (bancada de manutenção) | `resources/css/app.css`, `resources/js/layouts/auth/auth-simple-layout.tsx`, `resources/js/pages/auth/login.tsx` | `d891ed0e` |

## Evidências visuais reais

Capturas feitas com Chromium nas rotas HTTP reais das aplicações, com Vite servindo o código atualizado e SQLite temporário em `/tmp`. O código foi inspecionado em 1440 × 900 e 390 × 844. Não houve erros JavaScript nem overflow horizontal nas 12 telas.

| Projeto | Login desktop | Login mobile | Cadastro desktop | Cadastro mobile | Erro de login mobile |
| --- | --- | --- | --- | --- | --- |
| VetorSales | [PNG](evidencias-auth-ux-03/vetorsales-login-desktop.png) | [PNG](evidencias-auth-ux-03/vetorsales-login-mobile.png) | [PNG](evidencias-auth-ux-03/vetorsales-register-desktop.png) | [PNG](evidencias-auth-ux-03/vetorsales-register-mobile.png) | [PNG](evidencias-auth-ux-03/vetorsales-login-error-mobile.png) |
| VetorLeads | [PNG](evidencias-auth-ux-03/vetorleads-login-desktop.png) | [PNG](evidencias-auth-ux-03/vetorleads-login-mobile.png) | [PNG](evidencias-auth-ux-03/vetorleads-register-desktop.png) | [PNG](evidencias-auth-ux-03/vetorleads-register-mobile.png) | [PNG](evidencias-auth-ux-03/vetorleads-login-error-mobile.png) |
| VetorOS | [PNG](evidencias-auth-ux-03/vetoros-login-desktop.png) | [PNG](evidencias-auth-ux-03/vetoros-login-mobile.png) | [PNG](evidencias-auth-ux-03/vetoros-register-desktop.png) | [PNG](evidencias-auth-ux-03/vetoros-register-mobile.png) | [PNG](evidencias-auth-ux-03/vetoros-login-error-mobile.png) |

O card de cadastro do VetorSales cabe integralmente em 1440 × 900 (`scrollHeight` da página: 900). VetorLeads e VetorOS também cabem sem rolagem nessa resolução. Em 390 × 844, os três cadastros usam rolagem vertical natural.

## Validação funcional e técnica

- Login válido no navegador: VetorSales → `/admin`, VetorLeads → `/dashboard`, VetorOS → `/app`. Usuários de teste criados apenas nos bancos SQLite temporários.
- Login inválido: mensagem de credenciais exibida nos três produtos, sem erro JavaScript.
- Mostrar/ocultar senha: alternância `password` ↔ `text` nos três logins; botões acessíveis por nome, inclusive via teclado.
- Links de recuperação de senha e cadastro: presentes e apontando para as rotas de cada produto.
- Testes Laravel de autenticação, cadastro e recuperação: VetorSales 11 testes/32 asserções; VetorLeads 13 testes/29 asserções; VetorOS 11 testes/25 asserções. Todos passaram. A primeira execução do VetorSales falhou pela proteção da suíte que exige banco com nome terminado em `_testing`; a repetição com SQLite temporário `/tmp/abrasil_sales_testing` passou.
- TypeScript (`tsc --noEmit`), build Vite em `/tmp` e `git diff --check`: passaram nos três projetos. `public/build` não foi modificado.

## Branches e pushes

Os commits acima foram enviados para `origin/abrasil-auth-ux-03` de cada repositório: VetorSales, VetorLeads e VetorOS. Alterações preexistentes e não relacionadas foram deixadas fora dos commits.

## Pendências

Nenhuma pendência de implementação ou validação identificada. As branches foram enviadas ao remoto; merge e deploy não fazem parte desta execução.
