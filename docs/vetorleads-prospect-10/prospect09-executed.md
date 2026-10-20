# VETORLEADS-PROSPECT-09 — ensaio MySQL e preparação final

Executado em **19/10/2026 UTC**. O `correio.md` mudou em relação ao PROSPECT-08 registrado em Git e no relatório anterior.

- SHA-256 anterior: `00c7f00a20abb3be555f249a18bc801834e378ab00327a62d4e2d16f9b3c19af`.
- SHA-256 executado: `c3f86c8fd22b5b95f4bb9f6c22589844fedc7f43b0e6d0cb76892eda3d8b526f`.

**Resultado:** ensaio das duas migrations aprovado em MySQL **8.4.11**, dados anteriores preservados, testes funcionais aprovados e imagem candidata com assets recompilados preparada localmente. Não houve push, deploy, migration operacional, reinicialização de serviço operacional, escrita comercial real ou contato externo nesta execução.

## Estado operacional encontrado

O relatório PROSPECT-08 descrevia 46 migrations e duas pendências. A leitura atual encontrou **48 migrations, incluindo as duas**, e as seis colunas presentes. Portanto, a informação de pendência estava desatualizada antes deste trabalho; esta execução não as aplicou em produção nem atribui autoria à aplicação anterior.

Auditoria por PDO em transação READ ONLY com snapshot consistente, sem bootstrap Laravel operacional. A leitura final confirma o schema e as contagens: **61 prospects, 0 vínculos CRM provenientes de prospects e 0 vendas ganhas**. Evidências: [leitura inicial](../vetorleads-prospect-09/runtime-read-only.json) e [leitura final](../vetorleads-prospect-09/runtime-final-read-only.json).

## Ensaio e preservação de dados

Foi extraído um dump consistente de todas as **41 tabelas** do banco VetorLeads, restaurado num MySQL exclusivo, em rede Docker interna sem porta publicada. O aplicativo usou usuário novo restrito aos bancos de ensaio e testes, sem credenciais operacionais. Dump e credenciais permanecem em diretório privado, ignorado pelo Git.

Como o snapshot atual já estava migrado, o schema anterior foi reconstruído **somente no clone**, removendo as seis colunas e os dois registros de migrations. Essa reconstrução não é um snapshot histórico completo anterior à implantação; as colunas antigas e seus dados vieram de uma única cópia consistente atual.

| Migration | Resultado MySQL | Duração do DDL reportada | Processo completo |
| --- | --- | --- | --- |
| Identificação WebChat, `2026_10_18_210000` | Aprovada | 49,11 ms | 0,702 s |
| Critérios comerciais, `2026_10_19_000000` | Aprovada | 92,31 ms | 0,709 s |

Comparação SHA-256 de todas as linhas nas colunas anteriores, ordenando os hashes por tabela: **40 tabelas comerciais/de apoio, 310 linhas, sem diferenças**. A tabela `migrations` foi excluída dessa comparação porque recebe os dois registros esperados. Preservadas as **2 conversas existentes** e os **61 prospects**, incluindo identidades e tenants. A cópia não continha produtos anteriores; o comportamento de produtos foi exercitado com fixtures sintéticas MySQL.

Colunas WebChat: `subject` varchar(24), `identified_at` e `whatsapp_consent_at` timestamp, todas nullable e default NULL. Critérios: `target_segments` e `excluded_segments` text nullable; `website_preference` varchar(16), NOT NULL, default `any`. Índices consultados e preservados pelas migrations aditivas, que não contêm alterações de índices. [Schema e índices](../vetorleads-prospect-09/schema-and-indexes.txt), [hashes antes](../vetorleads-prospect-09/data-before.json), [hashes depois](../vetorleads-prospect-09/data-after.json), [tempos](../vetorleads-prospect-09/migration-timings.json).

Um ensaio separado com transação leitora aberta numa tabela descartável demonstrou bloqueio por metadata lock: timeout configurado em 1 s, erro 1205 em aproximadamente 1,11 s. Liberada a transação, o ALTER passou. Os tempos sem contenção não garantem a mesma duração sob carga operacional. [Evidência](../vetorleads-prospect-09/metadata-lock-probe.json).

## WebChat, prospecção e correções de testes

O controller de visitantes utiliza as colunas identificadas; a suíte MySQL validou identificação, consentimento, conversa prévia à identificação, reaproveitamento de contato, token individual, isolamento de tenant e rejeição de contexto forjado. Testes do proxy VetorOS cobriram contrato e disponibilidade, com integrações simuladas. Isso não equivale a uma chamada ponta a ponta autorizada no ambiente operacional.

A primeira execução MySQL revelou seis falhas nos testes: ordem de chaves JSON normalizada pelo MySQL, auditoria aberta dentro da transação dos fixtures e uma fixture com data inválida que o MySQL rejeita. Foram corrigidas **somente as condições dos testes**: comparação por conteúdo JSON, transação de auditoria independente no MySQL com schema sintético renovado entre testes e JSON externo malformado para exercitar erro de processamento. A proteção READ ONLY continua comprovando rejeição de UPDATE e restauração do estado da sessão. Nenhuma regra comercial ou proteção da aplicação foi relaxada.

