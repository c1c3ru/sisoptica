<?php

include_once 'SGBD.php';

/**
 * Essa classe encapsula os dados de uma conexão.
 * <br/>
 * Como diz o nome ela 'Prepara' a conexão com a base dados informada
 * @author Emanuel Oliceira <emanuel.oliveira23@gmail.com>
 */
class Prepare {
    
    /**
     * Host do banco de dados.
     * @access private
     */
    private $host;
    
    /**
     * Usuário do banco de dados.
     * @access private
     */
    private $user;
    
    /**
     * Senha do usuário do banco de dados.
     * @access private
     */
    private $password;
    
    /**
     * Nome do banco de dados.
     * @access private
     */
    private $dbname;
    
    /**
     * Informações específicas de apio a obtenção de conexão.
     * @access private
     */
    private $specificInfo = null;
    
    /**
     * Tipo de informação específica.
     * <br/>
     * Essa constante é ultiliza quando o banco de dados está concentrado em um arquivo. 
     */
    const PATH_DATABASE_FILE = 'pathToDatabase';
    
    /**
     * Tipo de informação específica.
     * <br/>
     * Representa o nome da propriedade driver usada em alguns SGBDs.
     */
    const DRIVER =  'driver';
    
    /**
     * Tipo de informação específica.
     * <br/>
     * Representa o nome da propriedade charset usada em alguns SGBDs.
     */
    const CHARSET = 'charset';
    
    /**
     * Essa classe encapsula os dados de uma conexão.
     * @param string $host hostname da instancia para conexão
     * @param string $user usuário da instancia para conexão
     * @param string $password senha da instancia para conexão
     */
    public function __construct($host, $user = "", $password = "") {
        $this->setHost($host);
        $this->setUser($user);
        $this->setPassword($password);
    }

    /**
     * @return string hostname da instancia
     */
    public function getHost(){
        return $this->host;
    }
    
    /**
     * Atribui o hostname dessa instancia.
     * @param string $host endereço do host (hostaname)
     */
    public function setHost($host){
        $this->host = $host;
    }
    
    /**
     * @return string nome do banco de dados
     */
    public function getDBName(){
        return $this->dbname;
    }
    
    /**
     * Atribui o nome do banco de dados dessa instancia.
     * @param string $dbname nome do banco
     */
    public function setDBName($dbname){
        $this->dbname = $dbname;
    }
    
    /**
     * @return string nome do usuário do banco de dados
     */
    public function getUser(){
        return $this->user;
    }
    
    /**
     * Atribui o nome do usuário do banco de dados dessa instancia.
     * @param string $usere nome usuário do banco
     */
    public function setUser($user){
        $this->user = $user;
    }
    
    /**
     * @return string senha do banco de dados
     */
    public function getPassword(){
        return $this->password;
    }
    
    /**
     * Atribui a senha do banco de dados dessa instancia.
     * @param string $dbname nome do banco
     */
    public function setPassword($password){
        $this->password = $password;
    }
    
    /**
     * @return array informações específicas para um tipo de SGBD.
     */
    public function getSpecificInfo(){
        return $this->specificInfo;
    }
    
    /**
     * Atribui informções específicas dessa instancia.
     * @param array $si informações úteis à conexão.
     */
    public function setSpecificInfo($si){
        $this->specificInfo = $si;
    }
    
}
?>
