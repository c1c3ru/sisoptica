<?php
class Equipe {
    
    var $id;
    var $nome;
    var $loja;
    var $lider;
    var $integrantes = array();
    
    public function __construct($id = 0, $nome = "", $loja = null, $lider = null) {
        $this->id   = $id;
        $this->nome = $nome;
        $this->loja = $loja;
        $this->lider= $lider;
    }
    
}
?>
