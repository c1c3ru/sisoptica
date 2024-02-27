<?php
//Incluindo classes associadas a esse modelo
include_once MODELS."cidade.php";
include_once MODELS."funcionario.php";

/**
 * Essa classe implementa o modelo da entidade Loja.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class LojaModel extends Database {
    
    /**
     * Nome da tabela de lojas no banco de dados 
     */
    const TABLE = "loja";
    
    /**
     * Nome da coluna do identificador da loja no banco de dados.
     */
    const ID = "id";
    
    /**
     * Nome da coluna da sigla da loja no banco de dados.
     */
    const SIGLA = "sigla_loja";
    
    /**
     * Nome da coluna da rua da loja no banco de dados.
     */
    const RUA = "rua";
    
    /**
     * Nome da coluna do numero do endereço da loja no banco de dados.
     */
    const NUMERO = "numero";
    
    /**
     * Nome da coluna do bairro da loja no banco de dados.
     */
    const BAIRRO = "bairro";
    
    /**
     * Nome da coluna do CEP da loja no banco de dados.
     */
    const CEP = "cep";
    
    /**
     * Nome da coluna do CNPJ da loja no banco de dados.
     */
    const CNPJ = "cnpj";
    
    /**
     * Nome da coluna do identificador da cidade da loja no banco de dados.
     */
    const CIDADE = "id_cidade";
    
    /**
     * Nome da coluna do CGC da loja no banco de dados.
     */
    const CGC = "cgc";
    
    /**
     * Nome da coluna do identificador do gerente da loja no banco de dados.
     */
    const GERENTE = "id_funcionario_gerente";
    
	/**
	 * Nome da coluna de flag para inatividade de transações no caixa diário
	 */
	const INATIVO_PARA_CAIXA = "inativo_para_caixa";
	
    /**
     * Insere uma loja na base de dados.
     * @param Loja $loja loja que vai ser inserida
     * @return int identificador da loja em caso de sucesso na inserção ou null em caso de falha.
     */
    public function insert(Loja $loja) {
        $values = get_object_vars($loja);
        $fields = implode(",", array(self::SIGLA, self::RUA, self::NUMERO, self::BAIRRO, 
                                     self::CEP, self::CNPJ, self::CIDADE, 
                                     self::CGC, self::GERENTE, self::INATIVO_PARA_CAIXA));
        unset($values["id"]); 
        unset($values["telefones"]);
        $res = parent::insert(self::TABLE, $fields, Database::turnInValues($values));
        if($res) return $this->getAnnalisses()->lastInsertedtId();
        return null;
    }
    
    /**
     * Seleciona uma lista de lojas com os <i>fields</i> preenchidos com os valores do banco.
     * @param mixed $fields colunas da seleção, pode ser um array (utilize as constantes das colunas).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de lojas do tipo Loja.
     */
    public function select($fields = " * ", $condition = null) {
        if(is_array($fields)) $fields = implode(",", $fields);
        $res = parent::select(self::TABLE, $fields, $condition);
        $ana = $this->getAnnalisses();
        $lojas = array();
        while(($row = $ana->fetchObject($res)) !== false){
            $lojas[] = new Loja(isset($row->{ self::ID })? $row->{ self::ID } : 0,
                                isset($row->{ self::SIGLA })? $row->{ self::SIGLA } : "",
                                isset($row->{ self::RUA })? ($row->{ self::RUA }) : "",
                                isset($row->{ self::NUMERO })? $row->{ self::NUMERO } : "",
                                isset($row->{ self::BAIRRO })? ($row->{ self::BAIRRO }) : "",
                                isset($row->{ self::CEP })? $row->{ self::CEP } : "",
                                isset($row->{ self::CNPJ })? $row->{ self::CNPJ } : "",
                                isset($row->{ self::CIDADE })? $row->{ self::CIDADE } : 0,
                                isset($row->{ self::CGC })? $row->{ self::CGC } : "",
                                isset($row->{ self::GERENTE})? $row->{ self::GERENTE} : "",       
                                isset($row->{ self::INATIVO_PARA_CAIXA})? $row->{ self::INATIVO_PARA_CAIXA} : FALSE
								);
        }
        return $lojas;
    }
    
    /**
     * Seleciona uma lista de lojas com todos os campos preenchidos com os valores do banco.
     * <br/>
     * Se diferencia do select comum por trazer valores visíveis (como o nome por exemplo) no lugar
     * das respectivas chaves estrangeiras (nome cidade, nome do geremte, etc.).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de lojas do tipo Loja.
     */
    public function superSelect($condition = null){
        $fields = array(self::TABLE.".".self::ID, self::SIGLA, 
                        self::TABLE.".".self::RUA, self::TABLE.".".self::NUMERO,  self::TABLE.".".self::BAIRRO, 
                        self::TABLE.".".self::CEP, self::CNPJ, CidadeModel::NOME, self::CGC, FuncionarioModel::NOME,
						self::TABLE.".".self::INATIVO_PARA_CAIXA);
        $fields_joined = implode(",", $fields);
        
        $join_cidade = "LEFT JOIN ".CidadeModel::TABLE." ON ".self::TABLE.".".self::CIDADE." = ".CidadeModel::TABLE.".".CidadeModel::ID;
        $join_gerente = "LEFT JOIN ".FuncionarioModel::TABLE." ON ".self::GERENTE." = ".FuncionarioModel::TABLE.".".FuncionarioModel::ID;
        
        $joins = array($join_cidade, $join_gerente);
        
        $res = parent::select(self::TABLE." ".implode(" ", $joins), $fields_joined, $condition);
        $ana = $this->getAnnalisses();
        $lojas = array();
        while(($row = $ana->fetchObject($res)) !== false){
            $lojas[] = new Loja($row->{ self::ID },
                                $row->{ self::SIGLA },
                                $row->{ self::RUA },
                                $row->{ self::NUMERO },
                                $row->{ self::BAIRRO },
                                $row->{ self::CEP },
                                $row->{ self::CNPJ },
                                ($row->{ CidadeModel::NOME }),
                                $row->{ self::CGC },
                                ($row->{FuncionarioModel::NOME }),
								$row->{ self::INATIVO_PARA_CAIXA }
                                );
        }
        return $lojas;
    }
    
    /**
     * Atualiza <i>loja</i> de acordo com o seu identificador.
     * @param Loja $loja loja que vai ser aualizada
     * @return bool true em caso de sucesso na atualização ou false em caso de falha
     */
    public function update(Loja $loja) {
        $dic = array( 
            self::SIGLA => $loja->sigla,
            self::RUA => $loja->rua,
            self::NUMERO => $loja->numero,
            self::BAIRRO => $loja->bairro,
            self::CEP => $loja->cep,
            self::CNPJ => $loja->cnpj,
            self::CIDADE => $loja->cidade,
            self::CGC => $loja->cgc,
            self::GERENTE => $loja->gerente,
			self::INATIVO_PARA_CAIXA => $loja->inativoParaCaixa
        );
        
        $values = Database::turnInUpdateValues($dic);
        
        return $this->formattedUpdates(self::TABLE, $values, self::ID." = ".$loja->id);
    }
    
    /**
     * Realiza a remoção de uma loja.
     * @param int $id_loja identificador da loja (pode string também).
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function delete($id_loja) {
        $condition = self::ID." = $id_loja";
        return parent::delete(self::TABLE, $condition);
    }
    
    /**
     * Manipula erros.
     * @return string mensagem de error à nível de usuário caso seja um erro conhecido ou
     * uma mensagem com código de erro.
     */
    public function handleError() {
        $err_code = $this->errorCode(); 
        if($err_code == Annalisses::ERR_DUPLICATE) 
            return "O CNPJ e a Sigla da loja tem que ser único";
        return parent::handleError();
    }
    
}

?>
