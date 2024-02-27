<?php
$config = Config::getInstance();

$controller = $config->currentController;

$tipo = $controller->getTipoProduto($config->filter("tipo"));

if(!empty($tipo->id)){
    $config->throwAjaxSuccess(get_object_vars($tipo));
}

$config->throwAjaxError("Falha na solicitação. Tipo de Produto Inexistente.");
?>
