<?php
namespace Sisoptica\Repository;

/**
 * Essa classe implementa o modelo da entidade TipoPagamento.
 * @author Emanuel Oliveira <emanuel.oliveira23@gmail.com>
 */
class TipoPagamentoModel extends Database {
    
    /**
     * Nome da tabela de tipo de pagamentos no banco de dados 
     */
    const TABLE = 'tipo_pagamento';
    
    /**
     * Nome da coluna do identificador do tipo de pagamento no banco de dados.
     */
    const ID = 'id';
    
    /**
     * Nome da coluna do nome do tipo de pagamento no banco de dados.
     */
    const NOME = 'nome';
    
    /**
     * Nome da coluna de observacao do tipo de pagamento no banco de dados.
     */
    const OBSERVACAO = 'observacao';
    
    /**
     * Seleciona uma lista de tipos de pagamentos com os <i>fields</i> preenchidos com os valores do banco.
     * @param mixed $fields colunas da seleção, pode ser um array (utilize as constantes das colunas).
     * @param string $condition filtra a seleção das linhas, se for <i>null</i> não será considerado.
     * @return array lista de tipos de pagamentos do tipo TipoPagamento.
     */
    public function select($fields = " * ", $condition = null) {
        if(is_array($fields)) $fields = implode(",", $fields);
        $res = parent::select(self::TABLE, $fields, $condition);		
        $ana = $this->getAnnalisses();		
        $tipos = array();
        while(($row = $ana->fetchObject($res)) !== false) {
           $tipos[] = new TipoPagamento(
                        isset($row->{self::ID})? $row->{self::ID} : 0, 
                        isset($row->{self::NOME})? ($row->{self::NOME}): "",
                        isset($row->{self::OBSERVACAO})? ($row->{self::OBSERVACAO}): ""       
                      );	
        }
        return $tipos;
    }
    
    /**
     * Insere um tipo de pagamento na base de dados.
     * @param TipoPagamento $tipo tipo de pagamento que vai ser inserido
     * @return bool true em caso de sucesso na inserção ou false em caso de falha
     */
    public function insert(TipoPagamento $tipo) {
        $values = get_object_vars($tipo);
        unset($values["id"]);
        $fields = implode(",", array(self::NOME, self::OBSERVACAO));
        return parent::insert(self::TABLE, $fields, Database::turnInValues($values));
    }

    /**
     * Atualiza <i>tipo</i> de acordo com o seu identificador.
     * @param TipoPagamento $tipo tipo de pagamento que vai ser atualizado
     * @return bool true em caso de sucesso na atualização ou false em caso de falha
     */
    public function update(TipoPagamento $tipo){
        $dic = array(
            self::NOME          => $tipo->nome,
            self::OBSERVACAO    => $tipo->observacao
        );
        $condition = self::ID.' = '.$tipo->id;
        return parent::formattedUpdates(self::TABLE, Database::turnInUpdateValues($dic), $condition);
    }
    
    /**
     * Realiza a remoção de um tipo de pagamento.
     * @param int $id_tipo identificador do tipo de pagamento (pode string também).
     * @return bool true em caso de sucesso, ou false em caso de falha
     */
    public function delete($id_tipo){
        return parent::delete(self::TABLE, self::ID." = $id_tipo");
    }
    
    /**
     * Manipula erros.
     * @return string mensagem de error à nível de usuário caso seja um erro conhecido ou
     * uma mensagem com código de erro.
     */
    public function handleError() {
        $err_code = $this->errorCode(); 
        if($err_code == Annalisses::ERR_DUPLICATE) 
            return "Já existe um tipo de pagamento com este nome";
        return parent::handleError();
    }
    
}

?>
