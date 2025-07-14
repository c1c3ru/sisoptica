<?php
namespace Sisoptica\Entity;



class Combustivel {
    
    var $id;
    var $litros;
    var $preco;
    var $kmInicial;
    var $kmFinal;
    var $despesa;
    
    public function __construct($id = 0, $litros = 0, $preco = 0, 
                                $kmInicial = 0, $kmFinal = 0, $despesa = 0) {
        $this->id           = $id;
        $this->litros       = $litros;
        $this->preco        = $preco;
        $this->kmInicial    = $kmInicial;
        $this->kmFinal      = $kmFinal;
        $this->despesa      = $despesa;
    }
    
}
?>
