# ABRASIL-SAAS-PADRAO-01.2 — continuação da execução

**Data:** 02/11/2026
**Instrução vigente:** `correio.md` da raiz, sem alteração desde o registro anterior (commit `a3fdece`). Esta execução continua as pendências da execução parcial anterior.
**VetorSales:** branch `abrasil-auth-ux-03`, HEAD `13fea968c645ca12091e3c7d9bc3addb203caa88`, igual à `main` remota.

## Implementado

- **Gestão de assinatura pelo tenant (Meu Plano):** novo painel "Alterar plano ou usuários". O responsável escolhe um plano público mensal e a quantidade de usuários adicionais, calcula os valores e confirma.
  - Cotação: mensalidade atual e nova, ciclo pago, dias restantes e diferença proporcional (`diferença mensal × dias restantes ÷ dias do ciclo`).
  - Acréscimo: gera uma cobrança de **ajuste proporcional** (`subscription_charges.kind = adjustment`). Ela é paga pelo mesmo checkout Efí (Pix, boleto/Bolix ou cartão). O novo plano ou limite é aplicado só quando a quitação é confirmada pela API Efí.
  - Redução ou troca sem custo: fica agendada para o próximo vencimento contratual, sem reembolso do ciclo em curso. A competência daquele vencimento já é emitida com o novo valor.
  - O dia contratual nunca muda. O ajuste não renova o vencimento. A carência de três dias permanece.
  - Bloqueios: competência vencida em aberto, período de teste, assinatura não mensal ou não migrada para Efí, plano não público e redução abaixo dos usuários ativos. Vendedores recebem 403.
  - Competência vencida é cobrada antes de ajuste pendente, para a renovação de conta suspensa continuar funcionando. Ajuste não pago até o fim do ciclo é cancelado pela rotina `subscriptions:maintain`, que também aplica as reduções agendadas.
  - Competências já emitidas e sem pagamento Efí em andamento são reprecificadas após uma alteração.
  - Alteração aguardando pagamento pode ser cancelada pelo responsável. Uma nova solicitação substitui a anterior, desde que não haja pagamento em andamento.
- **Migration** `2026_11_02_000001_create_subscription_changes`: cria a tabela `subscription_changes` e adiciona `kind`, `change_key` e `subscription_change_id` em `subscription_charges`. A unicidade passa de `(tenant_id, competence_month)` para `(tenant_id, competence_month, change_key)`. Up e down testados no MariaDB.
- **RootAdmin:** o valor mensal em "Assinaturas e cobranças" passou a incluir os usuários adicionais, que agora também aparecem na lista. A data de vencimento na migração financeira deixou de aparecer em ISO cru.
- **Correção de exibição:** em Meu Plano, o vencimento aparecia um dia antes (24/11 em vez de 25/11) por causa do fuso horário.
- `public/build` (versionado) regenerado. O manifest anterior não continha `admin/subscriptions/index`, o que fazia o teste `SubscriptionManagementTest` falhar.

## Validação

- **MySQL (MariaDB 11.8.6, banco isolado `vetorsales_reconcile_testing`):** suíte completa com **364 aprovados, 0 ignorados, 3124 asserções**. Inclui os três testes de `FOR UPDATE`, que antes só podiam ser ignorados no SQLite. O impedimento anterior (`Operation not permitted`) era do ambiente: com `DB_HOST=127.0.0.1` a conexão funciona.
- Testes novos em `tests/Feature/SubscriptionChangeTest.php` (7 testes) cobrem:
  - pró-rata de R$ 26,53 (20 de 30 dias), pago via Pix e aplicado sem mover o vencimento;
  - redução agendada e aplicada pela rotina;
  - reprecificação de competência já emitida;
  - limite de usuários;
  - permissão e plano privado;
  - prioridade da competência vencida e cancelamento do ajuste expirado;
  - substituição e cancelamento de alteração.
- `npm run types`, `npm run build`, ESLint dos arquivos alterados, Pint dos arquivos PHP novos ou alterados e `git diff --check`: aprovados.
- **Validação visual** em Chromium headless (Playwright), servidor local com banco isolado temporário (já removido) e configuração Efí fictícia em homologação. Desktop 1366 px e celular 390 px, **sem erros de console**. Telas verificadas:
  - RootAdmin: Configuração Efí e Assinaturas e cobranças;
  - Meu Plano, com cotação, alteração pendente e cobrança de ajuste;
  - checkout com Pix, boleto/Bolix e cartão;
  - conta suspensa redirecionada para a renovação, com o checkout aberto.

  Identidade verde preservada.
- **Não homologado:** nenhuma transação real ou de sandbox na Efí. Não há credenciais Efí de homologação nesta máquina, então a validação visual parou na abertura do checkout. Nenhuma cobrança real foi gerada.

## Git e publicação

- Commits no VetorSales:
  - `7ccfaff`: troca de plano e usuários com ajuste proporcional;
  - `cf19bc4`: RootAdmin e datas;
  - `13fea96`: rebuild dos assets.
- `git push origin HEAD:abrasil-auth-ux-03`: `7b8dd96..13fea96`. Integração à `main` por fast-forward, sem force: `727f2b2..13fea96`. `git ls-remote` confirma `13fea968c645ca12091e3c7d9bc3addb203caa88` nas duas branches.
- O remoto é HTTPS (`github.com/brasilgb/vetorsales`). Não há configuração de chave SSH específica para este repositório.
- **CI do GitHub na `main`:** falhou nos dois jobs, por problemas que já existiam e que também quebravam o commit anterior `727f2b2`.
  - `tests`: o workflow roda `npm ci`, mas o repositório usa `yarn.lock` e não tem `package-lock.json`.
  - `linter`: 399 erros de ESLint já existentes no repositório.

  Os arquivos alterados passam no ESLint local.
- Gitlink do VetorSales no superprojeto atualizado para `13fea96`. O gitlink do VetorLeads não foi alterado; suas mudanças locais preexistentes continuam lá.

## Impedimentos comprovados

- **Backup, migrations e deploy na VPS não foram executados.** Esta máquina é local: não tem `/opt/infra-abrasil` nem chave em `~/.ssh` (só `known_hosts`), e não há host da VPS configurado. A VPS continua na versão anterior. A nova migration precisa rodar em produção, depois de um backup verificável.
- Falta a homologação Efí ponta a ponta (sandbox) de Pix, boleto e cartão, incluindo o ajuste proporcional, antes de liberar cobranças reais.

## Próximos passos

1. Na VPS, fazer backup verificável do banco `vetorsales`.
2. Atualizar o checkout para `13fea96`, reconstruir a imagem, rodar `php artisan migrate --force` e verificar os serviços.
3. Homologar a Efí em sandbox com uma alteração de plano real de teste.
4. Corrigir os workflows de CI (`yarn install --frozen-lockfile` em vez de `npm ci`) e o passivo de ESLint.

**Estado:** a funcionalidade do critério de conclusão está implementada e testada; a homologação Efí e a publicação na VPS seguem pendentes pelos impedimentos acima.
