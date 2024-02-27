<?php
class PrestacaoConta {
    
    var $id;
    var $cobrador;
    var $status;
    var $dtInicial;
    var $dtFinal;
    var $seq;
    var $itens;
    var $cancelada;
    var $auditada;
    
    public function __construct($id = 0, $cobrador = 0, $status = 0, $dtInicial = "", 
                                $dtFinal = "", $seq = 0, $cancelada = false, $auditada = false) {
        $this->id       =  $id;
        $this->cobrador = $cobrador;
        $this->status   = $status;
        $this->dtInicial= $dtInicial;
        $this->dtFinal  = $dtFinal;
        $this->seq      = $seq;
        $this->cancelada= $cancelada;
        $this->auditada = $auditada;
    }
    
}
?>
