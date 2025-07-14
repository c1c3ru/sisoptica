<?php
namespace Sisoptica\Controller;

include_once ENTITIES.'tipo-pagamento.php';
include_once MODELS.'tipo-pagamento.php';


/**
 * Essa classe é quem controla o fluxo de informação do e para o modelo de tipos de pagamentos.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class TipoPagamentoController {
    
    /**
     * @var TipoPagamentoModel instancia do modelo de tipo de pagaento usado nesse controlador
     * @access private
     */
    private $modelTipo;

    /**
     * Essa classe é quem controla o fluxo de informação do e para o modelo de tipos de pagamentos.
     * @return TipoPagamentoController instancia do controlador de tipo de pagamento.
     */
    public function __construct() {
        $this->modelTipo = new TipoPagamentoModel();
    }
    
    /**
     * Este método adiciona ou atualiza um tipo de pagamento no modelo.
     * @param TipoPagamento $tipo tipo a ser inserido ou atualizado. Se for <b>null</b> os dados da 
     * requisição serão captados e atribuídos à <i>tipo</i>
     */
    public function addTipo(TipoPagamento $tipo = null){
        
        if($tipo == null){
            $tipo = new TipoPagamento();
            $this->linkTipo($tipo);
        }
        
        $config = Config::getInstance();
        
        $hasId  = $config->filter('for-update-id');
        $res    = false; 
        if(empty($hasId)){
            $res = $this->modelTipo->insert($tipo);
        } else {
            $tipo->id = $hasId;
            $res = $this->modelTipo->update($tipo);
        }
        
        if($res){
            $config->successInFunction();
        } else {
            $config->failInFunction($this->modelTipo->handleError());
        }
        
        $config->redirect('index.php?op=cad-tipo-pgmto');
        
    }
    
    /**
     * Esse método linka os dados da requisção que diz respeito ao tipo de pagamento. 
     * com um objeto passado como referência
     * @param TipoPagamento $tipo uma referência de um tipo de pagamento que vai ser preenchido com os dados da requisição
     * @access private
     */
    private function linkTipo(TipoPagamento &$tipo){
        $config = Config::getInstance();
        $tipo->nome         = mb_strtoupper($config->filter('nome'), 'UTF-8');
        $tipo->observacao   = mb_strtoupper($config->filter('observacao'), 'UTF-8');
    }
    
    /**
     * Remove um tipo de pagamento da base de dados de acordo com o identificador passado na requisição
     */
    public function removeTipo(){
        $config     = Config::getInstance();
        $id_tipo    = $config->filter('tipo');
        if(!empty($id_tipo)){
            if($this->modelTipo->delete($id_tipo)){
                $config->successInFunction();
            } else{ 
                $config->failInFunction($this->modelTipo->handleError());
            }
        } else {
            $config->failInFunction('Tipo de pagamento não informado');
        }
        $config->redirect('index.php?op=cad-tipo-pgmto');
    }
    
    /**
     * Obtém um tipo de pagamento do modelo com base no identificador.
     * @param int $id_tipo valor do identificador do tipo de pagamento
     * @return TipoPagamento o tipo de pagamento específico ou um tipo de pagamento vazio em caso de inexistência
     */
    public function getTipoPagamento($id_tipo){
        $condition  = TipoPagamentoModel::ID.' = '.$id_tipo;
        $res        = $this->modelTipo->select('*', $condition);
        if(empty($res)) return new TipoPagamento();
        return $res[0];
    }

    /**
     * Obtém a lista de todos os tipos de pagamento registrados no modelo.
     * @param mixed $ids [optional] os ids dos tipos. Pode ser um array ou uma string
     * @return array lista de tipos de pagamento do tipo TipoPagamento
     */
    public function getAllTiposPagamento($ids = null){
        $condition = null;
        if($ids != null) {
            if(is_array($ids)) $ids = implode(",", $ids);
            $condition  = TipoPagamentoModel::ID." IN ($ids)";
        }
        return $this->modelTipo->select('*', $condition);
    }

    /**
     * Obtém a lista de todos os tipos de pagamento registrados no modelo, exceto os especificados por nome.
     * @param mixed $exceptsNames [optional] os ids dos tipos. Pode ser um array ou uma string
     * @return array lista de tipos de pagamento do tipo TipoPagamento
     */
    public function getAllTiposPagamentoExcepts($exceptsNames = null){
        $condition = null;
        if($exceptsNames != null) {
            if(is_array($exceptsNames)) $exceptsNames = implode(",", $exceptsNames);
            $condition  = TipoPagamentoModel::NOME." NOT IN ($exceptsNames)";
        }
        return $this->modelTipo->select('*', $condition);
    }

    /**
     * Obtém os tipos de pagamento que envolvem dinheiro.
     * @param boolean $only_id indica se somente os ids dos tipos devem ser retornados
     * @return array lista de tipos ou somente os ids desses tipos
     */
    public function tiposDinheiro($only_id = false){
        $condition  = TipoPagamentoModel::NOME.' LIKE \'%DINHEIRO%\' ';
        $tipos      = $this->modelTipo->select('*', $condition);
        if($only_id){
            $ids = array();
            foreach ($tipos as $tipo) {
                $ids[] = $tipo->id;
            }
            return $ids;
        }
        return $tipos;
    }
    
    /**
     * Obtém os tipos de pagamento que <b>não</b> envolvem dinheiro.
     * @param boolean $only_id indica se somente os ids dos tipos devem ser retornados
     * @return array lista de tipos ou somente os ids desses tipos
     */
    public function tiposNaoDinheiro($only_id = false){
        $condition  = TipoPagamentoModel::NOME.' NOT LIKE \'%DINHEIRO%\' ';
        $tipos      = $this->modelTipo->select('*', $condition);
        if($only_id){
            $ids = array();
            foreach ($tipos as $tipo) {
                $ids[] = $tipo->id;
            }
            return $ids;
        }
        return $tipos;
    }
    
}
?>
