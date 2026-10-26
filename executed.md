# ABRASIL-AUTH-UX-03 — Executado

**Data:** 25/10/2026  
**Origem:** `correio.md` da raiz, alterado em relação à versão anterior (`DESGARRADOS-NEXT-29`).

Os logins de VetorSales, VetorLeads e VetorOS agora usam a mesma composição: imagem própria em tela cheia, overlay suave e card claro centralizado com medidas e estilos idênticos. Os cadastros seguem a mesma família visual, com largura de 896 px no desktop e rolagem natural no mobile. Campos, links, consentimentos e regras de cada aplicação foram preservados.

**Validação:** seis telas reais inspecionadas em desktop e mobile; 15 capturas PNG; nenhum overflow horizontal ou erro JavaScript. Login válido, login inválido, alternância de senha, recuperação e links auxiliares verificados. Testes Laravel passaram (11 VetorSales, 13 VetorLeads, 11 VetorOS); TypeScript, builds Vite e `git diff --check` passaram nos três projetos.

**Commits e pushes:** `origin/abrasil-auth-ux-03` enviado em VetorSales (`25e8f7c`), VetorLeads (`41b30e1`) e VetorOS (`d891ed0e`).

**Relatório e evidências:** [docs/ABRASIL-AUTH-UX-03.md](docs/ABRASIL-AUTH-UX-03.md).
