<?php
class EquipeFuncionario {
    
    var $equipe;
    var $funcionario;
    var $dataEntrada;
    
    public function __construct($equipe = null, $funcionario = null, $dataEntrada = '') {
        $this->equipe       = $equipe;
        $this->funcionario  = $funcionario;
        $this->dataEntrada  = $dataEntrada;
    }
    
}
?>
