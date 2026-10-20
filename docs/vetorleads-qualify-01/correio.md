# VETORLEADS-QUALIFY-01 — Qualificação inteligente de oportunidades B2B

## Objetivo

Evoluir o VetorLeads de uma plataforma de captura e validação empresarial para uma plataforma que identifica, classifica e prepara oportunidades comerciais reais.

## Diretrizes

1. Auditar os módulos existentes de prospecção, validação empresarial, CRM e automações antes de implementar.
2. Reaproveitar integralmente o VETORLEADS-VALIDATE-01, evitando funcionalidades duplicadas.
3. Criar pontuação comercial independente das pontuações de identidade e atividade.
4. Permitir definir perfil de cliente ideal (ICP) por tenant, considerando segmento, região, porte e outros critérios relevantes.
5. Classificar prospects conforme aderência ao ICP, qualidade dos contatos, evidências disponíveis e potencial comercial.
6. Não interpretar automaticamente presença em redes sociais, atividade cadastral ou popularidade como intenção de compra.
7. Identificar canais de contato públicos disponíveis e sua origem, respeitando LGPD, permissões e regras dos provedores.
8. Gerar sugestões de abordagem comercial específicas para o segmento, sem enviar mensagens automaticamente.
9. Priorizar empresas com maior potencial comercial e apresentar justificativas compreensíveis ao vendedor.
10. Criar filtros e indicadores no painel de prospecção, mantendo captura, validação, qualificação e CRM claramente organizados.

## Fluxo esperado

Captura → Deduplicação → Validação empresarial → Qualificação comercial → Revisão → CRM → Atendimento → Negociação → Venda.

## Interface

Priorizar a reutilização das telas existentes. Utilizar modais, abas e componentes compactos para evitar informações amontoadas.

## Regras técnicas

- Laravel e React/Inertia existentes.
- Isolamento rigoroso entre tenants.
- Consultas externas controladas por cache, orçamento e limites.
- Nenhuma API paga habilitada automaticamente.
- Nenhum disparo de WhatsApp, e-mail ou Instagram sem autorização e fluxo operacional apropriado.
- Preservar contatos, histórico e integrações existentes.
- Criar testes automatizados e validar o fluxo em ambiente isolado.

## Entrega

Implementar a evolução funcional possível, executar testes e documentar evidências e pendências no `executed.md`.

Não realizar publicação em produção sem autorização.