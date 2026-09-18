# Projeto `infra-abrasil` — Consolidar ecossistema Docker completo

Trabalhe exclusivamente dentro do projeto:

```text
infra-abrasil/
```

Estrutura base atual:

```text
infra-abrasil/
├── docker-compose.yml
├── .env
├── nginx/
│   └── conf.d/
├── gateway/
│   ├── vetoros/
│   ├── vetorpet/
│   └── abrasilsistema/
├── waha/
├── volumes/
└── scripts/
```

Os três sistemas já estão localizados dentro de:

```text
infra-abrasil/gateway/
```

São eles:

```text
gateway/vetoros
gateway/vetorpet
gateway/abrasilsistema
```

O objetivo é transformar `infra-abrasil` em uma stack Docker organizada, integrada e preparada para futuramente ser enviada para uma VPS Linux.

Neste momento:

* trabalhar localmente;
* validar tudo via Docker Compose;
* não fazer deploy em nuvem;
* não alterar regras funcionais dos sistemas sem necessidade;
* não misturar código entre os três projetos.

---

# 1. Premissas já definidas

Considere como decisões já tomadas:

## Backend

Os sistemas são Laravel/PHP.

Cada aplicação deve ter ambiente PHP adequado à sua versão real.

Audite a versão necessária antes de fixar imagem.

## Frontend

Os projetos Laravel utilizam Node para build dos assets.

Node deve estar disponível no processo de build.

Não utilizar servidor de desenvolvimento Vite em produção.

Preferir build dos assets durante a construção da imagem.

## Banco

O banco definido é:

```text
MySQL
```

Não considerar PostgreSQL.

Não migrar para outro banco.

## WhatsApp

WAHA será o motor compartilhado de WhatsApp.

Ele deve ficar na raiz de:

```text
infra-abrasil/waha/
```

WAHA não pertence exclusivamente ao VetorOS.

Será compartilhado por:

```text
VetorOS
VetorPet
ABrasil Sistemas
```

---

# 2. Primeiro faça auditoria dos três sistemas

Antes de alterar a infraestrutura, analise:

```text
gateway/vetoros
gateway/vetorpet
gateway/abrasilsistema
```

Para cada projeto descubra:

* versão Laravel;
* versão PHP necessária;
* versão Node necessária;
* gerenciador de pacotes (`npm`, `pnpm` ou `yarn`);
* banco/configuração MySQL;
* uso de Redis;
* uso de queues;
* scheduler;
* geração de PDF;
* GD/Imagick;
* uploads;
* storage persistente;
* websocket, se existir;
* comandos de build;
* comandos de produção;
* extensões PHP necessárias;
* dependências do sistema operacional;
* variáveis `.env`;
* workers;
* cron;
* integrações externas.

Não assumir que os três são idênticos.

---

# 3. Não alterar regras de negócio

Esta tarefa é de infraestrutura.

Não:

* alterar módulos funcionais;
* reestruturar domínio;
* refazer migrations sem necessidade;
* alterar multitenancy;
* modificar autenticação;
* mudar regras de clientes, OS, vendas, financeiro etc.;
* trazer arquitetura de outro projeto.

Pequenas mudanças necessárias para containerização são permitidas, mas devem ser documentadas.

---

# 4. Arquitetura desejada

O ecossistema deve ficar conceitualmente assim:

```text
                           NGINX
                             │
             ┌───────────────┼───────────────┐
             │               │               │
             ▼               ▼               ▼
          VetorOS         VetorPet      ABrasil Sistemas
             │               │               │
             └───────────────┼───────────────┘
                             │
                             ▼
                            WAHA
                             │
                             ▼
                          WhatsApp
```

E com infraestrutura compartilhada onde fizer sentido:

```text
MySQL
Redis, se necessário
workers
scheduler
volumes
```

---

# 5. WAHA deve ficar fora do VetorOS

Localize qualquer configuração antiga do WAHA ainda existente dentro de:

```text
gateway/vetoros/
```

Incluindo:

* docker-compose interno;
* volumes;
* scripts;
* `.env`;
* configurações Nginx;
* arquivos WAHA;
* referências de container;
* paths;
* documentação;
* serviços.

Mova para:

```text
infra-abrasil/waha/
```

somente o que realmente pertence ao WAHA.

Depois ajuste toda a infraestrutura para que o serviço seja controlado pelo:

```text
infra-abrasil/docker-compose.yml
```

Não deixar uma segunda instalação concorrente dentro do VetorOS.

