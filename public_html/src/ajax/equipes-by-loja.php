<?php

$config = Config::getInstance();

$lojaid = $config->filter("loja");

if (empty($lojaid)) {
    $config->throwAjaxError("Loja Inválida");
}

$equipes = $config->currentController->getEquipesByLoja($lojaid, true);

$arr = array();

foreach ($equipes as $regiao){
    $arr[] = get_object_vars($regiao);
}

$config->throwAjaxSuccess($arr);

?>