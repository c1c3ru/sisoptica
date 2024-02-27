<?php
$config = Config::getInstance();

$func_controller = $config->currentController;

$tipo = $config->filter("tipo");

$res = false;

if(!$tipo){
    
    include_once CONTROLLERS."loja.php";

    $loja_controller = new LojaController();

    $loja_user = $loja_controller->getLoja($_SESSION[SESSION_LOJA_FUNC]);

    if(empty($loja_user->gerente))
        $config->throwAjaxError("Sua loja ainda não possui gerente");

    $gerente = $func_controller->getFuncionario($loja_user->gerente);

    $senha = strtoupper($config->filter("senha"));

    $res = !strcasecmp($gerente->senha, md5($senha));
    
    $id_register = $gerente->id;
    
} else {
    
    $diretor = $func_controller->getFuncionario($tipo);
    
    if(empty($diretor->id) || $diretor->cargo != CargoModel::COD_DIRETOR){
        $config->throwAjaxError("Diretor inválido");
    }
    
    $senha = strtoupper($config->filter("senha"));

    $res = !strcasecmp($diretor->senha, md5($senha));
 
    $id_register = $diretor->id;
    
}

if(!$res){
    $config->throwAjaxError("Senha inválida");
}

$config->gerentConfirm($id_register);

$config->throwAjaxSuccess("Senha aceita");

?>
