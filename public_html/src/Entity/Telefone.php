<?php
namespace Sisoptica\Entity;



class Telefone {
    
    var $numero;
    var $dono;
    
    public function __construct($numero = "", $dono = null) {
        $this->numero = $numero;
        $this->dono = $dono;
    }
       
}
?>
