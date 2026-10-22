# VETORCHAT-SAAS-05.1 — Desbloqueio Git e consolidação técnica

## Objetivo

Eliminar os impedimentos identificados na VETORCHAT-SAAS-05 e preparar o ecossistema para homologação operacional, sem reimplementar funcionalidades existentes.

### 1. Resolver o Git do VetorPet

- Identificar a causa das permissões somente leitura em `.git/modules/gateway/vetorpet`.
- Verificar proprietário, permissões e possíveis restrições do filesystem.
- Corrigir somente as permissões necessárias, se houver autorização e segurança para isso.
- Preservar integralmente as alterações locais e os dois históricos divergentes.
- Fazer backup das alterações não commitadas antes de qualquer merge.
- Concluir a mesclagem das duas linhas sem escolher arbitrariamente uma versão.
- Resolver o gitlink do repositório principal.
- Não usar `git reset --hard`, `git clean -fd`, `git push --force` ou comandos destrutivos.
- Se houver bloqueio de permissões que exija privilégios não disponíveis, registrar o comando necessário e interromper somente essa operação.

### 2. Validar o VetorPet

- Conferir a instalação do widget e os arquivos alterados.
- Verificar dependências já declaradas.
- Executar testes quando houver ambiente compatível.
- Não instalar dependências desnecessárias.

### 3. Validar o VetorOS

- Revisar os 11 avisos dos testes.
- Distinguir problemas do ambiente de defeitos reais.
- Corrigir problemas comprovados.
- Repetir os testes afetados.

### 4. Revisar a arquitetura multitenant

- Confirmar que o tenant ABrasil utiliza uma chave pública comum para seus quatro produtos.
- Confirmar que `data-product` identifica corretamente cada produto.
- Validar a separação de sessões e conversas.
- Verificar a segurança das origens HTTPS.
- Confirmar que outros tenants não compartilham credenciais ou sessões WAHA.
- Garantir que chaves públicas não concedam permissões administrativas.

### 5. Revisar UX/UI

- Conferir o painel do VetorChat dentro do VetorLeads.
- Manter inbox, filtros, operadores, produtos e configurações bem organizados.
- Evitar duplicação de menus e formulários.
- Preservar a experiência profissional e responsiva.

### 6. Preparação de produção

Produzir checklist verificável para:

- Backup.
- Migrations.
- Build.
- Configurações de ambiente.
- Sessões WAHA.
- Produtos e domínios.
- Homologação do widget.
- Rollback.

Não executar publicação sem autorização.

## Regras obrigatórias

- Não criar, iniciar ou alterar containers Docker locais.
- Não fazer commit, push ou deploy sem autorização.
- Não alterar a VPS nesta etapa.
- Não enviar mensagens reais.
- Testar no navegador quando existir ambiente compatível.
- Se a ferramenta de navegador falhar, tentar alternativa disponível.
- Não insistir repetidamente em testes que falham por limitações ambientais.
- Não instalar novas ferramentas sem necessidade.
- Preservar toda a implementação funcional anterior.

## Relatório

Atualizar `executed.md` com:

1. Estado inicial e final do Git.
2. Correções efetivamente realizadas.
3. Arquivos alterados.
4. Testes executados e resultados.
5. Pendências.
6. Checklist de ativação.
7. Classificação final: PRONTO PARA HOMOLOGAÇÃO ou BLOQUEADO.

Não declarar homologação operacional sem testes reais.