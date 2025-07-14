<?php
namespace Sisoptica\Entity;



class TipoPagamento {
    
    var $id;
    var $nome;
    var $observacao;
    
    public function __construct($id = 0, $nome = "", $observacao = "") {
        $this->id           = $id;
        $this->nome         = $nome;
        $this->observacao   = $observacao;
    }
    
}
?>
