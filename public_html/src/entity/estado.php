<?php
class Estado {
    
    var $id;
    var $nome;
    var $sigla;
    
    public function __construct($id = 0, $nome = "", $sigla = ""){
        $this->id = $id;
        $this->nome = $nome;
        $this->sigla = $sigla;
    }
}
?>
