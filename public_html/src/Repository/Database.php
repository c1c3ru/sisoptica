<?php
namespace Sisoptica\Repository;

include_once 'Annalisses/Annalisses.php';

/**
 * Essa classe contém os métodos que preparam e executam as principais consulta SQL.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class Database{

    /**
     * @var $annalisses representação do framework de interface com banco de dados.
     * @access private
     * @static
     */
    private static $annalisses = null;

    /**
     * Esse método obtém os dados da conexão do arquivo dbconfig.xml e a realiza.
     * @static
     * @return bool resultado da conexão: true em caso de sucesso e false em caso de falha
     */
    public static function connect() {
        if(is_null(self::$annalisses)){
            $dbconfig = simplexml_load_file(MODELS."dbconfig.xml");
            $prepare = new Prepare($dbconfig->host, $dbconfig->user, $dbconfig->password);
            $prepare->setDBName($dbconfig->dbname);
            $annalisses = new Annalisses((string)$dbconfig->dbms, $prepare);   
            $con = $annalisses->connect();

            if($con) self::$annalisses = $annalisses;
            else { self::$annalisses = null; return false; }
        }

        return true;
    }

    /**
     * Obtém o objeto do framework Annalisses instanciado
     * @return Annalisses framework corrente
     */
    protected function getAnnalisses(){
        return self::$annalisses;
    }

    /**
     * Esse método executa o comando INSERT do SQL.
     * <br/>
     * Insere o <i>values</i> dos <i>fields</i> na <i>table</i>.
     * @param string $table nome de uma tabela da base de dados.
     * @param mixed $fields nomes dos campos separados por ',', pode ser um array
     * @param mixed $values valores dos campos separados por ',', pode ser um array
     * @param bool  $debug caso seja <i>true</i> a consulta formada será impressa no documento.  
     * @return bool resultado da execução da tentativa de inserção
     * @access protected
     */
    protected function insert($table, $fields, $values, $debug = false){
        $q = "INSERT INTO $table ";
        if(!empty($fields)) $q .= '('.( is_array($fields) ? implode(', ', $fields) : $fields ).') ';
        $q .= ' VALUES '.( is_array($values) ? implode(', ', $values) : '('.$values.')' );
        if($debug) echo "<pre>$q\n</pre>";
      //  print_r ($q);exit;
        $q = self::$annalisses->execute($q);
        return (bool) $q;
    }

    /**
     * Esse método prepara e executa o camando SELECT do SQL.
     * <br/>
     * Seleciona os <i>fields</i> da <i>table</i> quando <i>condition</i> for verdadeira.
     * @param string $table nome de uma tabela da base de dados.
     * @param mixed $fields nomes dos campos separados por ',', pode ser um array
     * @param string $condition clausula WHERE da consulta.
     * @param bool  $debug caso seja <i>true</i> a consulta formada será impressa no documento.
     * @param array $params parâmetros para a execução da consulta (opcional)
     * @return PDOStatment lista de resultados ou false(bool) em caso de falha.
     * @access protected
     */
    protected function select($table, $fields = " * ", $condition = null, $debug = false, $params = []){
        $q = "SELECT ".( is_array($fields) ? implode(', ', $fields) : $fields )." FROM $table ";
        if(!empty($condition)) $q .= " WHERE $condition ";
        if($debug) echo "<pre>$q\n</pre>";
        $q = self::$annalisses->execute($q, $params);
        return $q;
    }
    
    /**
     * Esse método prepara e executa o camando UPDATE do SQL.
     * <br/>
     * Atualiza os <i>fields</i> de <i>table</i> com os <i>values</i> quando 
     * <i>condition</i> for verdadeira.
     * @param string $table nome de uma tabela da base de dados.
     * @param mixed $fields array com os nomes dos campos ou string com o nome de um único campo 
     * tem que ser correspondente à <i>$values</i>
     * @param mixed $values array com os valores dos campos ou string com o valor de um único campo 
     * tem que ser correspondente à <i>$fields<i>
     * @param string $condition clausula WHERE da consulta.
     * @param bool  $debug caso seja <i>true</i> a consulta formada será impressa no documento.
     * @return PDOStatment lista de resultados ou false(bool) em caso de falha.
     * @access protected
     */
    protected function update($table, $fields, $values, $condition = null, $debug = false){
        $q = "UPDATE ".$table;
        $sets = array();
        if(is_array($fields)) {
            for($i = 0, $l = count($fields); $i < $l; $i++){
                $sets[] = $fields[$i]." = ".$values[$i].", ";
            }
        } else {
            $sets[] = $fields.' = '.$values;
        }
        if(!empty($sets)) $q .= ' SET '.implode (', ', $sets);
        if(!empty($condition)) $q .= " WHERE $condition ";
        if($debug) echo "<pre>$q\n</pre>";
        $q = self::$annalisses->execute($q);
        return $q;
    }

    /**
     * Esse método prepara e executa o camando UPDATE do SQL.
     * <br/>
     * Atualiza os <i>values</i> na <i>table</i> quando <i>condition</i> for verdadeira.
     * <br/>
     * A diferença deste método para o <i>update</i> comum é que <i>values</i> já 
     * vem adaptado ao formato do update. Essa adaptação pode ser feita com a função 
     * <i>turnInUpdateValues</i> 
     * @param string $table nome de uma tabela da base de dados.
     * @param mixed $values valores dos campos adapatados seguindo o padrão 'campo = valor , ...'
     * @param string $condition clausula WHERE da consulta.
     * @param bool  $debug caso seja <i>true</i> a consulta formada será impressa no documento.
     * @return PDOStatment lista de resultados ou false(bool) em caso de falha.
     * @access protected
     */
    protected function formattedUpdates($table, $values, $condition, $debug = false){
        $q = "UPDATE ".$table;
        $q .= " SET ".$values;
        $q .= " WHERE ".$condition;
        if($debug) echo "<pre>$q\n</pre>";
        $q = self::$annalisses->execute($q);
        return $q;
    }

    /**
     * Esse método prepara e executa o camando DELETE do SQL.
     * <br/>
     * Deleta os as tuplas na <i>table</i> quando <i>condition</i> for verdadeira.
     * @param string $table nome de uma tabela da base de dados.
     * @param string $condition clausula WHERE da consulta.
     * @param bool $no_fk_checks esssa flag desabilita a integridade de chave estrangeira 
     * durante a execução dessa consulta.
     * @param bool $debug caso seja <i>true</i> a consulta formada será impressa no documento.
     * @return PDOStatment lista de resultados ou false(bool) em caso de falha.
     * @access protected
     */
    protected function delete($table, $condition, $no_fk_check = false, $debug = false){
        $q = "DELETE FROM ".$table;
        $q .= " WHERE ".$condition;
        if($no_fk_check){
            $nq     = "SET FOREIGN_KEY_CHECKS = 0;";
            $nq    .= $q.";";  
            $nq    .= "SET FOREIGN_KEY_CHECKS = 1;";
            $q      = $nq;
        }
        if($debug) echo "<pre>$q\n</pre>";
        $q = self::$annalisses->execute($q);
        return $q;
    }

    /**
     * Inicia uma transação. Cumpre o papel de <pre>begin</pre> do SQL.
     * <br/> 
     * Encapsula o beginTransc do Annalisses.
     * @return bool true(bool) em caso de sucesso ou false(bool) em caso de falha.
     */
    public function begin(){
        return self::$annalisses->beginTransc();
    }	
    
    /**
     * Desfaz a transação corrente. Cumpre o papel de <pre>roolback</pre> do SQL. 
     * <br/>
     * Encapsula o rollBack do Annalisses.
     * @return bool true(bool) em caso de sucesso ou false(bool) em caso de falha.
     */
    public function rollBack(){
        return self::$annalisses->rollBackTransc();
    }

    /**
     * Executa a transação corrente. Cumpre o papel de <pre>commit</pre> do SQL. 
     * <br/> 
     * Encapsula o commitTransc do Annalisses.
     * @return bool true(bool) em caso de sucesso ou false(bool) em caso de falha.
     */
    public function commit() {
        return self::$annalisses->commitTransc();
    }
    
    /**
     * Verifica se existe uma transação corrente.
     * <br/> 
     * Encapsula o inTransc do Annalisses.
     * @return bool true(bool) em caso exista uma transação aberta ou false(bool) em caso de inexistência.
     */
    public function inTransaction(){
        return self::$annalisses->inTransc();
    }

    /**
     * Verifica a existência de uma conexão.
     * @return bool true caso exista uma conexão false caso contrário.
     */
    public function isConnected(){
        $con = self::$annalisses->getConnection(self::NAME);
        return (bool) $con;
    }

    /**
     * Esse método pode ser usado em conjunto com as constates de erro para 
     * obter melhores respostas aos usuários.
     * @return int código numérico do erro corrente
     */
    public function errorCode(){
        return self::$annalisses->errorCode();
    }

    /**
     * Esse método fecha a conexão corrente.
     */
    public static function close(){
        self::$annalisses->closeConnection();
        self::$annalisses = null;
    }

    /**
     * Esse método transforma um array de values em uma string formatada 
     * especificamente para execução do comando INSERT.
     * <br/>
     * Ele adpata os valores do array de acordo com o tipo: 
     * <ul>
     * <li>Se string, adiciona as aspas.</li>
     * <li>Se for null, adpata esse valor para NULL(SQL)</li>
     * <li>Se for booleano, relaciona à TRUE ou FALSE do SQL.</li>
     * </ul>
     * @param array $dic uma lista de valores para o insert.
     * @access protected
     * @static
     */
    protected static function turnInValues($dic){
        $attrs = array();
        if(is_object($dic)){
            $vars = get_object_vars($dic);
            foreach ($vars as $val){
                $values[] = $val; 
            }
            $dic = $values;
        }
        foreach($dic as $attr){
            if(is_null($attr)) $attrs[] = 'NULL';
            else if(is_numeric($attr) && !is_string($attr)) $attrs[] = $attr;
            else if(is_bool($attr)) $attrs[] = $attr ? 'TRUE' : 'FALSE';
            else $attrs[] = "\"$attr\"";
        }
        $res = implode(', ', $attrs);
        return $res;
    }

    /**
     * Esse método transforma um dicionário de dados em uma string formatada 
     * especificamente para execução do comando UPDATE.
     * <br/>
     * Ele adpata os valores do dicionário de acordo com o tipo: 
     * <ul>
     * <li>Se string, adiciona as aspas.</li>
     * <li>Se for null, adpata esse valor para NULL(SQL)</li>
     * <li>Se for booleano, relaciona à TRUE ou FALSE do SQL.</li>
     * </ul>
     * @param array $dic um dicionário com o itens no formato: campo_tabela = valor.
     * @access protected
     * @static
     */
    protected static function turnInUpdateValues($dic){
        $attrs = array();
        foreach($dic as $attr => $value){
            $_attr = $attr." = ";
            if(is_null($value)) $_attr .= 'NULL';
            else if(is_numeric($value) && !is_string($value)) $_attr .= $value;
            else if(is_bool($value)) $_attr .= $value ? 'TRUE' : 'FALSE';
            else  $_attr .= "\"$value\"";
            $attrs[] = $_attr;
        }
        $res = implode(', ', $attrs);
        return $res;
    }
    
    /**
     * Esse método cria uma string de junção à esquerada (LEFT OUTER JOIN) para o SELECT.
     * <br>
     * Para ultilizar esse método a classe <i>model</i> deve conter a constate <b>TABLE</b> 
     * que é nome da tabela na base e a constate <b>ID</b> que é o identificador de tupla dessa tabela.
     * <br/>
     * O modelo é: LEFT OUTER JOIN <i>model::TABLE</i> ON <i>field</i> = <i>model::ID</i>
     * @param string $model nome da classe modelo.
     * @param string $field nome do campo a ser associado a junção.
     * @return string LEFT OUTER JOIN construído
     */
    public static function leftJoin($model, $field){
        $refl   = new ReflectionClass($model);
        $table  = $refl->getConstant('TABLE');
        $id     = $refl->getConstant('ID');
        if ($table && $id) 
            return 'LEFT OUTER JOIN '.$table.' ON '.$field.' = '.$table.'.'.$id;        
        return '';
    }
    
    /**
     * Esse método cria uma string de junção à direita (RIGHT OUTER JOIN) para o SELECT.
     * <br>
     * Para ultilizar esse método a classe <i>model</i> deve conter a constate <b>TABLE</b> 
     * que é nome da tabela na base e a constate <b>ID</b> que é o identificador de tupla dessa tabela.
     * <br/>
     * O modelo é: RIGHT OUTER JOIN <i>model::TABLE</i> ON <i>field</i> = <i>model::ID</i>
     * @param string $model nome da classe modelo.
     * @param string $field nome do campo a ser associado a junção.
     * @return string RIGHT OUTER JOIN construído
     */
    public static function rightJoin($model, $field){
        $refl   = new ReflectionClass($model);
        $table  = $refl->getConstant('TABLE');
        $id     = $refl->getConstant('ID');
        if ($table && $id) 
            return 'RIGHT OUTER JOIN '.$table.' ON '.$field.' = '.$table.'.'.$id;        
        return '';
    }
    
    /**
     * Esse método cria uma string de junção interna (INNER JOIN) para o SELECT.
     * <br>
     * Para ultilizar esse método a classe <i>model</i> deve conter a constate <b>TABLE</b> 
     * que é nome da tabela na base e a constate <b>ID</b> que é o identificador de tupla dessa tabela.
     * <br/>
     * O modelo é: INNER JOIN <i>model::TABLE</i> ON <i>field</i> = <i>model::ID</i>
     * @param string $model nome da classe modelo.
     * @param string $field nome do campo a ser associado a junção.
     * @return string INNER JOIN construído
     */
    public static function innerJoin($model, $field){
        $refl   = new ReflectionClass($model);
        $table  = $refl->getConstant('TABLE');
        $id     = $refl->getConstant('ID');
        if ($table && $id) 
            return 'INNER JOIN '.$table.' ON '.$field.' = '.$table.'.'.$id;        
        return '';
    }
    
    /**
     * Esse método cria uma string de junção de omissão dupla (FULL OUTER JOIN) para o SELECT.
     * <br>
     * Para ultilizar esse método a classe <i>model</i> deve conter a constate <b>TABLE</b> 
     * que é nome da tabela na base e a constate <b>ID</b> que é o identificador de tupla dessa tabela.
     * <br/>
     * O modelo é: FULL OUTER JOIN <i>model::TABLE</i> ON <i>field</i> = <i>model::ID</i>
     * @param string $model nome da classe modelo.
     * @param string $field nome do campo a ser associado a junção.
     * @return string FULL OUTER JOIN construído
     */
    public static function fullJoin($model, $field){
        $refl   = new ReflectionClass($model);
        $table  = $refl->getConstant('TABLE');
        $id     = $refl->getConstant('ID');
        if ($table && $id) 
            return 'FULL OUTER JOIN '.$table.' ON '.$field.' = '.$table.'.'.$id;        
        return '';
    }
    
    /**
     * Captar o erro corrente.
     * @return string mensagem de erro padrão com o código do erro corrente.
     */
    public function handleError(){
        $errorCode = $this->errorCode();
        return "Mensagem de erro padrão: $errorCode";
    }
    
}
?>
