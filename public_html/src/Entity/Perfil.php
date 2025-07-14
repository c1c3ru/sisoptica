<?php
namespace Sisoptica\Entity;




class Perfil {
    
    var $id;
    var $nome;
    
    public function __construct($id = 0, $nome = "") {
        $this->id = $id;
        $this->nome = $nome;
    }
}
?>
