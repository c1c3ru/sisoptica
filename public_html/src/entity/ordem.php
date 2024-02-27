<?php
class OrdemServico {
    
    var $id;
    var $numero;
    var $dataEnvioLab;
    var $dataRecebimentoLab;
    var $armacaoLoja;
    var $valor;
    var $laboratorio;
    var $loja;
    var $cancelada;
    var $autor;
    var $venda;
    
    public function __construct($id = 0, $numero = "", $dataEnvioLab = "", 
                                $dataRecebimenoLab = "", $armacaoLoja = true,
                                $valor = 0, $laboratorio = 0, $loja = 0, 
                                $cancelada = false, $autor = 0, $venda = null) {
        $this->id                   = $id;
        $this->numero               = $numero;
        $this->dataEnvioLab         = $dataEnvioLab;
        $this->dataRecebimentoLab   = $dataRecebimenoLab;
        $this->armacaoLoja          = $armacaoLoja;
        $this->valor                = $valor;
        $this->laboratorio          = $laboratorio;
        $this->loja                 = $loja;
        $this->cancelada            = $cancelada;
        $this->autor                = $autor;
        $this->venda                = $venda;
        
    }
}
?>
