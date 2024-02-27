<?php
class CentralEstoque {
    
    var $id;
    var $data;
    var $valor;
    var $usuarioEstoque;
    var $usuarioConfirmacaoEntrada;
    var $usuarioConfirmacaoSaida;
    var $lojaOrigem;
    var $lojaDestino;
    var $status;
    var $observacao;
    
    public function __construct($id = 0, $data = "", $valor = 0,
                                $usuarioEstoque = null, $usuarioConfirmacaoEntrada = null, 
                                $usuarioConfirmacaoSaida = null, $lojaOrigem = null,
                                $lojaDestino = null, $status = 0, $observacao = "") 
    {
        $this->id                   = $id;
        $this->data                 = $data;
        $this->valor                = $valor;
        $this->usuarioEstoque       = $usuarioEstoque;
        $this->usuarioConfirmacaoEntrada= $usuarioConfirmacaoEntrada;
        $this->usuarioConfirmacaoSaida  = $usuarioConfirmacaoSaida;
        $this->lojaOrigem           = $lojaOrigem;
        $this->lojaDestino          = $lojaDestino;
        $this->status               = $status;
        $this->observacao           = $observacao;
    }
    
}
?>
