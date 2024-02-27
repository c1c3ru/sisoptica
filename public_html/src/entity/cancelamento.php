<?php
class Cancelamento  {
    
    var $venda;
    var $autor;
    var $autorizador;
    var $data;
    
    public function __construct($venda = 0, $autor = 0, $autorizador = 0, $data = '') {
        $this->venda        = $venda;
        $this->autor        = $autor;
        $this->autorizador  = $autorizador; 
        $this->data         = $data;
    }
    
}
?>
