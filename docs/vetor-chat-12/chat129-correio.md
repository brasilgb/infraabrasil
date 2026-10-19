# VETOR-CHAT-12.9 — Publicação do Laboratório e Homologação na VPS

## Objetivo

Publicar com segurança o Laboratório de Atendimento VetorLeads e sua integração com o widget VetorOS.

Concluir a homologação operacional do modo sandbox na infraestrutura real, sem ativar respostas automáticas públicas.

Não criar funcionalidades novas nem reconstruir módulos existentes.

## 1. Auditoria direcionada

Consultar `correio.md`, `executed.md` e os relatórios CHAT-12.7 e CHAT-12.8.

Verificar:

- Código local e imagens atualmente publicadas.
- Alterações acumuladas em VetorLeads e VetorOS.
- Migrations existentes e pendentes.
- Estado do banco e das sessões WAHA.
- Travas de envio automático.
- Testes e evidências da CHAT-12.8.

## 2. Versionamento reproduzível

Antes da publicação:

- Revisar o diff completo.
- Separar código funcional, migrations e evidências.
- Excluir segredos, backups e arquivos temporários.
- Garantir que os submódulos estejam consistentes.
- Registrar hashes das versões.
- Preparar os commits necessários para reproduzir as imagens.

Não usar `git add .` indiscriminadamente.

Não descartar alterações anteriores.

Preparar os commits e documentar os comandos, sem push automático.

## 3. Preparação do banco

- Realizar backup verificável.
- Restaurar em banco isolado para ensaio.
- Conferir migrations aplicadas.
- Aplicar somente migrations pendentes, na ordem correta.
- Verificar especialmente as tabelas `support_sandbox_conversations`, `support_sandbox_messages` e `support_sandbox_accesses`.
- Garantir que nenhuma migration remova dados comerciais ou históricos.

Não usar o banco operacional para executar testes automatizados.

## 4. Publicação coordenada

Após aprovação do ensaio e das verificações:

- Construir imagens identificáveis por revisão.
- Atualizar VetorLeads API, worker e scheduler conforme necessário.
- Atualizar o widget/proxy VetorOS.
- Preservar sessões e serviços não relacionados.
- Registrar digests das imagens efetivamente publicadas.
- Confirmar saúde de todos os serviços.

Não publicar versões incompatíveis de API e widget.

Não executar rollback destrutivo de migrations.

## 5. Homologação real do sandbox

Com administrador autorizado, testar no ambiente publicado:

1. Abrir o Laboratório.
2. Selecionar regra inativa.
3. Selecionar WebChat.
4. Gerar código temporário.
5. Abrir o widget VetorOS em homologação.
6. Resgatar a credencial.
7. Iniciar uma conversa.
8. Receber resposta automática de teste.
9. Simular qualificação comercial.
10. Simular atendimento humano.
11. Retornar ao automático.
12. Encerrar a conversa.
13. Revogar e expirar a credencial.
14. Confirmar bloqueio após revogação.

Garantir ausência de fallback para atendimento operacional.

## 6. Validação de isolamento

Confirmar que a homologação:

- Não cria leads comerciais reais.
- Não gera oportunidades operacionais.
- Não modifica histórico de visitantes públicos.
- Não produz mensagens WAHA.
- Não gera outbox operacional.
- Não altera indicadores comerciais.
- Não expõe regras de outro tenant.

Utilizar somente identidades autorizadas e dados sintéticos.

## 7. Revisão das regras

Manter as 14 regras produtivas inativas.

Revisar especialmente as precedências:

- Apresentação geral versus funcionalidades específicas.
- Financeiro versus emissão de notas fiscais.

Propor ajustes de prioridade quando necessário e validá-los no Laboratório, sem ativação pública automática.

Não configurar horários sem aprovação do responsável.

## 8. ESLint VetorOS

Registrar os 442 erros e 11 avisos preexistentes como passivo técnico separado.

Manter a verificação completa visível.

Garantir aprovação do lint dos arquivos alterados e ausência de novas violações relacionadas à publicação.

Não desativar globalmente regras de lint nem alterar centenas de arquivos não relacionados nesta fase.

## 9. Segurança

- Manter WhatsApp automático globalmente desligado.
- Manter habilitação por tenant desligada.
- Manter as 14 regras operacionais inativas.
- Não enviar mensagens reais.
- Não utilizar contatos comerciais para testes.
- Preservar possibilidade de revogação imediata de acessos sandbox.
- Não executar campanhas.

## 10. Relatório de conclusão

Atualizar `executed.md` contendo:

- Versões publicadas.
- Digests das imagens.
- Migrations aplicadas.
- Backup e ensaio.
- Resultado do teste do Laboratório publicado.
- Evidência da integração com o widget.
- Testes de segurança e isolamento.
- Estado das 14 regras.
- Estado WAHA.
- Pendências.
- Procedimento de reversão.
- Situação do Git.

Distinguir claramente publicação do Laboratório e ativação do atendimento público.

**Critério de conclusão:** Laboratório acessível na VPS, widget de teste integrado, jornada sandbox aprovada, ausência comprovada de efeitos comerciais e todas as travas produtivas preservadas.

Se qualquer etapa crítica falhar, interromper a publicação e registrar o bloqueio, sem declarar homologação operacional concluída.
