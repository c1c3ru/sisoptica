<?php
$config = Config::getInstance();

$regioes = $config->currentController->getRegioesByLoja($config->filter("loja"));

$arr = array();

foreach ($regioes as $regiao){
    $arr[] = array("id" => $regiao->id, "nome" => $regiao->nome);
}

$config->throwAjaxSuccess($arr);

?>
