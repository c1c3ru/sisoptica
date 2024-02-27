<?php
$config = Config::getInstance();

$controller = $config->currentController;

$localidades = $controller->getLocalidadesByCidade($config->filter("cidade"));

$res = array();

foreach ($localidades as $localidade) {
    $res[] = array("id" => $localidade->id, "nome" => $localidade->nome);
}

$config->throwAjaxSuccess($res);

?>
