<?php
namespace Sisoptica\Repository;

include_once MODELS."central-estoque.php";
include_once MODELS."produto.php";

class CentralEstoqueProdutoModel extends Database {
    
    const TABLE = "central_estoque_produto";
    
    const PRODUTO       = "id_produto";
    const CENTRAL       = "id_central_estoque";
    const QUANTIDADE_ENTRADA= "quantidade_entrada";
    const QUANTIDADE_SAIDA  = "quantidade_saida";
    const VALOR         = "valor";
    
    public function insert(CentralEstoqueProduto $cep) {
        $fields = implode(",", array(
            self::PRODUTO, self::CENTRAL, 
            self::QUANTIDADE_ENTRADA, self::QUANTIDADE_SAIDA, 
            self::VALOR
        ));
        $cep = get_object_vars($cep);
        $res = parent::insert(self::TABLE, $fields, Database::turnInValues($cep));
        return $res;
    }
    
    public function inserts($arrProdutos){
        if(empty($arrProdutos) || !is_array($arrProdutos)) return false;
        $values = array();
        foreach ($arrProdutos as $produto){
            $vars = get_object_vars($produto);
            $values[] = "(".Database::turnInValues($vars).")";
        }
        $fields = implode(",", array( 
            self::PRODUTO, self::CENTRAL, 
            self::QUANTIDADE_ENTRADA, self::QUANTIDADE_SAIDA, 
            self::VALOR
        ));
        return parent::insert(self::TABLE, $fields, $values);
    }
    
    public function select($fields = " * ", $condition = null) {
        if(is_array($fields)) $fields = implode(",", $fields);
        $res = parent::select(self::TABLE, $fields, $condition);
        $ana = $this->getAnnalisses();
        $ceps = array();        
        while(($row = $ana->fetchObject($res)) !== false){
            $cep = new CentralEstoqueProduto(
                isset($row->{self::PRODUTO}) ? $row->{self::PRODUTO} : null,
                isset($row->{self::CENTRAL}) ? $row->{self::CENTRAL} : null,
                isset($row->{self::QUANTIDADE_ENTRADA}) ? $row->{self::QUANTIDADE_ENTRADA} : 0,
                isset($row->{self::QUANTIDADE_SAIDA}) ? $row->{self::QUANTIDADE_SAIDA} : 0,
                isset($row->{self::VALOR}) ? $row->{self::VALOR} : 0
            );
            $ceps[] = $cep;
        }
        return $ceps;
    }

    public function delete($id_produto, $id_central) {
        $conditions = array();
        if(!is_null($id_produto)) {
            $conditions[] = self::PRODUTO." = ".$id_produto;
        } 
        if(!is_null($id_central)){
            $conditions[] = self::CENTRAL."=".$id_central;
        }
        if(empty($conditions)){ return false; }
        return parent::delete(self::TABLE ,implode("AND", $conditions), false);
    }
    
    
    public function update(CentralEstoqueProduto $cep) {
        $dic = array(
            self::QUANTIDADE_ENTRADA=> $cep->quantidadeEntrada,
            self::QUANTIDADE_SAIDA  => $cep->quantidadeSaida,
            self::VALOR   => $cep->valor
        );
        $condition = self::PRODUTO." = ".$cep->produto." AND ".self::CENTRAL."=".$cep->central;
        return $this->formattedUpdates(self::TABLE, self::turnInUpdateValues($dic), $condition);
    }
    
    public function simpleUpdate($fields, $values, $condition){
        return parent::update(self::TABLE, $fields, $values, $condition);
    }
}
?>
