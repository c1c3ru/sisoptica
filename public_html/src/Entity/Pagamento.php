<?php
namespace Sisoptica\Entity;




class Pagamento {
    
    var $id;
    var $valor;
    var $data;
    var $dataBaixa;
    var $numeroParcela;
    var $cobrador;
    var $vendaParcela;
    var $autor;
    var $dataEdicao;
    var $comDesconto;
    var $prestacaoConta;
    
    public function __construct($id = 0, $valor = 0, $data = "", $dataBaixa = "", $numeroParcela = 0, $cobrador = 0,
                                $vendaParcela = 0, $autor = 0, $dataEdicao = "", $comDesconto = false, $prestacaoConta = null) 
    {
        
        $this->id               = $id;
        $this->valor            = $valor;
        $this->data             = $data;
        $this->dataBaixa        = $dataBaixa;
        $this->numeroParcela    = $numeroParcela;
        $this->cobrador         = $cobrador;
        $this->vendaParcela     = $vendaParcela;
        $this->autor            = $autor;
        $this->dataEdicao       = $dataEdicao;
        $this->comDesconto      = $comDesconto;
        $this->prestacaoConta   = $prestacaoConta;
    }
}
?>
