<?php
namespace Sisoptica\Entity;



class LojaProduto {
    
    var $id;
    var $loja;
    var $produto;
    var $quantidade;
    
    public function __construct($id = 0, $loja = null, 
                                $produto = null, $quantidade = 0) {
        $this->id           = $id;
        $this->loja         = $loja;
        $this->produto      = $produto;
        $this->quantidade   = $quantidade;
    }
    
}
?>
