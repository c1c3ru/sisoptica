<?php
$config = Config::getInstance();

$ordem = $config->currentController->addOS(null, true);

if(!empty($ordem->id)){
    
    $ordem_res["id"]    = $ordem->id;
    $ordem_res["numero"]= $ordem->numero;  
    
    $config->throwAjaxSuccess($ordem_res);
    
}

$config->throwAjaxError("Falha ao cadastrar ordem de serviço!");

?>
