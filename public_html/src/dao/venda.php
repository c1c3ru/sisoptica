<?php
//Incluindo classes associadas a esse modelo
include_once MODELS."ordem.php";
include_once MODELS."funcionario.php";
include_once MODELS."loja.php";
include_once MODELS."cliente.php";
include_once MODELS."produto-venda.php";

/**
 * Essa classe implementa o modelo da entidade Venda.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class VendaModel extends Database {
    
    /**
     * Nome da tabela de vendas no banco de dados 
     */
    const TABLE         = "venda";
    
    /**
     * Nome da coluna do identificador da venda no banco de dados.
     */
    const ID            = "id";
    
    /**
     * Nome da coluna da data da venda no banco de dados.
     */
    const DT_VENDA      = "data_venda";
    
    /**
     * Nome da coluna da data de entrega da venda no banco de dados.
     */
    const DT_ENTREGA    = "data_entrega";
    
    /**
     * Nome da coluna da data de previsão de entrega da venda no banco de dados.
     */
    const DT_PREVISAO   = "data_previsao_entrega";
    
    /**
     * Nome da coluna do cliente da venda no banco de dados.
     */
    const CLIENTE       = "id_cliente";
    
    /**
     * Nome da coluna da loja da venda no banco de dados.
     */
    const LOJA          = "id_loja";
    
    /**
     * Nome da coluna do identificador do vendedor da venda no banco de dados.
     */
    const VENDEDOR      = "id_func_vendedor";
    
    /**
     * Nome da coluna do identificador do agente de vendas da venda no banco de dados.
     */
    const AGENTE        = "id_func_agente_vendas";
    
    /**
     * Nome da coluna do identificador da os da venda no banco de dados.
     */
    const OS            = "id_ordem_servico";
    
    /**
     * Nome da coluna do status da venda no banco de dados.
     */
    const STATUS        = "status";
    
    /**
     * Nome da coluna da antiga renegociada, da venda no banco de dados.
     */
    const VENDA_ANTIGA  = "venda_antiga";

    /**
     * Nome da coluna da equipe da venda no banco de dados.
     */
    const EQUIPE  = "id_equipe";

    /**
     * Nome da coluna do lider da equipe no momento da venda no banco de dados.
     */
    const LIDER_EQUIPE  = "lider_equipe";
   
    /**
     * Constante que indica todos os status de venda possíveis
     */
    const STATUS_TODOS          = "0";
    
    /**
     * Constante que indica status de venda ativa
     */
    const STATUS_ATIVA          = "1";
    
    /**
     * Constante que indica status de venda cancelada
     */
    const STATUS_CANCELADA      = "2";
    
    /**
     * Constante que indica status de venda quitada
     */
    const STATUS_QUITADA        = "3";
    
    /**
     * Constante que indica status de venda renegociada
     */
    const STATUS_RENEGOCIADA    = "4";
    
    /**
     * Constatnte que indica status de venda devolvida
     */
    const STATUS_DEVOLVIDA      = "5";

    /**
    * Atalho para o relacionamento com a tabela de funcionário como agente
    */
    const ALIAS_AGENTE          = 'FUNC_AGENTE';
    
    /**
    * Atalho para o relacionamento com a tabela de funcionário como vendedor
    */
    const ALIAS_VENDEDOR        = 'FUNC_VENDEDOR';

    /**
    * Atalho para o relacionamento com a tabela de funcionário como líder de equipe
    */
    const ALIAS_LIDER           = 'FUNC_LIDER';
    
    /**
     * Insere uma venda na base de dados e, em caso de sucesso, atribui o id inserido.
     * @param Venda $venda venda que vai ser inserida
     * @return bool true em caso de sucesso na inserção ou false em caso de falha
     */
    public function insert(Venda &$venda) {
        $fields = implode(",", array(self::DT_VENDA, self::DT_PREVISAO, self::DT_ENTREGA,
                                        self::CLIENTE, self::LOJA, self::VENDEDOR,
                                        self::AGENTE, self::OS, self::STATUS, self::VENDA_ANTIGA,
                                        self::EQUIPE, self::LIDER_EQUIPE));
        $vars = get_object_vars($venda);
        unset($vars["id"]);        
        unset($vars["valor"]);
        unset($vars["produtos"]);
        if(parent::insert(self::TABLE, $fields, Database::turnInValues($vars))){
            $venda->id = $this->getAnnalisses()->lastInsertedtId();
            return true;
        }
        return false;
    }
    
    /**
     * Atualiza <i>venda</i> de acordo com o seu identificador.
     * @param Venda $venda venda que vai ser atualizada
     * @return bool true em caso de sucesso na atualização ou false em caso de falha
     */
    public function update(Venda $venda){
        $dic = array(
            self::ID            => $venda->id,
            self::DT_VENDA      => $venda->dataVenda,
            self::DT_PREVISAO   => $venda->previsaoEntrega,
            self::DT_ENTREGA    => $venda->dataEntrega,
            self::CLIENTE       => $venda->cliente,
            self::LOJA          => $venda->loja,
            self::VENDEDOR      => $venda->vendedor,
            self::AGENTE        => $venda->agenteVendas,
            self::OS            => $venda->os,
            self::VENDA_ANTIGA  => $venda->vendaAntiga,
            self::EQUIPE        => $venda->equipe,
            self::LIDER_EQUIPE  => $venda->liderEquipe
        );
        $condition = self::ID." = {$venda->id}";
        return parent::formattedUpdates(self::TABLE, Database::turnInUpdateValues($dic), $condition);
    }
    
    /**
     * Seleciona uma lista de vendas com os <i>fields</i> preenchidos com os valores do banco.
     * @param mixed $fields colunas da seleção, pode ser um array (utilize as constantes das colunas).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de vendas do tipo Venda.
     */
    public function select($fields = " * ", $condition = null) {
        if(is_array($fields)) $fields = implode(",", $fields);
        $res = parent::select(self::TABLE, $fields, $condition);
        return $this->handleCursor($res);
    }
    
    /**
     * Seleciona uma lista de vendas com todos os campos preenchidos com os valores do banco.
     * <br/>
     * Se diferencia do select comum por trazer valores visíveis (como o nome por exemplo) no lugar
     * das respectivas chaves estrangeiras (vendedor, cliente, os, etc.).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de vendas do tipo Venda.
     */
    public function superSelect($condition = null){
        $asVendedor = "VENDEDOR_NOME";
        $asAgente   = "AGENTE_NOME";
        $asLider    = "LIDER_NOME";
        $asCliente  = "CLIENTE_NOME";
        $asOrdem    = "NUMERO_OS";

        $fields     = array(
            self::TABLE.".".self::ID, self::DT_VENDA, self::DT_PREVISAO, self::DT_ENTREGA, 
            self::TABLE.".".self::STATUS, self::VENDA_ANTIGA, self::TABLE.".".self::EQUIPE,
            LojaModel::SIGLA, ClienteModel::NOME." AS $asCliente", 
            self::ALIAS_VENDEDOR.".".FuncionarioModel::NOME." AS $asVendedor",
            self::ALIAS_LIDER.".".FuncionarioModel::NOME." AS $asLider",
            self::ALIAS_AGENTE.".".FuncionarioModel::NOME." AS $asAgente",
            OrdemServicoModel::TABLE.".".OrdemServicoModel::NUMERO." AS $asOrdem" 
        );
        
        $fields_joined = implode(",", $fields);
        
        $joins[] = "LEFT JOIN ".FuncionarioModel::TABLE." ".self::ALIAS_VENDEDOR." ON ".self::TABLE.".".self::VENDEDOR." = ".self::ALIAS_VENDEDOR.".".FuncionarioModel::ID;
        $joins[] = "LEFT JOIN ".FuncionarioModel::TABLE." ".self::ALIAS_AGENTE." ON ".self::TABLE.".".self::AGENTE." = ".self::ALIAS_AGENTE.".".FuncionarioModel::ID;
        $joins[] = "LEFT JOIN ".FuncionarioModel::TABLE." ".self::ALIAS_LIDER." ON ".self::TABLE.".".self::LIDER_EQUIPE." = ".self::ALIAS_LIDER.".".FuncionarioModel::ID;
        $joins[] = "LEFT JOIN ".LojaModel::TABLE." ON ".self::TABLE.".".self::LOJA." = ".LojaModel::TABLE.".".LojaModel::ID;
        $joins[] = "LEFT JOIN ".OrdemServicoModel::TABLE." ON ".self::OS." = ".OrdemServicoModel::TABLE.".".OrdemServicoModel::ID;
        $joins[] = "LEFT JOIN ".ClienteModel::TABLE." ON ".self::TABLE.".".self::CLIENTE." = ".ClienteModel::TABLE.".".ClienteModel::ID;
    
        $res = parent::select(self::TABLE." ".implode(" ", $joins), $fields_joined, $condition);
        $ana = $this->getAnnalisses();
        $vendas = array();
        while (($row = $ana->fetchObject($res)) !== FALSE){
            $vendas[] = new Venda(  $row->{self::ID}, 
                                    $row->{self::DT_VENDA},
                                    $row->{self::DT_PREVISAO},        
                                    $row->{self::DT_ENTREGA}, 
                                    $row->{$asCliente}, 
                                    $row->{LojaModel::SIGLA}, 
                                    $row->{$asVendedor}, 
                                    $row->{$asAgente},
                                    $row->{$asOrdem},
                                    $row->{self::STATUS},
                                    $row->{self::VENDA_ANTIGA},
                                    $row->{self::EQUIPE},
                                    $row->{$asLider});
        }
        return $vendas;
    }
    
    /**
     * Seleciona uma lista de vendas com os <i>fields</i> preenchidos com os valores do banco.
     * <br/>
     * Se diferencia do select e do superSelect por usar as junções (LEFT JOIN) para associar valores
     * textuais, além das chaves numéricas das tabelas relacionadas e mesmo assim retornar apenas valores
     * dessa entidade.
     * das respectivas chaves estrangeiras (vendedor, cliente, os, etc.).
     * @param mixed $fields colunas da seleção, pode ser um array (utilize as constantes das colunas).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de vendas do tipo Venda.
     */
    public function selectForSearch($fields = "*", $condition = null){
        
        if(is_array($fields)) $fields = implode (",", $fields);
        $selectvalor = "SELECT SUM(".ProdutoVendaModel::VALOR.") FROM ".
                        ProdutoVendaModel::TABLE." WHERE ".ProdutoVendaModel::VENDA.
                        " = ".self::TABLE.".".self::ID;
        $fields .= ", ($selectvalor) as valor ";
        
        $joins[] = "LEFT JOIN ".FuncionarioModel::TABLE." ON ".self::TABLE.".".self::VENDEDOR." = ".FuncionarioModel::TABLE.".".FuncionarioModel::ID;
        $joins[] = "LEFT JOIN ".LojaModel::TABLE." ON ".self::TABLE.".".self::LOJA." = ".LojaModel::TABLE.".".LojaModel::ID;
        $joins[] = "LEFT JOIN ".OrdemServicoModel::TABLE." ON ".self::OS." = ".OrdemServicoModel::TABLE.".".OrdemServicoModel::ID;
        $joins[] = "LEFT JOIN ".ClienteModel::TABLE." ON ".self::TABLE.".".self::CLIENTE." = ".ClienteModel::TABLE.".".ClienteModel::ID;
    
        $res = parent::select(self::TABLE." ".implode(" ", $joins), $fields, $condition);
        return $this->handleCursor($res);
    }
    
    /**
     * Seleciona uma lista de vendas da <i>regiao</i> com os <i>fields</i> preenchidos 
     * com os valores do banco.
     * <br/>
     * @param int $regiao identificador da regiao.
     * @param mixed $fields colunas da seleção, pode ser um array (utilize as constantes das colunas).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de vendas do tipo Venda.
     */
    public function selectForRegiao($regiao, $fields = "*", $condition = null){
        if(is_array($fields)) $fields = implode (",", $fields);
        
        $joins[] = "LEFT JOIN ".OrdemServicoModel::TABLE." ON ".self::TABLE.".".self::OS." = ".OrdemServicoModel::TABLE.".".OrdemServicoModel::ID;
        $joins[] = "LEFT JOIN ".ClienteModel::TABLE." ON ".self::TABLE.".".self::CLIENTE." = ".ClienteModel::TABLE.".".ClienteModel::ID;
        $joins[] = "LEFT JOIN ".LocalidadeModel::TABLE." ON ".ClienteModel::TABLE.".".ClienteModel::LOCALIDADE." = ".LocalidadeModel::TABLE.".".LocalidadeModel::ID;
        $joins[] = "LEFT JOIN ".RotaModel::TABLE." ON ".LocalidadeModel::TABLE.".".LocalidadeModel::ROTA." = ".RotaModel::TABLE.".".RotaModel::ID;
        $joins[] = "LEFT JOIN ".RegiaoModel::TABLE." ON ".RotaModel::TABLE.".".RotaModel::REGIAO." = ".RegiaoModel::TABLE.".".RegiaoModel::ID;
        
        if (!empty($regiao)) {
            $this_condition = RegiaoModel::TABLE.".".RegiaoModel::ID." = $regiao";
            
            if(is_null($condition)){
                $condition = $this_condition;
            } else {
                $condition .= " AND $this_condition ";
            }
        }

        $res = parent::select(self::TABLE." ".implode(" ", $joins), $fields, $condition);
        return $this->handleCursor($res);
    }
    
    /**
     * Seleciona uma lista de vendas da <i>rota</i> com os <i>fields</i> preenchidos 
     * com os valores do banco.
     * <br/>
     * @param int $rota identificador da rota.
     * @param mixed $fields colunas da seleção, pode ser um array (utilize as constantes das colunas).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de vendas do tipo Venda.
     */
    public function selectForRota($rota, $fields = "*", $condition = null){
        if(is_array($fields)) $fields = implode (",", $fields);
        
        $joins[] = "LEFT JOIN ".OrdemServicoModel::TABLE." ON ".self::TABLE.".".self::OS." = ".OrdemServicoModel::TABLE.".".OrdemServicoModel::ID;
        $joins[] = "LEFT JOIN ".ClienteModel::TABLE." ON ".self::TABLE.".".self::CLIENTE." = ".ClienteModel::TABLE.".".ClienteModel::ID;
        $joins[] = "LEFT JOIN ".LocalidadeModel::TABLE." ON ".ClienteModel::TABLE.".".ClienteModel::LOCALIDADE." = ".LocalidadeModel::TABLE.".".LocalidadeModel::ID;
        
        $this_condition = LocalidadeModel::TABLE.".".LocalidadeModel::ROTA." = $rota ORDER BY " . LocalidadeModel::SEQ_ROTA . "," .
                            ClienteModel::TABLE . "." . ClienteModel::ENDERECO;
        
        if(is_null($condition)){
            $condition = $this_condition;
        } else {
            $condition .= " AND $this_condition ";
        }
        
        $res = parent::select(self::TABLE." ".implode(" ", $joins), $fields, $condition);
        return $this->handleCursor($res);
    }

    public function selectJoins($fields, $joins, $condition) {
        if(is_array($fields)) $fields = implode (",", $fields);
        $joinsstr = implode(" ", $joins);
        $res = parent::select(self::TABLE." ".$joinsstr, $fields, $condition);
        $ana = $this->getAnnalisses();
        $vendas = array();
        while (($row = $ana->fetchObject($res)) !== FALSE){
            $venda = new Venda(isset($row->{self::ID})       ? $row->{self::ID} : 0,
                isset($row->{self::DT_VENDA})   ? $row->{self::DT_VENDA} : "",
                isset($row->{self::DT_PREVISAO})? $row->{self::DT_PREVISAO} : "",
                isset($row->{self::DT_ENTREGA}) ? $row->{self::DT_ENTREGA} : "",
                isset($row->{self::CLIENTE})    ? $row->{self::CLIENTE} : 0,
                isset($row->{self::LOJA})       ? $row->{self::LOJA} : 0,
                isset($row->{self::VENDEDOR})   ? $row->{self::VENDEDOR} : 0,
                isset($row->{self::AGENTE})     ? $row->{self::AGENTE} : 0,
                isset($row->{self::OS})         ? $row->{self::OS} : 0,
                isset($row->{self::STATUS})     ? $row->{self::STATUS} : false,
                isset($row->{self::VENDA_ANTIGA}) ? $row->{self::VENDA_ANTIGA} : null,
                isset($row->{self::EQUIPE})     ? $row->{self::EQUIPE} : null,
                isset($row->{self::LIDER_EQUIPE}) ? $row->{self::LIDER_EQUIPE} : null);
            $vendas[] = array($venda, isset($row->PAG_MAX) ? $row->PAG_MAX : '');
        }
        return $vendas;
    }

    private function handleCursor($resultCursor) {
        $ana = $this->getAnnalisses();
        $vendas = array();
        while (($row = $ana->fetchObject($resultCursor)) !== FALSE){
            $vendas[] = new Venda(isset($row->{self::ID})       ? $row->{self::ID} : 0,
                isset($row->{self::DT_VENDA})   ? $row->{self::DT_VENDA} : "",
                isset($row->{self::DT_PREVISAO})? $row->{self::DT_PREVISAO} : "",
                isset($row->{self::DT_ENTREGA}) ? $row->{self::DT_ENTREGA} : "",
                isset($row->{self::CLIENTE})    ? $row->{self::CLIENTE} : 0,
                isset($row->{self::LOJA})       ? $row->{self::LOJA} : 0,
                isset($row->{self::VENDEDOR})   ? $row->{self::VENDEDOR} : 0,
                isset($row->{self::AGENTE})     ? $row->{self::AGENTE} : 0,
                isset($row->{self::OS})         ? $row->{self::OS} : 0,
                isset($row->{self::STATUS})     ? $row->{self::STATUS} : false,
                isset($row->{self::VENDA_ANTIGA}) ? $row->{self::VENDA_ANTIGA} : null,
                isset($row->{self::EQUIPE})     ? $row->{self::EQUIPE} : null,
                isset($row->{self::LIDER_EQUIPE}) ? $row->{self::LIDER_EQUIPE} : null);
        }
        return $vendas;
    }

    /**
     * Obtém o identificador do cobrador de uma venda.
     * @param int $id_venda identificador da venda.
     * @return string id do cobrador.
     */
    public function cobradorForVenda($id_venda){
        $joins[] = "LEFT JOIN  ".ClienteModel::TABLE." ON ".ClienteModel::TABLE.".".ClienteModel::ID." = ".self::TABLE.".".self::CLIENTE; 
        $joins[] = "LEFT JOIN  ".LocalidadeModel::TABLE." ON ".LocalidadeModel::TABLE.".".LocalidadeModel::ID." = ".ClienteModel::TABLE.".".ClienteModel::LOCALIDADE; 
        $joins[] = "LEFT JOIN  ".RotaModel::TABLE." ON ".RotaModel::TABLE.".".RotaModel::ID." = ".LocalidadeModel::TABLE.".".LocalidadeModel::ROTA; 
        $joins[] = "LEFT JOIN  ".RegiaoModel::TABLE." ON ".RegiaoModel::TABLE.".".RegiaoModel::ID." = ".RotaModel::TABLE.".".RotaModel::REGIAO; 
        $condition  = self::TABLE.".".self::ID." = $id_venda";
        $joinsstr = implode(" ", $joins);
        $res = parent::select(self::TABLE." $joinsstr", RegiaoModel::COBRADOR, $condition);
        $anna = $this->getAnnalisses();
        $id_cobrador = 0;
        if(($row = $anna->fetchObject($res)) !== FALSE)            
            $id_cobrador = $row->{RegiaoModel::COBRADOR};
        return $id_cobrador;
    }
    
    /**
     * Realiza a remoção de uma venda.
     * @param int $id_venda identificador da venda (pode string também).
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function deleteReally($id_venda){
        $condition = self::ID." = $id_venda";
        return parent::delete(self::TABLE, $condition);
    }
    
    /**
     * Atualiza <i>field</i> com <i>value</i> de acordo com <i>condition</i>.
     * @param string $value campo da tabela
     * @param string $value valor a atribuido
     * @param string $condition cláusula WHERE
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function simpleUpdate($field, $value, $condition = null){
        return parent::update(self::TABLE, $field, $value, $condition);
    }
    
    /**
     * Manipula erros.
     * @return string mensagem de error à nível de usuário caso seja um erro conhecido ou
     * uma mensagem com código de erro.
     */
    public function handleError() {
        $err_code = $this->errorCode(); 
        if($err_code == Annalisses::ERR_DUPLICATE) 
            return "A ordem de serviço informada já pertence à uma venda";
        return parent::handleError();
    }
}
?>