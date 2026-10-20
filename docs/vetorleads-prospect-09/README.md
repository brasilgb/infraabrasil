# Evidências PROSPECT-09

Resultado principal: [executed.md](../../executed.md).

- Ensaio: snapshot completo restaurado em MySQL 8.4.11, 41 tabelas; hashes das 40 tabelas sem o ledger de migrations preservados.
- Testes: logs MySQL, SQLite, WebChat e extensão; a rodada inicial MySQL contém falhas corrigidas posteriormente.
- Publicação: versões, manifest, build e override validado, sem deploy.
- Snapshot e credenciais estão fora do Git em `staging/prospect09/`, com acesso restrito.
- O passo a passo e o plano de recuperação estão neste diretório.
