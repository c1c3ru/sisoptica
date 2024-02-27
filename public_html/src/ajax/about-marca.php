<?php
$config = Config::getInstance();

$controller = $config->currentController;

$marca = $controller->getMarca($config->filter("marc"));

if(!empty($marca->id)){
    $config->throwAjaxSuccess(get_object_vars($marca));
}

$config->throwAjaxError("Falha na solicitação. Marca Inexistente.");
?>
