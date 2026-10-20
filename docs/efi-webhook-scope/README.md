# Efí: HTTP 403 ao cadastrar webhook Pix

Diagnóstico em 19/10/2026 UTC, serviço VetorOS. A integração usa **homologação**. As credenciais e o certificado autenticam corretamente: uma nova autorização OAuth retornou HTTP 200. O token novo não inclui `webhook.write`; isso confirma falta de permissão na aplicação Efí, independentemente do cache local. O webhook ainda não consta como registrado.

O código utiliza `PUT /v2/webhook/{chave}`, endpoint correto para o registro. A Efí exige `webhook.write` para essa operação. O cadastro de permissões é feito na conta Efí, não no código do VetorOS.

Para corrigir:

1. Na conta Efí, abrir **API → Aplicações**, localizar a aplicação correspondente às credenciais Pix salvas no VetorOS e acessar **três pontinhos → Configurações**.
2. Na API Pix, habilitar **Alterar Webhooks (`webhook.write`) no ambiente de Homologação**, que está selecionado no VetorOS. Habilitar `webhook.read` também se for consultar o cadastro. Salvar/continuar.
3. No VetorOS, em RootAdmin → Financeiro → Efí Bank, executar **Testar conexão**. Essa ação já descarta o token Pix em cache e obtém um novo.
4. Executar **Cadastrar webhook Pix** novamente.

O teste de conexão sozinho valida a autenticação; não comprova permissão para cadastrar webhook. Se persistir, conferir se a aplicação editada é a mesma das credenciais Pix salvas, e se a permissão foi habilitada no ambiente correto.

Ajustar a configuração da aplicação na conta Efí requer acesso do titular àquela conta. Nesta execução não houve alteração de credenciais, ambiente, homologação de pagamentos, configuração do webhook ou criação de cobrança. Nenhum token, senha ou certificado foi exportado. O novo token usado no diagnóstico foi descartado; o cache existente não foi alterado. Não houve mudança de código ou deploy.

Evidência sanitizada: [diagnostic.json](diagnostic.json).

Fontes oficiais:

- [Credenciais, Certificado e Autorização](https://dev.efipay.com.br/docs/api-pix/credenciais/).
- [Webhooks Pix](https://dev.efipay.com.br/docs/api-pix/webhooks/).
