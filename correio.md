# VETOR-INTEL-04.2 — Aprovação da segunda entrega e preparação para homologação

A segunda entrega do VETOR-INTEL-04 está **APROVADA**.

Pode prosseguir com o commit controlado das alterações atualmente pendentes no submódulo `gateway/vetoros`.

## 1. Commit da segunda entrega

Criar um commit contendo exclusivamente os arquivos pertencentes à segunda entrega do VETOR-INTEL-04:

- `app/Http/Controllers/App/DashboardController.php`
- `app/Http/Controllers/App/IntelIndicatorController.php`
- `app/Services/Intel/OperationalIndicatorsService.php`
- `resources/js/pages/app/intel/indicators.tsx`
- `resources/js/pages/app/dashboard/ope-order/index.tsx`
- `resources/js/Utils/navLinks.ts`
- `tests/Feature/App/IntelIndicatorsPageTest.php`
- `tests/Feature/App/OperationalIndicatorsTest.php`
- `docs/architecture/vetor-intel-04-indicadores.md`

Mensagem sugerida:

`Finaliza painel de indicadores do VETOR-INTEL-04`

Não fazer push nem deploy.

## 2. Validação após o commit

Após o commit, executar novamente:

- suíte PHP completa;
- `npx tsc --noEmit`;
- Pint nos arquivos PHP modificados;
- verificações de lint/format necessárias.

Problemas preexistentes já documentados em arquivos não alterados por esta entrega não devem ser corrigidos nesta etapa.

Registrar:

- hash do novo commit;
- quantidade de testes;
- quantidade de asserções;
- resultado do TypeScript;
- estado final do Git.

## 3. Repositório principal

Após o commit no submódulo, atualizar o ponteiro de `gateway/vetoros` no repositório principal `infra-abrasil`.

Pode também incluir no commit principal:

- atualização do ponteiro do submódulo;
- `correio.md`;
- `executed.md`.

Não incluir alterações estranhas ao escopo.

Criar commit local no repositório principal, mas:

**NÃO FAZER PUSH.**  
**NÃO FAZER DEPLOY.**  
**NÃO EXECUTAR MIGRATIONS EM PRODUÇÃO.**

## 4. Não alterar as regras aprovadas

Preservar integralmente as decisões já homologadas:

- OS atrasada deve continuar sendo avaliada contra o prazo original;
- renegociação deve aparecer como contexto, sem apagar o atraso original;
- técnico histórico somente quando houver atribuição registrada na trilha;
- não atribuir retroativamente o técnico atual;
- produtividade não deve gerar ranking simplista;
- rentabilidade só pode considerar OS com dados suficientes;
- dados `null`/não calculáveis não podem aparecer como zero;
- isolamento por tenant deve permanecer;
- acesso aos Indicadores continua por `reports.view`;
- dashboard continua respeitando o escopo individual do usuário;
- WAHA, Spedy e demais integrações não fazem parte desta etapa.

## 5. Preparar homologação visual

Não é necessário implementar novos recursos agora.

Apenas deixar documentado no `executed.md` um roteiro curto para homologação manual da tela `/app/intel/indicators`, cobrindo pelo menos:

### Desktop
- menu Geral → Indicadores;
- filtros e atalhos;
- cards do resumo;
- Qualidade dos dados;
- tabelas;
- links para OS;
- estados vazio, carregando e erro;
- legibilidade de valores e textos.

### Mobile
- largura dos cards;
- tabelas/scroll;
- filtros;
- textos;
- botões;
- ausência de overflow ou elementos cortados.

### Dados reais
Conferir manualmente algumas OS conhecidas para validar:

- OS parada;
- OS atrasada;
- orçamento aguardando;
- orçamento vencido;
- técnico atribuído;
- OS sem trilha histórica;
- prazo original e renegociado;
- rentabilidade completa e incompleta.

## 6. Próxima fase

Não iniciar automaticamente um VETOR-INTEL-05.

Depois do commit e da validação, retornar com o `executed.md` para revisão.

O objetivo agora é **fechar tecnicamente o VETOR-INTEL-04 e homologar a experiência real de uso antes de ampliar o módulo inteligente**.