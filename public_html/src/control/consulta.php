<?php
//Impotando as classes de entidades usadas nesse controlador
include_once ENTITIES."consulta.php";

//Impotando as classes de modelos usadas nesse controlador
include_once MODELS."consulta.php";

/**
 * Essa classe é quem controla o fluxo de informação do e para o modelo de consulta.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class ConsultaController {
    
    /**
     * @var ConsultaModel instancia do modelo de consultas usado nesse controlador
     * @access private
     */
    private $modelConsulta;
    
    /**
     * Essa classe é quem controla o fluxo de informação do e para o modelo de consulta.
     * @return ConsultaController instancia de um controlador de ordens de consultas
     */
    public function __construct() {
        $this->modelConsulta = new ConsultaModel();
    }
    
    /**
     * Adiciona uma consulta na base de dados.
     * @param Consulta $consulta consulta a ser inserida
     * @param int $id_venda identificador da venda da consulta
     */
    public function addConsulta($consulta, $id_venda){
        if($consulta == null){
            $consulta = new Consulta();
            $this->linkConsulta($consulta);
        }
        $consulta->venda = $id_venda;
        
        $config = Config::getInstance();
        $hasId = $config->filter("consulta");
        $res = false;
        // Verificando a existência de um id na requisção. 
        // Caso exista a função de atualização do modelo será chamada, caso contrário, 
        // a de inserção será chamada.
        if(empty($hasId)){
            $res = $this->modelConsulta->insert($consulta);
        } else {
            $consulta->id = $hasId;
            $res = $this->modelConsulta->update($consulta);
        }
        return $res;
    }
    
    /**
     * Esse método linka os dados da requisção que diz respeito à consulta 
     * com um objeto passado como referência
     * @param Consulta $consulta uma referência de uma consulta que vai ser preenchida 
     * com os dados da requisição
     */
    public function linkConsulta(Consulta &$consulta){
        $config = Config::getInstance();
        $consulta->nomePaciente = mb_strtoupper($config->filter("nome-paciente"), 'UTF-8');
        $consulta->esfericoOD   = $config->filter("esferico-olho-direito");
        $consulta->esfericoOE   = $config->filter("esferico-olho-esquerdo");
        $consulta->cilindricoOD = $config->filter("cilindrico-olho-direito");
        $consulta->cilindricoOE = $config->filter("cilindrico-olho-esquerdo");
        $consulta->eixoOD       = $config->filter("eixo-olho-direito");
        $consulta->eixoOE       = $config->filter("eixo-olho-esquerdo");
        $consulta->dnpOD        = $config->filter("dnp-olho-direito");
        $consulta->dnpOE        = $config->filter("dnp-olho-esquerdo");
        $consulta->dp           = $config->filter("dp");
        $consulta->adicao       = $config->filter("adicao");
        $consulta->altura       = $config->filter("altura");
        $consulta->co           = $config->filter("co");
        $consulta->cor          = mb_strtoupper($config->filter("cor"), 'UTF-8');;
        $consulta->lente        = mb_strtoupper($config->filter("lente"), 'UTF-8');;
        $consulta->observacao   = mb_strtoupper($config->filter("observacao"), 'UTF-8');
        $consulta->oculista     = $config->filter("oculista");
    }
    
    /**
     * Remove uma consulta de acordo com os dados da requisição.
     */
    public function removerConsulta(){
        $config = Config::getInstance();
        $id_cons = $config->filter("cons");
        $res = $this->modelConsulta->delete($id_cons);
        if($res){
            $config->successInFunction();
        } else {
            $config->failInFunction($this->modelConsulta->handleError());
        }   
        $config->redirect("?op=cad-vend");
    }
    
    /**
     * Obtém uma consulta de uma venda.
     * @param int $venda_id identificador da venda
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras 
     * serão atribuídos no lugar dos indexes inteiros.
     * @return Consulta consulta específica de uma venda, ou uma consulta vazia em caso de inexistência.
     */
    public function getConsultaByVenda($venda_id, $foreign_values = false){
        $condition = ConsultaModel::VENDA." = $venda_id";
        if($foreign_values) $consulta = $this->modelConsulta->superSelect ($condition);
        else $consulta = $this->modelConsulta->select("*", $condition);
        if(empty($consulta)) return new Consulta();
        return $consulta[0];
    }
    
}
?>
