# PROSPECT-07 — evidências técnicas

Todos os fluxos de escrita e capturas de tela usam dados sintéticos em SQLite isolado, PHP 8.4, navegador Chromium e frontend compilado. A leitura operacional foi feita via PDO em transação READ ONLY, sem bootstrap do Laravel de produção. Nenhuma migration operacional, requalificação real, mensagem, commit, push ou deploy foi executado.

## Fluxo demonstrado

1. `/prospecting`: instruções e download da extensão, separados da configuração administrativa.
2. `POST /api/prospects/import`: contrato da extensão com token exclusivo do banco sintético; repetição da captura sem duplicar a empresa.
3. `/prospect-products`: cadastro de produto, segmentos desejados/excluídos e preferência por site próprio.
4. `/prospecting/prospects`: empresa capturada, contexto de produto, origem e prioridade; Detalhes → Prévia de qualificação → Confirmar qualificação.
5. Selecionar como prospect → Adicionar ao CRM → escolher produto → Criar leads.
6. `/leads`: empresa vinculada ao CRM; nota com prospect, fontes, produto e score/versão, inclusive quando o lead já existia. Repetir a conversão é idempotente.
7. `/dashboard`: contagens consultadas do banco, com períodos e totais da base identificados.

## Critérios comerciais

Os segmentos pertencem ao produto/tenant existente, sem um cadastro paralelo. Separe termos por vírgula, ponto e vírgula ou linha. A comparação ignora acentos e caixa, usa trechos do nome/atividade e não comprova intenção. Exclusão explícita tem prioridade (aderência 0); compatibilidade com segmentos e site informado vale 100; divergência explícita vale 25; atividade insuficiente vale 50. Sem critérios novos, as regras anteriores do v3 continuam. Pesos preservados: dados 20%, contato 45%, aderência 35%.

Salvar critérios não recalcula a base. A prévia individual não salva modelos nem consulta serviços externos. A confirmação exige token criptografado com tenant, usuário, prospect, produto, hash dos dados/critérios e validade de dez minutos; alterações exigem nova prévia. O erro volta para o diálogo comercial.

## Limites

A demonstração valida o contrato de importação e o fluxo comercial em ambiente sintético. Não valida a extensão instalada contra Google Maps real, permissões/fontes externas ou a operação publicada. A captura depende da integração administrativa inicial e dos dados disponibilizados pela fonte. Ausência de site significa ausência de site próprio informado; WhatsApp provável/possível não é disponibilidade confirmada. Não há geração de telefones, CNPJ ou localização, consultas pagas nem envios nesta validação.

O build está somente no ambiente de testes. A migration comercial nova e a pendência anterior do WebChat permanecem sem aplicação operacional; exigem revisão antes de publicação. Homologação operacional em produção continua pendente.
