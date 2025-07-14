# TODO - Refatoração e Modernização do SISOPTICA

## Refatoração de SQL e Segurança

- [x] Refatorar métodos de busca e filtros dinâmicos nas Controllers (ex: `searchOrdens`, `searchVendas`, `getCliente`, `getAllProdutos`, etc.) para uso de prepared statements e eliminação de concatenação direta de SQL.
- [ ] Refatorar scripts e arquivos legados (`teste.php`, `teste2.php`, `backup.php`) para uso de PDO e prepared statements, eliminando `mysql_*` e concatenação direta de SQL.
- [ ] Padronizar e revisar métodos de acesso ao banco em Models e outros pontos, garantindo uso seguro de parâmetros em todas as queries.

## Testes Automatizados

- [ ] Adicionar testes automatizados para métodos críticos refatorados, garantindo funcionamento e segurança.

## Modernização e Padronização

- [x] Adotar namespaces PSR-4 e autoload via Composer em todas as camadas principais (`Controller`, `Entity`, `Repository`, `Util`).
- [x] Remover includes/requires manuais de arquivos de classes, utilizando apenas o autoload do Composer.
- [ ] Documentar e padronizar o código conforme PSR-12.
- [ ] Modernizar a interface do usuário para melhor usabilidade.

## Automação e Rotinas

- [ ] Automatizar rotinas de backup e limpeza de logs.

---

**Observações:**
- O progresso é incremental e automatizado.
- Priorizar sempre segurança (SQL Injection, CSRF, validação de entrada).
- Validar cada etapa com testes automatizados. 