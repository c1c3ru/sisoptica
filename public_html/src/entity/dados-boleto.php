<?php
class DadosBoleto {
    
    var $loja;
    var $agencia;
    var $conta;
    var $digitoConta;
    var $contaCedente;
    var $digitoContaCedente;
    var $carteira;
    var $padrao;
    
    public function __construct($loja = 0, $agencia = "", $conta = "", 
                                $digitoConta = "", $contaCedente = "", 
                                $digitoContaCedente = "", $carteira = "", 
                                $padrao = false) {
        $this->loja                 = $loja;
        $this->agencia              = $agencia;
        $this->conta                = $conta;
        $this->digitoConta          = $digitoConta;
        $this->contaCedente         = $contaCedente;
        $this->digitoContaCedente   = $digitoContaCedente;
        $this->carteira             = $carteira;
        $this->padrao               = $padrao;
    }
    
}
?>
