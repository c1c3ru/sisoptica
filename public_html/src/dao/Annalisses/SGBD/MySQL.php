<?php

/**
 * Essa classe representa a especificação do <>
 * @author Emanuel Oliceira <emanuel.oliveira23@gmail.com>
 */
class MySQL extends SGBD {
    
    /**
     * Sobrescrita da forma de obtenção do dns para a conexão.
     * @return string dns para conexão.
     */
    public function getDNS() {
        $dns = "mysql:host=" . $this->getPrepare()->getHost(); 
        $dns .= "; dbname=" . $this->getPrepare()->getDBName();
        return $dns;
    }
    
}
?>
