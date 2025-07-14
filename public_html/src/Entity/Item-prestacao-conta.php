<?php
namespace Sisoptica\Entity;



class ItemPrestacaoConta {
    
    var $id;
    var $valor;
    var $tipo;
    var $data;
    var $prestacao;
    
    public function __construct($id = 0, $valor = 0, $tipo = 0, $data = '', $prestacao = 0) {
        $this->id       = $id;
        $this->valor    = $valor;
        $this->tipo     = $tipo;
        $this->data     = $data;
        $this->prestacao= $prestacao;
    }
    
}
?>
