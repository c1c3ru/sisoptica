<?php
//Impotando as classes de entidades usadas nesse controlador
include_once ENTITIES."rota.php";

//Impotando as classes de modelos usadas nesse controlador
include_once MODELS."rota.php";

/**
 * Essa classe é quem controla o fluxo de informação do e para o modelo de rotas.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class RotaController {
    
    /**
     * @var RotaModel instancia do modelo de rotas usado nesse controlador
     * @access private
     */
    private $modelRota = null;
    
    /**
     * Essa classe é quem controla o fluxo de informação do e para o modelo de rotas.
     * @return RotaController instancia de um controlador de rotas
     */
    public function __construct() {
        $this->modelRota = new RotaModel();
    }
    
    /**
     * Este método adiciona ou atualiza uma rota no modelo.
     * @param Rota $rota rota a ser inserida ou atualizada. Se for <b>null</b> os dados da 
     * requisição serão captados e atribuídos à <i>rota</i>
     */
    function addRota(Rota $rota = null){
        
        if($rota == null){
            $rota = new Rota();
            //Atribuindo dados da requisição
            $this->linkRota($rota);
        }
        
        $config = Config::getInstance();
        
        //Checando se os dados obigratórios estão sendo enviados dos dados
        if(empty($rota->nome) || empty($rota->regiao)){
            $config->failInFunction("Os campos: Nome e Região, são necessários para a inserção");
            $config->redirect("?op=cad-rota");
        }
        
        $hasIds = $config->filter("for-update-id");
        // Verificando a existência de um id na requisção. 
        // Caso exista a função de atualização do modelo será chamada, caso contrário, 
        // a de inserção será chamada.
        if(!empty($hasIds)) {
            //Atualização
            $rota->id = $hasIds;
            $res = $this->modelRota->update($rota);
        } else {
            //Inserção
            $res = $this->modelRota->insert($rota);
        }
        if($res){
            $config->successInFunction();
        } else {
            $config->failInFunction($this->modelRota->handleError());
        }
        
        $config->redirect("?op=cad-rota");
        
    }
    
    /**
     * Esse método linka os dados da requisção que diz respeito à rota 
     * com um objeto passado como referência
     * @param Rota $rota uma referência de uma rota que vai ser preenchida com os dados da requisição
     * @access private
     */
    private function linkRota(Rota &$rota){
        $config = Config::getInstance();
        $rota->nome =  mb_strtoupper($config->filter("nome"), 'UTF-8');
        $rota->regiao = (int) $config->filter("regiao");
    }
    
    /**
     * Remove uma ota do modelo com base no identificador passado na requisição.
     */
    function removeRota(){
        $config = Config::getInstance();
        $id_rota = $config->filter("rota");
        if(isset($id_rota)){
            if($this->modelRota->delete($id_rota)){
                $config->successInFunction();
            } else {
                $config->failInFunction($this->modelRota->handleError());
            }    
        } else {
            $config->failInFunction("Rota não informada");
        }
        $config->redirect("?op=cad-rota");
    }
    
    /**
     * Obtém uma lista de rotas do modelo.
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras 
     * serão atribuídos no lugar dos indexes inteiros.
     * @param int $byLoja indentificador da loja, esse parametro, se for um inteiro válido, 
     * filtra as vendas por loja.
     * @return array lista de rotas do tipo Rota
     */
    function getAllRotas($foreign_values = false, $byLoja = false){
        $condition = null;
        if($byLoja){
            $condition = RegiaoModel::TABLE.".".RegiaoModel::LOJA." = $byLoja";
        }
        if($foreign_values){
            return $this->modelRota->superSelect($condition);
        }
        return $this->modelRota->select("*", $condition);
    }
    
    /**
     * Obtém uma lista de regiões filtradas pela região.
     * @param int $id_regiao identificador da região
     * @return array lista de regiões do tipo Rota
     */
    public function getRotasByRegiao($id_regiao){
        $condition = RotaModel::TABLE.".".RotaModel::REGIAO." = $id_regiao";
        return $this->modelRota->select("*", $condition);
    }


    /**
     * Obtém uma rota em específico do modelo de rotas.
     * @param int $idrota identificador da rota
     * @return Rota rota em específico ou uma rota vazia em caso de inexistência.
     */
    function getRota($idrota){
        $rota = $this->modelRota->select(" * ", RotaModel::TABLE.".".RotaModel::ID." = ".$idrota);
        if(count($rota)) return $rota[0];
        return new Rota();
    }

    function getRotas($idsrota){
        if (empty($idsrota)) return array();
        return $this->modelRota->select(" * ", RotaModel::TABLE.".".RotaModel::ID." IN ( ".implode(',', $idsrota).')');
    }
}
?>
