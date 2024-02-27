<?php

/**
 * Essa classe representa a especificação do ODBC
 * @author Emanuel Oliceira <emanuel.oliveira23@gmail.com>
 */
class ODBC extends SGBD {
    
    /**
     * Driver usado para obter a conexão
     * @access private
     */
    private $driver;
    
    /**
     * Caminho do arquivo onde está o banco.
     * @access private
     */
    private $pathToDatabase;
    
    /**
     * Sobrescrita da forma de obtenção do dns para a conexão.
     * @return string dns para conexão.
     */
    public function getDNS() {
        $dns = "odbc:Driver=" . $this->getDriver();
        $dns .= ";Dbq=" . $this->getPathToDatabase();
        $dns .= "Uid=" . $this->getPrepare()->getUser();
        return $dns;
    }
    
    /**
     * Obtém o driver usado para obter a conexão.
     * @return string nome do driver
     */
    public function getDriver(){
        return $this->driver;
    }
    
    /**
     * Atribui o driver usado para obter a conexão
     * @param string $driver nome do driver
     */
    public function setDriver($driver){
        $this->driver = $driver;
    }
    
    /**
     * Obtém o caminho do arquivo onde está o banco.
     * @return string caminho do arquivo
     */
    public function getPathToDatabase(){
        return $this->pathToDatabase;
    }
    
    /**
     * Atribui o caminho do arquivo onde está o banco.
     * @param string $path caminho do arquivo
     */
    public function setPathToDatabase($path){
        $this->pathToDatabase = $path;
    }
    
}

?>
