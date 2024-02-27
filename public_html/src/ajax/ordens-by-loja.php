<?php
$config = Config::getInstance();

$loja = $config->filter("loja");

if(empty($loja)) $config->throwAjaxError("Loja inválida");

$ordens = $config->currentController->getOrdensDeServicoByLoja($loja);

$arr = array();

foreach($ordens as $ordem){
    $arr[] = array("numero" => $ordem->numero, "id" => $ordem->id);
}

$config->throwAjaxSuccess($arr);

?>
