<?php
namespace Sisoptica\Repository;
//Incluindo classes associadas a esse modelo
include_once MODELS."loja.php";
include_once MODELS."cargo.php";
include_once MODELS."cidade.php";
include_once MODELS."perfil.php";
require_once "database.php";

/**
 * Essa classe implementa o modelo da entidade Funcionário.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class FuncionarioModel extends Database {
    
    /**
     * Nome da tabela de funcionários no banco de dados 
     */
    const TABLE = "funcionario";
    
    /**
     * Nome da coluna do identificador do funcionário no banco de dados.
     */
    const ID        = "id";
    
    /**
     * Nome da coluna do nome do funcionário no banco de dados.
     */
    const NOME      = "nome_funcionario";
    
    /**
     * Nome da coluna do login do funcionário no banco de dados.
     */
    const LOGIN     = "login_sistema";
    
    /**
     * Nome da coluna da senha do funcionário no banco de dados.
     */
    const SENHA     = "senha";
    
    /**
     * Nome da coluna da data de nascimento do funcionário no banco de dados.
     */
    const NASCIMENTO= "data_nascimento";
    
    /**
     * Nome da coluna da data de admissão do funcionário no banco de dados.
     */
    const ADMISSAO  = "data_admissao";
    
    /**
     * Nome da coluna da data de demissão do funcionário no banco de dados.
     */
    const DEMISSAO  = "data_demissao";
    
    /**
     * Nome da coluna da cidade do funcionário no banco de dados.
     */
    const CIDADE    = "cidade";
    
    /**
     * Nome da coluna da rua do funcionário no banco de dados.
     */
    const RUA       = "rua";
    
    /**
     * Nome da coluna do número da residência do funcionário no banco de dados.
     */
    const NUMERO    = "numero";
    
    /**
     * Nome da coluna do bairro do funcionário no banco de dados.
     */
    const BAIRRO    = "bairro";
    
    /**
     * Nome da coluna do CEP do funcionário no banco de dados.
     */
    const CEP       = "cep";
    
    /**
     * Nome da coluna do CPF do funcionário no banco de dados.
     */
    const CPF       = "cpf";
    
    /**
     * Nome da coluna do registro geral(RG) do funcionário no banco de dados.
     */
    const RG        = "rg";
    
    /**
     * Nome da coluna do CPT do funcionário no banco de dados.
     */
    const CPT       = "cpt";
    
    /**
     * Nome da coluna da referência da residência do funcionário no banco de dados.
     */
    const REFERENCIA= "referencia";
    
    /**
     * Nome da coluna do banco do funcionário no banco de dados.
     */
    const BANCO     = "banco";
    
    /**
     * Nome da coluna da agência do funcionário no banco de dados.
     */
    const AGENCIA   = "agencia";
    
    /**
     * Nome da coluna da conta do funcionário no banco de dados.
     */
    const CONTA     = "conta";
    
    /**
     * Nome da coluna do identificador do cargo do funcionário no banco de dados.
     */
    const CARGO     = "id_funcionario_cargo";
    
    /**
     * Nome da coluna do identificador da loja do funcionário no banco de dados.
     */
    const LOJA      = "id_loja";
    
    /**
     * Nome da coluna do identificador do perfil do funcionário no banco de dados.
     */
    const PERFIL    = "id_perfil_sistema";
    
    /**
     * Nome da coluna do email do funcionário no banco de dados.
     */
    const EMAIL     = "email";
    
    /**
     * Nome da coluna do status do funcionário no banco de dados.
     */
    const STATUS    = "status";

	/**
	 * Nome da coluna de flag para inatividade de transações no caixa diário
	 */
	const INATIVO_PARA_CAIXA = "inativo_para_caixa";
    
    /**
     * Insere um funcionário na base de dados e, em caso de sucesso, atribui o id inserido.
     * @param Funcionario $funcionario funcionario que vai ser inserido
     * @return int id inserido em caso de sucesso na inserção ou null em caso de falha
     */
    public function insert(Funcionario $funcionario) {
        $fields = array(self::NOME,self::LOGIN,self::SENHA, self::NASCIMENTO,
                        self::ADMISSAO, self::DEMISSAO,    
                        self::CIDADE, self::RUA, self::NUMERO,
                        self::BAIRRO, self::CEP, self::CPF, self::RG, self::CPT,
                        self::REFERENCIA, self::BANCO, self::AGENCIA, self::CONTA,
                        self::CARGO, self::LOJA, self::PERFIL, self::EMAIL, 
						self::INATIVO_PARA_CAIXA);
        $vars = get_object_vars($funcionario);
        unset($vars["id"]);
        unset($vars["telefones"]);
        unset($vars["status"]);
        $values = Database::turnInValues($vars);
        $res = parent::insert(self::TABLE, implode(",", $fields), $values);
        if($res) return $this->getAnnalisses()->lastInsertedtId();
        return null;
    }
    
     /**
     * Seleciona uma lista de funcionários com os <i>fields</i> preenchidos com os valores do banco.
     * @param mixed $fields colunas da seleção, pode ser um array (utilize as constantes das colunas).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de funcionários do tipo Funcionario.
     */
    public function select($fields = " * ", $condition = null) {
        if(is_array($fields)) $fields = implode (",", $fields);
        $res = parent::select(self::TABLE, $fields, $condition);
        $ana = $this->getAnnalisses();
        $funcionarios = array();
        while(($row = $ana->fetchObject($res)) !== false){
            $funcionarios[] = new Funcionario(
                                isset($row->{ self::ID })? $row->{ self::ID } : 0,
                                isset($row->{ self::NOME })? $row->{ self::NOME } : "",
                                isset($row->{ self::LOGIN })? $row->{ self::LOGIN } : "",
                                isset($row->{ self::SENHA })? $row->{ self::SENHA } : "",
                                isset($row->{ self::NASCIMENTO })? $row->{ self::NASCIMENTO } : "",
                                isset($row->{ self::ADMISSAO })? $row->{ self::ADMISSAO } : "",
                                isset($row->{ self::DEMISSAO })? $row->{ self::DEMISSAO } : "",
                                isset($row->{ self::CIDADE })? $row->{ self::CIDADE } : 0,
                                isset($row->{ self::RUA })? $row->{ self::RUA } : "",
                                isset($row->{ self::NUMERO })? $row->{ self::NUMERO } : "",
                                isset($row->{ self::BAIRRO })? $row->{ self::BAIRRO } : "",
                                isset($row->{ self::CEP })? $row->{ self::CEP } : "",
                                isset($row->{ self::CPF })? $row->{ self::CPF } : "",
                                isset($row->{ self::RG })? $row->{ self::RG } : "",
                                isset($row->{ self::CPT })? $row->{ self::CPT } : "",        
                                isset($row->{ self::REFERENCIA })? $row->{ self::REFERENCIA } : "",
                                isset($row->{ self::BANCO })? $row->{ self::BANCO } : "",
                                isset($row->{ self::AGENCIA })? $row->{ self::AGENCIA } : "",
                                isset($row->{ self::CONTA })? $row->{ self::CONTA } : "",
                                isset($row->{ self::CARGO })? $row->{ self::CARGO } : "",
                                isset($row->{ self::LOJA })? $row->{ self::LOJA } : "",
                                isset($row->{ self::PERFIL })? $row->{ self::PERFIL } : "",
                                isset($row->{ self::EMAIL }) ? $row->{ self::EMAIL} : "",
                                isset($row->{ self::STATUS }) ? $row->{self::STATUS} : TRUE,
                                isset($row->{ self::INATIVO_PARA_CAIXA }) ? $row->{self::INATIVO_PARA_CAIXA} : FALSE
								);
        }
        return $funcionarios;
    }
    
    /**
     * Seleciona uma lista de funcionários com todos os campos preenchidos com os valores do banco.
     * <br/>
     * Se diferencia do select comum por trazer valores visíveis (como o nome por exemplo) no lugar
     * das respectivas chaves estrangeiras (cargo, perfil, etc.).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de funcionários do tipo Funcionario.
     */
    public function superSelect($condition = null){
        $fields = array(self::TABLE.".".self::ID, self::NOME,self::LOGIN,self::SENHA,
                        self::TABLE.".".self::RUA, self::DEMISSAO, self::ADMISSAO,
                        self::TABLE.".".self::NUMERO, CidadeModel::NOME, self::TABLE.".".self::BAIRRO,self::CPF,
                        self::NASCIMENTO, self::TABLE.".".self::CEP, self::RG, self::CPT,
                        self::REFERENCIA,self::BANCO,self::AGENCIA,self::CONTA,
                        CargoModel::NOME, LojaModel::SIGLA, PerfilModel::NOME, self::EMAIL, self::STATUS,
						self::TABLE.".".self::INATIVO_PARA_CAIXA);
        
        $fields_joined = implode(",", $fields);
        
        $joins[] = "LEFT JOIN ".CargoModel::TABLE." ON ".self::TABLE.".".self::CARGO." = ". CargoModel::TABLE.".".CargoModel::ID;
        $joins[] = "LEFT JOIN ".LojaModel::TABLE." ON ".self::TABLE.".".self::LOJA." = ".LojaModel::TABLE.".".LojaModel::ID;
        $joins[] = "LEFT JOIN ".PerfilModel::TABLE." ON ".self::PERFIL." = ".PerfilModel::TABLE.".".PerfilModel::ID;
        $joins[] = "LEFT JOIN ".CidadeModel::TABLE." ON ".self::TABLE.".".self::CIDADE." = ".CidadeModel::TABLE.".".CidadeModel::ID;
        
        $res = parent::select(self::TABLE." ".implode(" ", $joins), $fields_joined, $condition);
        $ana = $this->getAnnalisses();
        $funcionarios = array();
        while(($row = $ana->fetchObject($res)) !== false){
            $funcionarios[] = new Funcionario(
                                $row->{ self::ID },
                                $row->{ self::NOME },
                                $row->{ self::LOGIN },
                                $row->{ self::SENHA },
                                $row->{ self::NASCIMENTO},
                                $row->{ self::ADMISSAO},
                                $row->{ self::DEMISSAO},
                                $row->{ CidadeModel::NOME},       
                                $row->{ self::RUA },
                                $row->{ self::NUMERO },
                                $row->{ self::BAIRRO },
                                $row->{ self::CEP },
                                $row->{ self::CPF },
                                $row->{ self::RG },
                                $row->{ self::CPT },        
                                $row->{ self::REFERENCIA },
                                $row->{ self::BANCO },
                                $row->{ self::AGENCIA },
                                $row->{ self::CONTA },
                                $row->{ CargoModel::NOME },
                                $row->{ LojaModel::SIGLA },
                                $row->{ PerfilModel::NOME },
                                $row->{ self::EMAIL },
                                $row->{ self::STATUS },
                                $row->{ self::INATIVO_PARA_CAIXA });
        }
        return $funcionarios;
    }
    
    /**
     * Atualiza <i>funcionario</i> de acordo com o seu identificador.
     * @param Funcionario $funcionario funcionário que vai ser aualizado
     * @return bool true em caso de sucesso na atualização ou false em caso de falha
     */
    public function update(Funcionario $funcionario) {
        $dic = array (
            self::NOME          => $funcionario->nome,
            self::LOGIN         => $funcionario->login,
            self::NASCIMENTO    => $funcionario->nascimento,
            self::ADMISSAO      => $funcionario->admissao,
            self::DEMISSAO      => $funcionario->demissao,
            self::CIDADE        => $funcionario->cidade,
            self::RUA           => $funcionario->rua,
            self::NUMERO        => $funcionario->numero,
            self::BAIRRO        => $funcionario->bairro,
            self::CEP           => $funcionario->cep,
            self::CPF           => $funcionario->cpf,
            self::RG            => $funcionario->rg,
            self::CPT           => $funcionario->cpt,
            self::REFERENCIA    => $funcionario->referencia,
            self::BANCO         => $funcionario->banco,
            self::AGENCIA       => $funcionario->agencia,
            self::CONTA         => $funcionario->conta,
            self::CARGO         => $funcionario->cargo,
            self::LOJA          => $funcionario->loja,
            self::PERFIL        => $funcionario->perfil,
            self::EMAIL         => $funcionario->email,
			self::INATIVO_PARA_CAIXA	=> $funcionario->inativoParaCaixa
        );
        $condition = self::ID." = ".$funcionario->id;
        return $this->formattedUpdates(self::TABLE, Database::turnInUpdateValues($dic), $condition);
    }
    
    /**
     * Atualiza <i>field</i> com <i>value</i> de acordo com <i>condition</i>.
     * @param string $value campo da tabela
     * @param string $value valor a atribuido
     * @param string $condition cláusula WHERE
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function simpleUpdate($field, $value, $condition){
        return parent::update(self::TABLE, $field, $value, $condition);
    }
    
    /**
     * Realiza a remoção de um funcionário.
     * @param int $func_id identificador do funcionário (pode string também).
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function delete($func_id) {
        $condition = self::ID." = $func_id";
        return parent::delete(self::TABLE, $condition);
    }
    
    /**
     * Desativa um funcionário alterando seu status para FALSE.
     * @param int $func_id identificador do funcionário (pode string também).
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function desactive($func_id){
        return parent::update(self::TABLE, self::STATUS, 'FALSE', self::ID." = $func_id");
    }
    
    /**
     * Ativa um funcionário alterando seu status para TRUE.
     * @param int $func_id identificador do funcionário (pode string também).
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function active($func_id){
        return parent::update(self::TABLE, self::STATUS, 'TRUE', self::ID." = $func_id");
    }
    
    /**
     * Manipula erros.
     * @return string mensagem de error à nível de usuário caso seja um erro conhecido ou
     * uma mensagem com código de erro.
     */
    public function handleError() {
        $err_code = $this->errorCode();
        if($err_code == Annalisses::ERR_DUPLICATE) 
            return "Já existe um funcionário com o mesmo CPF, RG ou EMAIL";
        return parent::handleError();
    }
    
}
?>