---

# 6. Serviço WAHA central

Criar um serviço Docker claro:

```yaml
services:
  waha:
```

Requisitos:

* container próprio;
* persistência de sessão;
* API key via `.env`;
* healthcheck, se suportado;
* `restart: unless-stopped`;
* acesso pela rede Docker;
* logs limitados;
* não expor dados sensíveis;
* não depender do VetorOS para subir.

Os sistemas devem conseguir acessar internamente por:

```text
http://waha:<porta>
```

Nunca utilizar:

```text
localhost
127.0.0.1
```

entre containers.

---

# 7. Persistência WAHA

A sessão do WhatsApp deve sobreviver a:

```bash
docker compose restart
```

e recriações normais da stack.

Utilize:

```text
infra-abrasil/volumes/waha/
```

ou named volume equivalente.

Não manter sessão em filesystem efêmero do container.

Não apagar volumes existentes automaticamente.

Se houver sessões antigas dentro do VetorOS, avaliar migração antes de remover.

---

# 8. Separação das sessões WhatsApp

Preparar o ecossistema para nomes de sessão distintos por aplicação e cliente.

Padrão recomendado:

```text
vetoros_<tenant>
vetorpet_<tenant>
abrasilsistema_<tenant>
```

ou equivalente seguro.

A responsabilidade de montar esse identificador pertence às aplicações ou ao gateway lógico.

Docker apenas deve suportar isso.

---

# 9. Redes Docker

Criar uma rede compartilhada:

```text
infra-abrasil-network
```

ou nome equivalente.

Deve permitir:

```text
nginx → vetoros
nginx → vetorpet
nginx → abrasilsistema

vetoros → mysql
vetorpet → mysql
abrasilsistema → mysql

vetoros → waha
vetorpet → waha
abrasilsistema → waha

apps → redis
```

quando aplicável.

---

# 10. Nginx

O Nginx deve ser o reverse proxy central.

Estrutura recomendada:

```text
nginx/
├── nginx.conf
└── conf.d/
    ├── vetoros.conf
    ├── vetorpet.conf
    ├── abrasilsistema.conf
    └── waha.conf
```

No ambiente local, configurar hosts claros, por exemplo:

```text
vetoros.localhost
vetorpet.localhost
abrasilsistema.localhost
```

WAHA pode ter host próprio para administração/teste, se necessário.

Não hardcodar domínios reais de produção ainda.

---

# 11. Preparar para HTTPS futuro

Não é obrigatório emitir certificados agora.

Mas deixar a configuração pronta para futuramente usar:

```text
Let's Encrypt
Certbot
```

ou solução equivalente.

Documentar onde entrarão os certificados.

---

# 12. Containers Laravel

Cada aplicação deve ter seu próprio serviço.

Exemplo conceitual:

```text
vetoros
vetorpet
abrasilsistema
```

Cada uma deve usar PHP compatível com seu projeto.

Não forçar versão PHP única se houver incompatibilidade.

---

# 13. Node para build

Os três projetos Laravel devem ter Node disponível no processo de build quando necessário.

Preferir Dockerfile multi-stage:

```text
stage node
    npm/pnpm/yarn install
    build frontend

stage php
    composer install
    copiar assets compilados
```

Não executar servidor Vite em produção.

Não deixar `npm run dev` como dependência de runtime.

---

# 14. Composer

Instalar dependências PHP com Composer.

Para imagem de produção:

```text
composer install --no-dev --optimize-autoloader
```

ou equivalente compatível.

Não executar Composer toda vez que o container subir.

---

# 15. Extensões PHP

Auditar e instalar somente o necessário.

Exemplos comuns:

```text
pdo_mysql
mbstring
bcmath
intl
zip
gd
exif
opcache
pcntl
redis
```

Instalar Imagick somente se algum projeto realmente precisar.

---

# 16. PHP-FPM ou servidor apropriado

Organizar o runtime adequadamente.

Não utilizar:

```bash
php artisan serve
```

como solução definitiva de produção se houver Nginx + PHP-FPM disponível.

Preferir:

```text
Nginx → PHP-FPM
```

ou arquitetura equivalente consistente.

---

# 17. MySQL

O banco deve ser MySQL.

Avalie a melhor estratégia para os três sistemas.

Preferência:

```text
um serviço MySQL
```

com databases separados:

```text
vetoros
vetorpet
abrasilsistema
```

desde que isso seja seguro e adequado.

Não misturar tabelas dos três projetos no mesmo database.

