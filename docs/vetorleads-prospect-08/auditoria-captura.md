# Auditoria da captura e da jornada

## Contrato auditado

A extensão envia lotes de até 50 empresas para `POST /api/prospects/import`; o endpoint aceita até 100 por requisição. `AuthenticateTenantToken` limpa o contexto anterior, resolve o bearer token ativo/não expirado/não revogado, exige tenant ativo e define o contexto exclusivamente pelo token. Ao final limpa novamente o contexto. `tenant_id` forjado no payload não determina a propriedade dos dados. Configuração e emissão de token continuam restritas à área administrativa existente; apenas o hash é persistido.

| Item | Implementação observada e teste |
| --- | --- |
| Identificação e autenticação | Casos de token válido, global legado rejeitado, revogado, expirado, tenant inativo e tenant forjado cobertos na suíte funcional. |
| Nome e atividade | Nome público obrigatório; categoria quando fornecida. API aceita razão social/nome fantasia opcionais, mas a extensão Maps atual não garante razão social cadastral. Não transformar nome público em razão social comprovada. |
| Telefones e WhatsApp | Normalização de fixo/celular, formatos estrangeiros e serviços; sem inventar DDD. WhatsApp conhecido significa informado pela origem; provável/possível depende do tipo de telefone e não equivale a número ativo. |
| Website | URL pública normalizada; domínio próprio separado de redes sociais. Dados de análise anterior preservados em recaptura vazia. Ausência de site significa ausência informada/observada na captura. |
| Endereço, cidade e UF | Valores fornecidos e recuperação conservadora de cidade/UF explícitas no endereço. Não há inferência por DDD, coordenadas ou pesquisa externa automática. |
| Origem e lote | Fontes, payload, URL/identidade Maps, data e lote são vinculados às tabelas existentes; histórico e filtros na interface. Texto de produto enviado pela extensão é contexto da captura, não associação automática a um produto comercial. |
| Duplicidades | Identidade Maps/Place ID/FTID e fallback conservador; mesmo telefone com lugares distintos não funde empresas. Capturas idênticas em tenants diferentes permanecem isoladas. |
| Recaptura | Preenche lacunas, preserva campos comerciais, oposição e CRM. Correção PROSPECT-08: qualificação existente permanece intacta até prévia/confirmação. |
| Erros | Validação do payload e autenticação no servidor; transação do lote; extensão reporta HTTP 401/422/500 e falha de rede. O teste de transporte usa fetch simulado, sem chamada externa. |

## Jornada demonstrada em ambiente sintético

Captura por contrato autenticado → recaptura → cadastro de produto/segmentos → lista e contexto de produto → prévia → confirmação → seleção explícita → conversão → empresa no CRM → indicadores do dashboard.

A conversão é idempotente; vínculo a lead existente preserva dados preenchidos e registra origem/produto/score. A suíte cobre isolamento entre tenants, permissões, oposição, histórico e contagens; a repetição não cria outro lead ou nota. A lista permite filtrar origem, canais, prioridade, produto, situação e lote. Captura continua separada da configuração administrativa, sem novo menu duplicado.

O v3 e seus pesos foram preservados. A correção do selo `Qualificado` exige prioridade comercial alta e canal; um tier numérico alto com aderência baixa não basta. Casos de produto errado, segmento excluído e produto sem contexto suficiente foram exercitados com dados de contato fortes. A verificação opcional de site/CNPJ agora preserva a qualificação existente, pedindo prévia posterior. Não houve requalificação em massa.

A revisão de interface cobriu captura, prospects, catálogo, conversão, CRM e dashboard, com screenshots 1440/390/320 px e prévia nos diálogos. A tabela usa rolagem interna no celular. Testes de consentimento da landing e a suíte completa continuam aprovados. Capturas usam banco sintético e não representam empresas ou faturamento operacionais.

## Leitura operacional, sem escrita

O levantamento por PDO READ ONLY confirmou MySQL 8.4.11, PHP 8.4.26 e 46 migrations aplicadas. Totais agregados observados: 61 prospects, 0 prospects vinculados ao CRM, 0 leads provenientes desses prospects e 0 leads ganhos. Portanto, nenhuma conversão comercial real foi demonstrada nesta execução. Essas contagens vieram de SELECTs; não houve bootstrap do Laravel operacional, sessão autenticada real, criação/seleção/conversão de empresa ou contatos externos.

As evidências de testes estão nos logs desta pasta; `runtime-read-only.json` contém somente metadados, hashes e agregados. `interface/results.json` registra a demonstração sintética. A amostra real continua sem empresas escolhidas e sem autorização; o roteiro separado define o limite proposto e os critérios de aceite.
