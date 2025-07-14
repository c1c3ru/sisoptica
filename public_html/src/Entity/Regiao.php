<?php
namespace Sisoptica\Entity;



class Regiao {
    var $id;
    var $nome;
    var $cobrador;
    var $loja;
    public function __construct($id = 0, $nome = "", $cobrador = 0, $loja = 0) {
        $this->id = $id;
        $this->nome = $nome;
        $this->cobrador = $cobrador;
        $this->loja = $loja;
    }
}
?>
