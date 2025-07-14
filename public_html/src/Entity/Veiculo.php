<?php
namespace Sisoptica\Entity;



class Veiculo {
    
    var $id;
    var $nome;
    var $placa;
    var $motorista;
    var $loja;
	var $inativoParaCaixa;
    
    public function __construct($id = 0, $nome = "", $placa = "", 
                                $motorista = null, $loja = null, 
								$inativoParaCaixa = false) {
        $this->id           = $id;
        $this->nome         = $nome;
        $this->placa        = $placa;
        $this->motorista    = $motorista;
        $this->loja         = $loja;
		$this->inativoParaCaixa = $inativoParaCaixa;
    }
    
}
?>
