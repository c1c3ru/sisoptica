<?php
$config = Config::getInstance();
$nome = $config->filter("nome");
$clientes = $config->currentController->searchClientes($nome);
$arr = array();
foreach($clientes as $cliente){
    $arr[] = array( "nome"          => $cliente->nome, "id" => $cliente->id,
                    "cpf"           => $config->maskCPF($cliente->cpf),  
                    "localidade"    => $cliente->localidade, 
                    "endereco"      => $cliente->endereco.", nº: ".$cliente->numero,
                    "bloqueado"     => $cliente->bloqueado);
}
$config->throwAjaxSuccess($arr);
?>
