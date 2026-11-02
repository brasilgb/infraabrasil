# ABRASIL-SAAS-PADRAO-01.2 — Finalização operacional do VetorSales

**Prioridade:** Crítica
**Branch:** `abrasil-auth-ux-03`
**HEAD de referência:** `35857c9`

## Objetivo

Concluir as pendências reais da ABRASIL-SAAS-PADRAO-01.1, sem repetir auditorias e sem recriar as integrações Efí já recuperadas da main.

O VetorOS permanece como modelo oficial para o RootAdmin, assinaturas, checkout, renovação e suspensão.

## 1. Finalizar checkout Efí

Implementar no frontend do tenant a tokenização de cartão por meio da integração oficial Efí.

Criar um modal unificado:

- Pix.
- Boleto/Bolix.
- Cartão de crédito.

Reutilizar os serviços Efí existentes.

Não armazenar dados sensíveis do cartão no VetorSales.

Não disponibilizar métodos sem configuração e validação.

## 2. Concluir gestão de assinatura

Permitir ao tenant:

- Consultar plano e histórico.
- Selecionar outro plano.
- Contratar usuários adicionais.
- Visualizar valores e eventuais diferenças proporcionais.
- Confirmar alteração e prosseguir para pagamento.
- Renovar assinatura mesmo com acesso suspenso.

Respeitar o vencimento contratual fixo e a carência de três dias.

A Efí deve atender exclusivamente às cobranças SaaS dos tenants.

## 3. Testes completos

Corrigir as sete falhas comerciais remanescentes, identificando se são problemas preexistentes de SQLite ou defeitos reais.

Reexecutar a suíte completa após as correções.

Executar testes de integração, TypeScript e build.

Se MySQL continuar inacessível, registrar o impedimento, sem declarar homologação equivalente.

## 4. Validação visual

Homologar no navegador:

- RootAdmin — Configuração Efí.
- RootAdmin — Assinaturas e cobranças.
- Tenant — Meu Plano.
- Checkout Pix, boleto e cartão.
- Renovação de conta suspensa.

Testar desktop e celular.

Preservar identidade verde e layout padronizado com o VetorOS.

## 5. Git e publicação

Após os testes:

1. Criar commits específicos.
2. Confirmar branch e diferenças com main.
3. Publicar o branch usando a chave SSH correta.
4. Integrar à main sem force push.
5. Confirmar os SHAs remotos.
6. Realizar backup verificável antes de migrations.
7. Publicar na VPS e verificar todos os serviços.

Não modificar o gitlink VetorLeads.

Não gerar cobranças reais sem homologação e autorização.

## 6. Registro

Comparar `correio.md` com a instrução anteriormente executada.

Caso exista alteração, executar a instrução vigente e substituir integralmente o conteúdo de `executed.md` pelo resultado real.

Registrar testes, commits, push, deploy e impedimentos comprovados.

**Critério de conclusão:** fluxo de assinatura SaaS funcional e testado, sem divergências relevantes em relação ao padrão VetorOS.
