# VETOR-INTEL-04.3 — Encerramento e preparação para homologação

**Status:** Autorizado.

## 1. Encerrar o Git

- Confirmar o commit `8f46d37d` do VetorOS.
- Confirmar o commit do repositório principal `infra-abrasil`.
- Verificar que o ponteiro do submódulo está correto.
- Não realizar push ou deploy.

## 2. Finalizar rodapé e versão

Autorizar commit independente para:

- `resources/js/components/app-footer.tsx`
- `.env.example`

O rodapé deverá apresentar:

**ABrasil Sistemas — VetorOS | v2.0.0**

O nome ABrasil Sistemas deverá apontar para `https://abrasilsistemas.com.br`.

Verificar a possibilidade de centralizar a versão, mantendo compatibilidade com o frontend e os ambientes existentes.

Não alterar regras comerciais, fiscais ou operacionais.

## 3. Homologação

Preservar o roteiro de homologação do INTEL-04.2.

Verificar desktop, tablet, celular, permissões, filtros, indicadores, integridade dos dados e comportamento do dashboard.

Toda divergência encontrada deverá ser documentada antes de qualquer correção.

## 4. Restrições

- Não iniciar VETOR-INTEL-05.
- Não realizar push.
- Não realizar deploy.
- Não executar migrations em produção.
- Não modificar WAHA ou Spedy.
- Não introduzir funcionalidades fora deste escopo.

## 5. Entrega

Registrar no `executed.md`:

- commits e hashes;
- arquivos alterados;
- testes executados;
- estado do Git;
- pendências de homologação.

Encerrar e aguardar avaliação antes da próxima etapa.