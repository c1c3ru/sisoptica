<?php
class Venda {
    
    var $id;
    var $dataVenda;
    var $previsaoEntrega;
    var $dataEntrega;
    var $cliente;
    var $loja;
    var $vendedor;
    var $agenteVendas;
    var $os;
    var $valor;
    var $produtos;
    var $status;
    var $vendaAntiga;
    var $equipe;
    var $liderEquipe;
    
    public function __construct($id = 0, $dataVenda = "", $previsaoEntrega = "", $dataEntrega = "",
                                $cliente = null, $loja = null, $vendedor = null,
                                $agenteVendas = null, $os = null, $status = 1, $vendaAntiga = null,
                                $equipe = null, $liderEquipe = null) {
        
        $this->id               = $id;
        $this->dataVenda        = $dataVenda;
        $this->previsaoEntrega  = $previsaoEntrega;
        $this->dataEntrega      = $dataEntrega;
        $this->cliente          = $cliente;
        $this->loja             = $loja;
        $this->vendedor         = $vendedor;
        $this->agenteVendas     = $agenteVendas;
        $this->os               = $os;
        $this->status           = $status;
        $this->vendaAntiga      = $vendaAntiga;
        $this->equipe           = $equipe;
        $this->liderEquipe      = $liderEquipe;
    }
    
}
?>
