# VetorPet — erro 500 ao cadastrar produto com imagem

Data: 2026-10-19.

Causa confirmada nos logs de produção às 19:36:07 e 19:37:21 UTC: `League\Flysystem\UnableToCreateDirectory`, ao criar `/var/www/html/storage/app/public/products` durante o upload do produto. O processo PHP usa UID/GID 33 (`www-data`); o bind mount público pertencia a UID/GID 0 (`root`) com modo 0755. A pasta `products` não existia.

Correção operacional aplicada somente aos diretórios de upload: criação de `products` e ajuste de proprietário do diretório público e de `products` para `www-data:www-data`, mantendo 0755. Nenhum arquivo de cliente foi removido ou modificado, nenhum produto foi criado/alterado e nenhum serviço foi reiniciado. A mudança de proprietário no bind mount persiste nas reinicializações atuais.

Validação: o mesmo método usado pelo cadastro (`UploadedFile::store('products', 'public')`) foi executado como UID 33 com um PNG sintético. Gravação, leitura e comparação do conteúdo passaram; o arquivo temporário foi removido. Nenhuma operação de banco foi executada pelo verificador. Resultado em `upload-probe.json`.

Prevenção adicionada localmente em `gateway/vetorpet/docker-entrypoint.sh`: criar a pasta e garantir o proprietário dos dois diretórios após montar o volume. `sh -n` e `git diff --check` passaram. Esse ajuste do entrypoint está preparado para a próxima imagem; não houve build/deploy de imagem, commit ou push nesta correção. A correção operacional já está aplicada no volume em uso.

Não foi repetido o cadastro com dados comerciais. A validação cobriu precisamente a operação de upload que gerava a exceção.
