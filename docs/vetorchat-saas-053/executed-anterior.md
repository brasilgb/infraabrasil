# Execução — VETORCHAT-SAAS-05.1

Data: 22/10/2026. **Classificação: BLOQUEADO.** As correções independentes foram verificadas, mas não foi possível resolver o conflito do gitlink VetorPet: o `.git` do superprojeto é um ponto de montagem `ext4` somente leitura, fora do alcance de escrita desta sessão. Nenhuma permissão foi alterada e não houve deploy.

## Estado do Git

- Início e fim: `gateway/vetorpet` segue `UU` no superprojeto. Os estágios do índice permanecem intactos; não houve commit, push, reset, limpeza ou alteração de ponteiro.
- Submódulo VetorPet: checkout detached em `5ab9ab0`; `main` está em `5ab9ab0`, atrás de `origin/main` por 6 commits. O conflito do gitlink inclui as pontas `0321426` e `5ab9ab0`, com base `be43aee`.
- Alterações locais do widget preservadas no submódulo: `.env.example`, `config/services.php`, `resources/views/app.blade.php` e novo teste `tests/Feature/VetorChatInstallationTest.php`.
- Causa do bloqueio: `findmnt -T .git/modules/gateway/vetorpet` mostrou o ponto de montagem `.git` como `ext4 ro`; `test -w` falhou. A tentativa de merge anterior confirmou que o Git não consegue criar `ORIG_HEAD.lock`. Isso não é corrigível por `chmod` no diretório.
- Backup preventivo das alterações locais e dos estágios do índice: `docs/vetorchat-saas-051/vetorpet-local-changes.patch`, `VetorChatInstallationTest.php` e `vetorpet-unmerged-index.txt`; hashes em `checksums.txt`. O relatório e a instrução anteriores também foram arquivados nessa pasta.

Para retomar, um administrador do ambiente precisa tornar gravável o ponto de montagem (ou fornecer um checkout gravável). Com autorização administrativa e após confirmar o ponto de montagem, o comando esperado é:

```sh
sudo mount -o remount,rw /home/anderson/projects/laravel/infra-abrasil/.git
```

Depois verificar com `findmnt -T .../.git -o TARGET,OPTIONS`, conferir que o patch de backup corresponde às alterações e concluir uma mesclagem que preserve os dois históricos e o trabalho local. Não executei esse comando: esta sessão não tem privilégio para mudar a montagem e o `.git` está explicitamente fora da área gravável.

## VetorPet

- Revisei os arquivos de instalação: chave e URL do script vêm de `services.vetorchat`, e o snippet é limitado às páginas públicas, exige chave alfanumérica de 48 caracteres e URL HTTPS e marca `data-product="vetorpet"`.
- O teste adicionado cobre instalação na página pública e ausência do widget no login.
- `php -l gateway/vetorpet/config/services.php` e `git diff --check` dos arquivos alterados passaram.
- Testes Laravel não executáveis neste checkout: falta `vendor/autoload.php`. Dependências declaradas no `composer.json`; não instalei nenhuma.

## VetorOS

- Os 11 avisos anteriores eram iguais: dotenv reportava `gateway/vetoros/.env` ausente na criação da aplicação de teste. Não eram falhas das assertions.
- Repeti os dois testes do widget e os nove do proxy com uma `APP_KEY` sintética e um `.env` vazio temporário; **11 passaram, 34 assertions**. O arquivo foi removido ao final.

## Revisão técnica e de produto

- Os snippets já identificam os quatro produtos por valores distintos (`abrasil`, `vetoros`, `vetorleads`, `vetorpet`). A chave pública compartilhada do tenant ABrasil e sua configuração real na VPS não podem ser confirmadas sem consultar configuração de runtime; não li arquivos `.env` nem credenciais.
- O código revisado mantém chaves de widget e conexões WAHA por tenant; chave pública serve à resolução do widget e não dá acesso administrativo. Origens HTTPS são configuradas no painel do tenant. Esses controles têm testes locais anteriores no VetorLeads, mas não substituem homologação com domínios e sessões reais.
- A revisão anterior da inbox central e das respostas por canal continua válida; nenhuma duplicação de serviço ou código de aplicação foi necessária nesta etapa.
- Checklist de produção permanece: backup recuperável; migrations revisadas; build conferido; variáveis sem credenciais no repositório; chaves/domínios e produtos validados; sessão WAHA e HMAC por tenant; prova de widget → inbox → resposta por canal; idempotência/ACK/opt-out; rollback preparado. Nenhum desses passos foi executado na VPS.

## Pendências

- [ ] Disponibilizar `.git` gravável ou um checkout de trabalho gravável e concluir a mesclagem VetorPet, conferindo os dois históricos e as alterações locais.
- [ ] Com dependências já disponíveis em ambiente isolado, executar `VetorChatInstallationTest` do VetorPet.
- [ ] Confirmar a chave compartilhada e os quatro produtos do tenant ABrasil no ambiente autorizado sem expor a chave em relatório.
- [ ] Fazer homologação de navegador e WAHA em ambiente autorizado, com contatos sintéticos e sem mensagens reais.
- [ ] Planejar backup, migrations, build e rollback da VPS; publicar somente mediante autorização separada.

Nenhum código de produto, `.env` persistente, container ou configuração da VPS foi alterado. Nenhuma mensagem foi enviada. A entrega está bloqueada até a resolução do ponto de montagem e do conflito Git.
