<?php
namespace Sisoptica\Entity;




class Loja {
    
    var $id;
    var $sigla;
    var $rua;
    var $numero;
    var $bairro;
    var $cep;
    var $cnpj;
    var $cidade;
    var $cgc;
    var $gerente;
	var $inativoParaCaixa;
    var $telefones;
    
    public function __construct($id = 0, $sigla = "", $rua = "", $numero = "", $bairro = "", $cep = "", 
                                $cnpj = "", $cidade = null, $cgc = "", $gerente = null, 
								$inativoParaCaixa = false) {
        
        $this->id = $id;
        $this->sigla = $sigla;
        $this->rua = $rua;
        $this->numero = $numero;
        $this->bairro = $bairro;
        $this->cep = $cep;
        $this->cnpj = $cnpj;
        $this->cidade = $cidade;
        $this->cgc = $cgc;
        $this->gerente = $gerente;
		$this->inativoParaCaixa = $inativoParaCaixa;
    }
    
}
?>
