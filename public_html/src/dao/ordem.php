<?php
//Incluindo classes associadas a esse modelo
include_once MODELS."laboratorio.php";
include_once MODELS."funcionario.php";
include_once MODELS."venda.php";

/**
 * Essa classe implementa o modelo da entidade OrdemServico.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class OrdemServicoModel extends Database {
    
    /**
     * Nome da tabela de ordens de serviço no banco de dados 
     */
    const TABLE = 'ordem_servico';
    
    /**
     * Nome da coluna do identificador da ordem de serviço no banco de dados.
     */
    const ID                = 'id';
    
    /**
     * Nome da coluna do numero da ordem de serviço no banco de dados.
     */
    const NUMERO            = 'numero'; 
    
    /**
     * Nome da coluna da data de envio da ordem de serviço no banco de dados.
     */
    const DT_ENVIO          = 'data_envio_lab';
    
    /**
     * Nome da coluna da data de recebimento da ordem de serviço no banco de dados.
     */
    const DT_RECEBIMENTO    = 'data_recebimento_lab';
    
    /**
     * Nome da coluna que indica se armação da ordem de serviço é da loja.
     */
    const ARMACAO_LOJA      = 'armacao_loja';
    
    /**
     * Nome da coluna do valor da ordem de serviço no banco de dados.
     */
    const VALOR             = 'valor';
    
    /**
     * Nome da coluna do identificador do laboratório da ordem de serviço no banco de dados.
     */
    const LABORATORIO       = 'id_laboratorio';
    
    /**
     * Nome da coluna do identificador da loja da ordem de serviço no banco de dados.
     */
    const LOJA              = 'id_loja';
    
    /**
     * Nome da coluna do status de cancelada da ordem de serviço no banco de dados.
     */
    const CANCELADA         = 'cancelada';
    
    /**
     * Nome da coluna do identificador do autor da ordem de serviço no banco de dados.
     */
    const AUTOR             = 'func_autor';
    
    /**
     * Nome da coluna do identificador da venda da ordem de serviço no banco de dados.
     */
    const VENDA             = 'venda';
    
    /**
     * Insere uma ordem de serviço na base de dados e, em caso de sucesso, atribui o id inserido.
     * @param OrdemServico $os ordem de serviço que vai ser inserida
     * @return bool true em caso de sucesso na inserção ou false em caso de falha
     */
    public function insert(OrdemServico &$os) {
        $fields = implode(",", array(
            self::NUMERO, self::DT_ENVIO, self::DT_RECEBIMENTO, self::ARMACAO_LOJA,
            self::VALOR, self::LABORATORIO, self::LOJA, self::CANCELADA, self::AUTOR
        ));
        $vars = get_object_vars($os);
        unset($vars["id"]);
        unset($vars["venda"]);
        $res = parent::insert(self::TABLE, $fields, Database::turnInValues($vars));
        if($res){ 
            $os->id = $this->getAnnalisses()->lastInsertedtId(); 
            return true; 
        }
        return false;
    }
    
    /**
     * Seleciona uma lista de ordens de serviço com os <i>fields</i> preenchidos com os valores do banco.
     * @param mixed $fields colunas da seleção, pode ser um array (utilize as constantes das colunas).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de lista de ordens do tipo OrdemServico.
     */
    public function select($fields = " * ", $condition = null) {
        if(is_array($fields)) $fields = implode (",", $fields);
        
        if(strpos($fields, self::VENDA) !== FALSE || strpos($fields, '*') !== FALSE){
            $fields .= ',(SELECT '.VendaModel::ID.' FROM '.VendaModel::TABLE.' WHERE '.
                        VendaModel::OS.' = '.self::TABLE.'.'.self::ID.') AS '.self::VENDA;
        }
        
        $res = parent::select(self::TABLE, $fields, $condition);
        $ana = $this->getAnnalisses();
        $ordens = array();
        while(($row = $ana->fetchObject($res)) !== false){
            $ordens[] = new OrdemServico(
                            isset($row->{self::ID})?$row->{self::ID}:0,
                            isset($row->{self::NUMERO})?$row->{self::NUMERO}:0,        
                            isset($row->{self::DT_ENVIO})?$row->{self::DT_ENVIO}:"",
                            isset($row->{self::DT_RECEBIMENTO})?$row->{self::DT_RECEBIMENTO}:"",
                            isset($row->{self::ARMACAO_LOJA})?$row->{self::ARMACAO_LOJA}:false,        
                            isset($row->{self::VALOR})?$row->{self::VALOR}:0,        
                            isset($row->{self::LABORATORIO})?$row->{self::LABORATORIO}:0,
                            isset($row->{self::LOJA})?$row->{self::LOJA}:0,
                            isset($row->{self::CANCELADA})?$row->{self::CANCELADA}:false,
                            isset($row->{self::AUTOR})?$row->{self::AUTOR}:0,
                            isset($row->{self::VENDA})?$row->{self::VENDA}:null        
                        );
        }
        return $ordens;
    }
    
    /**
     * Seleciona uma lista de ordens de serviço com todos os campos preenchidos com os valores do banco.
     * <br/>
     * Se diferencia do select comum por trazer valores visíveis (como o nome por exemplo) no lugar
     * das respectivas chaves estrangeiras (vendedor, cliente, os, etc.).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de ordens de serviço do tipo OrdemServico.
     */
    public function superSelect($condition = null){
        $fields =  array(
            self::DT_ENVIO, self::DT_RECEBIMENTO, self::ARMACAO_LOJA, LojaModel::SIGLA, self::CANCELADA, FuncionarioModel::NOME,
            self::TABLE.".".self::NUMERO, self::VALOR, LaboratorioModel::NOME, self::TABLE.".".self::ID,
            '(SELECT '.VendaModel::ID.' FROM '.VendaModel::TABLE.' WHERE '.VendaModel::OS.' = '.self::TABLE.'.'.self::ID.') AS '.self::VENDA
        );
        $fields_joined = implode(",", $fields);
        $joins[] = "LEFT JOIN ".LaboratorioModel::TABLE." ON ".self::TABLE.".".self::LABORATORIO." = ".LaboratorioModel::TABLE.".".LaboratorioModel::ID;
        $joins[] = "LEFT JOIN ".LojaModel::TABLE." ON ".self::TABLE.".".self::LOJA." = ".LojaModel::TABLE.".".LojaModel::ID;
        $joins[] = "LEFT JOIN ".FuncionarioModel::TABLE." ON ".self::TABLE.".".self::AUTOR." = ".FuncionarioModel::TABLE.".".FuncionarioModel::ID;
    
        $res = parent::select(self::TABLE." ".implode(" ", $joins), $fields_joined, $condition);
        $ana = $this->getAnnalisses();
        $ordens = array();
        while(($row = $ana->fetchObject($res)) !== false){
            $ordens[] = new OrdemServico(
                            $row->{self::ID},
                            $row->{self::NUMERO},        
                            $row->{self::DT_ENVIO},
                            $row->{self::DT_RECEBIMENTO},
                            $row->{self::ARMACAO_LOJA},        
                            $row->{self::VALOR},        
                            $row->{LaboratorioModel::NOME},
                            $row->{LojaModel::SIGLA},
                            $row->{self::CANCELADA}, 
                            $row->{FuncionarioModel::NOME},
                            $row->{self::VENDA}        
                        );
        }
        return $ordens;
    }

    /**
     * Seleciona uma lista de tuplas, que são entidades específicas que agrupam dados de quantidade.
     * <br/>
     * Agrupam <b>quantidade</b> e <b>total</b> em um <b>identificador</b>, esse identificador 
     * ainda pode ter um titulo. Esse tipo de entidade é mais usado em <b>relatórios</b>, por exemplo, 
     * no relatório de OS. Auxilia a responder a pergunta: Qual a quantidade e OS e 
     * valor delas no período <i>tal</i>?
     * @param string $field_id coluna de identificação.
     * @param array $joins lista de junções que envolvem as tabelas que se relacionam com OS.
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @param string $titled indica o campo que será usado como titulo
     * @return array lista de tuplas do tipo Tupla.
     */
    public function selectTupla($field_id, $joins, $condition = null, $titled = null){
        $fields[] = $field_id.' AS I_FIELD';
        $fields[] = 'COUNT('.self::TABLE.'.'.self::ID.') AS C_FIELD';
        $fields[] = 'SUM('.self::TABLE.'.'.self::VALOR.') AS S_FIELD';
        
        if($titled != null) $fields[] = $titled.' AS T_FIELD'; 
        
        if($condition == null){ $condition = ' 1=1 GROUP BY I_FIELD'; }
        else { $condition .= ' GROUP BY I_FIELD'; }
        
        $res    = parent::select( self::TABLE.' '.implode(' ', $joins), implode(',', $fields), $condition );
        $anna   = $this->getAnnalisses();
        $tuplas = array();
        if($titled == null) {
            while(($row = $anna->fetchObject($res)) !== FALSE){
                $tuplas[] = new Tupla($row->I_FIELD, $row->C_FIELD, $row->S_FIELD);
            }
        } else {
            while(($row = $anna->fetchObject($res)) !== FALSE){
                $tupla          = new Tupla($row->I_FIELD, $row->C_FIELD, $row->S_FIELD);
                $tupla->nome    = $row->T_FIELD;
                $tuplas[]       = $tupla;
            }
        }
        return $tuplas;
    }
    
    /**
     * Atualiza <i>os</i> de acordo com o seu identificador.
     * @param OrdemServico $os venda que vai ser aualizada
     * @return bool true em caso de sucesso na atualização ou false em caso de falha
     */
    public function update(OrdemServico $os){
        $dic = array(
            self::NUMERO => $os->numero,
            self::DT_ENVIO => $os->dataEnvioLab,
            self::DT_RECEBIMENTO => $os->dataRecebimentoLab,
            self::ARMACAO_LOJA => $os->armacaoLoja,
            self::VALOR => $os->valor,
            self::LABORATORIO => $os->laboratorio,
            self::LOJA => $os->loja,
            self::AUTOR => $os->autor
        );
        $condition = self::ID." = {$os->id}";
        return $this->formattedUpdates(self::TABLE, Database::turnInUpdateValues($dic), $condition);
    }
    
    /**
     * Realiza o cancelamento de uma ordem de serviço.
     * @param int $id_os identificador da ordem de serviço (pode string também).
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function delete($id_os){
        $condition = self::ID." = $id_os";
        return parent::update(self::TABLE, self::CANCELADA, "1", $condition);
    }
    
    /**
     * Realiza a reliberação de uma ordem de serviço.
     * @param int $id_os identificador da ordem de serviço (pode string também).
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function undelete($id_os){
        $condition = self::ID." = $id_os";
        return parent::update(self::TABLE, self::CANCELADA, "0", $condition);
    }
    
    /**
     * Manipula erros.
     * @return string mensagem de error à nível de usuário caso seja um erro conhecido ou
     * uma mensagem com código de erro.
     */
    public function handleError() {
        $err_code = $this->errorCode(); 
        if($err_code == Annalisses::ERR_DUPLICATE) 
            return "Já uma ordem de serviço com esse número nessa loja";
        return parent::handleError();
    }
    
}
?>
