# Roteiro de publicação e homologação real — preparado, não executado

A autorização desta etapa permite testes sintéticos, leitura operacional e commits locais. **Push, deploy, migrations operacionais, reinicialização de serviços e escrita de registros reais não foram executados.** As etapas abaixo exigem autorização futura para os atos específicos.

## Pré-requisitos de publicação

1. Revisar os commits locais do VetorLeads, o commit existente do VetorOS e os ponteiros registrados na infraestrutura. Conferir a referência remota novamente antes de qualquer publicação; resolver divergências preservando o histórico, sem reset destrutivo.
2. Escolher versões identificáveis das imagens atuais para reversão; preservar também o build servido e sua manifestação de assets. O repositório contém um `public/build` histórico: o build validado aqui foi gerado em ambiente isolado, e uma imagem futura deve recompilar as fontes pelo Dockerfile. Não publicar o build histórico como se fosse o atual.
3. Criar backup consistente e testar restauração em banco isolado com a mesma versão MySQL 8.4. As duas migrations passaram no SQLite sintético, mas a aplicação/rollback de DDL em MySQL, locks e duração ainda exigem ensaio no clone; o teste SQLite não demonstra esses aspectos.
4. No clone, aplicar separadamente a migration de identificação WebChat e a migration de critérios comerciais; confirmar preservação das linhas e os defaults. Testar o aplicativo/worker/scheduler com a imagem candidata e o frontend reconstruído.
5. Conferir por presença, sem copiar segredos para relatórios: liberação comercial, token/tenant do WebChat, autenticação do proxy VetorOS, credenciais por tenant da extensão, configuração WAHA e assinatura dos eventos n8n. Não emitir eventos ou mensagens como teste implícito.

## Dependências verificadas

| Migration | Dependências e consumidores | Situação atual |
| --- | --- | --- |
| `2026_10_18_210000_add_identification_to_web_conversations` | Tabela `web_conversations`; adiciona `subject`, `identified_at`, `whatsapp_consent_at`. Consumida por identificação na API, listagens da Central e widget/proxy do VetorOS. | Pendente; as três colunas estão ausentes. O hash do controller WebChat no runtime coincide com o local. O caminho de identificação pode falhar por incompatibilidade de schema; não foi invocado em produção. |
| `2026_10_19_000000_add_commercial_criteria_to_prospect_products` | Tabela `prospect_products`; adiciona textos opcionais `target_segments`, `excluded_segments` e `website_preference` com default `any`. Consumida pelo catálogo, qualificador v3 e hash da prévia. | Pendente; campos ausentes em produção. Aplicada apenas nos testes sintéticos anteriores. Publicar o cadastro novo sem a migration faria as gravações falharem. |

Não há dependência direta de uma migration sobre a outra; ambas dependem das tabelas antigas já aplicadas. Como o Laravel executa todas as migrations pendentes em ordem, não usar um `migrate` amplo sem revisão das duas. Nenhum comando de migração é fornecido como ação já autorizada.

## Ordem proposta após autorização

1. Aprovar janela, backup/restauração e plano de reversão; delimitar serviços e alterações de schema.
2. Construir e testar a imagem candidata fora do runtime, incluindo PHP 8.4, GD JPEG/WebP, Wayfinder e assets. Conferir manifests e rotas; preservar assets anteriores durante a transição, pois o entrypoint copia o build para um volume compartilhado.
3. Aplicar apenas as migrations aprovadas no schema correto, após o ensaio MySQL. Verificar colunas e tabela de migrations antes de disponibilizar consumidores novos. Considerar a restrição do caminho WebChat já incompatível no runtime.
4. Publicar de forma coordenada somente VetorLeads e seus worker/scheduler, garantindo a mesma versão de código/schema. Tratar a versão do proxy/widget VetorOS em etapa coordenada própria, após a API estar compatível. Não reiniciar MySQL, WAHA, Redis, n8n, nginx global ou outros produtos por conveniência.
5. Validar leitura das páginas e saúde funcional. `php-fpm -t`/healthcheck verde não demonstra compatibilidade entre código e banco nem exercício do WebChat.
6. Somente com autorização específica da amostra, executar o roteiro real abaixo. Sem essa autorização, encerrar após verificações de leitura e registrar a homologação como pendente.

## Amostra real: autorização necessária antes de qualquer escrita

Proposta de limite: **até três empresas**, em **um tenant identificado** e em **um lote**. A autorização deve fixar tenant, operador, produto, empresas/URLs da fonte autorizada, campos permitidos, operações de recaptura, qualificação e conversão, além de quem revisará os resultados. Não escolher empresas reais por conta própria.

Antes de testar: registrar de forma privada os identificadores e contagens existentes; verificar duplicatas, oposição e vínculos. Dados de contato reais não devem entrar em screenshots públicos ou logs deste repositório. Revisar automações do tenant e sua configuração para garantir que criar/vincular leads não dispare contatos ou eventos externos não autorizados. Não modificar configurações operacionais como parte implícita do teste.

| Passo real autorizado | Verificação e escrita esperada |
| --- | --- |
| Capturar as empresas aprovadas pela extensão | Token pertence ao tenant; uma empresa por identidade; nome/atividade/contatos/localização somente quando fornecidos; origem e lote registrados. Nenhum lead criado automaticamente. |
| Recapturar uma empresa da amostra | Empresa não duplica; apenas lacunas são preenchidas; oposição, seleção, favorito, vínculo CRM e qualificação existente são preservados. Novo lote/fonte atualizados conforme o contrato. |
| Comparar produto correto e incompatível na prévia | Comparações não persistem qualificação. Verificar justificativas e prioridade; produto incorreto/exclusão não pode receber selo comercial favorável somente por telefone/site completos. |
| Confirmar uma qualificação | Uma qualificação do produto/prospect aprovado é gravada após prévia; dados ou critérios alterados invalidam a confirmação. Não há requalificação em massa. |
| Selecionar e converter uma empresa aprovada | Um lead criado ou vínculo ao existente; dados comerciais anteriores preservados; nota de origem/produto/score no histórico. Repetir a conversão não cria outro lead ou nota. |
| Dashboard e isolamento | Contagens refletem captura/vínculo e deduplicação; conversão para CRM não vira venda. Outro tenant não acessa prospect, produto ou lote. |
| Encerrar | Registrar resultado privado, eventuais divergências e decisão do responsável; deixar registros reais preservados. Sem exclusão/rollback de dados comerciais não autorizado. |

Não enviar WhatsApp, e-mail, campanha, follow-up, evento de automação de teste ou contato comercial. Não usar “Verificar site/CNPJ” ou IA na amostra sem autorização específica para consultas externas e custos; o fluxo principal é local.

## Reversão e interrupção

Interromper a homologação diante de erro de tenant, duplicação, perda de dados, envio externo inesperado ou métricas inconsistentes. Preservar logs sanitizados e identificadores para análise. A reversão de imagem/build deve usar versões previamente registradas e serviços delimitados. Colunas aditivas podem permanecer durante reversão do aplicativo após análise de compatibilidade; **não executar `migrate:rollback` automaticamente**, pois ele remove campos e dados. Correções ou remoções de registros comerciais exigem autorização própria e rastreabilidade.

## Critério de aceite real

Aceite somente após o responsável demonstrar e registrar a jornada inteira com a amostra autorizada no ambiente publicado, sem perda de dados, vazamento entre tenants, contato externo ou conversão confundida com venda. Esta execução demonstra a jornada sintética e prepara o roteiro; não declara esse aceite operacional.
