# VETORLEADS-PROSPECT — Análise e qualificação automática por lote

Implementar no VetorLeads uma evolução do módulo de prospecção para permitir a análise, normalização, enriquecimento e classificação de todos os prospects capturados pelo Google Maps.

## Objetivo

Eliminar a necessidade de abrir cada prospect e executar individualmente o botão "Verificar site/CNPJ".

Adicionar à página de resultados da captura o botão **"Analisar prospects"**, que executará o processamento completo do lote selecionado.

## Requisitos

1. Auditar e reutilizar os serviços já existentes de verificação de site, CNPJ, enriquecimento e score. Evitar duplicação de lógica.
2. Normalizar razão social/nome fantasia, telefones, URLs, endereços e categorias, preservando os valores originais.
3. Identificar empresas duplicadas dentro do lote e em outros registros do mesmo tenant, sem mesclar automaticamente casos ambíguos.
4. Verificar sites existentes, identificar contatos comerciais publicados e localizar referências a CNPJ e redes sociais.
5. Consultar a BrasilAPI somente quando houver CNPJ válido identificado, utilizando cache para evitar consultas repetidas.
6. Adicionar verificação de Instagram empresarial, priorizando links identificados no site e fontes autorizadas. Não implementar scraping que contorne bloqueios, autenticação ou limites da plataforma.
7. Cruzar nome, endereço, município, domínio e telefone para identificar possíveis correspondências, registrando nível de confiança.
8. Verificar sinais recentes de atividade comercial, diferenciando ausência de evidência de inatividade confirmada.
9. Implementar score comercial de 0 a 100 e confiança cadastral de 0 a 100, configuráveis e acompanhados das justificativas.
10. Classificar os prospects em alta, média ou baixa prioridade, com categoria separada para revisão necessária.
11. Criar processamento assíncrono por jobs, com controle de concorrência, retries limitados, timeout, progresso, cancelamento seguro e retomada.
12. Evitar consultas repetidas mediante cache com validade por fonte e data da última verificação.
13. Preservar o isolamento multitenant e impedir consultas ou alterações em prospects de outros tenants.
14. Registrar evidências, fontes, datas, falhas e histórico das análises.
15. Disponibilizar resumo e filtros na página do lote: total, analisados, prioridades, inconsistências, duplicados e pendentes.
16. Permitir selecionar e encaminhar prospects qualificados ao CRM sem disparar mensagens automaticamente.
17. Manter a análise individual existente, utilizando o mesmo motor do processamento em lote.
18. Permitir reanalisar apenas prospects desatualizados ou com falhas, sem repetir desnecessariamente todas as consultas.

## Cuidados

- Não excluir prospects por score baixo.
- Não alterar contatos confirmados sem validação.
- Não classificar como encerrada uma empresa apenas pela ausência de site ou redes sociais.
- Não criar custos com APIs pagas nesta etapa.
- Não utilizar IA generativa em todas as verificações: priorizar regras determinísticas e recorrer à IA somente para casos ambíguos, com orçamento e limites configuráveis.
- Não bloquear a interface durante a análise.
- Preservar o comportamento atual das capturas e do CRM.

## Entrega

Implementar backend, migrations necessárias, jobs, testes automatizados e uma interface profissional integrada à página de captura.

Executar uma validação com dados sintéticos, incluindo duplicidades, ausência de site, CNPJ inválido, empresa ativa, CNPJ baixado, indisponibilidade de fonte externa e correspondências ambíguas.

Registrar no `executed.md` as alterações, testes, limitações e pendências.

Não executar disparos WhatsApp, não consumir serviços pagos e não publicar em produção sem autorização.