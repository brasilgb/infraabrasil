# VETORCHAT-SAAS-08 — Consolidação Git, validação final e preparação de publicação

## OBJETIVO

Consolidar os trabalhos realizados nas etapas VETORCHAT-SAAS-01 a 07, concluir a revisão dos arquivos locais e preparar um pacote seguro de commits.

O sistema possui quatro aplicações envolvidas:

- ABrasil Sistemas
- VetorLeads
- VetorOS
- VetorPet

O VetorLeads é o backend multitenant do VetorChat. Os demais produtos utilizam o widget padronizado, preservando a identificação individual do produto.

Esta execução não autoriza publicação.

Ler `executed.md`, `correio.md` e os relatórios históricos antes de modificar arquivos.

Arquivar a instrução anterior e preservar o histórico de hashes.

## 1. Revisão completa dos arquivos

Inspecionar individualmente os arquivos modificados, deletados, staged e não rastreados em cada aplicação.

Utilizar os comandos Git necessários para identificar:

- Diferenças em relação ao HEAD.
- Diferenças em relação ao remoto, quando acessível.
- Arquivos pertencentes a trabalhos anteriores.
- Arquivos introduzidos pelo VetorChat.
- Arquivos pertencentes ao SEO.
- Arquivos de relatórios.
- Arquivos de branding/manual.
- Assets gerados.
- Logs.
- Configurações de ambiente.
- Alterações potencialmente sensíveis.

Não descartar nenhum arquivo sem autorização.

Criar o inventário detalhado:

`docs/vetorchat-saas-08/staging-manifest.md`

Para cada arquivo, informar:

- Repositório.
- Caminho.
- Tipo de alteração.
- Funcionalidade.
- Commit proposto.
- Necessidade de inclusão.
- Dependências.
- Riscos conhecidos.

Usar uma seção de "não incluir" para artefatos locais ou arquivos sem relação com a entrega.

Não utilizar `git add .` nem `git add -A`.

## 2. ABrasil Sistemas

Separar claramente duas entregas.

### Commit A — SEO

Incluir somente arquivos relacionados a:

- SEO institucional.
- Sitemap.
- Robots dinâmico.
- Metadados.
- Indexação.
- Integração de ferramentas de análise.
- Consentimento de cookies relacionado ao carregamento de analytics.

Verificar se a remoção do `public/robots.txt` está acompanhada da rota dinâmica correspondente.

### Commit B — VetorChat

Incluir somente:

- Instalação do widget.
- Identificação do produto ABrasil.
- Configurações relacionadas ao chat.
- Testes pertinentes.
- Ajustes necessários para comunicação com VetorLeads.

Examinar assets compilados e determinar se são necessários no repositório conforme a estratégia existente de build/deploy.

Não incluir artefatos apenas por terem sido gerados.

Confirmar novamente os 81 testes aplicáveis.

## 3. VetorLeads

Separar:

### Commit C — VetorChat

- Backend multitenant.
- APIs do widget.
- Inbox.
- Atendimento.
- Automação.
- Integração WAHA.
- Produtos de origem.
- Segurança.
- Testes automatizados.

Preservar a estrutura de autenticação existente.

Confirmar a compatibilidade das oito rotas do widget com e sem cache.

Executar novamente os 18 testes pertinentes.

### Commit D — Branding e manual

- Identidade visual.
- Manual operacional.
- Ajustes institucionais.
- Arquivos relacionados à apresentação do VetorLeads.

Não misturar com o backend do VetorChat.

Verificar a disponibilidade remota e registrar qualquer impedimento de autenticação.

Não solicitar credenciais em arquivos ou registrar tokens nos relatórios.

## 4. VetorOS

Separar:

### Commit E — Relatórios

Incluir:

- Interfaces TypeScript.
- Contratos e normalização de payloads.
- Telas.
- Componentes PDF.
- Testes relacionados.

Confirmar:

- ESLint direcionado sem erros.
- `npm run types` aprovado.
- Ausência de mudanças não intencionais em cálculos.

### Commit F — VetorChat

Incluir exclusivamente a instalação e integração do widget.

Revisar identificação do produto, domínio autorizado e chave pública.

### Warnings Pest

Investigar a configuração de `tests/TestCase.php` e o carregamento de ambiente.

Propor a menor correção segura que permita executar testes sem warnings ambientais.

Preferir configuração própria de testes e variáveis sintéticas temporárias.

Não criar `.env` permanente com valores reais.

Não modificar o bootstrap global de testes sem verificar o efeito nas demais suítes.

Se a solução exigir uma alteração maior, documentar e manter como pendência justificada.

Não mascarar warnings.

## 5. VetorPet

Examinar todos os arquivos da instalação VetorChat.

Confirmar:

