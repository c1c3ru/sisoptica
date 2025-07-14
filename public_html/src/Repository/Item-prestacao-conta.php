<?php
namespace Sisoptica\Repository;
include_once MODELS.'funcionario.php';
include_once MODELS.'tipo-pagamento.php';
/**
 * Essa classe implementa o modelo da entidade ItemPrestacaoConta.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class ItemPrestacaoContaModel extends Database {
    
    /**
     * Nome da tabela de itens de prestação de conta no banco de dados 
     */
    const TABLE     = 'prestacao_conta_item';
    
    /**
     * Nome da coluna do identificador do item de prestação de conta no banco de dados.
     */
    const ID        = 'id';
    
    /**
     * Nome da coluna do valor do item de prestação de conta no banco de dados.
     */
    const VALOR     = 'valor';
    
    /**
     * Nome da coluna do tipo do item de prestação de conta no banco de dados.
     */
    const TIPO      = 'tipo_pagamento';
    
    /**
     * Nome da coluna do tipo do item de prestação de conta no banco de dados.
     */
    const DATA      = 'data';
    
    /**
     * Nome da coluna da prestação de conta do item de prestação de conta no banco de dados.
     */
    const PRESTACAO = 'id_prest_conta';

    /**
     * Insere um item de prestação de conta na base de dados.
     * @param ItemPrestacaoConta $item item de prestação de conta vai ser inserido
     * @return bool true em caso de sucesso na inserção ou false em caso de falha
     */
    public function insert(ItemPrestacaoConta $item) {
        $fields = implode(',', array(
            self::VALOR, self::TIPO, self::DATA,self::PRESTACAO 
        ));
        $vars = get_object_vars($item);
        unset($vars['id']);
        return parent::insert(self::TABLE, $fields, Database::turnInValues($vars));
    }
    
    /**
     * Insere uma lista de itens de prestação de conta na base de dados.
     * @param array $itens itensde prestação de conta vão ser inseridos
     * @return bool true em caso de sucesso na inserção ou false em caso de falha
     */
    public function inserts($itens){
        if(empty($itens) || !is_array($itens)) return false;
        $values = array();
        foreach ($itens as $item){
            $vars = get_object_vars($item);
            unset($vars['id']);
            $values[] = "(".Database::turnInValues($vars).")";
        }
        $fields = implode(",", array( self::VALOR, self::TIPO, self::DATA, self::PRESTACAO ));
        return parent::insert(self::TABLE, $fields, $values);        
    }
    
    /**
     * Seleciona uma lista de itens de prestação de conta com todos os campos preenchidos com os valores do banco.
     * <br/>
     * Se diferencia do select comum por trazer valores visíveis (como o nome do tipo por exemplo) no lugar
     * das respectivas chaves estrangeiras (tipo).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de itens de prestação do tipo ItemPrestacaoConta.
     */
    public function superSelect($condition = null) {
        $fields     = array(self::TABLE.'.'.self::ID, self::PRESTACAO, 
                            self::VALOR, self::TABLE.'.'.self::DATA,
                            TipoPagamentoModel::TABLE.'.'.TipoPagamentoModel::NOME); 
        $joins[]    = self::leftJoin('PrestacaoContaModel', self::PRESTACAO);
        $joins[]    = self::leftJoin('FuncionarioModel', PrestacaoContaModel::COBRADOR);
        $joins[]    = self::leftJoin('TipoPagamentoModel', self::TABLE.'.'.self::TIPO);
        $res        = parent::select(self::TABLE.' '.implode(' ', $joins), $fields, $condition);		
        $ana        = $this->getAnnalisses();		
        $itens = array();
        while(($row = $ana->fetchObject($res)) !== false) {
            $itens[] = new ItemPrestacaoConta(
                $row->{self::ID},
                $row->{self::VALOR},
                $row->{TipoPagamentoModel::NOME},
                $row->{self::DATA},
                $row->{self::PRESTACAO}
            );	
        }
        return $itens;
    }
    
    /**
     * Seleciona uma lista de itens de prestação de conta com os <i>fields</i> preenchidos com os valores do banco.
     * @param mixed $fields colunas da seleção, pode ser um array (utilize as constantes das colunas).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de itens de prestação de conta do tipo ItemPrestacaoConta.
     */
    public function select($fields = " * ", $condition = null) {
        $all_fields = implode(',', array(
            self::TABLE.'.'.self::ID, self::TABLE.'.'.self::DATA,
            self::TABLE.'.'.self::PRESTACAO, self::TABLE.'.'.self::TIPO, 
            self::TABLE.'.'.self::VALOR)
        );
        if(is_array($fields)) $fields = implode(",", $fields);
        $fields = str_replace('*', $all_fields, $fields);
        $this_condition = self::PRESTACAO.' = '.PrestacaoContaModel::TABLE.'.'.PrestacaoContaModel::ID;
        $this_condition.= " AND ".PrestacaoContaModel::COBRADOR.' = '.FuncionarioModel::TABLE.'.'.FuncionarioModel::ID;
        if(is_null($condition)){
            $condition = $this_condition;
        } else {
            $condition = $this_condition.' AND '.$condition;
        }
        $res        = parent::select(
            self::TABLE.','.PrestacaoContaModel::TABLE.','.FuncionarioModel::TABLE, 
            $fields, $condition);		
        $ana        = $this->getAnnalisses();		
        $itens      = array();
        while(($row = $ana->fetchObject($res)) !== false) {
            $itens[] = new ItemPrestacaoConta(
                isset($row->{self::ID})         ? $row->{self::ID}:0,
                isset($row->{self::VALOR})      ? $row->{self::VALOR}:0,
                isset($row->{self::TIPO})       ? $row->{self::TIPO}:0,
                isset($row->{self::DATA})       ? $row->{self::DATA}:'',
                isset($row->{self::PRESTACAO})  ? $row->{self::PRESTACAO}:0
            );	
        }
        return $itens;
    }
    
    
    
    /**
     * Atualiza <i>item</i> de acordo com o seu identificador.
     * @param ItemPrestacaoConta $item item de prestação de conta que vai ser atualizado
     * @return bool true em caso de sucesso na atualização ou false em caso de falha
     */
    public function update(ItemPrestacaoConta $item) {
        $dic = array(
            self::VALOR     => $item->valor,
            self::TIPO      => $item->tipo,
            self::DATA      => $item->data,
            self::PRESTACAO => $item->prestacao
        );
        $condition = self::ID.' = '.$item->id;
        return parent::formattedUpdates(self::TABLE, Database::turnInUpdateValues($dic), $condition);
    }
    
    /**
     * Realiza a remoção de um item de prestação de conta.
     * @param int $id_item identificador doitem de prestação (pode string também).
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function delete($id_item) {
        $condition = self::ID.' = '.$id_item;
        return parent::delete(self::TABLE, $condition);
    }
    
    /**
     * Remove todos os itens associados a uma prestação de conta.
     * @param int $id_prest indetificador da prestação de conta
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function deleteFromPrestacao($id_prest){
        $condition = self::PRESTACAO.' = '.$id_prest;
        return parent::delete(self::TABLE, $condition);
    }
    
    /**
     * Obtém a soma dos valores dos itens de prestação de conta que obedecem uma 
     * condição.
     * @param string $condition condição de seleção SQL
     * @return float soma dos valores dos itens projetados 
     */
    public function getSomaValores($condition){
        $fields = 'SUM('.self::VALOR.') AS SOMA';
        $join[] = self::innerJoin('PrestacaoContaModel', self::PRESTACAO );
        $res    = parent::select(self::TABLE.' '.implode(' ', $join), $fields, $condition);
        $anna   = $this->getAnnalisses();
        if(($row = $anna->fetchObject($res)) !== FALSE){
            return (float) $row->SOMA;
        }
        return 0;
    }
    
}
?>
