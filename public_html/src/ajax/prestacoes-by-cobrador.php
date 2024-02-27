<?php
$config     = Config::getInstance(); 

$cobrador   = $config->filter('cobrador');

if(empty($cobrador)) $config->throwAjaxError('Cobrador não informado');

$prestacoes = $config->currentController->getPrestacoesByCobrador($cobrador, PrestacaoContaModel::STATUS_ABERTA);

$config->throwAjaxSuccess($prestacoes);

?>
