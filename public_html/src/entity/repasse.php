<?php
class Repasse {
    
    var $id;
    var $dtChegada;
    var $dtEnvioConserto;
    var $dtRecebimentoConserto;
    var $dtEnvioCliente;
    var $observacao;
    var $cobrador;
    var $venda;
    
    public function __construct($id = 0, $dtChegada = '', $dtEnvioConserto = '',
                                $dtRecebimentoConserto = '', $dtEnvioCliente = '',
                                $observacao = '', $cobrador = 0, $venda = 0) 
    {
        $this->id                       = $id;
        $this->dtChegada                = $dtChegada;
        $this->dtEnvioConserto          = $dtEnvioConserto;
        $this->dtRecebimentoConserto    = $dtRecebimentoConserto;
        $this->dtEnvioCliente           = $dtEnvioCliente;
        $this->observacao               = $observacao;
        $this->cobrador                 = $cobrador;
        $this->venda                    = $venda;
    }
    
}
?>
