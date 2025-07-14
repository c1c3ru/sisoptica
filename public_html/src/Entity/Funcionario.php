<?php
namespace Sisoptica\Entity;



class Funcionario {
    
    var $id;
    var $nome;
    var $login;
    var $senha;
    var $nascimento;
    var $admissao;
    var $demissao;
    var $cidade;
    var $rua;
    var $numero;
    var $bairro;
    var $cep;
    var $cpf;
    var $rg;
    var $cpt;
    var $referencia;
    var $banco;
    var $agencia;
    var $conta;
    var $cargo;
    var $loja;
    var $perfil;
    var $email;
    var $status;
    var $telefones;
    var $inativoParaCaixa;
	
    public function __construct($id = 0, $nome = "", $login = "", $senha = "", $nascimento = "",
                                $admissao = "", $demissao = "",
                                $cidade = null, $rua = "", $numero = "", $bairro = "", 
                                $cep = "", $cpf = "", $rg = "",
                                $cpt = "", $referencia = "", $banco = "", $agencia = "",
                                $conta = "", $cargo = null, $loja = null, $perfil = null,
                                $email = "", $status = true, $inativoParaCaixa = false) {
        
    
        $this->id           = $id;
        $this->nome         = $nome;
        $this->login        = $login;
        $this->senha        = $senha;
        $this->nascimento   = $nascimento;
        $this->admissao     = $admissao;
        $this->demissao     = $demissao;
        $this->cidade       = $cidade;
        $this->rua          = $rua;
        $this->numero       = $numero;
        $this->bairro       = $bairro;
        $this->cep          = $cep;
        $this->cpf          = $cpf;
        $this->rg           = $rg;
        $this->cpt          = $cpt;
        $this->referencia   = $referencia;
        $this->banco        = $banco;
        $this->agencia      = $agencia;
        $this->conta        = $conta;
        $this->cargo        = $cargo;
        $this->loja         = $loja;
        $this->perfil       = $perfil;
        $this->email        = $email;
        $this->status       = $status;
		$this->inativoParaCaixa	= $inativoParaCaixa;
    }
    
}
?>
