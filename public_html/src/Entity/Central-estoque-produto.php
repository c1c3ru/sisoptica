<?php
namespace Sisoptica\Entity;



class CentralEstoqueProduto {
    
    var $produto;
    var $central;
    var $quantidadeEntrada;
    var $quantidadeSaida;
    var $valor;
    
    public function __construct($produto = null, $central = null, 
                                $quantidadeEntrada = 0, $quantidadeSaida = 0,
                                $valor = 0) {
        $this->produto      = $produto;
        $this->central      = $central;
        $this->quantidadeEntrada    = $quantidadeEntrada;
        $this->quantidadeSaida      = $quantidadeSaida;
        $this->valor        = $valor;
    }
    
}
?>
