# VETORCHAT-SAAS-03 — Ativação institucional e homologação operacional

## Objetivo

Preparar e validar a integração real do chat da ABrasil Sistemas com o VetorLeads, utilizando a arquitetura multitenant implementada em VETORCHAT-SAAS-02.

O VetorLeads continua sendo o serviço central. Não criar um backend de chat independente na ABrasil.

## Etapas

1. Auditar a implementação existente antes de modificar qualquer código.
2. Verificar como o tenant ABrasil é identificado e como sua configuração de widget é persistida.
3. Validar o fluxo de geração, armazenamento e rotação da chave pública, sem exposição de credenciais privadas.
4. Preparar o snippet de instalação para `abrasilsistemas.com.br`.
5. Verificar a integração com o frontend institucional, evitando conflitos de layout, CSS, JavaScript e componentes existentes.
6. Confirmar a proteção de origem, CORS, rate limiting, isolamento entre tenants e permissões.
7. Verificar se mensagens enviadas pelo visitante aparecem corretamente no painel VetorLeads.
8. Confirmar que a resposta do atendente retorna ao widget e que o histórico permanece disponível.
9. Manter os contatos recebidos pelo site separados dos prospects captados pelas ferramentas de prospecção.
10. Validar a associação opcional de contatos e oportunidades comerciais, evitando leads duplicados.
11. Preparar o processo para permitir futuras instalações em outros tenants, sem configurações globais ou intervenção no código.
12. Registrar evidências e eventuais bloqueios para homologação na VPS.

## Critérios de aceitação

- O tenant ABrasil visualiza somente suas conversas.
- Nenhum outro tenant consegue consultar ou responder a essas conversas.
- O widget funciona em desktop e mobile.
- A instalação repetida não cria widgets duplicados.
- Mensagens não são duplicadas após atualização ou reconexão.
- A resposta humana chega corretamente ao visitante.
- O CRM distingue atendimento institucional de prospecção ativa.
- O processo de instalação é reutilizável por qualquer tenant.
- Nenhum segredo ou token administrativo fica exposto no navegador.

## Regras obrigatórias de execução

- Não criar, iniciar ou modificar containers Docker locais.
- Não executar migrations em bancos de produção.
- Não enviar mensagens reais sem autorização.
- Não fazer deploy, commit ou push sem autorização.
- Utilizar banco isolado e dados sintéticos para testes.
- Executar testes automatizados antes dos testes de navegador.
- Se uma ferramenta de navegador falhar repetidamente por limitação do ambiente, interromper as tentativas, registrar o motivo e utilizar testes alternativos confiáveis. Não insistir indefinidamente no mesmo erro.
- Não alterar funcionalidades de outros módulos que estejam fora do escopo.
- Preservar arquivos e alterações preexistentes.
- Não declarar homologação real quando apenas testes sintéticos tiverem sido executados.

## Relatório

Atualizar `executed.md` com:

- Comparação com o `correio.md` anterior.
- Arquivos modificados.
- Testes executados e seus resultados.
- Evidências da instalação e do fluxo de conversas.
- Pendências de configuração na VPS.
- Procedimento de ativação do primeiro tenant.
- Riscos e recomendações.

**Resultado esperado:** integração institucional tecnicamente pronta e documentada para ativação controlada na VPS, sem publicação automática.
