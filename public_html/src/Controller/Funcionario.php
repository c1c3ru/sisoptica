<?php
namespace Sisoptica\Controller;
//Impotando as classes de entidades usadas nesse controlador
include_once ENTITIES."cargo.php";
include_once ENTITIES."perfil.php";
include_once ENTITIES."funcionario.php";
include_once ENTITIES."telefone.php";

//Impotando as classes de modelos usadas nesse controlador
include_once MODELS."cargo.php";
include_once MODELS."perfil.php";
include_once MODELS."funcionario.php";
include_once MODELS."telefone.php";

/**
 * Essa classe é quem controla o fluxo de informação do e para o modelo de funcionários,
 * de perfis, cargos e telefones de funcionários.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class FuncionarioController {

    /**
     * @var CargoModel instancia do modelo de cargos usado nesse controlador
     * @access private
     */
    private $modelCargo;

    /**
     * @var PerilModel instancia do modelo de perfis usado nesse controlador
     * @access private
     */
    private $modelPefil;

    /**
     * @var FuncionarioModel instancia do modelo de funcionários usado nesse controlador
     * @access private
     */
    private $modelFuncionario;

    /**
     * @var TelefoneModel instancia do modelo de telefones de funcionários usado nesse controlador
     * @access private
     */
    private $modelTelefone;

    /**
     * Essa classe é quem controla o fluxo de informação do e para o modelo de funcionários,
     * de perfis, cargos e telefones de funcionários.
     * @return FuncionarioController instancia de um controlador de ordens de funcionários
     */
    public function __construct() {
        $this->modelCargo = new CargoModel();
        $this->modelPefil = new PerfilModel();
        $this->modelFuncionario = new FuncionarioModel();
        $this->modelTelefone = new TelefoneModel();
    }

    /**
     * Obtém uma lista de todos os cargos do sistema
     * @return array lista de cargos do tipo Cargo
     */
    public function getAllCargos(){
        return $this->modelCargo->select();
    }

    /**
     * Obtém um cargo em específico.
     * @param int $id_cargo identificador do cargo
     * @return Cargo cargo específico ou cargo vazio em caso de inexistência.
     */
    public function getCargo($id_cargo){
        $cargo = $this->modelCargo->select("*", CargoModel::ID."= $id_cargo");
        if(!empty($cargo)) return $cargo[0];
        return new Cargo();
    }

    /**
     * Obtém uma lista de todos os perfis do sistema
     * @param bool $noAdmin se for true, a lista de perfis desconsidera o perfil de administrador
     * @return array lista de perfis do tipo Perfil
     */
    public function getAllPerfis($noAdmin = false){
        $condition = null;
        if($noAdmin) $condition = PerfilModel::ID." <> ".PERFIL_ADMINISTRADOR;
        return $this->modelPefil->select("*", $condition);
    }

    /**
     * Obtém um perfil em específico.
     * @param int $id_perfil identificador do perfil
     * @return Perfil perfil específico ou perfil vazio em caso de inexistência.
     */
    public function getPerfil($id_perfil){
        if(empty($id_perfil)) return new Perfil();
        $perfil = $this->modelPefil->select("*", PerfilModel::ID."= $id_perfil");
        if(!empty($perfil)) return $perfil[0];
        return new Perfil();
    }

    /**
     * Obtém uma lista de todos os cobradores.
     * @param int $byloja identificador da loja. Se for false, todos os cobradores serão considerados
     * @param bool $ativados se true, todos os ativados serão obtidos, se for false todos serão considerados
     * @return array lista de cobradores do tipo Funcionario
     */
    public function getAllCobradores($byloja = false, $ativados = true){
        if($byloja) {
            $condition = FuncionarioModel::CARGO." = ".CargoModel::COD_COBRADOR;
            $condition .= " AND ".FuncionarioModel::LOJA." = ".$byloja;
        } else {
            $condition = FuncionarioModel::CARGO." = ".CargoModel::COD_COBRADOR;
        }
        if($ativados) $condition .= " AND ".FuncionarioModel::STATUS." = TRUE ";
        return $this->modelFuncionario->select("*", $condition);
    }

    /**
     * Obtém uma lista de todos os oculistas do sistema.
     * @return array lista de oculista do tipo Funcionario
     */
    public function getAllOculistas(){
        $condition = FuncionarioModel::CARGO." = ".CargoModel::COD_OCULISTA;
        return $this->modelFuncionario->select("*", $condition);
    }

    /**
     * Obtém uma lista de todos os gerentes do sistema.
     * @return array lista de gerentes do tipo Funcionario
     */
    public function getAllGerentes(){
        $condition = FuncionarioModel::PERFIL." = ".PERFIL_GERENTE;
        return $this->modelFuncionario->select("*", $condition);
    }

    /**
     * Obtém uma lista de todos os diretores de loja do sistema.
     * @return array lista de diretores do tipo Funcionario
     */
    public function getAllDiretores(){
        $condition = FuncionarioModel::CARGO." = ".CargoModel::COD_DIRETOR;
        return $this->modelFuncionario->select("*", $condition);
    }

    /**
     * Obtém uma lista de todos os funcionários por cargo informado.
     * @param mixed id do cargo ou uma lista de ids de cargos
     * @return array lista de funcionários do tipo Funcionario
     */
    public function getAllByCargo($cargoid, $byloja = false, $ativos = false) {

        if (is_array($cargoid) && !empty($cargoid)) {
            $condition = FuncionarioModel::CARGO." IN (".implode(',', $cargoid).") ";
        } else {
            $condition = FuncionarioModel::CARGO." = ".$cargoid;
        }

        if ($ativos) {
            $condition .= " AND ".FuncionarioModel::STATUS . " = " . $ativos;
        }

        if ($byloja) {
            $condition .= " AND ".FuncionarioModel::LOJA." = ".$byloja;
        }

        return $this->modelFuncionario->select("*", $condition);
    }

    /**
     * Obtém uma lista de todos os vendedores do sistema.
     * @param int $byloja identificador da loja. Se for false, todos os cobradores serão considerados
     * @param bool $ativados se true, todos os ativados serão obtidos, se for false todos serão considerados
     * @return array lista de vendedores do tipo Funcionario
     */
    public function getAllVendedores($byloja = false, $ativados = true){
        if($byloja) {
            $condition = FuncionarioModel::CARGO." = ".CargoModel::COD_VENDEDOR;
            $condition .= " AND ".FuncionarioModel::LOJA." = ".$byloja;
        } else {
            $condition = FuncionarioModel::CARGO." = ".CargoModel::COD_VENDEDOR;
        }
        if($ativados) $condition .= " AND ".FuncionarioModel::STATUS." = TRUE ";
        return $this->modelFuncionario->select("*", $condition);
    }

    /**
     * Obtém uma lista de todos os agentes de venda do sistema.
     * @param int $byloja identificador da loja. Se for false, todos os cobradores serão considerados
     * @param bool $ativados se true, todos os ativados serão obtidos, se for false todos serão considerados
     * @return array lista de agentes de venda do tipo Funcionario
     */
    public function getAllAgentesVenda($byloja = false, $ativados = true){
        if($byloja) {
            $condition = FuncionarioModel::CARGO." = ".CargoModel::COD_AGENTE;
            $condition .= " AND ".FuncionarioModel::LOJA." = ".$byloja;
        } else {
            $condition = FuncionarioModel::CARGO." = ".CargoModel::COD_AGENTE;
        }
        if($ativados) $condition .= " AND ".FuncionarioModel::STATUS." = TRUE ";
        return $this->modelFuncionario->select("*", $condition);
    }

    /**
     * Este método adiciona ou atualiza um funcionário no modelo.
     * @param Funcionario $funcionario funcionario a ser inserido ou atualizado. Se for <b>null</b> os dados da
     * requisição serão captados e atribuídos à <i>funcionario</i>
     */
    public function addFuncionario(Funcionario $funcionario = null){

        if ($funcionario == null) {
            $funcionario = new Funcionario();
            //Atribuindo dados da requisição
            $this->linkFuncionario($funcionario);
        }

        $config = Config::getInstance();

        $hasId = $config->filter("for-update-id");

        //Checando se os dados obigratórios estão sendo enviados dos dados
        if (empty($funcionario->nome) || empty($funcionario->cpf) ||
            empty($funcionario->rg) || (!$hasId && empty($funcionario->cargo)) ||
            empty($funcionario->loja) ) {

            $config->failInFunction("Os campos: Nome, CPF, RG e Cargo, são necessários para inserção");
            $config->redirect("index.php?op=cad-func");
        }

        $res = false;
        $this->modelFuncionario->begin();//Iniciando transição

        // Verificando a existência de um id na requisção.
        // Caso exista a função de atualização do modelo será chamada, caso contrário,
        // a de inserção será chamada.
        if (empty($hasId)) {
            //Inserção de funcionário
            $funcionario->senha = md5($funcionario->senha);
            $res = $this->modelFuncionario->insert($funcionario);
            if($res) {
                //Inserindo os telefones
                foreach ($funcionario->telefones as $telefone){
                    $telefone->dono = $res;
                }
                if(!empty($funcionario->telefones))
                    $res = $this->modelTelefone->insert(TelefoneModel::TABLE_FUNC, $funcionario->telefones);
            }
        } else {

            // Caso cargo tenha sido alterado, somente diretor pode alterá-lo.
            $oldFuncionario = $this->getFuncionario($hasId);
            if (!empty($funcionario->cargo)) {
                if ($oldFuncionario->cargo !== $funcionario->cargo &&
                    /* notIsDiretor: */ $_SESSION[SESSION_CARGO_FUNC] != CargoModel::COD_DIRETOR ) {

                    $config->failInFunction("Você não tem permissão para alterar o cargo do funcionário");
                    $config->redirect("index.php?op=cad-func");
                }
            } else {
                $funcionario->cargo = $oldFuncionario->cargo;
            }

            //Atualizando o funcionário
            $funcionario->id = $hasId;
            $res = $this->modelFuncionario->update($funcionario);

            //Atualizando somente a senha, caso o campo tenha sido alterado
            if(!empty($funcionario->senha)){
                $this->modelFuncionario->simpleUpdate(  FuncionarioModel::SENHA,
                                                        "'".md5($funcionario->senha)."'",
                                                        FuncionarioModel::ID." = {$funcionario->id}");
            }

            if($res) {

                //Inserindo os telefones
                foreach ($funcionario->telefones as $telefone){
                    $telefone->dono = $funcionario->id;
                }

                if(empty($funcionario->telefones)){
                    $res = $this->modelTelefone->delete(TelefoneModel::TABLE_FUNC, $funcionario->id);
                } else {
                    $res =  $this->modelTelefone->delete(TelefoneModel::TABLE_FUNC, $funcionario->id)
                            && $this->modelTelefone->insert(TelefoneModel::TABLE_FUNC, $funcionario->telefones);
                }

            }

            if ($config->filter("reativar") != null) {
                $this->modelFuncionario->active($funcionario->id);
            }
        }

        if ($res) {
            $this->modelFuncionario->commit(); //Commitando as alterações
            $config->successInFunction();
        } else {
            $this->modelFuncionario->rollBack(); //Rollback das mudanças
            $config->failInFunction($this->modelFuncionario->handleError());
        }
        $config->redirect("index.php?op=cad-func");
    }

    /**
     * Esse método linka os dados da requisção que diz respeito ao funcionário
     * com um objeto passado como referência
     * @param Funcionário $funcionario uma referência de um funcionário que vai ser preenchida com os dados da requisição
     * @access private
     */
    private function linkFuncionario(Funcionario &$funcionario){
        $config = Config::getInstance();
        $funcionario->nome          = mb_strtoupper($config->filter("nome"), 'UTF-8');
        $funcionario->login         = mb_strtoupper($config->filter("login"), 'UTF-8');
        if(empty($funcionario->login)) $funcionario->login = null; //UNIQUES AND CAN BE NULL
        $funcionario->senha         = mb_strtoupper($config->filter("senha"), 'UTF-8');
        $funcionario->nascimento    = $config->filter("nascimento");
        $funcionario->nascimento    = empty($funcionario->nascimento) ? null : $funcionario->nascimento;
        $funcionario->admissao      = $config->filter("admissao");
        $funcionario->admissao      = empty($funcionario->admissao) ? null : $funcionario->admissao;
        $funcionario->demissao      = $config->filter("demissao");
        $funcionario->demissao       = empty($funcionario->demissao) ? null : $funcionario->demissao;
        $funcionario->cidade        = $config->filter("cidade");
        $funcionario->rua           = mb_strtoupper($config->filter("rua"), 'UTF-8');
        $funcionario->numero        = $config->filter("numero");
        $funcionario->bairro        = mb_strtoupper($config->filter("bairro"), 'UTF-8');
        $cep = $config->filter("cep");
        $funcionario->cep           = str_replace(array(".","-"), "", $cep);
        $cpf = $config->filter("cpf");
        $funcionario->cpf           = str_replace(array(".","-"), "", $cpf);
        $funcionario->rg            = $config->filter("rg");
        $funcionario->cpt           = $config->filter("cpt");
        $funcionario->referencia    = mb_strtoupper($config->filter("referencia"), 'UTF-8');
        $funcionario->banco         = mb_strtoupper($config->filter("banco"), 'UTF-8');
        $funcionario->agencia       = $config->filter("agencia");
        $funcionario->conta         = $config->filter("conta");
        $funcionario->cargo         = $config->filter("cargo");
        $funcionario->loja          = $config->filter("loja");
        $funcionario->perfil        = $config->filter("perfil");
        $funcionario->email         = mb_strtoupper($config->filter("email"), 'UTF-8');
        if(empty($funcionario->email)) $funcionario->email = null;//UNIQUES AND CAN BE NULL
        $funcionario->status        = !is_null($config->filter("reativar"));
        $funcionario->inativoParaCaixa = !is_null($config->filter("inativo-para-caixa"));
		$funcionario->telefones = array();
        $i = 1;
        $telefone = $config->filter("telefone-$i");
        while(!is_null($telefone)){
            if(!empty($telefone)){
                $tel = str_replace(array("(",")","-"," "), "", $telefone);
                $funcionario->telefones[] = new Telefone($tel);
            }
            $i++;
            $telefone = $config->filter("telefone-$i");
        }
    }

    /**
     * Remove um funcionário da base de dados de acordo com o identificador passado na requisição.
     */
    public function removeFuncionario(){
        $config = Config::getInstance();
        $id_func = $config->filter("func");
        if(isset($id_func)){
            $res = $this->modelFuncionario->desactive($id_func);
            if($res) {
                $config->successInFunction();
            } else {
                $config->failInFunction($this->modelFuncionario->handleError());
            }
        } else {
            $config->failInFunction("Funcionário não informado");
        }
        $config->redirect("?op=cad-func");
    }

    /**
     * Obtém todos os funcionários do sistema.
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras
     * serão atribuídos no lugar dos indexes inteiros.
     * @param int $byLoja indentificador da loja, esse parametro, se for um inteiro válido,
     * filtra as vendas por loja.
     * @return array uma lista de funcionários do tipo Funcionario
     */
    public function getAllFuncionarios($foreign_values = false, $byLoja = false, $so_ativos = false){
        $conditions = array();
		if($byLoja) $conditions[] = FuncionarioModel::LOJA." = $byLoja";
        if($so_ativos) $conditions[] = FuncionarioModel::STATUS . " = TRUE ";
        $condition = implode(" AND ", $conditions);
        if($foreign_values){
            return $this->modelFuncionario->superSelect($condition);
        }
        return $this->modelFuncionario->select(" * ", $condition);
    }

	/**
     * Obtém todos os funcionários do sistema.
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras
     * serão atribuídos no lugar dos indexes inteiros.
     * @param int $byLoja indentificador da loja, esse parametro, se for um inteiro válido,
     * filtra as vendas por loja.
     * @return array uma lista de funcionários do tipo Funcionario
     */
	public function getAllFuncionariosNaoInativosParaCaixa($foreign_values = false, $byLoja = false){
		$conditions = array();
		if($byLoja) $conditions[] = FuncionarioModel::LOJA." = $byLoja";
		$conditions[] = FuncionarioModel::STATUS . " = TRUE ";
        $conditions[] = FuncionarioModel::INATIVO_PARA_CAIXA . " = FALSE ";
        $condition = implode(" AND ", $conditions);
		if($foreign_values){
            return $this->modelFuncionario->superSelect($condition);
        }
        return $this->modelFuncionario->select(" * ", $condition);
	}

    /**
     * Obtém um funcionário em específico.
     * @param mixed $id_func_or_login id de um funcionário ou login.
     * @param string $senha_login se não for vazia, ela será usada para o login de funcionários.
     * @param bool $foreign_values se for true, os valores textuais das chaves estrangeiras
     * @return Funcionaro um funcionário específico, ou um funcionário vazio em caso de inexistência.
     */
    public function getFuncionario($id_func_or_login, $senha_login = "", $foreign_values = false){
        if(empty($senha_login))
            $condition = FuncionarioModel::TABLE.'.'.FuncionarioModel::ID."= $id_func_or_login";
        else
            $condition = FuncionarioModel::LOGIN." = '$id_func_or_login' AND ".
                         FuncionarioModel::SENHA." = md5('".mb_strtoupper($senha_login, 'UTF-8')."')";
        if(!$foreign_values) {
            $funcionario = $this->modelFuncionario->select("*", $condition);
        } else {
            $funcionario = $this->modelFuncionario->superSelect($condition);
        }
        if(!empty($funcionario)) return $funcionario[0];
        return new Funcionario();
    }

    /**
     * Obtém todos os telefones de um funcionário.
     * @param int $func_id identificador do funcionário.
     * @return array lista de telefones do tipo Telefone.
     */
    public function getAllTelefonesOfFuncionario($func_id){
        $condition = TelefoneModel::FUNCIONARIO." = $func_id";
        return $this->modelTelefone->select(TelefoneModel::TABLE_FUNC,$condition);
    }
}

?>
