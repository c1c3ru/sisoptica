<?php
//Impotando as classes de entidades usadas nesse controlador
include_once ENTITIES."cidade.php";
include_once ENTITIES."estado.php";
include_once ENTITIES."regiao.php";

//Impotando as classes de modelos usadas nesse controlador
include_once MODELS."estado.php";
include_once MODELS."cidade.php";
include_once MODELS."regiao.php";

/**
 * Essa classe é quem controla o fluxo de informação do e para o modelo de regiões, 
 * modelo de cidades e modelo de estado.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class RegiaoController {
	
    /**
     * @var EstadoModel instancia do modelo de estados usado nesse controlador
     * @access private
     */
    private $modelEstado;
    
    /**
     * @var CidadeModel instancia do modelo de cidades usado nesse controlador
     * @access private
     */
    private $modelCidade;
    
    /**
     * @var RegiaoModel instancia do modelo de regioes usado nesse controlador
     * @access private
     */
    private $modelRegiao;

    /**
     * Essa classe é quem controla o fluxo de informação do e para o modelo de regiões, 
     * modelo de cidades e modelo de estado.
     * @return RegiaoController instancia de um controlador de regiões
     */
    public function __construct() {
        $this->modelEstado = new EstadoModel();	
        $this->modelCidade = new CidadeModel();	
        $this->modelRegiao = new RegiaoModel();
    }

    /**
     * Este método adiciona ou atualiza uma região no modelo.
     * @param Regiao $regiao regiao a ser inserida ou atualizada. Se for <b>null</b> os dados da 
     * requisição serão captados e atribuídos à <i>regiao</i>
     */
    public function addRegiao(Regiao $regiao = null){
        
        if($regiao == null) {
            $regiao = new Regiao();
            //Atribuindo dados da requisição
            $this->linkRegiao($regiao);
        }
        
        $config = Config::getInstance();
        
        //Checando se os dados obigratórios estão sendo enviados dos dados
        if(empty($regiao->nome) || empty($regiao->cobrador) || empty($regiao->loja)){
            $config->failInFunction("Os campos: Nome, Cobrador, e Loja, são necessários para inserção");
            $config->redirect("index.php?op=cad-regi");
        }
        
        $hasId = $config->filter("for-update-id");
        $res = false;
        
        // Verificando a existência de um id na requisção. 
        // Caso exista a função de atualização do modelo será chamada, caso contrário, 
        // a de inserção será chamada.
        if(!empty($hasId)){
            //Atualização
            $regiao->id = $hasId;
            $res = $this->modelRegiao->update($regiao);
        } else {
            //Inserção
            $res = $this->modelRegiao->insert($regiao);
        }
        
        if($res){
            $config->successInFunction();
        } else {
            $config->failInFunction($this->modelRegiao->handleError());                
        }
        $config->redirect("index.php?op=cad-regi");
    }
    
    /**
     * Esse método linka os dados da requisção que diz respeito à região 
     * com um objeto passado como referência
     * @param Regiao $regiao uma referência de uma regiao que vai ser preenchida com os dados da requisição
     * @access private
     */
    private function linkRegiao(Regiao &$regiao){
        $config = Config::getInstance();
        $regiao->nome = mb_strtoupper($config->filter("nome"), 'UTF-8');
        $regiao->cobrador = (int) $config->filter("cobrador");
        $regiao->loja = (int) $config->filter("loja");
    }
    
    /**
     * Remove uma região do modelo com base no identificador passado na requisição.
     */
    public function removerRegiao(){
        $config = Config::getInstance();
        
        $regiao_id = $config->filter("regi");
        
        $condition = RegiaoModel::ID." = ".$regiao_id;
        $res = $this->modelRegiao->delete($condition);
        if($res){
            $config->successInFunction();
        } else {
            $config->failInFunction($this->modelRegiao->handleError());
        }
        $config->redirect("index.php?op=cad-regi");
    }
    
    /**
     * Obtém uma região em específico do modelo de regiões.
     * @param int $regiao_id identificador da região
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras 
     * serão atribuídos no lugar dos indexes inteiros.
     * @return Regiao região em específico ou uma região vazia em caso de inexistência.
     */
    public function getRegiao($regiao_id, $foreign_values = false){
        if($foreign_values)
            $regiao = $this->modelRegiao->superSelect(RegiaoModel::TABLE.".".RegiaoModel::ID." = ".$regiao_id);
        else
            $regiao = $this->modelRegiao->select("*", RegiaoModel::TABLE.".".RegiaoModel::ID." = ".$regiao_id);
        
        if(count($regiao)) return $regiao;
        return new Regiao();
    }
    
    /**
     * Obtém uma lista de regiões do modelo.
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras 
     * serão atribuídos no lugar dos indexes inteiros.
     * @param int $byLoja indentificador da loja, esse parametro, se for um inteiro válido, 
     * filtra as vendas por loja.
     * @return array lista de regiões do tipo Regiao
     */
    public function getAllRegioes($foreign_values = false, $byLoja = false){
        $condition = null;
        if($byLoja){
            $condition = RegiaoModel::TABLE.".".RegiaoModel::LOJA." = $byLoja";
        }
        if($foreign_values){
            return $this->modelRegiao->superSelect($condition);
        }
        return $this->modelRegiao->select("*", $condition);
    }
    
    /**
     * Obtém uma lista de regiões filtradas pelo cobrador.
     * @param int $idCobrador identificador do cobrador
     * @return array lista de regiões do tipo Regiao
     */
    public function getRegioesByCobrador($idCobrador){
        $condition = RegiaoModel::COBRADOR." = $idCobrador";
        return $this->modelRegiao->select("*", $condition);
    }
    
    /**
     * Obtém uma lista de regiões filtradas pela loja.
     * @param int $id_loja identificador da loja
     * @return array lista de regiões do tipo Regiao
     */
    public function getRegioesByLoja($id_loja){
        $condition = RegiaoModel::LOJA." = $id_loja ORDER BY " . RegiaoModel::NOME;
        return $this->modelRegiao->select("*", $condition);
    }

    /**
     * Obtém a lisa de estado do sistema.
     * @return array lista de estados do tipo Estado
     */
    public function getEstados(){
        return $this->modelEstado->select(array(EstadoModel::SIGLA, EstadoModel::ID));	
    }
    
    /**
     * Obtém um estado específico do modelo.
     * @param int $id_estado identificador do estatdo
     * @return Estado estado específico ou estado vazio em caso de inexistência
     */
    public function getEstado($id_estado){
        $condition = EstadoModel::ID." = $id_estado";
        $estado = $this->modelEstado->select("*", $condition);
        return empty($estado)? new Estado(): $estado[0];
    }

    /**
     * Obtém uma lista de cidades filtradas pelo estado.
     * @param int $idestado identificador do estado
     * @return array lisat de cidades do tipo Cidade
     */
    public function getCidadesByEstado($idestado){
        return $this->modelCidade->select("*", CidadeModel::ESTADO . " = " . $idestado);
    }
    
    /**
     * Obtém uma cidade específica do modelo.
     * @param int $idCidade identificador da cidade
     * @return Cidade cidade espcífica ou cidade vazia em caso de inexistência
     */
    public function getCidade($idCidade){
        $cidade = $this->modelCidade->select("*", CidadeModel::ID." = $idCidade");
        if(!empty($cidade)) return $cidade[0];
        return new Cidade();
    }
    
}
?>
