<?php
class Produto {
    
    var $id;
    var $codigo;
    var $descricao;
    var $precoCompra;
    var $precoVenda;
    var $precoVendaMin;
    var $tipo;
    var $marca;
    var $categoria;
    
    public function __construct($id = 0, $codigo = "", $descricao = "",
                                $precoCompra = 0, $precoVenda = 0, $precoVendaMin = 0,
                                $tipo = null, $marca = null, $categoria=0) {
        $this->id = $id;
        $this->codigo = $codigo;
        $this->descricao = $descricao;
        $this->precoCompra = $precoCompra;
        $this->precoVenda = $precoVenda;
        $this->precoVendaMin = $precoVendaMin;
        $this->tipo = $tipo;
        $this->marca = $marca;
        $this->categoria = $categoria;
    }
    
}
?>