| Verificação | Resultado |
| --- | --- |
| Suíte completa MySQL 8.4 | **1.270 testes / 13.475 assertions** |
| Auditoria MySQL com isolamento final | **7 testes / 42 assertions** |
| Regressão completa SQLite com configuração final | **1.270 testes / 13.475 assertions** |
| VetorOS, proxy/disponibilidade WebChat | **5 testes / 17 assertions** |
| Extensão | **18 testes aprovados** |
| Pint | 5 arquivos aprovados |
| Imagem candidata | Build Docker/Wayfinder/frontend concluído; manifest extraído |
| App/worker/scheduler | PHP 8.4 e extensões conferidos; comandos de worker/scheduler disponíveis sem processar filas |
| Compose base e override de publicação | Configuração válida, sem aplicação |

A suíte completa MySQL passou antes do refinamento final do isolamento da auditoria; os sete testes afetados foram repetidos no MySQL com sucesso após esse refinamento, seguidos pela regressão SQLite completa. Os logs preservam também a primeira rodada MySQL com falhas para rastreabilidade. Não foi repetida homologação visual nesta etapa.

## Commits, artefatos e referências

- VetorLeads: base `adcc6e40f8025161882c95631f29cb69eab0bdba`; novo commit local **`00282e4ab046b4fc0f7bd0a5f33766255104df3e`** consolida a portabilidade dos testes.
- Imagem preparada: **`vetorleads:prospect09-00282e4`**, também disponível como `vetorleads:prospect09-candidate`.
- ID imutável: `sha256:4d9deab99bb7ab4d2ffbe660983cb668ca34a931ae34cd4e982d957d8181a733`.
- VetorOS encontrado e preservado: `953b90a825c76239055c29f90d77530770e6c2a5`. Sua mudança de ponteiro em relação à infraestrutura era preexistente e não foi publicada nem revertida.
- Infraestrutura base: `1165aa9bd35d8fa455ab100340d7cf1e20f78594`; relatório e evidências preparados localmente. Referências remotas não foram atualizadas nesta etapa; a comparação com upstream disponível é apenas local e precisa ser reconferida antes de push.

A imagem foi construída a partir das fontes atuais e recompilou os assets pelo Dockerfile. App, worker e scheduler devem consumir esse mesmo ID. Não foi substituída a imagem operacional nem escrito o volume de assets servido. **Divergência operacional preexistente:** app, worker e scheduler usam três IDs de imagem distintos; o worker registra sete reinícios acumulados. A causa e o estado funcional do worker não foram diagnosticados nesta etapa e precisam ser conferidos antes da publicação. O override candidato fixa a mesma imagem para os três processos, mas ainda não foi aplicado. IDs operacionais e data de inicialização estão em [versoes.json](../vetorleads-prospect-09/versoes.json); [manifest novo](../vetorleads-prospect-09/manifest.json), [build](../vetorleads-prospect-09/image-build.log) e [override preparado](../vetorleads-prospect-09/publication.override.yml).

O container exclusivo `prospect09-mysql` foi parado após o ensaio. Volume e snapshot privados foram preservados; nenhum serviço compartilhado foi parado. Não há descarte de dados comerciais.

## Publicação, homologação e recuperação

Procedimentos detalhados e reproduzíveis: [reproduzir ensaio](../vetorleads-prospect-09/reproduzir-ensaio.md) e [publicação/recuperação](../vetorleads-prospect-09/publicacao-e-recuperacao.md).

O roteiro de homologação limita-se a um tenant e até três empresas expressamente autorizadas: captura → recaptura preservando score → listagem/filtros → produto/segmentos → prévia/confirmação individual → conversão idempotente → histórico/dashboard. Deve comprovar bloqueio das automações de envio antes de criar/vincular leads. Nenhuma empresa real foi escolhida ou contatada nesta etapa.

A recuperação mantém as seis colunas aditivas, fixa as imagens anteriores dos três processos e restaura os assets preservados antes da janela. Não executar `migrate:rollback`: os métodos down removem dados. Havendo corrupção, restaurar o backup em outro banco, comparar por tenant/ID e propor reparação seletiva auditada, sem sobrepor produção ou apagar escritas posteriores.

## Pendências e autorização exata

**Preparação técnica concluída; publicação e aceite comercial operacional não executados.** Ainda dependem de autorização específica:

1. Push dos commits e aprovação dos ponteiros de infraestrutura, inclusive a alteração preexistente do VetorOS.
2. Janela e substituição apenas de VetorLeads/app/worker/scheduler pela imagem candidata. Não incluir serviços compartilhados nem implantar VetorOS implicitamente.
3. Tenant, operador, produto e até três empresas para escritas comerciais de homologação; comprovação prévia de que automações não emitirão mensagens/eventos externos.
4. Qualquer alteração operacional de automações, schema adicional ou recuperação seletiva de dados. As duas migrations deste correio já estão aplicadas: não há autorização nem necessidade de reaplicação.

Antes da janela, registrar tags de recuperação das imagens atuais e cópia dos assets servidos, produzir backup fresco e reconferir os remotos. A recuperação desta cópia foi demonstrada; a janela futura precisa do seu próprio ponto de recuperação. Contenção sob carga real e teste ponta a ponta operacional continuam limitados ao procedimento aprovado para produção.