- URL HTTPS.
- Validação da chave pública.
- Identificação `vetorpet`.
- Ausência de widget em páginas administrativas.
- Segurança dos valores de configuração.
- Testes existentes.

Verificar se existe ambiente previamente preparado para a suíte.

Se não existir, não instalar dependências automaticamente nem inventar aprovação de testes.

Preparar:

### Commit G — VetorChat VetorPet

Não executar o commit.

## 6. Infraestrutura compartilhada

Inspecionar as alterações de `docker-compose.override.yml` e outras configurações, sem executá-las.

Revisar:

- Tags das imagens.
- Política `pull_policy`.
- Referências aos serviços.
- Variáveis necessárias.
- Comunicação entre aplicações.
- Configuração WAHA.
- Dependências entre produtos.
- Compatibilidade com `/opt/infra-abrasil`.

Comparar as tags com os commits que serão necessários para a publicação.

Determinar se o processo atual de publicação compila novas imagens ou espera imagens existentes.

Não presumir que uma tag local corresponde ao código atual.

Não executar Docker, Compose, build de imagem nem qualquer alteração operacional.

Preparar uma proposta de atualização da infraestrutura, mas não aplicá-la.

## 7. Contrato universal VetorChat

Revisar a integração compartilhada.

O ecossistema ABrasil deverá utilizar o mesmo tenant institucional para os produtos internos, diferenciando o atendimento por produto.

Tenants externos terão suas próprias credenciais e configurações.

Validar:

- Isolamento multitenant.
- Identificação de origem.
- Domínios autorizados.
- Tokens de visitantes.
- Registro da conversa.
- Associação ao produto.
- Inbox.
- Configuração de WAHA.
- Atendimento humano.
- Automação habilitada.
- Resposta ao widget.

Utilizar apenas dados sintéticos.

Não realizar envios reais.

Não expor credenciais ou segredos.

## 8. Política obrigatória de testes

Não criar containers locais.

Não instalar Docker ou iniciar serviços.

Não instalar navegadores para executar testes.

Utilizar navegador somente se já houver ambiente funcional.

Caso o navegador falhe por limitação ambiental, registrar e prosseguir com testes que funcionem.

Não insistir repetidamente em ferramentas que falham sem alteração concreta.

Executar preferencialmente:

- Pest/PHPUnit.
- TypeScript.
- ESLint direcionado.
- Testes de integração sintéticos.
- Verificações HTTP locais já disponíveis.
- Build de aplicação, quando aplicável e seguro.

Não executar comandos globais com `--fix` que possam alterar arquivos fora do escopo.

Utilizar `git diff --check`.

Não modificar arquivos para satisfazer artificialmente os testes.

## 9. Preparação dos commits

Criar uma lista exata dos arquivos de cada commit proposto.

Organizar a ordem:

1. ABrasil SEO.
2. ABrasil VetorChat.
3. VetorLeads VetorChat.
4. VetorLeads branding/manual.
5. VetorOS relatórios.
6. VetorOS VetorChat.
7. VetorPet VetorChat.
8. Superprojeto — atualização dos gitlinks.
9. Infraestrutura — pinagem e configurações, em alteração independente.

Para cada commit, apresentar:

- Nome.
- Repositório.
- Arquivos exatos.
- Dependências.
- Testes.
- Riscos.
- Necessidade de geração de assets.

Não efetuar staging nem commits.

Quando um arquivo misturar funcionalidades, documentar a necessidade de staging seletivo por hunks.

## 10. Segurança

Proibido:

- Commit.
- Push.
- Deploy.
- Migrations de produção.
- Alterar VPS.
- Criar ou modificar containers.
- Enviar WhatsApp real.
- Executar pagamentos reais.
- Apagar arquivos preexistentes.
- Fazer reset destrutivo.
- Expor segredos.
- Alterar indiscriminadamente `.env`.
- Atualizar submódulos sem revisão.

## 11. Relatório final

Atualizar `executed.md` com:

1. Resumo executivo.
2. Estado de cada aplicação.
3. Resultado dos testes.
4. Warnings pendentes.
5. Inventário completo de alterações.
6. Manifesto de staging.
7. Lista precisa de commits.
8. Dependências entre commits.
9. Situação dos remotos.
10. Situação dos gitlinks.
11. Situação das imagens.
12. Riscos para produção.
13. Pendências de homologação.
14. Recomendação para a etapa 09.

A etapa será considerada concluída quando houver um pacote auditável e reproduzível de commits, mesmo que a homologação operacional ainda dependa de ambiente autorizado.

**Não declarar pronto para produção sem validação real do fluxo WAHA e dos demais serviços envolvidos.**

Resultado esperado: todos os arquivos classificados, alterações separadas por funcionalidade, testes pertinentes confirmados e sequência de commits preparada, sem publicação.