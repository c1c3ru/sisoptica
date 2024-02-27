<?php

$config = Config::getInstance();
$controller = $config->currentController;
$rota = $config->filter('rota');
$localidades = $controller->getLocalidadesByRota($rota);

$res = array_map( Mapper\object_mapper('id', 'nome'), $localidades);

$config->throwAjaxSuccess($res);

?>