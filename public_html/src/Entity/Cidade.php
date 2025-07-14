<?php
namespace Sisoptica\Entity;



class Cidade {
    var $id;
    var $nome;
    var $estado;   
    public function __construct($id = 0, $nome = "", $estado = null){
            $this->id = $id;
            $this->nome = $nome;
            $this->estado = $estado;
    }
}
?>
