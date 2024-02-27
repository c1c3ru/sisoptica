<?php
//Incluindo classes associadas a esse modelo
include_once MODELS."cidade.php";
include_once MODELS."localidade.php";
include_once MODELS."venda.php";

/**
 * Essa classe implementa o modelo da entidade Cliente.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class ClienteModel extends Database {
    
    /**
     * Nome da tabela de clientes no banco de dados 
     */
    const TABLE = "cliente";
    
    /**
     * Nome da coluna do identificador do cliente no banco de dados.
     */
    const ID                    = "id";
    
    /**
     * Nome da coluna do nome do cliente no banco de dados.
     */
    const NOME                  = "nome";
    
    /**
     * Nome da coluna da data de nascimento do cliente no banco de dados.
     */
    const NASCIMENTO            = "data_nascimento";
    
    /**
     * Nome da coluna do apelido do cliente no banco de dados.
     */
    const APELIDO               = "apelido";
    
    /**
     * Nome da coluna do registo geral (RG) do cliente no banco de dados.
     */
    const RG                    = "rg";
    
    /**
     * Nome da coluna do orgão emissor do RG do cliente no banco de dados.
     */
    const ORGAO_EMISSOR         = "orgao_emissor";
    
    /**
     * Nome da coluna do CPF do cliente no banco de dados.
     */
    const CPF                   = "cpf";
    
    /**
     * Nome da coluna do conjugue do cliente no banco de dados.
     */
    const CONJUGUE              = "conjugue";
    
    /**
     * Nome da coluna do nome do pai do cliente no banco de dados.
     */
    const NOME_PAI              = "nome_pai";
    
    /**
     * Nome da coluna do nome da mãe do cliente no banco de dados.
     */
    const NOME_MAE              = "nome_mae";
    
    /**
     * Nome da coluna do endereço do cliente no banco de dados.
     */
    const ENDERECO              = "endereco";
    
    /**
     * Nome da coluna do numero da residencia do cliente no banco de dados.
     */
    const NUMERO                = "numero";
    
    /**
     * Nome da coluna do bairro do cliente no banco de dados.
     */
    const BAIRRO                = "bairro";
    
    /**
     * Nome da coluna do ponto de referência do cliente no banco de dados.
     */
    const REFERENCIA            = "referencia";
    
    /**
     * Nome da coluna da apropriação da resiência do cliente no banco de dados.
     */
    const CASA_PROPRIA          = "casa_propria";
    
    /**
     * Nome da coluna do tempo na casa própria do cliente no banco de dados.
     */
    const TEMPO_CASA_PROPRIA    = "tempo_casa_propria";
    
    /**
     * Nome da coluna de observação sobre o cliente no banco de dados.
     */
    const OBSERVACAO            = "observacao";
    
    /**
     * Nome da coluna da renda mensal do cliente no banco de dados.
     */
    const RENDA_MENSAL          = "renda_mensal";
    
    /**
     * Nome da coluna do identificador da loclaidade do cliente no banco de dados.
     */
    const LOCALIDADE            = "id_localidade";
    
    /**
     * Nome da coluna que indica se o cliente está bloqueado no banco de dados.
     */
    const BLOQUEADO             = "bloqueado";
    
    /**
     * Insere um cliente na base de dados e, em caso de sucesso, atribui o id inserido.
     * @param Cliente $cliente cliente que vai ser inserido
     * @return bool true em caso de sucesso na inserção ou false em caso de falha
     */
    public function insert(Cliente $cliente) {
        $fields = array( self::NOME,self::NASCIMENTO,self::APELIDO,
                         self::RG, self::ORGAO_EMISSOR, self::CPF,self::CONJUGUE,
                         self::NOME_PAI,self::NOME_MAE,self::ENDERECO,
                         self::NUMERO,self::BAIRRO,self::REFERENCIA,
                         self::CASA_PROPRIA,self::TEMPO_CASA_PROPRIA,self::OBSERVACAO,
                         self::RENDA_MENSAL,self::LOCALIDADE);
        $values = get_object_vars($cliente);
        unset($values["id"]); 
        unset($values["telefones"]);
        unset($values["bloqueado"]);
        if(parent::insert(self::TABLE, implode(",", $fields), Database::turnInValues($values))){
            return $this->getAnnalisses()->lastInsertedtId();
        }
        return null;   
    }
    
    /**
     * Realiza a remoção de um cliente.
     * @param int $id_cliente identificador do cliente (pode string também).
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function delete($id_cliente) {
        $condition = self::ID." = $id_cliente";
        return parent::delete(self::TABLE, $condition);
    }
    
    /**
     * Atualiza <i>cliente</i> de acordo com o seu identificador.
     * @param Cliente $cliente cliente que vai ser aualizado
     * @return bool true em caso de sucesso na atualização ou false em caso de falha
     */
    public function update(Cliente $cliente){
        $dic = array(
            self::NOME                  => $cliente->nome,
            self::NASCIMENTO            => $cliente->nascimento,
            self::APELIDO               => $cliente->apelido,
            self::RG                    => $cliente->rg,
            self::ORGAO_EMISSOR         => $cliente->orgaoEmissor,
            self::CPF                   => $cliente->cpf,
            self::CONJUGUE              => $cliente->conjugue,
            self::NOME_PAI              => $cliente->nomePai,
            self::NOME_MAE              => $cliente->nomeMae,
            self::ENDERECO              => $cliente->endereco,
            self::NUMERO                => $cliente->numero,
            self::BAIRRO                => $cliente->bairro,
            self::REFERENCIA            => $cliente->referencia,
            self::CASA_PROPRIA          => $cliente->casaPropria,
            self::TEMPO_CASA_PROPRIA    => $cliente->tempoCasaPropria,
            self::OBSERVACAO            => $cliente->observacao,
            self::RENDA_MENSAL          => $cliente->rendaMensal,
            self::LOCALIDADE            => $cliente->localidade,
            self::BLOQUEADO             => $cliente->bloqueado
        );
        $condition = self::ID." = ".$cliente->id;
        return parent::formattedUpdates(self::TABLE, Database::turnInUpdateValues($dic), $condition);
    }
    
    /**
     * Seleciona uma lista de clientes com os <i>fields</i> preenchidos com os valores do banco.
     * @param mixed $fields colunas da seleção, pode ser um array (utilize as constantes das colunas).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de clientes do tipo Cliente.
     */
    public function select($fields = " * ", $condition = null) {
        if(is_array($fields)) $fields = implode (",", $fields);
        $res = parent::select(self::TABLE, $fields, $condition);
        $ana = $this->getAnnalisses();
        $clientes = array();
        while(($row = $ana->fetchObject($res)) !== false){
            $clientes[] = new Cliente( 
                        isset($row->{self::ID})? $row->{self::ID} : 0,
                        isset($row->{self::NOME})? ($row->{self::NOME}) : "",
                        isset($row->{self::NASCIMENTO})? $row->{self::NASCIMENTO} : "",
                        isset($row->{self::APELIDO})? ($row->{self::APELIDO}) : "",
                        isset($row->{self::RG})? $row->{self::RG} : "",
                        isset($row->{self::ORGAO_EMISSOR})? $row->{self::ORGAO_EMISSOR}: "",        
                        isset($row->{self::CPF})? $row->{self::CPF} : "",
                        isset($row->{self::CONJUGUE})? ($row->{self::CONJUGUE}) : "",
                        isset($row->{self::NOME_PAI})? ($row->{self::NOME_PAI}) : "",
                        isset($row->{self::NOME_MAE})? ($row->{self::NOME_MAE}) : "",
                        isset($row->{self::ENDERECO})? ($row->{self::ENDERECO}) : "",
                        isset($row->{self::NUMERO})? $row->{self::NUMERO} : "",
                        isset($row->{self::BAIRRO})? ($row->{self::BAIRRO}) : "",
                        isset($row->{self::REFERENCIA})? ($row->{self::REFERENCIA}) : "",
                        isset($row->{self::CASA_PROPRIA})? $row->{self::CASA_PROPRIA} : false,
                        isset($row->{self::TEMPO_CASA_PROPRIA})? $row->{self::TEMPO_CASA_PROPRIA} : "",
                        isset($row->{self::OBSERVACAO})? ($row->{self::OBSERVACAO}) : "",
                        isset($row->{self::RENDA_MENSAL})? $row->{self::RENDA_MENSAL} : 0.0,
                        isset($row->{self::LOCALIDADE})? $row->{self::LOCALIDADE} : 0,
                        isset($row->{self::BLOQUEADO})? $row->{self::BLOQUEADO} : 0        
                    );
        }
        return $clientes;
    }
    
    /**
     * Seleciona uma lista de clientes com todos os campos preenchidos com os valores do banco.
     * <br/>
     * Se diferencia do select comum por trazer valores visíveis (como o nome por exemplo) no lugar
     * das respectivas chaves estrangeiras (localidade, cidade, etc.).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de clientes do tipo Cliente.
     */
    public function superSelect($condition = null){
        $fields = array( self::TABLE.".".self::ID, self::NOME,self::NASCIMENTO,self::APELIDO,
                         self::RG, self::ORGAO_EMISSOR, self::CPF,self::CONJUGUE,
                         self::NOME_PAI,self::NOME_MAE,self::ENDERECO,
                         self::NUMERO,self::BAIRRO,self::REFERENCIA,
                         self::CASA_PROPRIA,self::TEMPO_CASA_PROPRIA,self::OBSERVACAO, self::BLOQUEADO,
                         self::RENDA_MENSAL, LocalidadeModel::NOME, CidadeModel::NOME );
        
        $this_condition = self::LOCALIDADE." = ".LocalidadeModel::TABLE.".".LocalidadeModel::ID;
        $this_condition .= " AND ".LocalidadeModel::CIDADE." = ".CidadeModel::TABLE.".".CidadeModel::ID;
        $this_condition .= " ORDER BY ".self::NOME;
        
        if(is_null($condition)) $condition = $this_condition;
        else $condition .= " AND ".$this_condition;
        
        $tables = array(self::TABLE, LocalidadeModel::TABLE, CidadeModel::TABLE);
        
        $res = parent::select(implode(",", $tables), implode(",", $fields), $condition);
        
        $ana = $this->getAnnalisses();
        $clientes = array();
        while(($row = $ana->fetchObject($res)) !== false){
            $clientes[] = new Cliente( 
                        $row->{self::ID},
                        $row->{self::NOME},
                        $row->{self::NASCIMENTO},
                        $row->{self::APELIDO},
                        $row->{self::RG},
                        $row->{self::ORGAO_EMISSOR},        
                        $row->{self::CPF},
                        $row->{self::CONJUGUE},
                        $row->{self::NOME_PAI},
                        $row->{self::NOME_MAE},
                        $row->{self::ENDERECO},
                        $row->{self::NUMERO},
                        $row->{self::BAIRRO},
                        $row->{self::REFERENCIA},
                        $row->{self::CASA_PROPRIA},
                        $row->{self::TEMPO_CASA_PROPRIA},
                        $row->{self::OBSERVACAO},
                        $row->{self::RENDA_MENSAL},
                        $row->{LocalidadeModel::NOME}." - ".$row->{CidadeModel::NOME},
                        $row->{self::BLOQUEADO}       
                    );
        }
        return $clientes;
    }
    
    /**
     * Esse método é um select otimizado para a listagem, retornando apenas os campos 
     * que irão ser exibidos na grid.
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de clientes do tipo Cliente.
     */
    public function selectOnlyList($condition = null){
        $fields = array( self::TABLE.".".self::ID, self::NOME, self::BLOQUEADO,
                         self::CPF, LocalidadeModel::NOME );
        
        $this_condition = self::LOCALIDADE.' = '.LocalidadeModel::TABLE.'.'.LocalidadeModel::ID;
        
        if(empty($condition)) $condition = $this_condition;
        else $condition .= " AND ".$this_condition;

        $leftOuterJoinVendas = ' LEFT OUTER JOIN ' . VendaModel::TABLE . ' ON ' . VendaModel::TABLE.'.'.VendaModel::CLIENTE.' = '.self::TABLE.'.'.self::ID;
        $tables = ' (' . self::TABLE . ',' . LocalidadeModel::TABLE . ') ' . $leftOuterJoinVendas;
        
        $res = parent::select($tables, implode(",", $fields), $condition);
        
        $ana = $this->getAnnalisses();
        $clientes = array();
        while(($row = $ana->fetchObject($res)) !== false){
            $cliente = new Cliente();
            $cliente->id    = $row->{self::ID};
            $cliente->nome  = $row->{self::NOME};
            $cliente->cpf   = $row->{self::CPF};
            $cliente->bloqueado     = $row->{self::BLOQUEADO};
            $cliente->localidade    = $row->{LocalidadeModel::NOME};
            $clientes[] = $cliente;
        }
        return $clientes;
    }
    
    /**
     * Manipula erros.
     * @return string mensagem de error à nível de usuário caso seja um erro conhecido ou
     * uma mensagem com código de erro.
     */
    public function handleError() {
        $err_code = $this->errorCode(); 
        if($err_code == Annalisses::ERR_DUPLICATE) return "Não pode haver cliente duplicado";
        if($err_code == Annalisses::ERR_FK_VIOLATION) return "Existe vendas associadas à esse cliente.";
        return parent::handleError();
    }
}
?>
