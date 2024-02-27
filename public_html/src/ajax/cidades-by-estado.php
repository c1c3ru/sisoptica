<?php

$config = Config::getInstance();

$estado = $config->filter("e");

$controller = $config->currentController;

$cidades = $controller->getCidadesByEstado($estado);

$arr_res = array();

foreach($cidades as $cidade){
    $arr_res[] = array("id" => $cidade->id, "nome" => $cidade->nome);
}

$config->throwAjaxSuccess($arr_res);

?>
