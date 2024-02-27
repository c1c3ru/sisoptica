<?php

class Laboratorio {
    
    var $id;
    var $nome;
    var $telefone;
    var $cnpj;
    var $principal;
    
    public function __construct($id = 0, $nome = "", $telefone = "", $cnpj = "", $principal = false) {
        $this->id = $id;
        $this->nome = $nome;
        $this->telefone = $telefone;
        $this->cnpj = $cnpj;
        $this->principal = $principal;
    }
}
?>
