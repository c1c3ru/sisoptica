<?php
class Cliente {
    
    var $id;
    var $nome;
    var $nascimento;
    var $apelido;
    var $rg;
    var $orgaoEmissor;
    var $cpf;
    var $conjugue;
    var $nomePai;
    var $nomeMae;
    var $endereco;
    var $numero;
    var $bairro;
    var $referencia;
    var $casaPropria;
    var $tempoCasaPropria;
    var $observacao;
    var $rendaMensal;
    var $localidade;
    var $bloqueado;
    var $telefones;
           
    public function __construct( $id = 0, $nome = "", $nascimento = "",
                                 $apelido = "", $rg = "", $orgaoEmissor = "", $cpf = "",
                                 $conjugue = "", $nomePai = "", $nomeMae = "",
                                 $endereco = "", $numero = "", $bairro = "",
                                 $referencia = "", $casaPropria = false, $tempoCasaPropria = 0,
                                 $observacao = "", $rendaMensal = 0, $localidade = null, $bloqueado = false) {
        
    
        $this->id                   = $id;
        $this->nome                 = $nome;
        $this->nascimento           = $nascimento;
        $this->apelido              = $apelido;
        $this->rg                   = $rg;
        $this->orgaoEmissor         = $orgaoEmissor;
        $this->cpf                  = $cpf;
        $this->conjugue             = $conjugue;
        $this->nomePai              = $nomePai;
        $this->nomeMae              = $nomeMae;
        $this->endereco             = $endereco;
        $this->numero               = $numero;
        $this->bairro               = $bairro;
        $this->referencia           = $referencia;
        $this->casaPropria          = $casaPropria;
        $this->tempoCasaPropria     = $tempoCasaPropria;
        $this->observacao           = $observacao;
        $this->rendaMensal          = $rendaMensal;
        $this->localidade           = $localidade;
        $this->bloqueado            = $bloqueado;
    }
    
}
?>
