<?php
namespace Sisoptica\Entity;



class Tupla {
    
    var $id;
    var $nome;
    var $quantidade;
    var $total;

    public function __construct($id = 0, $quantidade = 0, $total = 0) {
        $this->id           = $id;
        $this->quantidade   = $quantidade;
        $this->total        = $total;
    }
    
}
?>