Criar usuários distintos por aplicação, se possível:

```text
vetoros_user
vetorpet_user
abrasilsistema_user
```

Cada usuário deve ter acesso somente ao seu database.

Se houver razão técnica forte para instâncias MySQL separadas, documentar.

---

# 18. Persistência MySQL

Usar volume persistente.

Exemplo:

```text
volumes/mysql/
```

ou named volume.

Nunca armazenar dados do MySQL apenas dentro do container.

---

# 19. Redis

Audite se os projetos realmente utilizam Redis.

Se sim, pode haver um serviço compartilhado:

```text
redis
```

Mas configurar isolamento por:

* prefixo;
* database;
* nome de aplicação.

Evitar colisão de:

```text
cache
session
queues
locks
```

Se compartilhamento não for seguro, criar Redis separado.

---

# 20. Queue workers

Se algum projeto usa Laravel Queue:

Criar containers separados reutilizando a mesma imagem.

Exemplo:

```text
vetoros-worker
vetorpet-worker
abrasilsistema-worker
```

Executando algo equivalente a:

```bash
php artisan queue:work
```

Configurar:

* timeout;
* retries;
* sleep;
* stopwait;
* restart;
* conexão correta.

Não criar worker para aplicação que não utiliza fila.

---

# 21. Scheduler

Se houver tarefas agendadas Laravel:

Criar scheduler adequado.

Exemplo:

```text
vetoros-scheduler
vetorpet-scheduler
abrasilsistema-scheduler
```

ou estratégia central de cron.

Não executar scheduler onde não existir necessidade.

---

# 22. Storage Laravel

Garantir permissões corretas para:

```text
storage/
bootstrap/cache/
```

Não usar:

```text
chmod 777
```

como solução permanente.

Configurar usuário/grupo adequadamente.

---

# 23. Uploads

Auditar onde cada projeto grava:

* imagens;
* anexos;
* PDFs;
* documentos;
* uploads de usuários.

Persistir apenas diretórios necessários.

Não persistir toda a aplicação.

---

# 24. Volumes

Organizar volumes aproximadamente assim:

```text
volumes/
├── mysql/
├── waha/
├── vetoros/
├── vetorpet/
└── abrasilsistema/
```

Dentro de cada aplicação, persistir apenas os dados necessários, por exemplo:

```text
storage/app/public
uploads
```

---

# 25. `.env` da infraestrutura

O arquivo:

```text
infra-abrasil/.env
```

deve controlar infraestrutura comum.

Exemplos:

```env
MYSQL_ROOT_PASSWORD=
MYSQL_PORT=

WAHA_API_KEY=
WAHA_PORT=

REDIS_PORT=
```

Não colocar secrets reais no repositório.

Criar:

```text
.env.example
```

com todas as variáveis necessárias.

---

# 26. `.env` das aplicações

Cada aplicação pode manter seu próprio `.env`.

Atualizar para usar hostnames Docker.

Exemplo:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
```

com seu database específico.

Para WAHA:

```env
WAHA_BASE_URL=http://waha:3000
```

ajustando porta real.

---

# 27. Nomes de databases

Sugestão:

```text
vetoros
vetorpet
abrasilsistema
```

Não inventar outro padrão sem necessidade.

---

# 28. Healthchecks

Adicionar healthcheck para:

```text
mysql
redis
waha
vetoros
vetorpet
abrasilsistema
```

quando tecnicamente possível.

`depends_on` deve considerar health status onde fizer sentido.

Não confiar somente na ordem dos containers.

---

# 29. Restart policies

Para serviços de runtime, usar:

```yaml
restart: unless-stopped
```

ou equivalente.

Não aplicar cegamente em jobs de build ou init.

---

# 30. Logs

Configurar rotação de logs do Docker.

Exemplo:

```yaml
logging:
  options:
    max-size: "10m"
    max-file: "3"
