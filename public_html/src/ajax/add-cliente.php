<?php
$config = Config::getInstance();

$cliente = $config->currentController->addCliente(null, true);

if(!empty($cliente->id)){
    
    include_once CONTROLLERS."localidade.php";
    
    $localidade_controller = new LocalidadeController();
    $localidade = $localidade_controller->getLocalidade($cliente->localidade, true);
    
    $res = array( "nome"       => $cliente->nome, "id" => $cliente->id,
                  "localidade" => $localidade->nome." - ".$localidade->cidade, 
                  "endereco"   => $cliente->endereco.", nº: ".$cliente->numero );
    
    $config->throwAjaxSuccess($res);
}

$config->throwAjaxError("Falha ao cadastrar cliente!");

?>
