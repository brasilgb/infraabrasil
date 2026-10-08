# VETOR-MAIL-01 — Auditoria e centralização dos e-mails

**Projeto:** VetorOS  
**Infraestrutura:** `infra-abrasil`  
**Prioridade:** após estabilização do VETOR-INTEL em produção

## Objetivo

Auditar e organizar toda a comunicação por e-mail do VetorOS, separando os envios institucionais do SaaS dos envios operacionais dos tenants.

## 1. SMTP central do RootAdmin

Criar ou reaproveitar uma configuração administrativa para:

- Confirmação de cadastro;
- Boas-vindas;
- Recuperação de senha;
- Avisos de assinatura;
- Cobranças e vencimentos;
- Envio de notas fiscais do SaaS;
- Comunicados administrativos.

Permitir configurar servidor, porta, criptografia, usuário, senha e remetente pelo RootAdmin.

Armazenar segredos de forma criptografada. Nunca exibir senhas recuperáveis na interface ou nos logs.

## 2. SMTP individual dos tenants

Preservar as configurações de e-mail de cada empresa.

Utilizar essas configurações para notificações de ordens de serviço, orçamentos e mensagens operacionais.

Garantir isolamento entre tenants.

## 3. Auditoria dos jobs

Analisar especificamente:

- `SendOrderCreatedNotification`;
- `SendOrderStatusUpdatedNotification`.

Investigar as 860 falhas históricas registradas em 20/09/2026.

Verificar qual configuração de envio cada job utiliza e se existe tratamento apropriado quando o SMTP está ausente ou indisponível.

Não reenviar nem excluir as notificações antigas automaticamente.

## 4. Segurança e confiabilidade

- Não permitir que credenciais SMTP de um tenant sejam utilizadas por outro.
- Proteger senhas e tokens.
- Implementar testes de envio autorizados.
- Registrar falhas sem expor informações sensíveis.
- Garantir tratamento de tentativas e limites para evitar envios duplicados.
- Manter compatibilidade com o sistema atual.

## 5. Execução

**Nesta etapa, realizar primeiro uma auditoria somente leitura e apresentar a arquitetura proposta.**

Não executar migrations, commits, push ou deploy sem aprovação posterior.

Documentar os resultados em `executed.md`.