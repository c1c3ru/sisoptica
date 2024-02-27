<?php
$config = Config::getInstance();

$loja = $config->filter("loja");

if(empty($loja)) $config->throwAjaxError("Loja inválida");

$vendedores = $config->currentController->getAllVendedores($loja);

$arr = array();

foreach ($vendedores as $vendedor){
    $arr[] = array("nome" => $vendedor->nome, "id" => $vendedor->id);
}

$config->throwAjaxSuccess($arr);

?>
