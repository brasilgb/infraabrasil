# Execução de `correio.md`: VETOR-DEPLOY-01 — Push, build e validação na VPS — **BLOQUEADO**

- **Data:** 2026-10-08 (America/Sao_Paulo).
- **SHA-256 do `correio.md` executado:** `340281df6ed3f1520d3adfb7f05c160ed0b234bc419fb824b6d4cd703cac6788`.
- **SHA-256 da execução anterior:** `7a9e51278b89152316eaf6257621a3210ca1d85cafb795396c7ba0e85a214b98` (VETOR-HML-01.2). O arquivo mudou, então foi executado.

## Classificação: **BLOQUEADO**

O **push** (item 1) foi **negado pela política de permissões do ambiente** do Claude Code, classificado como publicação externa. As etapas seguintes dependem dele, porque a VPS precisa obter os commits aprovados do remoto. A tentativa seguinte, só de localizar o acesso SSH à VPS, foi negada pelo mesmo motivo.

Conforme o correio e a própria política, **nada foi contornado**:
- nenhum código enviado por outro meio;
- nenhuma conexão com a VPS;
- nenhum backup, build, migration ou troca de containers;
- **produção não foi tocada**.

## 1. Verificação de sincronização (feita, somente leitura)

`git fetch` nos dois repositórios: os dois estão **apenas à frente** do remoto, **nada atrás**. O push seria um *fast-forward* simples, sem force e sem sobrescrever nada.

| Repositório | Remoto | Branch | À frente | Atrás |
|---|---|---|---|---|
| `gateway/vetoros` | `github.com/brasilgb/vetoros` | `main` @ `4c4ce74c` | 9 | 0 |
| `infra-abrasil` | `github.com/brasilgb/infraabrasil` | `main` @ `b7c7782` | 4 | 0 |

**Atenção:** o push do `vetoros` publica **9 commits**, não só os 4 citados no correio. Os outros 5 são ancestrais de `4c4ce74c`, aprovados em etapas anteriores, mas nunca enviados:

```
4c4ce74c Ajusta layout das prioridades e permissões do técnico na OS (VETOR-HML-01.2)
0f8ce438 Adiciona auditoria somente leitura da APP_KEY (security:audit-app-key)
6ccff9af Corrige divergências da homologação VETOR-HML-01.1
7b54fd70 Adiciona atalhos de Caixa, PDV e Calendário junto às prioridades do dashboard (VETOR-UX-DASH-01)
7e9e005e Atualiza rodapé para ABrasil Sistemas — VetorOS e centraliza a versão em v2.0.0
8f46d37d Finaliza painel de indicadores do VETOR-INTEL-04
84355362 Implementa indicadores operacionais e comerciais do VETOR-INTEL-04
4fda449a Adiciona modelo e importação CSV de produtos (VETOR-PROD-IMPORT-01)
9c69101a Implanta o núcleo VETOR-INTEL: trilha da OS, integridade financeira, orçamento versionado e comunicação
```

Ou seja, o deploy leva para produção **todo o VETOR-INTEL** (trilha da OS, integridade financeira, orçamento versionado, comunicação, indicadores e importação CSV), com as **migrations** correspondentes.

**CI:** o push dispara apenas `gateway/vetoros/.github/workflows/ci.yml` (instalação, tipos e testes). **Não há deploy automático**, então o push sozinho não altera produção.

## 2–7. Não executados

| Etapa | Situação |
|---|---|
| 2. Preparação da VPS (estado, containers, backup, rollback) | ⛔ não executada (bloqueio) |
| 3. Auditoria da `APP_KEY` em produção | ⛔ não executada |
| 4. Build | ⛔ não executado |
| 5. Migrations | ⛔ não levantadas em produção |
| 6. Atualização de `vetoros`, `vetoros-worker` e `vetoros-scheduler` | ⛔ não executada |
| 7. Homologação (inclusive OS de serviço externo editada por técnico) | ⛔ não executada |

## Como prosseguir

**Opção A — você executa o push** (fast-forward, na ordem pedida):

```bash
cd ~/projects/laravel/infra-abrasil/gateway/vetoros && git push origin main
git branch -r --contains 4c4ce74c          # deve listar origin/main
cd .. && git push origin main               # infra-abrasil (ponteiro → 4c4ce74c)
```

Depois, para as etapas na VPS, é preciso **autorização explícita do acesso** (host e usuário SSH), ou que você as execute seguindo o correio.

**Opção B — liberar a permissão** de push e de SSH à VPS para o Claude Code nesta sessão (regra nas configurações) e pedir a reexecução.

**Ordem obrigatória na VPS, quando houver acesso**, conforme o correio:
1. estado atual e containers;
2. **backup verificável** do banco e dos dados persistentes;
3. `security:audit-app-key --verify-hash` sem substituir a aplicação ativa. **Parar se houver ilegíveis.** No banco local, 664 de 664 chaves públicas e 4 de 4 senhas SMTP não decifram com a chave local;
4. revisar as migrations INTEL pendentes **antes** de trocar containers;
5. build;
6. troca só dos 3 serviços VetorOS;
7. homologação.

## Pendências

1. Push dos dois repositórios: por você ou com permissão liberada.
2. Acesso autorizado à VPS para as etapas 2 a 7.
3. **`gateway/desgarrados`** aparece com alterações locais no repositório principal. Não são desta execução e não foram tocadas.
4. **VETOR-INTEL-05:** não iniciado.
