<?php

/**
 * Essa classe representa a especificação do Firebird
 * @author Emanuel Oliceira <emanuel.oliveira23@gmail.com>
 */
class Firebird extends SGBD {
    
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
        $dns = "firebird:dbname=" . $this->getPrepare()->getHost();
        $dns .= ":" . $this->getPathToDatabase();
        return $dns;
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
