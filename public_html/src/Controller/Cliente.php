<?php
namespace Sisoptica\Controller;
//Impotando as classes de entidades usadas nesse controlador
include_once ENTITIES."cliente.php";
include_once ENTITIES."telefone.php";

//Impotando as classes de modelos usadas nesse controlador
include_once MODELS."cliente.php";
include_once MODELS."telefone.php";

/**
 * Essa classe é quem controla o fluxo de informação do e para o modelo de clientes e 
 * modelo de telefone para clientes.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class ClienteController {
    
    /**
     * @var ClienteModel instancia do modelo de cliente usado nesse controlador
     * @access private
     */
    private $modelCliente;
    
    /**
     * @var TelefoneModel instancia do modelo de telefone usado nesse controlador
     * @access private
     */
    private $modelTelefone;

    /**
     * Essa classe é quem controla o fluxo de informação do e para o modelo de clientes e
     * modelo de telefone para clientes.
     * @return ClienteController instancia de um controlador de ordens de clientes
     */ 
    public function __construct() {
        $this->modelCliente = new ClienteModel();
        $this->modelTelefone = new TelefoneModel();
    }
    
    /**
     * Este método adiciona ou atualiza um cliente no modelo.
     * @param Cliente $cliente cliente a ser inserido ou atualizado. Se for <b>null</b> os dados da 
     * requisição serão captados e atribuídos à <i>cliente</i>
     * @param bool $ajaxMode indica se a resposta deve ser gravada em cookie ou escrita em JSON. 
     */
    public function addCliente(Cliente $cliente = null, $ajaxMode = false){
        
        if(is_null($cliente)){
            $cliente = new Cliente();
            //Atribuindo dados da requisição
            $this->linkCliente($cliente);
        }
        
        $messageError = $ajaxMode ? "throwAjaxError": "failInFunction";
        
        $config = Config::getInstance();
        
        //Checando se os dados obigratórios estão sendo enviados dos dados
        if( empty($cliente->nome) || empty($cliente->endereco) || empty($cliente->localidade)
           || empty($cliente->numero) || empty($cliente->bairro) ) {
            $config->$messageError("Os campos: Nome, Endereço, Número, Bairro e Localidade são necessários para inserção");
            $config->redirect("index.php?op=cad-clie");
        }

        $hasId = $config->filter("for-update-id");
        $res = false; //Resultado da inserção do cliente
        $resTel = true; //Resultado da inserção dos telefones
        // Verificando a existência de um id na requisção. 
        // Caso exista a função de atualização do modelo será chamada, caso contrário, 
        // a de inserção será chamada.
        if(empty($hasId)){
            //Inseridno e obtendo o id
            $res = $this->modelCliente->insert($cliente);
            if($res) {
                $cliente->id = $res;
                foreach ($cliente->telefones as $telefone){
                    $telefone->dono = $cliente->id;
                }
                $resTel = empty($cliente->telefones) ? true : $this->modelTelefone->insert(TelefoneModel::TABLE_CLIE, $cliente->telefones);
            }
        } else {
            
			$oldCliente = $this->getCliente($hasId);
			if($oldCliente->bloqueado && $cliente->bloqueado == false &&
                $_SESSION[SESSION_PERFIL_FUNC] != PERFIL_ADMINISTRADOR &&
                $_SESSION[SESSION_CARGO_FUNC] != CargoModel::COD_DIRETOR) {
				$cliente->bloqueado = true;
			}
			
			$cliente->id = $hasId;
            $res = $this->modelCliente->update($cliente);
            if ($res) {
				foreach ($cliente->telefones as $telefone){
					$telefone->dono = $cliente->id;
				}
				if(empty ($cliente->telefones)){
					$resTel = $this->modelTelefone->delete(TelefoneModel::TABLE_CLIE, $cliente->id);
				}  else
					$resTel = $this->modelTelefone->delete(TelefoneModel::TABLE_CLIE, $cliente->id) && 
							  $this->modelTelefone->insert(TelefoneModel::TABLE_CLIE, $cliente->telefones);
            }
        }
        
        if($res){
            $message = ($resTel) ? null : "Porém telefones não foram inseridos";
            
            if(!$ajaxMode) $config->successInFunction($message);
            else return $cliente;
        
        } else {
            $config->$messageError($this->modelCliente->handleError());
        }
        
        $config->redirect("index.php?op=cad-clie");
    }
    
    /**
     * Esse método remove um cliente de acordo com o identificador passado na requisição.
     */
    public function removeCliente(){
        $config = Config::getInstance();
        $id_cliente = $config->filter("clie");
        if(isset($id_cliente)){
            $this->modelCliente->begin();
            $res = $this->modelTelefone->delete(TelefoneModel::TABLE_CLIE, $id_cliente) && $this->modelCliente->delete($id_cliente); 
            if($res){
                $this->modelCliente->commit();
                $config->successInFunction();
            } else {
                $this->modelCliente->rollBack();
                $config->failInFunction($this->modelCliente->handleError());
            }    
        } else {
            $config->failInFunction("Cliente não informado");
        }
        $config->redirect("index.php?op=cad-clie");
    }
    
    /**
     * Esse método linka os dados da requisção que diz respeito ao cliente 
     * com um objeto passado como referência
     * @param Cliente $cliente uma referência de um cliente que vai ser preenchido com os dados da requisição
     * @access private
     */
    private function linkCliente(Cliente &$cliente){
        $config = Config::getInstance();
        $cliente->id                = $config->filter("id");
        $cliente->nome              = mb_strtoupper($config->filter("nome"), 'UTF-8');
        $cliente->nascimento        = $config->filter("nascimento");
        $cliente->apelido           = mb_strtoupper($config->filter("apelido"), 'UTF-8');
        $rg                         = $config->filter("rg");
        $cliente->rg                = empty($rg) ? null : $rg;
        $cliente->orgaoEmissor      = mb_strtoupper($config->filter("orgao-emissor"), 'UTF-8');
        $cpf                        = $config->filter("cpf");
        $cliente->cpf               = empty($cpf) ? null : str_replace(array(".","-"), "", $cpf);
        $cliente->conjugue          = mb_strtoupper($config->filter("conjugue"), 'UTF-8');
        $cliente->nomePai           = mb_strtoupper($config->filter("nome-pai"), 'UTF-8');
        $cliente->nomeMae           = mb_strtoupper($config->filter("nome-mae"), 'UTF-8');
        $cliente->endereco          = mb_strtoupper($config->filter("endereco"), 'UTF-8');
        $cliente->numero            = $config->filter("numero");
        $cliente->bairro            = mb_strtoupper($config->filter("bairro"), 'UTF-8');
        $cliente->referencia        = mb_strtoupper($config->filter("referencia"), 'UTF-8');
        $cliente->casaPropria       = (bool) $config->filter("casa-propria");
        $cliente->tempoCasaPropria  = mb_strtoupper($config->filter("tempo-casa-propria"), 'UTF-8');
        $cliente->observacao        = mb_strtoupper($config->filter("observacao"), 'UTF-8');
        $cliente->rendaMensal       = (double)$config->filter("renda");
        $cliente->localidade        = (int) $config->filter("localidade");
        $cliente->bloqueado         = !is_null($config->filter("bloqueado"));
        $cliente->telefones         = array();
        //Obtendo os telefones da requisição
        $i = 1;
        $telefone = $config->filter("telefone-$i");
        while($telefone != null){
            if(!empty($telefone)){
                $tel = str_replace(array("(",")","-"," "), "", $telefone);
                $cliente->telefones[] = new Telefone($tel);
            }
            $i++;
            $telefone = $config->filter("telefone-$i");
        }
    }
    
    /**
     * Obtém todos os clientes da base de dados.
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras 
     * serão atribuídos no lugar dos indexes inteiros.
     * @return array lista de clientes do tipo Cliente
     */
    public function getAllClientes($foreign_values = false){
        $condition = null;
        if($foreign_values)
            return $this->modelCliente->superSelect($condition);
        return $this->modelCliente->select("*", $condition);
    }
    
    /**
     * Obtém uma lista de clientes da base de dados filtrados pelo nome e pelo CPF. 
     * Mais utilizada para a Grid de Cleintes devido a lentidão causada pela totalidade da busca.
     * @param string $name nome ou parte do nome de clientes.
     * @param string $cpf cpf ou parte do cpf de clientes.
     * @return array uma lista de clientes do tipo cliente.
     */
    public function getClientesForListView($name = "", $cpf = "", $venda = ""){
        $conditions  = array();
        $params = array();
        if(!empty($name)) {
            $conditions[] = ClienteModel::NOME." LIKE ?";
            $params[] = "%$name%";
        }
        if(!empty($cpf)) {
            $conditions[] = ClienteModel::CPF." LIKE ?";
            $params[] = "%$cpf%";
        }
        if(!empty($venda)) {
            $conditions[] = VendaModel::TABLE.".".VendaModel::ID." = ?";
            $params[] = $venda;
        }
        return $this->modelCliente->selectOnlyList(implode(' AND ', $conditions), $params);
    }
    
    /**
     * Obtém os telefones de um cliente.
     * @param int $cliente_id identificador do cliente
     * @return array lista de telefones do tipo Telefone
     */
    public function getAllTelefonesOfCliente($cliente_id){
        $condition = TelefoneModel::CLIENTE." = $cliente_id";
        return $this->modelTelefone->select(TelefoneModel::TABLE_CLIE, $condition);
    }
    
    /**
     * Obtém um cliente em específico da base de dados.
     * @param int $id_cliente identificador do cliente
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras 
     * serão atribuídos no lugar dos indexes inteiros.
     * @return Cliente cliente específico, ou um cliente vazio em caso de inexistência do identificador
     */
    public function getCliente($id_cliente, $foreign_values = false){
        $condition = ClienteModel::TABLE.".".ClienteModel::ID." = $id_cliente";
        if($foreign_values)
            $cliente = $this->modelCliente->superSelect($condition);
        else
            $cliente = $this->modelCliente->select("*", $condition);
        if(!empty($cliente)) return $cliente[0];
        return  new Cliente();
    }
    
    /**
     * Obtém uma lista de clientes filtrados pelo nome.
     * @param string $nome nome ou parte do nome de clientes
     * @return array lista de clientes do tipo cliente.
     */
    public function searchClientes($nome){
        $condition = ClienteModel::NOME." LIKE '%$nome%' ";
        return $this->modelCliente->superSelect($condition);
    }
    
}
?>
