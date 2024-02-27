<?php

include_once MODELS."produto.php";
include_once MODELS."loja.php";

class LojaProdutoModel extends Database {
    
    const TABLE = "loja_produto";
    
    const ID            = "id";
    const LOJA          = "id_loja";
    const PRODUTO       = "id_produto";
    const QUANTIDADE    = "quantidade";
    
    public function insert(LojaProduto $lojaProduto) {
        $fields = implode(",", array(self::LOJA, self::PRODUTO, self::QUANTIDADE));
        $lojaProduto = get_object_vars($lojaProduto);
        unset($lojaProduto["id"]);
        $res = parent::insert(self::TABLE, $fields, Database::turnInValues($lojaProduto));
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
            self::LOJA, self::PRODUTO, self::QUANTIDADE
        ));
        return parent::insert(self::TABLE, $fields, $values);
    }
    
    public function select($fields = " * ", $condition = null) {
        if(is_array($fields)) $fields = implode(",", $fields);
        $res = parent::select(self::TABLE, $fields, $condition);
        $ana = $this->getAnnalisses();
        $lojaProdutos = array();        
        while(($row = $ana->fetchObject($res)) !== false){
            $lojaProduto = new LojaProduto(
                isset($row->{self::ID}) ? $row->{self::ID} : null,
                isset($row->{self::LOJA}) ? $row->{self::LOJA} : null,
                isset($row->{self::PRODUTO}) ? $row->{self::PRODUTO} : 0,
                isset($row->{self::QUANTIDADE}) ? $row->{self::QUANTIDADE} : 0
            );
            $lojaProdutos[] = $lojaProduto;
        }
        return $lojaProdutos;
    }
    
    public function distinctProdutos() {
        $res = parent::select(self::TABLE, 'DISTINCT('.self::PRODUTO.') AS PRODUTO');
        $ana = $this->getAnnalisses();
        $produtos = array();
        while(($row = $ana->fetchObject($res))!== false){
            $produtos[] = $row->PRODUTO;
        }
        return $produtos;
    }
    
    public function superSulect($condition = null) {
        $joins[] = self::leftJoin("ProdutoModel", self::TABLE.".".self::PRODUTO);
        $joins[] = self::leftJoin("LojaModel", self::TABLE.".".self::LOJA);
        $fields = implode(",", array(ProdutoModel::CODIGO, ProdutoModel::DESCRICAO, 
                                     LojaModel::SIGLA,
                                     self::TABLE.".".self::QUANTIDADE, self::TABLE.".".self::ID));
        $res = parent::select(self::TABLE." ".implode(" ", $joins), $fields, $condition);
        $ana = $this->getAnnalisses();
        $lojaProdutos = array();        
        while(($row = $ana->fetchObject($res)) !== false){
            $lojaProduto = new LojaProduto(
                $row->{self::ID},
                $row->{ProdutoModel::CODIGO}.":".$row->{ProdutoModel::DESCRICAO},
                $row->{LojaModel::SIGLA},
                $row->{self::QUANTIDADE}
            );
            $lojaProdutos[] = $lojaProduto;
        }
        return $lojaProdutos;
    }
 
    public function delete($id_produto_loja) {
        $condition = self::ID." = ".$id_produto_loja;
        return parent::delete(self::TABLE ,$condition);
    }
    
    public function update(LojaProduto $lojaProduto) {
        $dic = array(
            self::LOJA      => $lojaProduto->loja,
            self::PRODUTO   => $lojaProduto->produto,
            self::QUANTIDADE=> $lojaProduto->quantidade
        );
        $condition = self::ID." = ".$lojaProduto->id;
        return $this->formattedUpdates(self::TABLE, self::turnInUpdateValues($dic), $condition);
    }
    
}
?>