```

ou equivalente.

Evitar crescimento ilimitado.

---

# 31. Dados sensíveis

Não registrar em logs:

* senhas;
* API keys;
* QR Code WAHA;
* cookies;
* tokens;
* dados de sessão;
* secrets;
* credenciais MySQL.

---

# 32. Portas

Na futura VPS, o ideal é expor publicamente somente:

```text
80
443
```

MySQL, Redis e WAHA devem ficar internos sempre que possível.

No local, portas extras podem ser publicadas para debug, mas documentar.

---

# 33. Docker Compose

O arquivo principal deve ser:

```text
infra-abrasil/docker-compose.yml
```

Evitar múltiplos Compose concorrentes controlando a mesma infraestrutura.

Compose internos dos projetos podem permanecer apenas se houver razão de desenvolvimento isolado, mas a stack consolidada deve ser controlada pela raiz.

---

# 34. Dockerfiles

Criar Dockerfile adequado para cada aplicação.

Se forem realmente compatíveis, pode haver base compartilhada.

Mas não sacrificar compatibilidade para reduzir arquivos.

---

# 35. Build frontend

Para cada aplicação:

1. instalar dependências Node;
2. compilar assets;
3. copiar assets finais para imagem PHP;
4. não carregar `node_modules` no runtime se não for necessário.

---

# 36. Cache Laravel

Preparar para ambiente de produção.

Ao construir ou inicializar corretamente, considerar:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

somente se o projeto suportar sem problemas.

Não mascarar erros de configuração.

---

# 37. Migrations

Não rodar migrations destrutivas automaticamente a cada restart.

Documentar comandos manuais:

```bash
docker compose exec vetoros php artisan migrate
docker compose exec vetorpet php artisan migrate
docker compose exec abrasilsistema php artisan migrate
```

ou nomes reais dos serviços.

---

# 38. Seeders

Não executar seeders automaticamente em produção.

Somente documentar quando necessário.

---

# 39. Scripts auxiliares

Criar scripts simples se ajudar:

```text
scripts/
├── up.sh
├── down.sh
├── rebuild.sh
├── logs.sh
└── status.sh
```

Exemplo:

```bash
docker compose up -d --build
```

Não criar automação excessiva.

---

# 40. Desenvolvimento local

O ambiente deve conseguir subir com:

```bash
docker compose up -d --build
```

Depois validar:

```bash
docker compose ps
```

---

# 41. Não depender de Windows/WSL

A stack final deve funcionar em Linux Docker Engine.

Não usar:

* paths Windows;
* scripts `.bat`;
* dependências específicas de WSL;
* paths absolutos do computador atual.

---

# 42. WAHA compartilhado

Os três sistemas devem poder consumir a mesma instância WAHA.

Fluxo:

```text
VetorOS ──────────┐
                  │
VetorPet ─────────┼──> WAHA
                  │
ABrasil Sistemas ─┘
```

Não criar três containers WAHA sem necessidade.

---

# 43. Gateway próprio futuro

Prepare a infraestrutura para futuramente adicionar:

```text
WhatsApp Gateway próprio
```

entre as aplicações e WAHA:

```text
VetorOS
VetorPet
ABrasil Sistemas
       ↓
WhatsApp Gateway
       ↓
