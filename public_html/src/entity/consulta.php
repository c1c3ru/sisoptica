<?php
class Consulta{
    
    var $id;
    var $venda;
    var $nomePaciente;
    var $esfericoOD;
    var $esfericoOE;
    var $cilindricoOD;
    var $cilindricoOE;
    var $eixoOD;
    var $eixoOE;
    var $dnpOD;
    var $dnpOE;
    var $dp;
    var $adicao;
    var $altura;
    var $co;
    var $cor;
    var $lente;
    var $observacao;
    var $oculista;
    
    public function __construct($id = 0, $venda = 0, $nomePaciente = "",
                                $esfericoOD = 0, $esfericoOE = 0, $cilindricoOD = 0,
                                $cilindricoOE = 0, $eixoOD = 0, $eixoOE = 0,
                                $dnpOD = 0, $dnpOE = 0,    
                                $dp = 0, $adicao = 0, $altura = 0,
                                $co = 0, $cor = "", $lente = "",
                                $observacao = "", $oculista = 0) {
        
        $this->id           = $id;
        $this->venda        = $venda;
        $this->nomePaciente = $nomePaciente;
        $this->esfericoOD   = $esfericoOD;
        $this->esfericoOE   = $esfericoOE;
        $this->cilindricoOD = $cilindricoOD;
        $this->cilindricoOE = $cilindricoOE;
        $this->eixoOD       = $eixoOD;
        $this->eixoOE       = $eixoOE;
        $this->dnpOD        = $dnpOD;
        $this->dnpOE        = $dnpOE;
        $this->dp           = $dp;
        $this->adicao       = $adicao;
        $this->altura       = $altura;
        $this->co           = $co;
        $this->cor          = $cor;
        $this->lente        = $lente;
        $this->observacao   = $observacao;
        $this->oculista     = $oculista;
    }
    
}
?>
