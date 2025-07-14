<?php
namespace Sisoptica\Entity;




class Rota {
    
    var $id;
    var $nome;
    var $regiao;
    
    public function __construct($id = 0, $nome = "", $regiao = null) {
        $this->id = $id;
        $this->nome = $nome;
        $this->regiao = $regiao;
    }
}
?>
