<?php
class Parcela {
    
    var $numero;
    var $validade;
    var $remarcacao;
    var $valor;
    var $status;
    var $venda;
    var $porBoleto;
    var $cancelada;
    
    public function __construct($numero = 0, $validade = "", $remarcacao = "", $valor = 0,
                                 $status = false, $venda = 0, $porBoleto = true, $cancelada = false) {
        
        $this->numero   = $numero;
        $this->validade = $validade;
        $this->remarcacao = $remarcacao;
        $this->valor    = $valor;
        $this->status   = $status;
        $this->venda    = $venda; 
        $this->porBoleto= $porBoleto;
        $this->cancelada= $cancelada;
    }
    
}
?>
