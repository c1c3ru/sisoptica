<?php
//Incluindo classes associadas a esse modelo
include_once MODELS."funcionario.php";
include_once MODELS.'prestacao-conta.php';
include_once MODELS.'venda.php';
include_once MODELS.'parcela.php';

/**
 * Essa classe implementa o modelo da entidade Pagamento.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class PagamentoModel extends Database {
    
    /**
     * Nome da tabela de pagamentos no banco de dados 
     */
    const TABLE = "venda_parcela_pgmto";
    
    /**
     * Nome da coluna do identificador do pagamento no banco de dados.
     */
    const ID                = "id";
    
    /**
     * Nome da coluna do valor do pagamento no banco de dados.
     */
    const VALOR             = "valor_pgmto";
    
    /**
     * Nome da coluna da data do pagamento no banco de dados.
     */
    const DATA              = "data_pgmto";
    
    /**
     * Nome da coluna da data da baixa identificador do pagamento no banco de dados.
     */
    const DATA_BAIXA        = "data_baixa";
    
    /**
     * Nome da coluna do nuemro da parcela do pagamento no banco de dados.
     */
    const NUMERO_PARCELA    = "numero_parcela_venda_parcela";
    
    /**
     * Nome da coluna do identificador do cobrador do pagamento no banco de dados.
     */
    const COBRADOR          = "id_funcionario_cobrador";
    
    /**
     * Nome da coluna do identificador da venda pagamento no banco de dados.
     */
    const VENDA_PARCELA     = "id_venda_venda_parcela";
    
    /**
     * Nome da coluna do autor do pagamento no banco de dados.
     */
    const AUTOR             = "id_funcionario_autor";
    
    /**
     * Nome da coluna da data de edição do pagamento no banco de dados.
     */
    const DATA_EDICAO       = "data_edicao";
    
    /**
     * Nome da coluna que indica se o pagamento foi feito junto com o desconto no banco de dados.
     */
    const COM_DESCONTO      = "com_desconto";
    
    /**
     * Nome da coluna da prestação de conta do pagamentos no banco de dados.
     */
    const PREST_CONTA       = "id_prest_conta";

    const VENDA_ALIAS       = "V";

    /**
     * Seleciona uma lista de pagamentos com os <i>fields</i> preenchidos com os valores do banco.
     * @param mixed $fields colunas da seleção, pode ser um array (utilize as constantes das colunas).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de pagamentos do tipo Pagamento.
     */
    public function select($fields = " * ", $condition = null) {
        if(is_array($fields)) $fields = implode(",", $fields);
        $res = parent::select(self::TABLE, $fields, $condition);
        $anna = $this->getAnnalisses();
        $pagamentos = array();
        while(($row = $anna->fetchObject($res)) !== FALSE){
            $pagamentos[] = new Pagamento(
                            isset($row->{self::ID})?$row->{self::ID}:0,
                            isset($row->{self::VALOR})?$row->{self::VALOR}:0,
                            isset($row->{self::DATA})?$row->{self::DATA}:"",
                            isset($row->{self::DATA_BAIXA})?$row->{self::DATA_BAIXA}:"",        
                            isset($row->{self::NUMERO_PARCELA})?$row->{self::NUMERO_PARCELA}: 0,
                            isset($row->{self::COBRADOR})?$row->{self::COBRADOR}: 0,
                            isset($row->{self::VENDA_PARCELA})?$row->{self::VENDA_PARCELA}: 0,
                            isset($row->{self::AUTOR})?$row->{self::AUTOR}: 0,
                            isset($row->{self::DATA_EDICAO})?$row->{self::DATA_EDICAO}:"",
                            isset($row->{self::COM_DESCONTO})?$row->{self::COM_DESCONTO}:FALSE,
                            isset($row->{self::PREST_CONTA})?$row->{self::PREST_CONTA}:null
                            );
        }
        return $pagamentos;
    }
    
    /**
     * Seleciona uma lista de pagamentos com todos os campos preenchidos com os valores do banco.
     * <br/>
     * Se diferencia do select comum por trazer valores visíveis (como o nome por exemplo) no lugar
     * das respectivas chaves estrangeiras (autor).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de pagamentos do tipo Pagamento.
     */
    public function superSelect($condition = null){
        $fields = implode(",", array(
            self::TABLE.".".self::ID, self::TABLE.".".self::VALOR, self::TABLE.".".self::DATA,
            self::TABLE.".".self::DATA_BAIXA, self::TABLE.".".self::NUMERO_PARCELA, self::TABLE.".".self::VENDA_PARCELA,
            self::TABLE.".".self::AUTOR, self::TABLE.".".self::DATA_EDICAO, self::TABLE.".".self::COM_DESCONTO,
            FuncionarioModel::TABLE.".".FuncionarioModel::NOME, PrestacaoContaModel::TABLE.'.'.PrestacaoContaModel::SEQ,
            PrestacaoContaModel::TABLE.'.'.PrestacaoContaModel::DT_INICIAL,
            PrestacaoContaModel::TABLE.'.'.PrestacaoContaModel::DT_FINAL
        ));

        $joins[] = self::TABLE;
        $joins[] = "INNER JOIN ".ParcelaModel::TABLE.
            " ON ".self::TABLE.".".self::NUMERO_PARCELA." = ".ParcelaModel::TABLE.".".ParcelaModel::NUMERO .
            " AND " . self::TABLE.".".self::VENDA_PARCELA." = ".ParcelaModel::TABLE.".".ParcelaModel::VENDA;
        $joins[] = "INNER JOIN ".VendaModel::TABLE. " AS ".self::VENDA_ALIAS.
            " ON ".self::TABLE.".".self::VENDA_PARCELA." = V.".VendaModel::ID;
        $joins[] = "LEFT JOIN ".FuncionarioModel::TABLE.
            " ON ".self::TABLE.".".self::COBRADOR." = ".FuncionarioModel::TABLE.".".FuncionarioModel::ID;
        $joins[] = "LEFT JOIN ".PrestacaoContaModel::TABLE.
            " ON ".self::TABLE.".".self::PREST_CONTA." = ".PrestacaoContaModel::TABLE.".".PrestacaoContaModel::ID;

        $res = parent::select(implode(" ", $joins), $fields, $condition);
        $anna = $this->getAnnalisses();
        $pagamentos = array();
        while(($row = $anna->fetchObject($res)) !== FALSE){
            $pagamentos[] = new Pagamento(
                            $row->{self::ID},
                            $row->{self::VALOR},
                            $row->{self::DATA},
                            $row->{self::DATA_BAIXA},        
                            $row->{self::NUMERO_PARCELA},
                            $row->{FuncionarioModel::NOME},
                            $row->{self::VENDA_PARCELA},
                            $row->{self::AUTOR},
                            $row->{self::DATA_EDICAO},
                            $row->{self::COM_DESCONTO},
                            $row->{PrestacaoContaModel::SEQ}.'|'.
                            $row->{PrestacaoContaModel::DT_INICIAL}.'|'.
                            $row->{PrestacaoContaModel::DT_FINAL}
                            );
        }
        return $pagamentos;
    }
    
    /**
     * Seleciona uma lista de tuplas, que são entidades específicas que agrupam dados de quantidade.
     * <br/>
     * Agrupam <b>quantidade</b> e <b>total</b> em um <b>identificador</b>, esse identificador 
     * ainda pode ter um titulo. Esse tipo de entidade é mais usado em <b>relatórios</b>, por exemplo, 
     * no relatório de Lançamentos. Auxilia a responder a pergunta: Qual o valor lançado pelos cobradores 
     * no período <i>tal</i>?
     * @param string $field_id coluna de identificação.
     * @param string $titles indica os campo que seram usados como colunas textuais
     * @param array $joins lista de junções que envolvem as tabelas que se relacionam com OS.
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de tuplas do tipo Tupla.
     */
    public function selectTupla($field_id, $titles, $joins, $condition = null){
        $fields[] = $field_id.' AS I_FIELD';
        $fields[] = 'COUNT('.self::TABLE.'.'.self::ID.') AS C_FIELD';
        $fields[] = 'SUM('.self::TABLE.'.'.self::VALOR.') AS S_FIELD';
        
        $c = 0;
        $l = count($titles);
        while ($c < $l) {
            $f = 'F_TITLE_'.($c+1);
            $fields[] = $titles[$c].' AS '.$f;
            $order_by[] = $f;
            $c++;
        }
        
        if($condition == null){ $condition = ' 1=1 GROUP BY '. implode(',', $order_by); }
        else if(isset($order_by)){ $condition .= ' GROUP BY '.implode(',', $order_by); }
        
        $res    = parent::select( self::TABLE.' '.implode(' ', $joins), implode(',', $fields), $condition);
        $anna   = $this->getAnnalisses();
        $tuplas = array();
        while(($row = $anna->fetchObject($res)) !== FALSE){
            $tupla          = new Tupla($row->I_FIELD, $row->C_FIELD, $row->S_FIELD);
            $c = 0;
            while ($c < $l) {
                $f = 'F_TITLE_'.(++$c);
                $tupla->nome[] = $row->$f;
            }
            $tuplas[]       = $tupla;
        }
        return $tuplas;
    }
    
    /**
     * Insere um pagamento na base de dados.
     * @param Pagamento $pagamento pagamento que vai ser inserido
     * @return bool true em caso de sucesso na inserção ou false em caso de falha
     */
    public function insert(Pagamento $pagamento) {
        $fields = implode(",", array(self::VALOR, self::DATA, self::DATA_BAIXA, self::NUMERO_PARCELA, self::COBRADOR,
                                     self::VENDA_PARCELA, self::AUTOR, self::COM_DESCONTO, self::PREST_CONTA));
        $vars   = get_object_vars($pagamento);
        unset($vars["id"]); 
        unset($vars["dataEdicao"]);
        return parent::insert(self::TABLE, $fields, Database::turnInValues($vars));
    }
    
    /**
     * Atualiza <i>pagamento</i> de acordo com o seu identificador.
     * @param Pagamento $pagamento pagamento que vai ser aualizado
     * @return bool true em caso de sucesso na atualização ou false em caso de falha
     */
    public function update(Pagamento $pagamento){
        $dic = array(
            self::VALOR         => $pagamento->valor,
            self::DATA          => $pagamento->data,
            self::COBRADOR      => $pagamento->cobrador,
            self::AUTOR         => $pagamento->autor,
            self::DATA_EDICAO   => $pagamento->dataEdicao,
            self::PREST_CONTA   => $pagamento->prestacaoConta
        );
        $condition = self::ID." = {$pagamento->id}";
        return parent::formattedUpdates(self::TABLE, Database::turnInUpdateValues($dic), $condition);
    }
    
    /**
     * Realiza a remoção de um pagamento.
     * @param int $id_venda identificador da venda (pode string também).
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function delete($id_pagamento_or_numero, $venda = null) {
        if(is_null($venda)) $condition = self::ID." = $id_pagamento_or_numero";
        else $condition = self::NUMERO_PARCELA." = $id_pagamento_or_numero AND ".self::VENDA_PARCELA." = $venda";
        return parent::delete(self::TABLE, $condition);
    }
}
?>
