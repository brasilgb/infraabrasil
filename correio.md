# VETOR-CONSOLIDATE-01 — Commit e continuidade

**Projeto:** VetorOS  
**Infraestrutura:** infra-abrasil  
**Repositório:** gateway/vetoros

## 1. Consolidar o desenvolvimento

Autorizo realizar commits locais das etapas já implementadas e aprovadas:

- VETOR-INTEL-01
- VETOR-INTEL-02 e INTEL-02.1
- VETOR-INTEL-03 e homologação
- VETOR-PROD-IMPORT-01

Antes de commitar:

1. Conferir branch, HEAD e git status.
2. Revisar os arquivos modificados e novos.
3. Separar commits por funcionalidade, quando possível.
4. Não incluir arquivos .env, credenciais, backups, dumps, temporários ou artefatos desnecessários.
5. Preservar as alterações existentes sem descartar trabalho.
6. Conferir o estado do submódulo e do repositório principal.

## 2. Validar importação CSV

Revisar o fluxo de produtos:

- Download do template.
- Seleção e prévia do CSV.
- Importação de produtos válidos.
- Tratamento de duplicados.
- Registro de entrada no estoque.
- Exibição dos erros de importação.

Executar testes automatizados. Quando houver navegador disponível, verificar também o funcionamento visual.

## 3. Executar commits

Após validação, criar commits locais organizados com mensagens descritivas.

Não realizar push ou deploy nesta etapa.

Registrar os hashes dos commits e o estado final da árvore de trabalho.

## 4. Próxima implementação

Após consolidar, iniciar o **VETOR-INTEL-04 — Indicadores Operacionais e Comerciais**.

Iniciar pela auditoria das fontes existentes e pela definição documentada das regras de cálculo.

Priorizar:

- OS paradas e atrasadas.
- Orçamentos aguardando aprovação.
- Orçamentos próximos do vencimento.
- Conversão de orçamentos.
- Tempo médio de aprovação.
- Produtividade dos técnicos.
- Cumprimento dos prazos.
- Rentabilidade real das OS.
- Indicadores com dados incompletos ou pendentes.

Reutilizar os serviços e regras já existentes.

Não criar indicadores financeiros com valores presumidos.

Implementar incrementalmente, com testes, respeitando tenants, permissões e compatibilidade.

## 5. Relatório

Atualizar `executed.md` com:

- Commits realizados.
- Testes executados.
- Situação da importação CSV.
- Evolução do INTEL-04.
- Pendências e riscos.
- Próxima etapa recomendada.

**Autorização:** commits locais e implementação incremental. Push e deploy permanecem sujeitos a autorização posterior.