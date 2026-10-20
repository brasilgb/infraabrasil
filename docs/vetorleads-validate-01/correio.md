# VETORLEADS-VALIDATE-01 — Validação inteligente de empresas prospectadas

## Objetivo

Implementar no VetorLeads uma camada de validação de existência e atividade das empresas capturadas pelo Google Maps, antes de encaminhá-las à prospecção comercial.

Integrar essa camada ao enriquecimento futuro pelo Instagram.

## 1. Identificação empresarial

Utilizar os dados já disponíveis:

- Nome comercial e possíveis variações.
- Endereço, número, bairro, cidade e UF.
- Telefone e website.
- Categoria e segmento.
- Identificador do Google Maps, quando disponível.
- CNPJ, quando conhecido.

Normalizar informações para correspondência, preservando os dados originais.

## 2. Busca e comparação

Implementar mecanismo modular para consultar fontes permitidas:

- BrasilAPI: situação cadastral e dados vinculados ao CNPJ identificado.
- Pesquisa web: localização de referências comerciais atuais, respeitando termos de uso.
- Websites oficiais: identificação de endereço, contatos e links sociais.
- Instagram: perfil empresarial e sinais recentes de atividade, quando acessíveis legalmente.
- Google Places: integração opcional, inicialmente desativada, com controle de orçamento.

Nunca presumir que empresas com nomes semelhantes sejam a mesma organização.

Não selecionar CNPJ exclusivamente por similaridade de nome sem conferir localidade e outros identificadores.

## 3. Motor de correspondência

Criar algoritmo determinístico de pontuação que considere:

- Coincidência exata de CNPJ.
- Similaridade do nome comercial.
- Correspondência de endereço.
- Município e UF.
- Telefone.
- Domínio do website.
- Identificadores de perfis comerciais.

Separar a confiança de identidade da confiança de atividade operacional.

## 4. Situação operacional

Classificar empresas como:

- Atividade confirmada.
- Provavelmente ativa.
- Situação indeterminada.
- Possivelmente encerrada.
- Encerramento confirmado.
- Mudança de endereço identificada.
- Dados conflitantes.

Registrar fonte, evidência, data e nível de confiança de cada conclusão.

Não considerar ausência de resultados como encerramento.

## 5. Integração com CRM

Antes de criar oportunidades comerciais:

- Consultar dados de validação existentes.
- Evitar empresas duplicadas.
- Priorizar empresas com sinais confiáveis de operação.
- Encaminhar divergências para revisão.
- Suspender abordagens automáticas de estabelecimentos com encerramento confirmado.

Não eliminar automaticamente leads antigos nem sobrescrever contatos sem preservar histórico.

## 6. Interface de prospecção

Adicionar à listagem:

- Coluna Situação da empresa.
- Indicador de confiança.
- Data da última validação.
- Filtro por situação.
- Ação Validar empresa.
- Ação Validar selecionadas.

Na ficha do lead, criar a aba "Validação empresarial", apresentando fontes, evidências, divergências e histórico de verificações.

## 7. Eficiência

Implementar:

- Cache e prazo configurável de revalidação.
- Processamento em lotes.
- Filas com limites de execução.
- Consultas gratuitas primeiro.
- Controle de custos de APIs.
- Estratégia de novas tentativas.
- Registro de falhas sem bloquear o CRM.

A consulta Instagram deve usar exclusivamente meios autorizados.

## 8. Homologação

Validar com casos sintéticos:

- Empresa ativa.
- Empresa encerrada.
- Empresa que mudou de endereço.
- Empresa com nome comercial diferente da razão social.
- Empresas homônimas.
- CNPJ incompatível.
- Empresa sem presença digital.
- Empresa com dados conflitantes.

Respeitar o isolamento multitenant.

Documentar implementação, limitações, testes e custos em `executed.md`.

Não publicar automaticamente.

## Resultado esperado

O VetorLeads deverá transformar registros capturados pelo Maps em empresas verificadas, com evidências e grau de confiança, prontas para qualificação comercial quando apropriado.