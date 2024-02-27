<?php
class Localidade {
    var $id;
    var $nome;
    var $cidade;
    var $rota;
    var $ordem;
    var $loja;
    var $regiao;
    public function __construct($id = 0, $nome = "", 
                                $cidade = null, $rota = null, $ordem = null) {
        $this->id = $id;
        $this->nome = $nome;
        $this->cidade = $cidade;
        $this->rota = $rota;
        $this->ordem = $ordem;
    }
}

?>
