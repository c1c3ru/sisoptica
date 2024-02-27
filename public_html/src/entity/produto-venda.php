<?php

class ProdutoVenda {
    var $id;
    var $produto;
    var $venda;
    var $valor;
    
    public function __construct($id = 0, $produto = 0, $venda = 0, $valor = 0) {
        $this->id       = $id;
        $this->produto  = $produto;
        $this->venda    = $venda;
        $this->valor    = $valor;
    }
    
}
?>