WAHA
```

Mas não criar complexidade desnecessária nesta etapa se ainda não houver necessidade.

---

# 44. VetorOS

No VetorOS:

* remover dependência de WAHA interno;
* ajustar URL para WAHA compartilhado;
* manter integração atual funcionando;
* não alterar regras funcionais.

---

# 45. VetorPet

Deixar infraestrutura preparada para consumir WAHA.

Não implementar funcionalidades WhatsApp completas se ainda não existirem.

Apenas garantir conectividade e configuração.

---

# 46. ABrasil Sistemas

O nome correto é:

```text
ABrasil Sistemas
```

O diretório é:

```text
gateway/abrasilsistema
```

Não utilizar nomes:

```text
abrasilweb
ABrasilWeb
```

Deixar infraestrutura preparada para WAHA da mesma forma que os demais.

---

# 47. Nginx local

Criar arquivos separados.

Exemplo:

```text
vetoros.conf
vetorpet.conf
abrasilsistema.conf
waha.conf
```

Configurar upstreams usando nomes dos serviços Docker.

---

# 48. Produção futura

A stack deve poder ser levada para uma VPS apenas com:

* clone/cópia do projeto;
* preenchimento do `.env`;
* DNS;
* certificados;
* volumes;
* `docker compose up -d --build`.

Evitar configurações manuais desnecessárias.

---

# 49. Backup futuro

Não precisa implementar sistema completo agora, mas organizar volumes para facilitar backup de:

```text
MySQL
uploads
WAHA sessions
```

---

# 50. Validação obrigatória

Após ajustes executar, quando possível:

```bash
docker compose config
docker compose build
docker compose up -d
docker compose ps
```

Também verificar logs:

```bash
docker compose logs --tail=100
```

dos serviços com problema.

---

# 51. Validar MySQL

Confirmar:

* container healthy;
* databases existentes;
* usuários corretos;
* aplicações conectando;
* isolamento entre databases.

---

# 52. Validar WAHA

Confirmar:

* container healthy;
* API acessível internamente;
* volume persistente;
* reinício não remove sessão;
* VetorOS consegue acessar;
* VetorPet consegue resolver hostname;
* ABrasil Sistemas consegue resolver hostname.

---

# 53. Validar Nginx

Confirmar acesso local aos três hosts configurados.

Exemplo:

```text
vetoros.localhost
vetorpet.localhost
abrasilsistema.localhost
```

Se `.localhost` não funcionar no ambiente, usar solução equivalente e documentar.

---

# 54. Validar Node/build

Confirmar que os assets dos três projetos são compilados corretamente.

Não considerar aplicação pronta se backend sobe mas frontend está sem build.

---

# 55. Validar Laravel

Para cada sistema verificar:

```bash
php artisan about
```

ou equivalente.

Também validar:

* config;
* conexão banco;
* storage;
* cache;
* rotas;
* frontend.

---

# 56. Não ficar preso em erros do Docker

Se algum comando falhar por:

* socket;
* permissão;
* contexto;
* ambiente;

não ficar repetindo indefinidamente.

Documentar:

* comando;
* erro;
* solução provável;
* comando manual para operador.

Continue o restante do trabalho.

---

# 57. Não declarar concluído cedo

Somente declarar concluído quando:

* Compose estiver válido;
* builds estiverem definidos;
* MySQL configurado;
* WAHA movido para raiz;
* volumes configurados;
* rede funcionando;
* Nginx configurado;
* três aplicações definidas;
* Node incluído no build;
* PHP correto;
* `.env.example` criado;
* scripts/documentação disponíveis;
* stack preparada para VPS.

---

# 58. Estrutura final esperada

Algo próximo de:

```text
infra-abrasil/
├── docker-compose.yml
├── .env
├── .env.example
├── nginx/
│   ├── nginx.conf
│   └── conf.d/
│       ├── vetoros.conf
│       ├── vetorpet.conf
│       ├── abrasilsistema.conf
│       └── waha.conf
├── gateway/
│   ├── vetoros/
│   │   └── Dockerfile
│   ├── vetorpet/
│   │   └── Dockerfile
│   └── abrasilsistema/
│       └── Dockerfile
├── waha/
├── volumes/
│   ├── mysql/
│   ├── waha/
│   ├── vetoros/
│   ├── vetorpet/
│   └── abrasilsistema/
└── scripts/
    ├── up.sh
    ├── down.sh
    ├── rebuild.sh
    └── logs.sh
```

Ajustar somente se houver justificativa técnica.

---

# 59. Entrega final

Ao concluir, gerar relatório detalhado com:

## Auditoria inicial

* versões Laravel;
* versões PHP;
* versões Node;
* package manager;
* extensões;
* banco;
* Redis;
* queues;
* scheduler.

## Arquitetura final

Diagrama textual dos serviços.

## Containers

Listar todos os serviços criados.

## MySQL

* databases;
* usuários;
* volumes;
* portas.

## PHP

Versão utilizada por aplicação.

## Node

Como o build frontend foi feito.

## Nginx

Hosts e upstreams.

## WAHA

* localização;
* serviço;
* persistência;
* healthcheck;
* integração.

## Volumes

Todos os volumes e função.

## Rede

Nome e comunicação entre containers.

## Workers

Workers ativos.

## Scheduler

Schedulers ativos.

## Variáveis de ambiente

Variáveis novas ou alteradas, sem revelar secrets.

## Arquivos alterados

Lista completa.

## Arquivos removidos/movidos do WAHA antigo

Listar claramente.

## Testes realizados

Comandos e resultados.

## Comandos manuais

Tudo que eu precisar executar manualmente.

## Pendências

Somente pendências reais.

---

# Objetivo final

Transformar:

```text
infra-abrasil
```

em uma infraestrutura única, organizada e pronta para nuvem, contendo:

```text
Laravel/PHP
Node para build
MySQL
Nginx
WAHA compartilhado
Redis quando necessário
workers
scheduler
volumes persistentes
```

atendendo:

```text
VetorOS
VetorPet
ABrasil Sistemas
```

com o WAHA centralizado na raiz da infraestrutura e não mais dentro do VetorOS.

Primeiro fazer funcionar localmente.

Depois de validado, o projeto será enviado para uma VPS Linux.

Não realizar o deploy em nuvem nesta tarefa.
