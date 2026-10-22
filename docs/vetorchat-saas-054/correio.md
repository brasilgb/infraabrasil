# VETORCHAT-SAAS-05.4 — Correções finais e versionamento por produto

## Objetivo

Concluir as pendências técnicas identificadas na VETORCHAT-SAAS-05.3, preservando integralmente as implementações de SEO, VetorChat, relatórios e demais melhorias.

Não realizar nova auditoria geral. Trabalhar diretamente sobre os problemas já identificados.

## 1. Correções técnicas

### ABrasil Sistemas
- Corrigir o erro de ESLint em `cookie-consent.tsx` sem modificar indevidamente o comportamento do consentimento.
- Corrigir os espaços finais identificados em `correio.md`.
- Preservar o SEO implementado e o `robots.txt` dinâmico.
- Confirmar que a remoção do arquivo estático faça parte da alteração de SEO.
- Reexecutar os testes afetados.

### VetorLeads
- Corrigir os cinco erros de lint em `chat-products.tsx` e `support-inbox/index.tsx`.
- Preservar as funcionalidades da inbox, widget, canais, produtos, automações e WAHA.
- Verificar o problema de declaração duplicada da função `productStart` nos testes.
- Evitar alterações desnecessárias na arquitetura.

### VetorOS
- Corrigir as 57 ocorrências de `no-explicit-any` nos relatórios utilizando tipagem TypeScript apropriada.
- Preservar os cálculos, filtros, PDFs e componentes existentes.
- Reexecutar verificações estáticas e testes afetados.

### VetorPet
- Validar configuração e instalação do widget.
- Executar os testes se houver dependências já disponíveis.
- Se não houver ambiente compatível, documentar a limitação sem insistir em instalações ou tentativas repetitivas.

## 2. Versionamento

Organizar as alterações em commits independentes por repositório e funcionalidade:

- ABrasil: SEO e VetorChat.
- VetorLeads: VetorChat e branding/manual.
- VetorOS: relatórios e VetorChat.
- VetorPet: instalação do VetorChat.
- Superprojeto: gitlinks e infraestrutura, separadamente.

Nesta etapa, preparar os commits e apresentar exatamente o conteúdo de cada um, mas não executar commit ou push sem autorização.

Não alterar automaticamente `docker-compose.override.yml`. Registrar o significado das tags de imagens e sua compatibilidade com o código atual.

## 3. Testes

Executar as verificações estáticas, testes automatizados e build aplicáveis.

Utilizar navegador somente quando houver ambiente compatível. Se a ferramenta falhar por ausência de Chrome ou Chromium, não repetir tentativas sem perspectiva de sucesso.

Não considerar testes HTTP substitutos de uma homologação visual completa.

## 4. Restrições obrigatórias

- Não criar, iniciar ou modificar containers Docker locais.
- Não executar migrations de produção.
- Não alterar configurações da VPS.
- Não realizar deploy, push ou commit sem autorização.
- Não descartar arquivos não rastreados ou alterações locais.
- Não utilizar comandos destrutivos de Git.
- Não instalar dependências desnecessárias.
- Não insistir em testes que falhem repetidamente por problemas ambientais.
- Não enviar mensagens reais a clientes.

## 5. Entrega

Atualizar `executed.md` com:
- Problemas corrigidos.
- Arquivos alterados.
- Testes executados.
- Erros restantes.
- Plano de commits por repositório.
- Pendências operacionais.
- Checklist para VETORCHAT-SAAS-06.

Classificar a entrega como PRONTO PARA HOMOLOGAÇÃO ou BLOQUEADO, com justificativa.