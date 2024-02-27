<?php

/**
 * Essa classe faz uma interface genérica aos tipos de SGBDs que o Annalisses dão suporte.
 * <br/>
 * É uma abstração de como o SGBD escolhido na configuração executa conexão.
 * @author Emanuel Oliceira <emanuel.oliveira23@gmail.com>
 */
class SGBD {
    
    /**
     * Configurações de conexão.
     * @access protecetd
     */
    protected $prepare;
    
    /**
     * Essa propriedade aponta para necissidade da ultilização de login para obter a conexão.
     */
    protected $onlyDNS = false;
    
    /**
     * @param Prepare $prepare configurações da conexão
     */
    public function __construct(Prepare $prepare) {
        $this->setPrepare($prepare);
    }
    
    /**
     * @return Prepare configuração da conexão.
     */
    public function getPrepare(){
        return $this->prepare;
    }
    
    /**
     * Atribui as configurações da conexão
     * @param Prepare $prepare instancia de prepare com os dados da conexão.
     */
    public function setPrepare(Prepare $prepare){
        $this->prepare = $prepare;
    }
    
    /**
     * Esse método carrega informações adicionais do SGBD.
     * <br/>
     * As informações carregdas são obtidas do objeto prepare da istancia.
     */
    public function loadSpecificInfo(){
        $eis = $this->getPrepare()->getSpecificInfo();
        foreach($eis as $si => $value){
            $set = 'set' . ucfirst($si);
            if(method_exists($this, $set)){
                $this->$set($value);
            }
        }
    }
    
    /**
     * Declaração genérica do método que obtém o dns de conexão.
     */
    protected function getDNS() { ; }
    
    /**
     * Esse método obtém a conexão de forma genérica. 
     * <br/>
     * Cada tipo específico de SGBD re-implementa sua forma de obter o dns. 
     * <br/>
     * De acordo com a preparação da conexão, um objeto PDO é criado e retornado.
     * @return PDO Em caso de sucesso, um objeto PDO é retornado, caso contrário false(bool) é retornado
     */
    public function getConnection () {
       try{
            $dns = $this->getDNS();
            $con = null;
            if($this->onlyDNS){
                $con = new PDO($dns);
            } else {    
                $prepare    = $this->getPrepare();
                $con        = new PDO($dns, $prepare->getUser(), $prepare->getPassword());
            }
            return $con;
        } catch (PDOException $e){
            echo 'ERRO CONNECTION: '.$e->getMessage().' ['.$e->getCode().']';
            return false;
        }
    } 
    
}

?>
