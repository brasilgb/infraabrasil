# VETOR-HML-01.2 — Finalização do polimento e consolidação

**Projeto:** VetorOS  
**Infraestrutura:** `infra-abrasil`  
**Data:** 2026-10-08

## Objetivo

Concluir as pendências da homologação VETOR-HML-01.1 e consolidar as alterações aprovadas.

### 1. D6 — Layout responsivo

- Preservar os atalhos Caixa, PDV e Calendário junto de "Prioridades de hoje".
- Redimensionar os atalhos conforme a largura disponível.
- Manter todos os oito indicadores legíveis.
- Permitir rótulos com até duas linhas quando necessário.
- Caso não exista espaço suficiente, posicionar os três atalhos abaixo da barra, lado a lado.
- Não criar consultas adicionais.
- Validar especificamente 1024, 1280, 1366, 1920, 768 e 375 px.
- Não considerar a tarefa concluída enquanto houver truncamento que prejudique a compreensão.

### 2. D2 — Permissões do técnico

- Mostrar cliente e equipamento em modo somente leitura quando o usuário não possuir a permissão necessária.
- Evitar chamadas de busca que inevitavelmente retornariam 403.
- Reforçar no backend a autorização para alteração de cliente e equipamento, sem ampliar privilégios.
- Preservar a possibilidade de o técnico atualizar legitimamente sua própria OS.
- Criar testes específicos de autorização e regressão.

### 3. Consolidar código

Preservar todas as correções D1, D3, D4 e D5 aprovadas na etapa anterior.

Realizar revisão dos arquivos alterados, verificando especialmente possíveis regressões de autorização e sessão.

### 4. Git

A execução anterior não conseguiu criar commits devido à política do ambiente.

Não contornar restrições de permissão.

Caso o ambiente esteja autorizado, criar commits locais separados para:

1. VETOR-UX-DASH-01;
2. correções VETOR-HML-01.1;
3. comando de auditoria APP_KEY;
4. ajustes VETOR-HML-01.2.

Se a criação de commits continuar bloqueada, registrar os comandos necessários para execução manual.

Atualizar o ponteiro do submódulo em `infra-abrasil` somente após os commits respectivos.

**Não realizar push, deploy ou migrations de produção.**

### 5. Validação

- Executar toda a suíte de testes;
- validar TypeScript e build;
- executar verificações de lint;
- testar visualmente todas as resoluções previstas;
- confirmar que técnicos não recebem erros 403 desnecessários na interface;
- verificar isolamento entre tenants;
- validar que nenhuma regra financeira foi alterada.

### 6. Entrega

Atualizar `executed.md` informando:

- arquivos alterados;
- evidências de homologação;
- testes aprovados e reprovados;
- commits locais;
- pendências existentes;
- recomendação para iniciar a homologação externa.

Não iniciar VETOR-INTEL-05 e não tocar nas integrações WAHA ou Spedy nesta etapa.