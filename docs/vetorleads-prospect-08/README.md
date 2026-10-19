# PROSPECT-08 — consolidação e preparação comercial

Execução em 19/10/2026 UTC. Escritas somente em testes sintéticos e commits locais; leitura operacional limitada a SELECT/metadados em transação READ ONLY. Sem homologação operacional real, migrations operacionais, push, deploy, reinicialização de serviços operacionais ou contatos externos.

- [Auditoria da captura e jornada](auditoria-captura.md).
- [Publicação, reversão e homologação real](publicacao-e-homologacao.md): roteiro preparado; depende de autorização futura específica.
- `commits-vetorleads.json`: três commits novos, preservando os onze anteriores. `commit-infra.json` e `commit-infra-confirmacao.json`: configuração/ponteiros locais da infraestrutura. O commit final de documentação aparece no histórico Git da infra; o snapshot `repositorios-antes-commit-documentacao.json` foi gerado antes dele.
- `runtime-read-only.json`, `codigo-runtime-local.json`, `migrations.json`: versões, hashes comparados e duas migrations pendentes. Nenhuma credencial ou linha identificável de empresa.
- `servicos.json`: 21 serviços operacionais running/healthy no momento da leitura; isso não demonstra compatibilidade de schema do WebChat.

## Verificações executadas

| Verificação | Resultado |
| --- | --- |
| VetorLeads completo, antes das correções | 1.268 testes / 13.460 assertions |
| VetorLeads completo, final | **1.270 testes / 13.475 assertions** |
| PHPStan | Sem erros |
| Pint | Aprovado, 321 arquivos |
| Frontend | ESLint, Prettier, TypeScript e build aprovados |
| Consentimento da landing, JavaScript | 3 testes aprovados |
| Extensão, parsers/recaptura/contrato/transporte | 18 testes aprovados, HTTP/rede simulados |
| VetorOS, proxy e disponibilidade WebChat | 5 testes / 17 assertions, HTTP simulado |
| Chromium, jornada comercial | Fluxo completo aprovado, 1440/390/320 px, sem erros JS |
| Revisão visual estável | Aprovada, lista posicionada à esquerda e diálogos após animação |
| Compose | Configuração validada sem aplicação |

Os dois testes novos verificam a preservação da qualificação na recaptura e a ausência de selo favorável para produtos/segmentos incompatíveis. Um teste existente passou também a verificar que consultar site/CNPJ preserva a qualificação. A extensão ganhou teste de bearer token, erros HTTP e rede. Dados externos nesses testes são simulados; o container PHP não tinha rede.

## Evidências visuais sintéticas

[Captura](interface/1440-capture.png), [critérios](interface/1440-commercial-criteria.png), [lista](interface/1440-leads.png), [lista celular](interface/320-leads.png), [prévia celular](interface/390-preview.png), [conversão](interface/1440-conversion.png), [CRM](interface/1440-crm.png), [dashboard](interface/1440-dashboard.png).

Os números das telas vêm do banco sintético. Os totais operacionais separados, obtidos por SELECT, foram 61 prospects, zero vínculos ao CRM e zero ganhos. Nenhum faturamento ou conversão sintética foi apresentado como produção. A captura no Maps real com extensão instalada permanece pendente; a demonstração exercitou o contrato HTTP autenticado e toda a jornada interna.
