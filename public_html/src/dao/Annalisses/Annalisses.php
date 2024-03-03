<?php

include_once 'Prepare.php';

/**
 * Essa classe tem como principal intuito encapsular as principains funções
 * usadas para manipulação de dados independente do SGBD.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class Annalisses {
    
    /**
     * Constante para a classe do MySQL
     */
    const MYSQL     = "MySQL";
    
    /**
     * Constante para a classe do PostgreSQL
     */
    const POSTGRE   = "PostgreSQL";
    
    /**
     * Constante para a classe do SQLite
     */
    const SQLITE    = "SQLite";
    
    /**
     * Constante para a classe do Firebird
     */
    const FIREBIRD  = "Firebird";
    
    /**
     * Constante para a classe do Oracle
     */
    const ORACLE    = "Oracle";
    
    /**
     * Constante para a classe do ODBC
     */
    const ODBC      = "ODBC";

    /**
     * Código de errro duplicação de indexes
     */
    const ERR_DUPLICATE     = "1062";
    
    /**
     * Código de errro erro de sintaxe
     */
    const ERR_SYNTAX        = "1064";
    
    /**
     * Código de errro inexistência
     */
    const ERR_NOT_EXISTS    = "1452";
    
    /**
     * Código de errro violação de chave estrangeira
     */
    const ERR_FK_VIOLATION  = "1451";
    
    /**
     * @var string nome da classe do SGBD settado como padrão dessa instância
     * @access private
     */
    private $SGBD;
    
    /**
     * @var Prepare Objeto de preparação settado como padrão dessa instância
     * @access private
     */
    private $prepare;
    
    /**
     * @var array pool de conexões
     * @static
     */
    static private $connections = array();
    
    /**
     * Essa classe tem como principal intuito encapsular as principains funções
     * usadas para manipulação de dados independente do SGBD.
     * @param string $SGBD nome da classe do Sistema de Gerenciamento de Banco 
     * de Dados padrão dessa instância
     * @param Prepare $prepare prepare settado como padrão dessa instância
     */
    public function __construct($SGBD, $prepare) {
        $this->setSGBD($SGBD);
        $this->setPrepare($prepare);
    }
    
    /**
     * @param string $SGBD nome da classe do SGBD
     */
    public function setSGBD($SGBD){
        if(file_exists(MODELS.'Annalisses/SGBD/'.$SGBD.'.php')){
            include_once MODELS.'Annalisses/SGBD/'.$SGBD.'.php';
        } 
        $this->SGBD = $SGBD;
    }
    
    /**
     * @return string nome da classe do SGBD
     */
    public function getSGBD(){
        return $this->SGBD;
    }
    
    /**
     * @param Prepare $prepare instancia de Prepare que define dados da conexão
     */
    public function setPrepare(Prepare $prepare){
        $this->prepare = $prepare;
    }
    
    /**
     * @return Prepare objeto de prepare dessa instancia
     */
    public function getPrepare(){
        return $this->prepare;
    }
    
    /**
     * Esse método obtém o objeto conexão do banco <i>dbname<i>.
     * <br/>
     * Se <b>dbname</b> é <i>null</i> o último objeto instancia no pool de conexões é retornado
     * @param string $dbname nome do banco da conexão desejada
     * @return PDO é uma representação da conexão.
     */
    public function getConnection($dbname = null){
        if($dbname !== null) {
            return self::$connections[$dbname];
        }
        return end(self::$connections);
    }
    
    /**
     * Esse método fecha uma conexão do banco <i>dbname</i>.
     * <br/>
     * Se <b>dbname</b> é <i>null</i>, a última conexão estabelecida no pool de conexões é fechada.
     * @param string $dbname nome do banco da conexão desejada
     */
    public function closeConnection($dbname = null){
        if(!is_null($dbname)){
            self::$connections[$dbname] = null;
            unset(self::$connections[$dbname]);
        } else {
            array_pop(self::$connections);
        }
    }
    
    /**
     * Esse método estabelece uma conexão com os dados de <b>prepare settado</b> dessa 
     * instancia e do <b>SGBD settado</b>.
     * <br/>
     * Caso a conexão seja realizada com sucesso ela será inserida no pool de conexões.
     * @return PDO um objeto do tipo conexão ou false(bool) em caso de falha na conexão.
     */
    public function connect(){
        $SGBD       = $this->getSGBD();
        $prepare    = $this->getPrepare();
        $objSGBD    = new $SGBD($prepare);
        $si         = $prepare->getSpecificInfo();
        if(!empty($si)){ $objSGBD->loadSpecificInfo();}
        $con = $objSGBD->getConnection();
        if($con) {
            self::$connections[(string)$prepare->getDBName()] = $con;
        }
        return $con;
    }
    
    /**
     * Executa um instrução SQL e retorna um PDOStatement como uma lista de 
     * resultados ou false(bool) em caso de falha
     * @param srting $query a consulta SQL.
     * @param string $dbname nome do banco de dados para que possa ser obtida a conexão
     * @return false|PDOStatement
     */
    public function execute($query, $dbname = null){
        $connection = $this->getConnection($dbname);

        try {
            return $connection->query($query);
        } catch (PDOException $e) {
            // Tratar a exceção PDO aqui
            // Por exemplo, você pode registrar o erro em um arquivo de log
            error_log("Erro ao executar a consulta: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Obtém a próxima tupla na forma array associativo <i>(coula => valor)</i>.
     * @param PDOStatement $query instrução sql obtida da execução da consulta SQL.
     * @return array tupla chaveada pelos nomes das colunas da tabela
     */
    public function fetchAssoc($query){
        return $this->fetch($query, PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtém a próxima tupla na forma objeto padrão <i>$obj->nome_coluna</i>.
     * @param PDOStatement $query instrução sql obtida da execução da consulta SQL.
     * @return objeto da tupla tendo com atributos o nome das colunas e seus respectivos valores
     */
    public function fetchObject($query){
        return $this->fetch($query, PDO::FETCH_OBJ);
    }
    
    /**
     * Obtém a próxima tupla na foram array indexado numericamente.
     * @param PDOStatement $query instrução sql obtida da execução da consulta SQL.
     * @return array tupla chaveada indexes numéricos das colunas na tabela
     */
    public function fetchArray($query){
        return $this->fetch($query, PDO::FETCH_NUM);
    }
    
    /**
     * Obtém a próxima tupla na foram array indexado, ou numericamente ou pelo nomes das colunas.
     * <br/>
     * É uma mistura de fetchArray e fetchAssoc.
     * @param PDOStatement $query instrução sql obtida da execução da consulta SQL.
     * @return array tupla chaveada indexes numéricos e nomeados das colunas na tabela
     */
    public function fetchArrayAssoc($query){
        return $this->fetch($query, PDO::FETCH_BOTH);
    }
    
    /**
     * Esse método obtém a próxima tupla de acordo com o <i>style</i>.
     * @param mixed $query pode ser uma consulta sql ainda em string ou um PDOStatement.
     * @param const $style PDOStatement::FETCH_* constantes para definição de tipos de retorno.
     * @return mixed tupla representada com uma entidade (<i>array</i>, <i>objeto</i>, etc).
     */
    public function fetch($query, $style){
        if(is_string($query)){
            $queryResult = $this->execute($query);
            if ($queryResult === false) {
                return false; // Retorna false se houver um erro na execução da consulta
            }
        } else {
            $queryResult = $query; // Se $query já for um objeto PDOStatement, apenas atribua a $queryResult
        }

        // Verificar se $queryResult é um objeto PDOStatement antes de chamar fetch()
        if ($queryResult instanceof PDOStatement) {
            return $queryResult->fetch($style);
        }

        return false; // Se não for um objeto PDOStatement, retorne false
    }


    /**
     * Esse método pode ser usado em conjunto com as constates de erro para 
     * obter melhores respostas aos usuários.
     * @return int código numérico do erro corrente
     */
    public function errorCode(){
        $info = $this->getConnection()->errorInfo();
        return $info[1];
    }
    
    /**
     * Esse método retorna o número de linhas afetadas na última consulta executada. 
     * @param PDOStatment $query última consulta executada. 
     * @return int núemro de linhas
     */
    public function numRows($query){
        return $query->rowCount();
    }
    
    /**
     * Esse método obtém o último id inserido na conexão.
     * @param string $dbname nome do banco que representa a conexão no pool.
     * @return type Description
     */
    public function lastInsertedtId($dbname = null){
        return $this->getConnection($dbname)->lastInsertId();
    }

    
    /**
     * Inicia uma transação. Cumpri o papel de <pre>begin</pre> do SQL. 
     * <br/>
     * Se <b>dbname</b> for <i>null</i> a últiam conexão estabelicida no pool é usada.
     * @param string $dbname nome do banco de dado sque representa a conexão. 
     * @return bool true(bool) em caso de sucesso ou false(bool) em caso de falha.
     */
    public function beginTransc($dbname = null){
        $con = $this->getConnection($dbname);
        $con->setAttribute(PDO::ATTR_AUTOCOMMIT, FALSE);
        return $con->beginTransaction();
    }
    
    /**
     * Executa a transação corrente. Cumpri o papel de <pre>commit</pre> do SQL. 
     * <br/>
     * Se <b>dbname</b> for <i>null</i> a últiam conexão estabelicida no pool é usada.
     * @param string $dbname nome do banco de dado sque representa a conexão. 
     * @return bool true(bool) em caso de sucesso ou false(bool) em caso de falha.
     */
    public function commitTransc($dbname = null){
        $con = $this->getConnection($dbname);
        $res = $con->commit();
        $con->setAttribute(PDO::ATTR_AUTOCOMMIT, TRUE);
        return $res;
    }
    
    /**
     * Desfaz a transação corrente. Cumpri o papel de <pre>roolback</pre> do SQL. 
     * <br/>
     * Se <b>dbname</b> for <i>null</i> a últiam conexão estabelicida no pool é usada.
     * @param string $dbname nome do banco de dado sque representa a conexão. 
     * @return bool true(bool) em caso de sucesso ou false(bool) em caso de falha.
     */
    public function rollBackTransc($dbname = null){
        $con = $this->getConnection($dbname);
        return $con->rollBack();	
    }  
    
    /**
     * Verifica se existe uma transação corrente.
     * <br/>
     * Se <b>dbname</b> for <i>null</i> a últiam conexão estabelicida no pool é usada.
     * @param string $dbname nome do banco de dado sque representa a conexão. 
     * @return bool true(bool) em caso exista uma transação aberta ou false(bool) em caso de inexistência.
     */
    public function inTransc($dbname = null){
        $con = $this->getConnection($dbname);
        return $con->inTransaction ();	
    }
    
}

?>
