# ABRASIL-AUTH-UX-03 — Padronização obrigatória e idêntica das telas de login

**Data:** 25/10/2026  
**Projetos:** VetorSales, VetorLeads e VetorOS  
**Prioridade:** Máxima

## 1. Objetivo obrigatório

Padronizar **definitivamente** as telas de login das três aplicações para que tenham **a mesma estrutura visual e a mesma aparência de layout**.

**Não é para apenas ficar “parecido”.**  
**Não é para cada sistema ter um layout diferente.**  
**Não é para manter fundo escuro genérico.**  
**Não é para usar interpretações livres.**

### Regra principal

As três aplicações devem exibir **a mesma tela-base de login**, com:

- **imagem de fundo ocupando a tela**
- **formulário centralizado**
- **card central com aparência idêntica**
- **mesma hierarquia visual**
- **mesmos espaçamentos**
- **mesmo estilo de campos**
- **mesmo estilo de botão**
- **mesma lógica de composição**
- **mesma sensação visual**

O usuário deve perceber claramente que os três produtos pertencem ao mesmo ecossistema e usam **o mesmo layout de autenticação**.

## 2. O que pode mudar entre os produtos

Apenas os seguintes pontos podem variar:

1. **Imagem de fundo**
   - VetorSales: operação comercial, representantes, vendas, clientes.
   - VetorLeads: prospecção B2B, equipe comercial, análise de leads, oportunidades.
   - VetorOS: assistência técnica, bancada, manutenção, equipamentos, gestão de serviços.

2. **Logo e nome do produto**
   - cada aplicação deve manter sua própria marca.

3. **Texto institucional**
   - frase curta compatível com o produto.

4. **Campos do formulário**
   - manter somente os campos e ações realmente pertencentes àquela aplicação.

## 3. Estrutura visual obrigatória

A tela de login das três aplicações deve seguir **exatamente a mesma composição**:

### Layout obrigatório

- Tela ocupando 100% da viewport.
- **Imagem de fundo em tela cheia**.
- Overlay suave para legibilidade, sem escurecer excessivamente.
- **Card/formulário centralizado na tela** (horizontal e verticalmente).
- Card com fundo claro ou translúcido claro, visual moderno e limpo.
- Bordas arredondadas.
- Sombra suave.
- Logo no topo do card.
- Título e subtítulo curtos.
- Campos do formulário organizados de forma limpa.
- Botão principal destacado.
- Link de recuperação de senha, quando existir.
- Link de cadastro, quando permitido.
- Sem painéis laterais.
- Sem layout dividido.
- Sem fundo escuro chapado substituindo a imagem.
- Sem aparência de dashboard ou tela administrativa.

### Portanto:

**Não usar split screen.**  
**Não usar área lateral separada.**  
**Não usar formulário alinhado à esquerda.**  
**Não usar tela diferente para cada projeto.**

O padrão agora é:

> **imagem de fundo + formulário centralizado + mesmo card + mesma composição em todas as aplicações**

## 4. Padrão de aparência

Os três logins devem parecer feitos a partir do **mesmo componente base**.

Padronizar entre os três projetos:

- largura do card
- altura mínima
- padding
- tipografia
- raio das bordas
- sombra
- tamanho dos campos
- altura dos botões
- alinhamento dos textos
- espaço entre elementos
- tratamento de erro e validação
- responsividade

Se houver diferença perceptível de layout entre os três logins, a tarefa **não está concluída**.

## 5. Cadastro

As telas de cadastro também devem seguir um padrão comum, porém respeitando os campos de cada aplicação.

### Requisitos

- formulário centralizado
- largura semelhante entre os produtos
- fundo claro
- visual limpo
- sem barras de rolagem desnecessárias em desktop
- manter rolagem natural em telas menores quando necessário
- preservar campos obrigatórios, consentimentos, termos e regras de negócio

O cadastro **não precisa ser idêntico no conteúdo**, mas deve parecer parte da mesma família visual.

## 6. Implementação técnica

Antes de alterar:

- localizar os layouts e componentes reais usados nas rotas de login
- confirmar que não há outro layout sobrescrevendo o principal
- verificar dark mode, classes globais, wrappers e overrides
- identificar se há duplicidade de telas de autenticação

Implementar preferencialmente com uma estrutura comum por projeto, por exemplo:

- `AuthLayout`
- `AuthBackground`
- `AuthCard`
- `AuthHeader`
- `AuthForm`
- `PasswordField`

Não criar complexidade desnecessária.  
O objetivo é **resultado visual consistente e efetivo**.

## 7. Proibição de interpretação parcial

Esta tarefa **não pode** ser considerada concluída se ocorrer qualquer um destes casos:

- VetorSales com um layout, VetorLeads com outro e VetorOS com outro
- fundo escuro liso no lugar da imagem
- apenas troca de cores
- apenas centralização do formulário sem padronização total
- apenas alteração de CSS sem efeito real em tela
- manutenção de layouts antigos por “peculiaridade”
- nenhuma checagem visual no navegador

## 8. Validação visual obrigatória

Após implementar, abrir e inspecionar **as telas reais** de:

1. Login VetorSales
2. Login VetorLeads
3. Login VetorOS
4. Cadastro VetorSales
5. Cadastro VetorLeads
6. Cadastro VetorOS

### Confirmar explicitamente:

- os três logins têm o mesmo layout
- os três logins têm imagem de fundo
- os três logins têm formulário centralizado
- os três logins têm a mesma aparência de card
- cada sistema usa sua própria imagem
- cada sistema preserva seu próprio formulário
- os cadastros seguem a mesma linha visual

Capturar evidências visuais reais.

## 9. Testes

Validar:

- login com sucesso
- login com erro
- mostrar/ocultar senha
- recuperação de senha
- links auxiliares
- responsividade em desktop e mobile
- ausência de overflow horizontal
- formulário utilizável com teclado virtual em mobile

## 10. Commit e push obrigatórios

Depois de concluir:

- revisar alterações por projeto
- executar validações cabíveis
- gerar commits objetivos
- fazer push de **todos os repositórios alterados**
- informar branch e SHA final de cada um

Se o push falhar por permissão:

1. identificar o bloqueio
2. solicitar a autorização necessária
3. tentar novamente
4. se ainda falhar, informar o comando exato a ser executado manualmente

**Não encerrar a tarefa sem tentar o push.**

## 11. Relatório obrigatório

Criar `docs/ABRASIL-AUTH-UX-03.md` com:

- diagnóstico do problema anterior
- arquivos alterados por projeto
- descrição do layout final adotado
- imagens escolhidas por produto
- evidências visuais
- testes realizados
- commits e SHAs
- resultado dos pushes
- pendências ou bloqueios

## 12. Critério de aceite

A tarefa só estará concluída quando:

- VetorSales, VetorLeads e VetorOS exibirem **o mesmo layout de login**
- todos tiverem **imagem de fundo**
- todos tiverem **formulário centralizado**
- todos tiverem **a mesma aparência visual de layout**
- cada um tiver **sua própria imagem**
- cada um preservar **seu próprio formulário**
- as telas tiverem sido verificadas visualmente
- os repositórios alterados tiverem commit e push confirmados

## 13. Frase de comando

**Force a padronização visual real. Não interpretar, não adaptar parcialmente, não manter layouts diferentes. As três aplicações devem sair com a mesma tela de login, mudando apenas a imagem de fundo, a marca e os campos próprios do formulário.**
