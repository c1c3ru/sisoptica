<?php
$config = Config::getInstance();

$regioes = $config->currentController->getRegioesByCobrador($config->filter("cobr"));

$arr = array();

foreach ($regioes as $regiao){
    $arr[] = array("id" => $regiao->id, "nome" => $regiao->nome);
}

$config->throwAjaxSuccess($arr);

?>
