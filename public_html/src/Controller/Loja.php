<?php
namespace Sisoptica\Controller;
//Impotando as classes de entidades usadas nesse controlador
include_once ENTITIES.'loja.php';
include_once ENTITIES."telefone.php";
include_once ENTITIES."dados-boleto.php";

//Impotando as classes de modelos usadas nesse controlador
include_once MODELS.'loja.php';
include_once MODELS."telefone.php";
include_once MODELS."dados-boleto.php";

/**
 * Essa classe é quem controla o fluxo de informação do e para o modelo de lojas e
 * de telefones das lojas.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class LojaController {
    
    /**
     * @var LojaModel instancia do modelo de lojas usado nesse controlador
     * @access private
     */
    private $modelLoja;
    
    /**
     * @var TelefoneModel instancia do modelo de telefones usado nesse controlador
     * @access private
     */
    private $modelTelefone;
    
    /**
     * @var DadosBoletoModel instancia do modelo de dados bancarios usado nesse controlador
     */
    private $modelDadosBancarios;
    
    /**
     * Essa classe é quem controla o fluxo de informação do e para o modelo de lojas e
     * de telefones das lojas.
     * @return LojaController instancia de um controlador de ordens de lojas
     */
    public function __construct() {
        $this->modelLoja = new LojaModel();
        $this->modelTelefone = new TelefoneModel();
        $this->modelDadosBancarios = new DadosBoletoModel();
    }

    /**
     * Este método adiciona ou atualiza uma loja no modelo.
     * @param Loja $loja loja a ser inserida ou atualizada. Se for <b>null</b> os dados da 
     * requisição serão captados e atribuídos à <i>loja</i>
     */
    public function addLoja(Loja $loja = null){
        if($loja == null){
            $loja = new Loja();
            //Atribuindo dados da requisição
            $this->linkLoja($loja);
        }
        
        $config = Config::getInstance();
        
        //Checando se os dados obigratórios estão sendo enviados dos dados
        if( empty($loja->rua) || empty($loja->bairro) || empty($loja->cep) ||
            empty($loja->cnpj) || empty($loja->cidade) || empty($loja->sigla)||
            empty($loja->cgc) )
        {
            $config->failInFunction("Os campos: Sigla, Cidade, Rua, Bairro, CEP, CNPJ e CGC são necessários para inserção");
            $config->redirect("index.php?op=cad-loja");
        }
          
        $hasId = $config->filter("for-update-id");
        $res = false; //Resultado para loja
        $resTel = true; //Resultado para telefones
        // Verificando a existência de um id na requisção. 
        // Caso exista a função de atualização do modelo será chamada, caso contrário, 
        // a de inserção será chamada.
        if(empty($hasId)){
            //Inserção
            $res = $this->modelLoja->insert($loja);
            if($res){
                foreach ($loja->telefones as $telefone){
                    $telefone->dono = $res;
                }
                $resTel = empty($loja->telefones) ? true : $this->modelTelefone->insert(TelefoneModel::TABLE_LOJA, $loja->telefones);
            }
        } else {
            //Atualização
            $loja->id = $hasId;
            $res = $this->modelLoja->update($loja);
            foreach ($loja->telefones as $telefone){
                $telefone->dono = $loja->id;
            }
            if(empty ($loja->telefones)){
                $resTel =  $this->modelTelefone->delete(TelefoneModel::TABLE_LOJA, $loja->id);
            } else
                $resTel =  $this->modelTelefone->delete(TelefoneModel::TABLE_LOJA, $loja->id) && 
                           $this->modelTelefone->insert(TelefoneModel::TABLE_LOJA, $loja->telefones);
        }
        
        if($res){
            $message = ($resTel) ? null : "Porém telefones não foram inseridos";
            $config->successInFunction($message);
        } else {
            $config->failInFunction($this->modelLoja->handleError());                
        }
        
        $config->redirect("index.php?op=cad-loja");
        
    }
    
    /**
     * Remove uma loja da base de dados de acordo com o identificador passado na requisição
     */
    public function removeLoja(){
        $config = Config::getInstance();
        $id_loja = $config->filter("loja");
        if(isset($id_loja)){
            $this->modelLoja->begin();
            if( $this->modelTelefone->delete(TelefoneModel::TABLE_LOJA, $id_loja) &&
                $this->modelLoja->delete($id_loja) )
            {
                $this->modelLoja->commit();
                $config->successInFunction();
            } else {
                $this->modelLoja->rollBack();
                $config->failInFunction($this->modelLoja->handleError());
            }    
        } else {
            $config->failInFunction("Loja não informada");
        }
        $config->redirect("?op=cad-loja");
    }
    
    /**
     * Esse método linka os dados da requisção que diz respeito à loja 
     * com um objeto passado como referência
     * @param Loja $loja uma referência de uma loja que vai ser preenchida com os dados da requisição
     * @access private
     */
    private function linkLoja(Loja &$loja){
        $config = Config::getInstance();
        $loja->sigla = mb_strtoupper($config->filter("sigla"), 'UTF-8');
        $loja->rua = mb_strtoupper($config->filter("rua"), 'UTF-8');
        $loja->numero = $config->filter("numero");
        $loja->bairro = mb_strtoupper($config->filter("bairro"), 'UTF-8');
        $cep = $config->filter("cep");
        $loja->cep = str_replace(array(".","-"), "", $cep);
        $cnpj = $config->filter("cnpj");
        $loja->cnpj = str_replace(array(".","-","/"), "", $cnpj);
        $loja->cidade = (int) $config->filter("cidade");
        $loja->cgc = $config->filter("cgc");
        $loja->gerente = $config->filter("gerente");
        $loja->inativoParaCaixa = !is_null($config->filter("inativo-para-caixa"));
		$loja->telefones = array();
        //Atribuindo os telefones
        $i = 1;
        $telefone = $config->filter("telefone-$i");
        while(!is_null($telefone)){
            if(!empty($telefone)){
                $tel = str_replace(array("(",")","-"," "), "", $telefone);
                $loja->telefones[] = new Telefone($tel);
            }
            $i++;
            $telefone = $config->filter("telefone-$i");
        }
    }
    
    /**
     * Obtém todas as lojas do sistema.
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras 
     * serão atribuídos no lugar dos indexes inteiros.
     * @return array lista de lojas do tipo Loja
     */
    public function getAllLojas($foreign_values = false){
        if($foreign_values){
            return $this->modelLoja->superSelect();
        }
        return $this->modelLoja->select();
    }
	
	/**
     * Obtém todas as lojas do sistema não inativas para operações de caixa.
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras 
     * serão atribuídos no lugar dos indexes inteiros.
     * @return array lista de lojas do tipo Loja não inativas para operações de caixa
     */
    public function getAllLojasNaoInativasParaCaixa($foreign_values = false){
        $condition = LojaModel::INATIVO_PARA_CAIXA . " = FALSE";
		if($foreign_values){
            return $this->modelLoja->superSelect($condition);
        }
        return $this->modelLoja->select("*", $condition);
    }
    
    /**
     * Obtém todos os telefones de uma loja.
     * @param int $loja_id identificador
     * @return array lista de telefones do tipo Telefone
     */
    public function getAllTelefonesOfLoja($loja_id){
        $condition = TelefoneModel::LOJA." = $loja_id";
        return $this->modelTelefone->select(TelefoneModel::TABLE_LOJA, $condition);
    }
    
    /**
     * Obtém uma loja específica do sistema.
     * @param int $id_loja identificador da loja
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras 
     * serão atribuídos no lugar dos indexes inteiros. 
     * @return Loja a loja específica ou uma loja vazia em caso de inexistência
     */
    public function getLoja($id_loja, $foreign_values = false){
        $condition = LojaModel::TABLE.".".LojaModel::ID." = $id_loja";
        if($foreign_values) { 
            $loja = $this->modelLoja->superSelect($condition);
        }
        else $loja = $this->modelLoja->select("*", $condition);
        if(!empty($loja)) return $loja[0];
        return new Loja();
    }
    
    /**
     * Obtém os dados para faturamento de boleto de uma loja.
     * @param int $loja_id identificador da loja
     * @return DadosBoleto dados de faturamento bancário dos boletos de uma loja
     */
    public function getDadosBancariosLoja($loja_id){
        $condition  = DadosBoletoModel::LOJA.'='.$loja_id;
        $res        = $this->modelDadosBancarios->select('*', $condition);
        if(empty($res)) { return new DadosBoleto(); }
        return $res[0];
    }
    
    /**
     * Obtém os dados para faturamento de boleto da loja padrão de faturamento.
     * @return DadosBoleto dados de faturamento bancário dos boletos da loja padrão
     */
    public function getDadosBancariosDaLojaPadrao(){
        $condition  = DadosBoletoModel::PADRAO.' = TRUE';
        $res        = $this->modelDadosBancarios->select('*', $condition);
        if(empty($res)) { return new DadosBoleto(); }
        return $res[0];
    }
    
    /**
     * Obtém os dados para faturamento de boleto de uma loja ou da loja padrão, 
     * se o da loja não existir.
     * @param int $loja_id identificador da loja
     * @return DadosBoleto dados de faturamento bancário dos boletos de uma loja 
     * ou da lja padrão, caso o registro não tenha sido efetuado
     */
    public function getDadosBancariosLojaOrPadrao($loja_id){
        $dados = $this->getDadosBancariosLoja($loja_id);
        if($dados->loja == 0){
            $dados = $this->getDadosBancariosDaLojaPadrao();
        }
        return $dados;
    }
    
}
?>
