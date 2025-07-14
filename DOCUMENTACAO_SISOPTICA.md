# Documentação do Sistema SisOptica

## 1. Visão Geral da Arquitetura

### Estrutura de Pastas

- **public_html/**  
  Raiz da aplicação web. Contém arquivos de entrada (index.php, print.php, etc.), scripts, imagens, estilos e a pasta `src` com o código principal.

- **public_html/src/**
  - **control/**: Controladores das regras de negócio (ex: cliente, venda, produto, etc).
  - **dao/**: Objetos de acesso a dados (DAO) para cada entidade.
  - **entity/**: Definição das entidades do sistema (modelos de dados).
  - **view/**: Views e formulários (HTML/PHP) para interação com o usuário.
  - **ajax/**: Endpoints para requisições AJAX (integração assíncrona).
  - **print/**: Relatórios e impressões (ex: relatórios de vendas, prestação de contas).
  - **util/**: Utilitários, helpers, configurações e logs.

- **Outros arquivos/pastas relevantes**
  - **css/**, **images/**, **script/**: Recursos estáticos (estilos, imagens, JS).
  - **criar-virtualhosts.sh**: Script para configuração de virtual hosts.
  - **u293830981_os.sql**: Dump do banco de dados.

---

## 2. Fluxos Principais do Sistema

### 2.1 Cadastro de Cliente

#### Visão Geral do Fluxo

1. **Formulário de Cadastro**  
   - Local: `public_html/src/view/forms/cad-clie.php`
   - Campos: nome, apelido, nascimento, RG, órgão emissor, CPF, cônjuge, nome dos pais, endereço, número, bairro, referência, casa própria, tempo, observação, renda, localidade, bloqueado, telefones.
   - Envio: via POST para `?op=add_clie` ou via AJAX para `ajax.php?code=7565`.

2. **Recepção dos Dados**
   - Se AJAX: endpoint `public_html/src/ajax/add-cliente.php`
   - Se tradicional: roteamento via `public_html/src/util/content.controller.php` para o controlador correto.

3. **Controlador**
   - Local: `public_html/src/control/cliente.php`
   - Classe: `ClienteController`
   - Função principal: `addCliente()`
     - Valida campos obrigatórios.
     - Cria objeto `Cliente` e popula com dados do request.
     - Chama o modelo para inserir ou atualizar no banco.
     - Insere/atualiza telefones associados.
     - Retorna sucesso ou erro.

4. **Modelo/DAO**
   - Local: `public_html/src/dao/cliente.php`
   - Classe: `ClienteModel`
   - Função: `insert($cliente)`
     - Insere na tabela `cliente`.
     - Retorna o ID inserido.
   - Função: `update($cliente)`
     - Atualiza registro existente.

5. **Entidade**
   - Local: `public_html/src/entity/cliente.php`
   - Classe: `Cliente`
   - Representa o cliente com todos os atributos do formulário.

6. **Banco de Dados**
   - Tabela: `cliente`
   - Estrutura: ver abaixo.

#### Estrutura da Tabela `cliente`

```sql
CREATE TABLE `cliente` (
  `id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(150) NOT NULL,
  `data_nascimento` date DEFAULT NULL,
  `apelido` varchar(50) DEFAULT NULL,
  `rg` varchar(20) DEFAULT NULL,
  `orgao_emissor` varchar(10) DEFAULT NULL,
  `cpf` varchar(11) DEFAULT NULL,
  `conjugue` varchar(150) DEFAULT NULL,
  `nome_pai` varchar(150) DEFAULT NULL,
  `nome_mae` varchar(150) DEFAULT NULL,
  `endereco` varchar(100) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `bairro` varchar(100) NOT NULL,
  `referencia` varchar(100) DEFAULT NULL,
  `casa_propria` tinyint(1) DEFAULT NULL,
  `tempo_casa_propria` varchar(10) DEFAULT NULL,
  `observacao` text DEFAULT NULL,
  `renda_mensal` float DEFAULT NULL,
  `id_localidade` smallint(5) UNSIGNED NOT NULL,
  `bloqueado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
```

- Telefones são armazenados em tabela separada: `cliente_telefone`.

---

### 2.2 Cadastro de Venda

#### Visão Geral do Fluxo

1. **Formulário de Cadastro**
   - Local: `public_html/src/view/forms/cad-vend.php`
   - Campos: cliente, loja, vendedor, agente de vendas, produtos, valores, datas (venda, entrega, previsão), parcelas, entrada, equipe, entre outros.
   - Envio: via POST para `?op=add_vend`.

2. **Recepção dos Dados**
   - Roteamento via `public_html/src/util/content.controller.php` e `function.controller.php` para o controlador correto (`VendaController`).

3. **Controlador**
   - Local: `public_html/src/control/venda.php`
   - Classe: `VendaController`
   - Função principal: `addVenda()`
     - Valida datas e regras de negócio.
     - Cria objeto `Venda` e popula com dados do request.
     - Chama o modelo para inserir ou atualizar no banco.
     - Insere produtos associados à venda.
     - Sincroniza estoque.
     - Cria parcelas de pagamento (via `ParcelaController`).
     - Cria consulta associada (via `ConsultaController`).
     - Retorna sucesso ou erro.

4. **Modelo/DAO**
   - Local: `public_html/src/dao/venda.php`
   - Classe: `VendaModel`
   - Função: `insert($venda)`
     - Insere na tabela `venda`.
     - Retorna o ID inserido.
   - Função: `update($venda)`
     - Atualiza registro existente.

5. **Entidade**
   - Local: `public_html/src/entity/venda.php`
   - Classe: `Venda`
   - Representa a venda com todos os atributos do formulário.

6. **Banco de Dados**
   - Tabela: `venda`
   - Estrutura: ver abaixo.

#### Estrutura da Tabela `venda`

```sql
CREATE TABLE `venda` (
  `id` int(10) UNSIGNED NOT NULL,
  `data_venda` date NOT NULL,
  `data_previsao_entrega` date NOT NULL,
  `data_entrega` date DEFAULT NULL,
  `id_cliente` int(10) UNSIGNED NOT NULL,
  `id_loja` tinyint(3) UNSIGNED NOT NULL,
  `id_func_vendedor` smallint(5) UNSIGNED NOT NULL,
  `id_func_agente_vendas` smallint(5) UNSIGNED NOT NULL,
  `id_ordem_servico` int(10) UNSIGNED DEFAULT NULL,
  `status` smallint(5) DEFAULT 1,
  `venda_antiga` int(10) UNSIGNED DEFAULT NULL,
  `id_equipe` int(10) UNSIGNED DEFAULT NULL,
  `lider_equipe` smallint(5) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
```

---

## 3. Entidades Principais

### 3.1 Cliente

- **Arquivo:** `public_html/src/entity/cliente.php`
- **Atributos:**
  - id, nome, nascimento, apelido, rg, orgaoEmissor, cpf, conjugue, nomePai, nomeMae, endereco, numero, bairro, referencia, casaPropria, tempoCasaPropria, observacao, rendaMensal, localidade, bloqueado, telefones.

### 3.2 Venda

- **Arquivo:** `public_html/src/entity/venda.php`
- **Atributos:**
  - id, dataVenda, previsaoEntrega, dataEntrega, cliente, loja, vendedor, agenteVendas, os, valor, produtos, status, vendaAntiga, equipe, liderEquipe

### 3.3 ProdutoVenda

- **Arquivo:** `public_html/src/entity/produto-venda.php`
- **Atributos:**
  - id, produto, venda, valor

### 3.4 Produto

- **Arquivo:** `public_html/src/entity/produto.php`
- **Atributos:**
  - id, descricao, precoVenda, precoVendaMin, etc.

### 3.5 Parcela

- **Arquivo:** `public_html/src/entity/parcela.php`
- **Atributos:**
  - id, numero, dataVencimento, valor, quitada, venda, etc.

### 3.6 Consulta

- **Arquivo:** `public_html/src/entity/consulta.php`
- **Atributos:**
  - id, venda, data, resultado, etc.

---

## 4. Relacionamentos

- **Cliente** 1:N **Telefone**
- **Cliente** 1:N **Venda**
- **Venda** 1:N **ProdutoVenda** (uma venda pode ter vários produtos)
- **Venda** 1:N **Parcela** (uma venda pode ter várias parcelas)
- **Venda** 1:1 **Consulta** (cada venda gera uma consulta)
- **Venda** N:1 **Cliente**
- **Venda** N:1 **Loja**
- **Venda** N:1 **Funcionário** (vendedor e agente de vendas)
- **Venda** N:1 **Equipe**

---

## 5. Tecnologias e Dependências

- **Backend:** PHP (orientado a objetos, MVC customizado)
- **Banco de Dados:** MySQL/MariaDB (estrutura relacional)
- **Frontend:** HTML, CSS, JavaScript (jQuery, DataTables, máscaras)
- **AJAX:** Integração assíncrona para cadastros e buscas rápidas
- **Relatórios:** Geração em PHP (arquivos em `print/`)
- **Configuração:** Arquivos `.ini`, `.php` e SQL

---

## 6. Pontos Críticos, Problemas e Melhorias

### 6.1 Organização e Arquitetura

**Pontos Críticos:**
- O sistema utiliza um padrão MVC próprio, mas mistura lógica de negócio, acesso a dados e apresentação em alguns pontos.
- Muitos arquivos PHP grandes e com múltiplas responsabilidades.
- Falta de namespaces e PSR (padrões modernos do PHP).

**Sugestões de Melhoria:**
- Refatorar para separar melhor as camadas (Controller, Model, View).
- Adotar namespaces e autoload (PSR-4).
- Modularizar funções utilitárias e helpers.

---

### 6.2 Segurança

**Pontos Críticos:**
- Falta de proteção contra SQL Injection em alguns pontos (uso direto de variáveis em queries).
- Falta de CSRF Token nos formulários.
- Falta de validação e sanitização de dados do usuário em todos os pontos.
- Possível exposição de informações sensíveis em mensagens de erro.

**Sugestões de Melhoria:**
- Utilizar prepared statements em todas as operações SQL.
- Implementar CSRF Token em todos os formulários.
- Validar e sanitizar todos os dados recebidos do usuário.
- Tratar erros de forma genérica para o usuário e detalhada apenas em logs internos.

---

### 6.3 Performance

**Pontos Críticos:**
- Consultas SQL podem ser otimizadas (uso excessivo de joins, selects sem índices).
- Carregamento de muitos dados em memória (arrays grandes).
- Falta de cache para dados estáticos (ex: cidades, produtos).

**Sugestões de Melhoria:**
- Revisar e otimizar queries SQL, criar índices onde necessário.
- Implementar paginação em todas as listagens.
- Utilizar cache para dados pouco mutáveis.

---

### 6.4 Manutenibilidade

**Pontos Críticos:**
- Código pouco documentado em alguns arquivos.
- Nomes de variáveis e funções pouco descritivos em alguns pontos.
- Falta de testes automatizados.

**Sugestões de Melhoria:**
- Comentar e documentar todas as funções e classes.
- Padronizar nomes de variáveis e funções.
- Implementar testes unitários e de integração.

---

### 6.5 Usabilidade

**Pontos Críticos:**
- Interface visual desatualizada.
- Falta de feedback visual em algumas ações (ex: loading, sucesso/erro).
- Falta de responsividade para dispositivos móveis.

**Sugestões de Melhoria:**
- Atualizar o layout com frameworks modernos (ex: Bootstrap).
- Adicionar feedback visual em todas as ações do usuário.
- Tornar o sistema responsivo.

---

### 6.6 Outras Observações

- **Logs:** O sistema gera muitos logs, mas não há rotação/limpeza automática.
- **Backup:** Há script de backup, mas recomenda-se automatizar e validar restauração periodicamente.
- **Dependências:** Atualizar bibliotecas JS e CSS para versões mais recentes.

---

## 7. Resumo das Recomendações

1. Refatorar arquitetura para separar responsabilidades.
2. Adotar padrões modernos de PHP (namespaces, autoload, PSR).
3. Reforçar segurança (SQL Injection, CSRF, validação).
4. Otimizar queries e uso de memória.
5. Documentar e padronizar código.
6. Modernizar interface e melhorar usabilidade.
7. Automatizar rotinas de backup e limpeza de logs.
8. Implementar testes automatizados.

---

*Documentação gerada automaticamente por IA com base na análise do código-fonte e banco de dados do sistema.* 