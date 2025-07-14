<?php
namespace Sisoptica\Entity;



class NaturezaDespesa {
    var $id;
    var $nome;
    var $tipo;
    var $entrada;
    public function __construct($id = 0, $nome = "", $tipo = 0, $entrada = false) {
        $this->id   = $id;
        $this->nome = $nome;
        $this->tipo = $tipo;
        $this->entrada = $entrada;
    }
}
?>
