<?php

/**
 * Essa classe representa a especificação do Oracle
 * @author Emanuel Oliceira <emanuel.oliveira23@gmail.com>
 */
class Oracle extends SGBD {
    
    /**
     * Charset de condificação do banco
     * @access private
     */
    private $charset;
    
    /**
     * Sobrescrita da forma de obtenção do dns para a conexão.
     * @return string dns para conexão.
     */
    public function getDNS() {
        $dns = "OCI:dbname=" . $this->getPrepare()->getDBName();
        $dns .= ";charset=" . $this->getCharset();
        return $dns;
    }
    
    /**
     * Obtém a codificação usada do banco.
     * @return string tipo de codificação usada
     */
    public function getCharset(){
        return $this->charset;
    }
    
    /**
     * Atribui a condificação usada para essa conexão.
     * @param string $charset codificação
     */
    public function setCharset($charset){
        $this->charset = $charset;
    }
    
}

?>
