# VETORLEADS-DEPLOY-01 — Implantação e homologação operacional na VPS

O build já foi enviado para a VPS.

**Objetivo:** colocar o VetorLeads em funcionamento com as implementações mais recentes, preservando dados e serviços existentes.

## 1. Auditoria inicial

Verifique o estado atual de `/opt/infra-abrasil`:

- Repositórios, submódulos e commits.
- Containers, Docker Compose e Nginx.
- Configurações de ambiente.
- Build frontend disponível.
- Migrations pendentes.
- Banco MariaDB operacional.
- Configurações de WAHA, n8n e WebChat.
- Flags de liberação comercial.

Confirme que o código disponível contém as implementações VETORLEADS-06, UX-04 e PROSPECT-05, além das correções posteriores presentes no repositório.

Não sobrescreva alterações locais nem execute `git reset --hard`.

## 2. Preparação e segurança

Antes de qualquer alteração:

- Verifique os backups existentes.
- Gere backup consistente do banco VetorLeads.
- Confirme a possibilidade de restauração.
- Identifique as migrations pendentes.
- Verifique alterações potencialmente destrutivas.
- Preserve configurações, usuários, tenants, prospects, históricos e integrações.

Não exponha credenciais nos relatórios.

## 3. Implantação

Se a auditoria não identificar bloqueios:

- Consolide os arquivos e o build já enviados.
- Reconstrua somente os serviços necessários.
- Execute migrations compatíveis e não destrutivas.
- Atualize caches do Laravel.
- Confirme permissões de diretórios.
- Verifique a configuração comercial `SAAS_COMMERCIAL_ENABLED`.
- Habilite a área comercial somente após verificar autorização e compatibilidade com os módulos atuais.
- Preserve VetorOS, n8n, WAHA e os demais sistemas da infraestrutura.

Não recrie volumes do banco, não execute `docker compose down -v` e não substitua configurações operacionais sem necessidade.

## 4. Homologação funcional

Verifique no ambiente publicado:

1. Cadastro e login.
2. Isolamento entre tenants.
3. Dashboard comercial.
4. Capturar empresas.
5. Download da extensão AB Prospect.
6. Leads captados e filtros.
7. Qualificação comercial v3.
8. Conversão explícita ao CRM.
9. Cadastro e edição de leads.
10. Funil Kanban configurável.
11. Follow-ups e atividades.
12. Valores estimados e negócios ganhos.
13. Configuração da integração AB Prospect.
14. Landing page e páginas legais.

Realize inicialmente verificações somente de leitura. Utilize dados sintéticos isolados para testes que exijam escrita, sem enviar mensagens reais ou alterar prospects operacionais.

## 5. Validação dos prospects reais

Verifique os registros existentes utilizando consultas somente de leitura.

Apresente quantos possuem telefone, localização, website, CNPJ e qualificação v3.

Não requalifique os registros em massa sem autorização específica.

## 6. Relatório

Atualize `executed.md` informando:

- Versão efetivamente implantada.
- Estado dos containers.
- Backup realizado.
- Migrations executadas.
- Testes e verificações.
- Funcionalidades homologadas.
- Problemas encontrados.
- Pendências para liberação comercial.

Se encontrar um bloqueio que coloque dados ou serviços em risco, interrompa a implantação e explique a situação.

**Prioridade absoluta: prospecção B2B, qualificação, conversão ao CRM e acompanhamento das vendas.**

Não adicionar novas funcionalidades nesta execução.
