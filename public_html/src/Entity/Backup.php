<?php
namespace Sisoptica\Entity;



class Backup {
    
    var $id; 
    var $tipo;
    var $objeto;
    var $autor;
    var $edicao;
    
    public function __construct($id = 0, $tipo = 0, $objeto = "", 
                                $autor =0, $edicao = true) {
        $this->id = $id;
        $this->tipo = $tipo;
        $this->objeto = $objeto;
        $this->autor = $autor;
        $this->edicao = $edicao;
    }
    
}
?>
