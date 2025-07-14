<?php
namespace Sisoptica\Entity;



class Caixa {
    var $id;
    var $data;
    var $saldo;
    var $status;
    var $loja;
    public function __construct($id = 0, $data = '', $saldo = 0, $status = 0, $loja = 0) {
        $this->id       = $id;
        $this->data     = $data;
        $this->saldo    = $saldo;
        $this->status   = $status;
        $this->loja     = $loja; 
    }
}
?>
