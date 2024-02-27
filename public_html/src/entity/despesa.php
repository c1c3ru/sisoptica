<?php
class Despesa {
    
    var $id;
    var $valor;
    var $natureza;
    var $entidade;
    var $observacao;
    var $caixa;
    
    public function __construct($id = 0, $valor = 0, $natureza = 0, 
                                $entidade = 0, $observacao = '', $caixa = 0) {
        $this->id           = $id;
        $this->valor        = $valor;
        $this->natureza     = $natureza;
        $this->entidade     = $entidade;
        $this->observacao   = $observacao;
        $this->caixa        = $caixa;
    }
    
}
?>
