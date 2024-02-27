<?php
$config = Config::getInstance();
$tipo   = $config->filter('tipo');
if(empty($tipo)) $config->throwAjaxError('Tipo de Pagamento não informado');

$tipo   = $config->currentController->getTipoPagamento($tipo);
if(empty($tipo->id)) $config->throwAjaxError('Tipo de Pagamento inválido');

$config->throwAjaxSuccess(get_object_vars($tipo));
?>
