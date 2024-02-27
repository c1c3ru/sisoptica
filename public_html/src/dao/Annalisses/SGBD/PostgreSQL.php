<?php

/**
 * Essa classe representa a especificação do PostgreSQL
 * @author Emanuel Oliceira <emanuel.oliveira23@gmail.com>
 */
class PostgreSQL extends SGBD {
   
    /**
     * Porta para obter a conexão do PostgreSQL
     * @access private
     */
    private $port;
    
    /**
     * Sobrescrita da forma de obtenção do dns para a conexão.
     * @return string dns para conexão.
     */
    public function getDNS() {
        $dns = "pgsql:host=" . $this->getPrepare()->getHost();
        if(isset($this->getPort()))
            $dns .= " port=" . $this->getPort();
        $dns .= " dbname=" . $this->getPrepare()->getDBName();
        return $dns;
    }
    
    /**
     * Obtém a porta para a conexão do banco.
     * @return string porta
     */
    public function getPort(){
        return $this->port;
    }
    
    /**
     * Atribui a porta para a conexão do banco.
     * @param string $port porta
     */
    public function setPort($port){
        $this->port = $port;
    }
    
}

?>
