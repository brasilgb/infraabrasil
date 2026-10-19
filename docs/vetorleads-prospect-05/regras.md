# Qualificação comercial v3

Cálculo determinístico local por prospect e produto da empresa ativa. Regras centralizadas em `ProspectQualifier::WEIGHTS`; sem consulta externa ou IA para calcular o score. Cada contribuição é arredondada e a soma é o score geral.

| Dimensão | Peso geral | Pontos internos (0–100) |
| --- | --- | --- |
| Qualidade cadastral | 20% | nome 20; categoria 15; cidade/UF 20; telefone 20; endereço 10; website 5; CNPJ válido 5; situação ativa consultada 5 |
| Potencial de contato | 45% | fixo ou celular 50; WhatsApp informado pela origem 20, provável 8, possível em fixo 3, desconhecido 0; e-mail 15; website 5; captura recebida em até 180 dias 10 |
| Aderência comercial | 35% | 100 quando há evidência positiva documentada; 50 neutro quando não há contexto ou evidência suficiente |

Cinquenta de aderência neutra não significa 50% de probabilidade de compra. Sem produto, usa-se o segmento explícito da pesquisa; o lote genérico “Captura AB Prospect” não é segmento comercial. Com produto, compara-se nome/atividade da empresa com os termos do nome do produto existente, com normalização simples de acentos e plural. Vocabulários auxiliares: albumina/suplementos/nutrição; assistência/reparo/equipamento/manutenção; distribuidor/revendedor/atacado. Academia só entra como canal de suplementos quando o nome/atividade também evidencia loja ou suplementos. Produto de criação de sites pode ter aderência positiva quando não existe domínio próprio ou há evidência de site inacessível. São sinais de compatibilidade, sem comprovar intenção de compra.

Não são atribuídos pontos a estrelas ou quantidade de avaliações. Esses dados permanecem disponíveis nos detalhes. Fixo e celular recebem os mesmos 50 pontos como canal telefônico; WhatsApp continua sendo um sinal separado. Nenhum celular é automaticamente confirmado no WhatsApp e nenhum telefone é considerado atendível por verificação de rede inexistente.

Website/CNPJ não são requisitos de prioridade alta. A ausência de website afeta apenas disponibilidade cadastral/canal adicional, com diferença geral de até quatro pontos por arredondamento; para produto de criação de sites, a ausência pode ser evidência positiva de aderência. A ausência de CNPJ/situação consultada representa até dois pontos gerais.

| Prioridade | Critérios |
| --- | --- |
| Dados insuficientes | potencial de contato menor que 20 |
| Alta | contato pelo menos 40, aderência pelo menos 60 e score pelo menos 60 |
| Média | contato pelo menos 25 e score pelo menos 40, sem atender à alta |
| Baixa | demais registros com contato pelo menos 20 |

Listagem não recalcula dados. Valores de regras anteriores ficam identificados como pendentes de qualificação comercial e não recebem prioridade v3. O filtro de prioridade e a ordenação por potencial usam a regra atual, por produto. Os filtros/ordenação por score legados continuam disponíveis.

Reprocessamento: ação individual em Detalhes, sem consultas externas. A última qualificação anterior de cada produto fica em `enrichment.qualification_previous`; campos anteriores a uma recuperação de localização ficam em `enrichment.normalization_previous`. O hash considera apenas os dados usados no cálculo, sem incluir essas cópias; entradas iguais preservam o registro existente. A ação registra tenant, usuário, prospect, produto, versão e hash no log. Não foi criado nem executado recálculo em massa; qualquer operação futura deve primeiro revisar impacto e preparar recuperação a partir de backup/snapshots.

Localização: preserva cidade/UF explícitos e recupera pares estruturados no endereço, registrando origem e confiança derivada. Não converte DDD, coordenadas ou suposições sobre bairros em cidade. Endereços ambíguos permanecem pendentes. A extração textual não é validação geográfica oficial.

Enriquecimento por CNPJ é uma ação distinta. Exige CNPJ válido e configuração existente, reutilizando resultado do mesmo CNPJ por até 30 dias. Ausência, invalidade ou qualificação local não disparam BrasilAPI.
