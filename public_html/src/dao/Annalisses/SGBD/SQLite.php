<?php

/**
 * Essa classe representa a especificação do SQLIte
 * @author Emanuel Oliceira <emanuel.oliveira23@gmail.com>
 */
class SQLite extends SGBD {
    
    /**
     * Caminho do arquivo onde está o banco.
     * @access private
     */
    private $pathToDatabase;
    
    /**
     * Sobrescrita de construtor para incializar a extensão do sqlite e informar 
     * ao sistema que ele só necessita do dns.
     * @param Prepare $prepare configurações da conexão
     */
    public function __construct(Prepare $prepare) {
        parent::__construct($prepare);
        $this->onlyDNS = true;
        ini_set("extension", "sqlite.so");
    }

    /**
     * Sobrescrita da forma de obtenção do dns para a conexão.
     * @return string dns para conexão.
     */
    public function getDNS() {
        $dns = "sqlite:" . $this->getPathToDatabase(); 
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
